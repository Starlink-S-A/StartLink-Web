<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/configuracionInicial.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
    header("Location: " . BASE_URL . "bienvenida.php");
    exit();
}

$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"] ?? 'Usuario';
$userRoleGlobal = $_SESSION["id_rol"] ?? 2;

$currentPage = basename($_SERVER['PHP_SELF']);

// Obtener la foto de perfil
$defaultImage = BASE_URL . 'images/default-profile.jpg';
$profileImage = $defaultImage;
if (!empty($_SESSION['foto_perfil'])) {
    $rutaAbsoluta = ROOT_PATH . 'uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
    $rutaPublica = BASE_URL . 'uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
    if (file_exists($rutaAbsoluta)) { // CORREGIDO: $rutaAbsuta a $rutaAbsoluta
        $profileImage = $rutaPublica;
    }
}

// Variable para determinar si el usuario tiene rol de administrador/contratador de empresa
$esAdminEmpresa = false; 
// Variable para determinar si el usuario tiene un rol de trabajador activo (global o de empresa)
$esTrabajadorActivo = false; 
// Nueva variable: Para determinar si el enlace "Publicar Mi Perfil" debe mostrarse (basado en id_rol global)
// La lógica para mostrar este enlace ahora está en perfiles_candidatos.php, no en el navbar.
$showPublishProfileLink = false; 

$conexion = getDbConnection();

// Inicializar $userCompanyRole para evitar "Undefined variable"
$userCompanyRole = null;

