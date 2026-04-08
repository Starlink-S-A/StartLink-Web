<?php
// views/dashboardView/navbar_view.php
$userName = $userName ?? ($_SESSION['user_name'] ?? 'Usuario');
$rolLabel = "Administrador"; 
if (isset($esAdminGlobal) && $esAdminGlobal) {
    $rolLabel = 'Administrador';
} elseif (isset($rolEmpresa)) {
    if ((int)$rolEmpresa === 1) $rolLabel = 'Admin Empresa';
    elseif ((int)$rolEmpresa === 2) $rolLabel = 'Contratador';
    elseif ((int)$rolEmpresa === 3) $rolLabel = 'Empleado';
    else $rolLabel = 'Candidato';
}

$userInitials = strtoupper(substr($userName, 0, 2));
if (strpos($userName, ' ') !== false) {
    $parts = explode(' ', $userName);
    $userInitials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1] ?? '', 0, 1));
}

$userEmail = $_SESSION['user_email'] ?? '';

$notificaciones = $notificaciones ?? null;
$unread_notifications_count = $unread_notifications_count ?? 0;

if ($notificaciones === null && isset($_SESSION["user_id"])) {
    $notificaciones = [];
    $link = function_exists('getDbConnection') ? getDbConnection() : null; 
    if ($link instanceof PDO) {
        try {
            $stmt_notif = $link->prepare("SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion FROM notificaciones WHERE user_id = ? ORDER BY fecha_creacion DESC LIMIT 10");
            $stmt_notif->execute([$_SESSION["user_id"]]);
            while ($row = $stmt_notif->fetch(PDO::FETCH_ASSOC)) {
                if (empty($row['icono'])) {
                    switch ($row['tipo']) {
                        case 'success': $row['icono'] = 'fas fa-check-circle text-success'; break;
                        case 'warning': $row['icono'] = 'fas fa-exclamation-triangle text-warning'; break;
                        case 'error': $row['icono'] = 'fas fa-times-circle text-danger'; break;
                        default: $row['icono'] = 'fas fa-info-circle text-info'; break;
                    }
                } elseif (strpos($row['icono'], 'text-') === false) {
                    $row['icono'] .= ' text-primary';
                }
                $notificaciones[] = $row;
                if (!$row['leida']) {
                    $unread_notifications_count++;
                }
            }
        } catch (PDOException $e) { /* ignore */ }
    }
}
$notificaciones = $notificaciones ?? [];

$pageTitle = $pageTitle ?? 'StartLink';

if (!isset($profileImage) && !empty($_SESSION['foto_perfil'])) {
    $profileImage = rtrim(BASE_URL, '/') . '/' . ltrim((string)$_SESSION['foto_perfil'], '/');
}