// Lógica para determinar el rol de la empresa y si es un trabajador activo
try {
    // Obtener el ID del rol 'TRABAJADOR'
    $stmtRolTrabajador = $conexion->prepare("SELECT id FROM ROL WHERE nombre_rol = 'TRABAJADOR'");
    $stmtRolTrabajador->execute();
    $rolTrabajadorId = $stmtRolTrabajador->fetchColumn();

    // Obtener el rol global del usuario actual
    $stmtUserGlobalRole = $conexion->prepare("SELECT id_rol FROM USUARIO WHERE id = ?");
    $stmtUserGlobalRole->execute([$userId]);
    $currentUserGlobalRole = $stmtUserGlobalRole->fetchColumn();

    // El enlace "Publicar Mi Perfil" se muestra si el rol global del usuario NO es 'TRABAJADOR'
    // Esta variable ya no se usa para mostrar el enlace en el navbar, pero se mantiene por si es necesaria en otro lugar.
    if ($currentUserGlobalRole != $rolTrabajadorId) {
        $showPublishProfileLink = true;
    }


    // Si ya hay una empresa seleccionada en la sesión, usamos ese rol.
    // Esto es importante para mantener el contexto de "Mi Empresa".
    if (isset($_SESSION['id_empresa']) && isset($_SESSION['id_rol_empresa'])) {
        $userCompanyRole = $_SESSION['id_rol_empresa'];
        // Si el rol de la empresa actual es 1 o 2, es administrador/contratador
        if (in_array($userCompanyRole, [1, 2])) {
            $esAdminEmpresa = true;
        }
        // Si el rol de la empresa actual es 2 o 3, es un trabajador activo de esa empresa
        if (in_array($userCompanyRole, [2, 3])) {
            $esTrabajadorActivo = true;
        }
    } else {
        // Si no hay una empresa seleccionada, buscamos la primera asociación de empresa
        // para establecer $_SESSION['id_empresa'] y $_SESSION['id_rol_empresa']
        // y así el navbar refleje correctamente los permisos y enlaces.
        $stmtCompanyAssociation = $conexion->prepare("
            SELECT id_empresa, id_rol_empresa 
            FROM USUARIO_EMPRESA 
            WHERE id_usuario = ? 
            ORDER BY id_rol_empresa ASC 
            LIMIT 1
        ");
        $stmtCompanyAssociation->execute([$userId]);
        $userCompanyAssociation = $stmtCompanyAssociation->fetch(PDO::FETCH_ASSOC);

        if ($userCompanyAssociation) {
            // Establecer las variables de sesión para el contexto de la empresa
            $_SESSION['id_empresa'] = $userCompanyAssociation['id_empresa'];
            $_SESSION['id_rol_empresa'] = $userCompanyAssociation['id_rol_empresa'];
            $userCompanyRole = $userCompanyAssociation['id_rol_empresa'];

            // Actualizar variables de permiso basadas en la asociación encontrada
            if (in_array($userCompanyRole, [1, 2])) {
                $esAdminEmpresa = true;
            }
            if (in_array($userCompanyRole, [2, 3])) {
                $esTrabajadorActivo = true;
            }
        } else {
            // Si el usuario no tiene ninguna asociación de empresa, asegurar que las variables estén vacías
            unset($_SESSION['id_empresa'], $_SESSION['id_rol_empresa']);
            // $userCompanyRole ya es null por la inicialización
        }
    }

    // Finalmente, la variable $esTrabajadorActivo también se activa si el rol global es 3 (TRABAJADOR)
    // Esto es un respaldo si no tiene un rol de empresa específico pero es un trabajador general
    // Si el rol global 3 es el que indica que ya está contratado, entonces $showPublishProfileLink ya lo maneja.
    // Si $esTrabajadorActivo tiene otro propósito, se mantiene.
    if ($userRoleGlobal == $rolTrabajadorId) { // Comparar con el ID del rol 'TRABAJADOR'
        $esTrabajadorActivo = true;
    }

    // --- Lógica para obtener el número de notificaciones no leídas y las últimas 5 ---
    $unreadNotificationsCount = 0;
    $latestNotifications = []; 

    // Manejo de acciones AJAX para notificaciones
    if (isset($_GET['action']) && $_GET['action'] === 'get_notifications') {
        header('Content-Type: application/json');

        $stmtNotifications = $conexion->prepare("
            SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion, postulacion_id, solicitud_contratacion_id
            FROM NOTIFICACIONES
            WHERE user_id = ?
            ORDER BY fecha_creacion DESC
            LIMIT 5
        ");
        $stmtNotifications->execute([$userId]);
        $latestNotifications = $stmtNotifications->fetchAll(PDO::FETCH_ASSOC);

        // DEBUG PHP: Comprobar cuántas notificaciones se obtuvieron para la llamada AJAX
        error_log("DEBUG PHP: Notificaciones obtenidas para AJAX: " . count($latestNotifications));

        $stmtUnreadCount = $conexion->prepare("
            SELECT COUNT(*) 
            FROM NOTIFICACIONES 
            WHERE user_id = ? AND leida = 0
        ");
        $stmtUnreadCount->execute([$userId]);
        $unreadNotificationsCount = $stmtUnreadCount->fetchColumn();

        echo json_encode([
            'count' => $unreadNotificationsCount,
            'notifications' => $latestNotifications
        ]);
        exit();
    }

    // Manejo de acciones AJAX para marcar notificación como leída
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_notification_read') {
        header('Content-Type: application/json');
        $notificationId = $_POST['notification_id'] ?? null;

        if ($notificationId && is_numeric($notificationId)) {
            $stmtUpdate = $conexion->prepare("
                UPDATE NOTIFICACIONES
                SET leida = 1
                WHERE id = ? AND user_id = ?
            ");
            $stmtUpdate->execute([$notificationId, $userId]);

            if ($stmtUpdate->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo marcar la notificación como leída o no pertenece a este usuario.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'ID de notificación inválido.']);
        }
        exit();
    }

    // Si no es una solicitud AJAX, cargar las notificaciones iniciales para el renderizado de la página
    $stmtNotifications = $conexion->prepare("
        SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion, postulacion_id, solicitud_contratacion_id
        FROM NOTIFICACIONES
        WHERE user_id = ?
        ORDER BY fecha_creacion DESC
        LIMIT 5
    ");
    $stmtNotifications->execute([$userId]);
    $latestNotifications = $stmtNotifications->fetchAll(PDO::FETCH_ASSOC);

    // Procesar las notificaciones para añadir iconos por defecto si no están definidos
    foreach ($latestNotifications as &$notification) {
        if (empty($notification['icono'])) {
            switch ($notification['tipo']) {
                case 'success': $notification['icono'] = 'fas fa-check-circle text-success'; break;
                case 'warning': $notification['icono'] = 'fas fa-exclamation-triangle text-warning'; break;
                case 'error': $notification['icono'] = 'fas fa-times-circle text-danger'; break;
                case 'contratacion': $notification['icono'] = 'fas fa-handshake text-info'; break; // Nuevo tipo
                default: $notification['icono'] = 'fas fa-info-circle text-info'; break;
            }
        } elseif (strpos($notification['icono'], 'text-') === false) {
            // Si tiene icono pero no color, añadir color primario por defecto
            $notification['icono'] .= ' text-primary';
        }
    }
    unset($notification); // Romper la referencia del último elemento

    // DEBUG PHP: Comprobar cuántas notificaciones se obtuvieron para el renderizado inicial
    error_log("DEBUG PHP: Notificaciones obtenidas para renderizado inicial: " . count($latestNotifications));


    $stmtUnreadCount = $conexion->prepare("
        SELECT COUNT(*) 
        FROM NOTIFICACIONES 
        WHERE user_id = ? AND leida = 0
    ");
    $stmtUnreadCount->execute([$userId]);
    $unreadNotificationsCount = $stmtUnreadCount->fetchColumn();


} catch (PDOException $e) {
    error_log("Error al consultar asociación de empresa o notificaciones para navbar: " . $e->getMessage());
    // En caso de error, asegurar que los permisos estén restringidos por seguridad
    $esAdminEmpresa = false;
    $esTrabajadorActivo = false;
    $showPublishProfileLink = false; // También resetear esta variable
    $unreadNotificationsCount = 0; // Resetear el contador de notificaciones
    $latestNotifications = []; // Asegurar que esté vacío
    unset($_SESSION['id_empresa'], $_SESSION['id_rol_empresa']); // Limpiar por seguridad
    $userCompanyRole = null; // Asegurar que sea null en caso de error
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>styles/navbar_styles.css">
    <!-- Font Awesome para el icono de campana -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .notification-item.unread {
            background-color: #e9f5ff; /* Azul claro para no leídas */
            font-weight: bold;
        }
        .notification-item small {
            font-size: 0.75em;
            color: #6c757d;
        }
        /* Estilos para los botones de acción en notificaciones */
        .notification-actions {
            display: flex;
            gap: 5px;
            margin-top: 5px;
            justify-content: flex-end; /* Alinea los botones a la derecha */
        }
        .notification-actions .btn {
            padding: 3px 8px;
            font-size: 0.8em;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">TalentLink</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard.php">Inicio</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'configurar_perfil.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil.php">Mi Perfil</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'ofertas_empleo.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>ofertas_empleo.php">Ofertas de Empleo</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'perfiles_candidatos.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>perfiles_candidatos.php">Perfiles Candidatos</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'capacitaciones.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>capacitaciones.php">Capacitaciones</a>
                </li>

                <?php if ($userRoleGlobal != 1): // Si no es administrador global ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'crear_empresa.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>crear_empresa.php">Crear Empresa</a>
                    </li>
                <?php endif; ?>

                <?php 
                // Si es Administrador de Empresa (id_rol_empresa = 1), Contratador (id_rol_empresa = 2),
                // Empleado Interno (id_rol_empresa = 3), o Administrador Global (id_rol = 1)
                // Se asume que $userRolEmpresa y $userRoleGlobal están definidos.
                $canAccessMiEquipo = ($esAdminEmpresa || ($userCompanyRole == 2) || ($userCompanyRole == 3) || ($userRoleGlobal == 1));
                if ($canAccessMiEquipo): 
                ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage == 'seleccionar_empresa.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>seleccionar_empresa.php">Mi Equipo</a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center">

                <!-- Dropdown de Notificaciones -->
                <li class="nav-item dropdown me-2">
                    <a class="btn btn-outline-light position-relative dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if ($unreadNotificationsCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount">
                                <?= htmlspecialchars($unreadNotificationsCount) ?>
                                <span class="visually-hidden">notificaciones no leídas</span>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" id="notificationsList">
                        <?php
                        // DEBUG PHP: Mostrar si $latestNotifications está vacío aquí
                        error_log("DEBUG PHP: En navbar.php, \$latestNotifications (para renderizado HTML) está vacío: " . (empty($latestNotifications) ? 'true' : 'false'));
                        ?>
                        <?php if (empty($latestNotifications)): ?>
                            <li><span class="dropdown-item text-muted">No hay notificaciones.</span></li>
                        <?php else: ?>
                            <?php foreach ($latestNotifications as $notification): ?>
                                <li id="notification-item-<?= htmlspecialchars($notification['id']) ?>">
                                    <a class="dropdown-item notification-item <?= $notification['leida'] ? '' : 'unread' ?>"
                                       href="<?= htmlspecialchars($notification['url_redireccion'] ?? '#') ?>"
                                       data-notification-id="<?= htmlspecialchars($notification['id']) ?>">
                                        <i class="<?= htmlspecialchars($notification['icono']) ?> me-2"></i>
                                        <?= htmlspecialchars($notification['mensaje']) ?>
                                        <small class="text-muted float-end"><?= (new DateTime($notification['fecha_creacion']))->format('H:i') ?></small>
                                    </a>
                                    <?php if ($notification['tipo'] === 'oferta' && !$notification['leida'] && !empty($notification['postulacion_id'])): ?>
                                        <div class="notification-actions px-3 pb-2">
                                            <button class="btn btn-success btn-sm accept-offer-btn" 
                                                    data-notification-id="<?= htmlspecialchars($notification['id']) ?>"
                                                    data-postulacion-id="<?= htmlspecialchars($notification['postulacion_id']) ?>">
                                                Aceptar Oferta
                                            </button>
                                            <button class="btn btn-danger btn-sm decline-offer-btn"
                                                    data-notification-id="<?= htmlspecialchars($notification['id']) ?>"
                                                    data-postulacion-id="<?= htmlspecialchars($notification['postulacion_id']) ?>">
                                                Rechazar Oferta
                                            </button>
                                        </div>
                                    <?php elseif ($notification['tipo'] === 'contratacion' && !$notification['leida'] && !empty($notification['solicitud_contratacion_id'])): ?>
                                        <div class="notification-actions px-3 pb-2">
                                            <button class="btn btn-success btn-sm accept-hiring-request-btn" 
                                                    data-notification-id="<?= htmlspecialchars($notification['id']) ?>"
                                                    data-solicitud-id="<?= htmlspecialchars($notification['solicitud_contratacion_id']) ?>">
                                                Aceptar Solicitud
                                            </button>
                                            <button class="btn btn-danger btn-sm decline-hiring-request-btn"
                                                    data-notification-id="<?= htmlspecialchars($notification['id']) ?>"
                                                    data-solicitud-id="<?= htmlspecialchars($notification['solicitud_contratacion_id']) ?>">
                                                Rechazar Solicitud
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li>
                            <!-- Botón para ver todas las notificaciones -->
                            <a class="dropdown-item text-center" href="<?= BASE_URL ?>notificaciones.php">
                                <button class="btn btn-primary btn-sm w-100">Ver todas las notificaciones</button>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <!-- Asegúrate de que este ID sea único para el dropdown del perfil de usuario -->
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Foto" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                        <span><?= htmlspecialchars($userName) ?></span>
                    </a>
                    <!-- Asegúrate de que aria-labelledby apunte al ID correcto del enlace -->
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userProfileDropdown">
                        <li>
                            <a class="dropdown-item <?= $currentPage == 'configurar_perfil.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil.php">
                                Editar Perfil
                            </a>
                        </li>
                        <?php if ($esAdminEmpresa): ?>
                            <li>
                                <a class="dropdown-item <?= $currentPage == 'mis_empresas.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>mis_empresas.php">
                                    Mis Empresas
                                </a>
                            </li>
                        <?php endif; ?>

                        <li>
                            <a class="dropdown-item <?= $currentPage == 'historial_usuario.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>historial_usuario.php">
                                Mi Historial Laboral
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= $currentPage == 'mis_chats.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>mis_chats.php">
                                Mis Chats
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/src">Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded in navbar.php');

    const notificationsDropdownElement = document.getElementById('notificationsDropdown');
    const userProfileDropdownElement = document.getElementById('userProfileDropdown');
    const notificationsList = document.getElementById('notificationsList');
    const notificationCountSpan = document.getElementById('notificationCount');
    const viewAllNotificationsButton = document.querySelector('#notificationsList .btn-primary'); // Selecciona el botón

    // DEBUG JS: Comprobar si el botón "Ver todas las notificaciones" existe
    if (viewAllNotificationsButton) {
        console.log('DEBUG JS: Botón "Ver todas las notificaciones" encontrado en el DOM.');
    } else {
        console.log('DEBUG JS: Botón "Ver todas las notificaciones" NO encontrado en el DOM.');
    }

    // Funciones de modales personalizados (reutilizadas)
    function customAlert(message) {
        // Asegúrate de que el modal de alerta esté en el HTML principal
        // Si no lo está, deberías incluirlo en la página padre (ej. dashboard.php)
        // o generarlo dinámicamente aquí. Para este contexto, asumimos que existe.
        const alertDialog = new bootstrap.Modal(document.getElementById('alertDialog') || document.createElement('div'));
        const alertDialogBody = document.getElementById('alertDialogBody') || document.createElement('div');
        alertDialogBody.textContent = message;
        alertDialog.show();
    }


    // Function to fetch and update notifications
    function fetchNotifications() {
        console.log('Fetching notifications...');
        fetch('<?= BASE_URL ?>navbar.php?action=get_notifications')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Notifications data received:', data);
                // Update notification count
                if (notificationCountSpan) {
                    if (data.count > 0) {
                        notificationCountSpan.textContent = data.count;
                        notificationCountSpan.style.display = 'block'; // Show badge
                    } else {
                        notificationCountSpan.style.display = 'none'; // Hide badge
                    }
                }

                // Update notifications dropdown list
                notificationsList.innerHTML = ''; // Clear current list

                if (data.notifications.length === 0) {
                    const noNotificationsItem = document.createElement('li');
                    noNotificationsItem.innerHTML = '<span class="dropdown-item text-muted">No hay notificaciones.</span>';
                    notificationsList.appendChild(noNotificationsItem);
                } else {
                    data.notifications.forEach(notification => {
                        const listItem = document.createElement('li');
                        listItem.id = `notification-item-${notification.id}`; // Add ID to list item
                        
                        const notificationLink = document.createElement('a');
                        notificationLink.classList.add('dropdown-item', 'notification-item');
                        if (!notification.leida) {
                            notificationLink.classList.add('unread');
                        }
                        notificationLink.href = notification.url_redireccion || '#';
                        notificationLink.dataset.notificationId = notification.id;
                        notificationLink.innerHTML = `
                            <i class="${notification.icono} me-2"></i>
                            ${notification.mensaje}
                            <small class="text-muted float-end">${new Date(notification.fecha_creacion).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</small>
                        `;
                        listItem.appendChild(notificationLink);

                        // Add action buttons for offer notifications if unread
                        if (notification.tipo === 'oferta' && !notification.leida && notification.postulacion_id) {
                            const actionsDiv = document.createElement('div');
                            actionsDiv.classList.add('notification-actions', 'px-3', 'pb-2');
                            actionsDiv.innerHTML = `
                                <button class="btn btn-success btn-sm accept-offer-btn" 
                                        data-notification-id="${notification.id}"
                                        data-postulacion-id="${notification.postulacion_id}">
                                    Aceptar Oferta
                                </button>
                                <button class="btn btn-danger btn-sm decline-offer-btn"
                                        data-notification-id="${notification.id}"
                                        data-postulacion-id="${notification.postulacion_id}">
                                    Rechazar Oferta
                                </button>
                            `;
                            listItem.appendChild(actionsDiv);
                        } else if (notification.tipo === 'contratacion' && !notification.leida && notification.solicitud_contratacion_id) {
                            const actionsDiv = document.createElement('div');
                            actionsDiv.classList.add('notification-actions', 'px-3', 'pb-2');
                            actionsDiv.innerHTML = `
                                <button class="btn btn-success btn-sm accept-hiring-request-btn" 
                                        data-notification-id="${notification.id}"
                                        data-solicitud-id="${notification.solicitud_contratacion_id}">
                                    Aceptar Solicitud
                                </button>
                                <button class="btn btn-danger btn-sm decline-hiring-request-btn"
                                        data-notification-id="${notification.id}"
                                        data-solicitud-id="${notification.solicitud_contratacion_id}">
                                    Rechazar Solicitud
                                </button>
                            `;
                            listItem.appendChild(actionsDiv);
                        }

                        notificationsList.appendChild(listItem);
                    });

                    // Add divider and "View All" button
                    const divider = document.createElement('li');
                    divider.innerHTML = '<hr class="dropdown-divider">';
                    notificationsList.appendChild(divider);

                    const viewAllItem = document.createElement('li');
                    viewAllItem.innerHTML = `
                        <a class="dropdown-item text-center" href="<?= BASE_URL ?>notificaciones.php">
                            <button class="btn btn-primary btn-sm w-100">Ver todas las notificaciones</button>
                        </a>
                    `;
                    notificationsList.appendChild(viewAllItem);
                }
                // Re-attach event listeners after updating the list
                attachNotificationActionListeners();
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
                // Optionally, show a message to the user or log it
            });
    }

    // Function to attach event listeners to dynamically added buttons
    function attachNotificationActionListeners() {
        document.querySelectorAll('.accept-offer-btn').forEach(button => {
            button.onclick = function(event) {
                event.preventDefault();
                event.stopPropagation(); // Prevent dropdown from closing immediately
                const notificationId = this.dataset.notificationId;
                const postulacionId = this.dataset.postulacionId;
                handleOfferResponse(notificationId, postulacionId, 'accept_offer');
            };
        });

        document.querySelectorAll('.decline-offer-btn').forEach(button => {
            button.onclick = function(event) {
                event.preventDefault();
                event.stopPropagation(); // Prevent dropdown from closing immediately
                const notificationId = this.dataset.notificationId;
                const postulacionId = this.dataset.postulacionId;
                handleOfferResponse(notificationId, postulacionId, 'decline_offer');
            };
        });

        document.querySelectorAll('.accept-hiring-request-btn').forEach(button => {
            button.onclick = function(event) {
                event.preventDefault();
                event.stopPropagation(); // Prevent dropdown from closing immediately
                const notificationId = this.dataset.notificationId;
                const solicitudId = this.dataset.solicitudId;
                handleHiringRequestResponse(notificationId, solicitudId, 'accept_hiring_request');
            };
        });

        document.querySelectorAll('.decline-hiring-request-btn').forEach(button => {
            button.onclick = function(event) {
                event.preventDefault();
                event.stopPropagation(); // Prevent dropdown from closing immediately
                const notificationId = this.dataset.notificationId;
                const solicitudId = this.dataset.solicitudId;
                handleHiringRequestResponse(notificationId, solicitudId, 'decline_hiring_request');
            };
        });

        // Event listener for clicking on a notification item to mark as read
        // This should be separate from the button clicks
        document.querySelectorAll('.notification-item').forEach(item => {
            item.onclick = function(event) {
                const notificationId = this.dataset.notificationId;
                // Only mark as read if it's not an offer/hiring request notification with active buttons
                // or if it's already read.
                const isActionableNotification = this.closest('li').querySelector('.notification-actions');
                if (!this.classList.contains('unread') || (this.classList.contains('unread') && !isActionableNotification)) {
                    markNotificationAsRead(notificationId);
                }
                // If it's an unread actionable notification with buttons, let the buttons handle the action
                // and the markAsRead will be called after accept/decline.
            };
        });
    }

    // Function to send AJAX request for offer response
    function handleOfferResponse(notificationId, postulacionId, action) {
        fetch('<?= BASE_URL ?>responder_oferta.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=${action}&postulacion_id=${postulacionId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                customAlert(data.message);
                // Mark the notification as read after successful action
                markNotificationAsRead(notificationId);
                // Re-fetch all notifications to update the list and count
                fetchNotifications(); 
            } else {
                customAlert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error al responder la oferta desde la notificación:', error);
            customAlert('Hubo un error al procesar tu respuesta. Inténtalo de nuevo.');
        });
    }

    // Function to send AJAX request for hiring request response
    function handleHiringRequestResponse(notificationId, solicitudId, action) {
        fetch('<?= BASE_URL ?>responder_solicitud_contratacion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=${action}&solicitud_id=${solicitudId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                customAlert(data.message);
                // Mark the notification as read after successful action
                markNotificationAsRead(notificationId);
                // Re-fetch all notifications to update the list and count
                fetchNotifications(); 
            } else {
                customAlert(`Error: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error al responder la solicitud de contratación desde la notificación:', error);
            customAlert('Hubo un error al procesar tu respuesta. Inténtalo de nuevo.');
        });
    }

    // Function to mark a notification as read via AJAX
    function markNotificationAsRead(notificationId) {
        fetch('<?= BASE_URL ?>navbar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=mark_notification_read&notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Notification marked as read:', notificationId);
                // Update the visual state immediately
                const notificationElement = document.getElementById(`notification-item-${notificationId}`);
                if (notificationElement) {
                    notificationElement.querySelector('.notification-item')?.classList.remove('unread');
                    const actionsDiv = notificationElement.querySelector('.notification-actions');
                    if (actionsDiv) {
                        actionsDiv.remove(); // Remove action buttons after being read/acted upon
                    }
                }
                // Decrement count visually
                if (notificationCountSpan && parseInt(notificationCountSpan.textContent) > 0) {
                    notificationCountSpan.textContent = parseInt(notificationCountSpan.textContent) - 1;
                    if (parseInt(notificationCountSpan.textContent) === 0) {
                        notificationCountSpan.style.display = 'none';
                    }
                }
            } else {
                console.error('Failed to mark notification as read:', data.message);
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }


    // Initial fetch of notifications
    fetchNotifications();

    // Set interval to refresh notifications every 30 seconds
    setInterval(fetchNotifications, 30000); // 30 seconds

    // --- Inicialización explícita de Dropdowns de Bootstrap ---
    // Selecciona todos los elementos con el atributo data-bs-toggle="dropdown"
    const dropdownToggleElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdownToggleElements.forEach(function(dropdownToggle) {
        // Crea una nueva instancia de Dropdown para cada elemento
        new bootstrap.Dropdown(dropdownToggle);
    });

    // Event listeners para los dropdowns (para depuración, si es necesario)
    if (notificationsDropdownElement) {
        notificationsDropdownElement.addEventListener('click', function() {
            console.log('Notifications dropdown clicked!');
        });
    }
    if (userProfileDropdownElement) {
        userProfileDropdownElement.addEventListener('click', function() {
            console.log('User profile dropdown clicked!');
        });
    }
});
</script>

</body>
</html>