// Fetch recent messages
$recent_messages = [];
$unread_messages_count = 0;
if (isset($_SESSION["user_id"])) {
    $link = function_exists('getDbConnection') ? getDbConnection() : null; 
    if ($link instanceof PDO) {
        try {
            $stmt_unread_msgs = $link->prepare("SELECT COUNT(*) FROM notificaciones WHERE user_id = ? AND leida = 0 AND tipo = 'chat'");
            $stmt_unread_msgs->execute([$_SESSION["user_id"]]);
            $unread_messages_count = (int)$stmt_unread_msgs->fetchColumn();

            $stmt_msgs = $link->prepare("
                SELECT
                    C.id_conversacion,
                    C.tipo_conversacion,
                    C.titulo_conversacion,
                    M.contenido AS ultimo_mensaje_contenido,
                    M.fecha_envio AS ultimo_mensaje_fecha_envio,
                    U_REM.nombre AS remitente_nombre,
                    U_REM.foto_perfil AS remitente_foto,
                    U_REM.id AS remitente_id
                FROM conversacion_participante CP
                JOIN conversacion C ON CP.id_conversacion = C.id_conversacion
                LEFT JOIN (
                    SELECT m1.*
                    FROM mensaje m1
                    JOIN (
                        SELECT id_conversacion, MAX(fecha_envio) AS max_fecha
                        FROM mensaje
                        GROUP BY id_conversacion
                    ) m2
                        ON m1.id_conversacion = m2.id_conversacion AND m1.fecha_envio = m2.max_fecha
                ) M ON M.id_conversacion = C.id_conversacion
                LEFT JOIN usuario U_REM ON U_REM.id = M.id_remitente
                WHERE CP.id_usuario = ?
                ORDER BY M.fecha_envio DESC
                LIMIT 5
            ");
            $stmt_msgs->execute([$_SESSION["user_id"]]);
            $recent_messages = $stmt_msgs->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { /* ignore */ }
    }
}
?>

<div class="top-navbar mb-4">
    <!-- Left: Title -->
    <div class="d-flex align-items-center">
        <h2 class="navbar-title"><?= htmlspecialchars($pageTitle) ?></h2>
    </div>
    
    <div class="navbar-actions">
        <!-- Notificaciones Dropdown -->
        <div class="dropdown">
            <button class="nav-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                <i class="far fa-bell"></i>
                <span class="nav-badge" id="notifications-badge" style="<?= $unread_notifications_count > 0 ? '' : 'display:none;' ?>">
                    <?= $unread_notifications_count > 0 ? $unread_notifications_count : '' ?>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end nav-dropdown-menu" style="width: 380px;">
                <div class="dropdown-header-custom">
                    <h6>
                        <i class="far fa-bell me-1"></i> Notificaciones 
                        <?php if($unread_notifications_count > 0): ?>
                            <span class="badge bg-danger rounded-pill" style="font-size: 0.7rem;"><?= $unread_notifications_count ?></span>
                        <?php endif; ?>
                    </h6>
                </div>
                <div class="d-flex justify-content-between px-3 py-2 border-bottom" style="font-size: 0.78rem;">
                    <a href="#" id="navbar-mark-all-read-btn" class="text-dark fw-bold text-decoration-none">Marcar todas como leídas</a>
                    <a href="#" id="navbar-delete-all-btn" class="text-danger fw-bold text-decoration-none">Borrar todas</a>
                </div>
                <div id="notifications-list" style="max-height: 320px; overflow-y: auto;">
                    <?php if(empty($notificaciones)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="far fa-bell-slash d-block mb-2" style="font-size: 1.5rem;"></i>
                            No tienes notificaciones
                        </div>
                    <?php else: ?>
                        <?php foreach($notificaciones as $notif): 
                            $bgClass = empty($notif['leida']) ? '' : '';
                            $dot = empty($notif['leida']) ? '<div class="bg-primary rounded-circle flex-shrink-0" style="width: 7px; height: 7px;"></div>' : '';
                            
                            $iconColorBg = '#f1f5f9';
                            $iconColorText = '#64748b';
                            if (strpos($notif['icono'], 'text-success') !== false) { $iconColorBg = '#d1fae5'; $iconColorText = '#059669'; }
                            elseif (strpos($notif['icono'], 'text-warning') !== false) { $iconColorBg = '#fef3c7'; $iconColorText = '#d97706'; }
                            elseif (strpos($notif['icono'], 'text-danger') !== false) { $iconColorBg = '#fee2e2'; $iconColorText = '#dc2626'; }
                            elseif (strpos($notif['icono'], 'text-info') !== false) { $iconColorBg = '#e0f2fe'; $iconColorText = '#0284c7'; }
                            
                            $fechaTexto = "Hace un momento";
                            if (!empty($notif['fecha_creacion'])) {
                                $creacion = new DateTime($notif['fecha_creacion']);
                                $ahora = new DateTime();
                                $diff = $ahora->diff($creacion);
                                if ($diff->d > 0) $fechaTexto = "Hace {$diff->d} día" . ($diff->d > 1 ? 's' : '');
                                elseif ($diff->h > 0) $fechaTexto = "Hace {$diff->h} hora" . ($diff->h > 1 ? 's' : '');
                                elseif ($diff->i > 0) $fechaTexto = "Hace {$diff->i} minutos";
                            }
                        ?>
                        <div class="notif-item position-relative" id="notification-item-<?= (int)$notif['id'] ?>">
                            <div class="notif-icon-circle" style="background-color: <?= $iconColorBg ?>; color: <?= $iconColorText ?>;">
                                <i class="<?= htmlspecialchars($notif['icono'] ?? 'fas fa-bell') ?>"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1 fw-600" style="font-size: 0.85rem;"><?= htmlspecialchars(ucfirst($notif['tipo'] ?? 'Aviso')) ?></h6>
                                    <?= $dot ?>
                                </div>
                                <p class="mb-1 text-muted" style="font-size: 0.78rem; line-height: 1.4;"><?= htmlspecialchars($notif['mensaje']) ?></p>
                                <small class="text-muted" style="font-size: 0.7rem;"><?= $fechaTexto ?></small>
                            </div>
                            <a class="stretched-link"
                               href="<?= BASE_URL ?>src/index.php?action=notificaciones&sub_action=redirect&notification_id=<?= (int)$notif['id'] ?>"></a>
                            <button class="notif-delete-btn position-relative" style="z-index:2;" data-notification-id="<?= (int)$notif['id'] ?>" title="Eliminar" type="button"><i class="far fa-trash-alt"></i></button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="<?= BASE_URL ?>src/index.php?action=notificaciones" class="dropdown-view-all">Ver todas las notificaciones</a>
            </div>
        </div>

        <!-- Mensajes Dropdown -->
        <div class="dropdown">
            <button class="nav-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                <i class="far fa-comment-dots"></i>
                <span class="nav-badge" id="messages-badge" style="<?= $unread_messages_count > 0 ? '' : 'display:none;' ?>">
                    <?= $unread_messages_count > 0 ? $unread_messages_count : '' ?>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end nav-dropdown-menu" style="width: 380px;">
                <div class="dropdown-header-custom">
                    <h6>
                        <i class="far fa-comment-dots me-1"></i> Mensajes 
                    </h6>
                </div>
                <div class="p-2 border-bottom">
                    <input type="text" class="form-control form-control-sm rounded-pill bg-light border-0" placeholder="Buscar mensajes..." style="font-size: 0.82rem;">
                </div>
                <div style="max-height: 320px; overflow-y: auto;">
                    <?php if (empty($recent_messages)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="far fa-comments d-block mb-2" style="font-size: 1.5rem;"></i>
                            No tienes mensajes recientes
                        </div>
                    <?php else: ?>
                        <?php foreach($recent_messages as $msg): 
                            $fechaMsg = "Hace un momento";
                            if (!empty($msg['ultimo_mensaje_fecha_envio'])) {
                                $creacionMsg = new DateTime($msg['ultimo_mensaje_fecha_envio']);
                                $ahoraMsg = new DateTime();
                                $diffMsg = $ahoraMsg->diff($creacionMsg);
                                if ($diffMsg->d > 0) $fechaMsg = "Hace {$diffMsg->d} día" . ($diffMsg->d > 1 ? 's' : '');
                                elseif ($diffMsg->h > 0) $fechaMsg = "Hace {$diffMsg->h} hora" . ($diffMsg->h > 1 ? 's' : '');
                                elseif ($diffMsg->i > 0) $fechaMsg = "Hace {$diffMsg->i} min";
                            }
                            
                            $msgTitle = $msg['remitente_nombre'] ?? 'Usuario';
                            if ($msg['tipo_conversacion'] == 'oferta_grupal' || $msg['tipo_conversacion'] == 'empresa_interna') {
                                $msgTitle = $msg['titulo_conversacion'];
                            } elseif ($msg['remitente_id'] == $_SESSION['user_id']) {
                                $msgTitle = "Tú: " . $msgTitle;
                            }
                            
                            $msgInitials = strtoupper(substr($msg['remitente_nombre'] ?? 'U', 0, 2));
                            $contactPhotoUrl = null;
                            if (!empty($msg['remitente_foto'])) {
                                $fotoBase = basename($msg['remitente_foto']);
                                $contactPhotoUrl = BASE_URL . 'assets/images/Uploads/profile_pictures/' . $fotoBase;
                            }
                            $msgUrl = BASE_URL . 'src/index.php?action=mis_chats&chat_id=' . $msg['id_conversacion'];
                        ?>
                        <a href="<?= htmlspecialchars($msgUrl) ?>" class="notif-item text-decoration-none text-dark d-flex">
                            <div class="msg-avatar" style="<?= $contactPhotoUrl ? 'background: transparent;' : '' ?>">
                                <?php if ($contactPhotoUrl): ?>
                                    <img src="<?= htmlspecialchars($contactPhotoUrl) ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                                <?php else: ?>
                                    <?= htmlspecialchars($msgInitials) ?>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-0 text-truncate fw-600" style="font-size: 0.85rem;"><?= htmlspecialchars($msgTitle) ?></h6>
                                    <small class="text-muted text-nowrap ms-2" style="font-size: 0.7rem;"><?= $fechaMsg ?></small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <p class="mb-0 text-muted text-truncate" style="font-size: 0.78rem;"><?= htmlspecialchars($msg['ultimo_mensaje_contenido']) ?></p>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="<?= BASE_URL ?>src/index.php?action=mis_chats" class="dropdown-view-all">Ver todos los mensajes</a>
            </div>
        </div>

        <!-- Perfil Dropdown -->
        <div class="dropdown">
            <button class="nav-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="nav-avatar">
                    <?php if (isset($profileImage) && $profileImage): ?>
                        <img src="<?= $profileImage ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php else: ?>
                        <?= $userInitials ?>
                    <?php endif; ?>
                </div>
                <div class="d-none d-md-block">
                    <span class="nav-user-name"><?= htmlspecialchars($userName) ?></span>
                </div>
            </button>
            <div class="dropdown-menu dropdown-menu-end nav-dropdown-menu" style="width: 320px;">
                <div class="dropdown-header-custom border-0 pb-0">
                    <h5>Mi Cuenta</h5>
                    <button type="button" class="btn-close" style="font-size: 0.65rem;" data-bs-dismiss="dropdown"></button>
                </div>
                <div class="p-3 text-center border-bottom">
                    <div class="nav-avatar mx-auto mb-2" style="width: 56px; height: 56px; font-size: 1.3rem;">
                        <?php if (isset($profileImage) && $profileImage): ?>
                            <img src="<?= $profileImage ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <?php else: ?>
                            <?= $userInitials ?>
                        <?php endif; ?>
                    </div>
                    <h6 class="mb-0 fw-700"><?= htmlspecialchars($userName) ?></h6>
                    <small class="text-muted d-block"><?= $rolLabel ?></small>
                    <?php if (!empty($userEmail)): ?>
                        <small class="text-muted d-block" style="font-size: 0.75rem;"><?= htmlspecialchars($userEmail) ?></small>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>configurar_perfil" class="btn btn-sm rounded-pill w-100 py-2 mt-2" style="background-color: #00a680; color: white; border: none; font-weight: 600;">Editar perfil</a>
                </div>
                <div class="py-1">
                    <a href="<?= BASE_URL ?>configurar_perfil" class="dropdown-item-custom">
                        <i class="far fa-user"></i>
                        <div>
                            <span class="d-block fw-500">Mi perfil</span>
                            <small class="text-muted" style="font-size: 0.7rem;">Información personal y configuración</small>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>src/index.php?action=configurar_perfil&step=personal#seccion-seguridad" class="dropdown-item-custom">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <span class="d-block fw-500">Seguridad y privacidad</span>
                            <small class="text-muted" style="font-size: 0.7rem;">Contraseña y configuración de seguridad</small>
                        </div>
                    </a>
                    <a href="<?= BASE_URL ?>src/index.php?action=nominas" class="dropdown-item-custom">
                        <i class="far fa-file-alt"></i>
                        <div>
                            <span class="d-block fw-500">Historial laboral</span>
                            <small class="text-muted" style="font-size: 0.7rem;">Nóminas y desempeño</small>
                        </div>
                    </a>
                    <a href="#" class="dropdown-item-custom border-bottom pb-2 mb-1">
                        <i class="fas fa-headset"></i>
                        <span class="fw-500">Ayuda y soporte</span>
                    </a>
                    <a href="<?= BASE_URL ?>logout" class="dropdown-item-custom text-danger">
                        <i class="fas fa-sign-out-alt text-danger"></i>
                        <span class="fw-700">Cerrar sesión</span>
                    </a>
                </div>
                <div class="p-2 text-center border-top">
                    <small class="text-muted" style="font-size: 0.6rem;">StartLink Cloud v2.0<br>&copy; 2026 Todos los derechos reservados</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    if (typeof BASE_URL === 'undefined') {
        window.BASE_URL = '<?= BASE_URL ?>';
    }
</script>
<script src="<?= BASE_URL ?>src/public/js/notificaiones.js" defer></script>
