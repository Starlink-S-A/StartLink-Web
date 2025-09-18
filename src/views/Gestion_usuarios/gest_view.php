<?php
// views/Gestion_usuarios/gest_view.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>

    <!-- CSS de esta página -->
    <!-- Bootstrap primero -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<!-- Tus estilos personalizados -->
<link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/navbar_styles.css">

<link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/gestion_usuarios.css">

<!-- Font Awesome solo íconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="gestion-usuarios-page">

<?php
$userName = $_SESSION['user_name'] ?? 'Usuario';
$profileImage = $_SESSION['user_foto'] ?? 'https://static.thenounproject.com/png/4154905-200.png';
$unreadNotificationsCount = $_SESSION['notificaciones_no_leidas'] ?? 0;
$latestNotifications = $_SESSION['notificaciones_recientes'] ?? [];
$esAdminEmpresa = ($_SESSION['id_rol_empresa'] ?? null) === 1;
$showPublishProfileLink = !($_SESSION['profile_completed'] ?? true);

extract([
    'userName',
    'profileImage',
    'unreadNotificationsCount',
    'latestNotifications',
    'esAdminEmpresa',
    'showPublishProfileLink'
]);

include __DIR__ . '/../dashboardView/navbar_view.php';
?>

<?php
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$busqueda      = $busqueda ?? '';
$genero        = $genero ?? '';
$usuarios      = $usuarios ?? [];
$usuarioEditar = $usuarioEditar ?? null;
$roles         = $roles ?? [];
$estado        = $estado ?? '';
// >>> PATCH: ID del usuario actual para deshabilitar su propio "Bloquear"
$yoId = (int)($_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? 0));

// >>> PATCH: Centralizar mensajes de servidor para mostrarlos con SweetAlert2 (sin romper layout)
// Capturamos y limpiamos las variables de sesión; las pasamos a window.__flash para usarlas al final del documento
$__flash = [
    'success' => !empty($_SESSION['flash_success']) ? $_SESSION['flash_success'] : null,
    'error'   => !empty($_SESSION['flash_error'])   ? $_SESSION['flash_error']   : null,
    'alert'   => !empty($_SESSION['flash_alert'])   ? $_SESSION['flash_alert']   : null,
];
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_alert']);
?>
<script>
// Disponibilizar los mensajes en el cliente sin invocar Swal antes de cargar la librería
window.__flash = <?= json_encode($__flash, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
</script>

<div class="gestion-usuarios">
    <div class="gu-container container">
        <h2 class="mb-3">Usuarios Registrados</h2>

        <!-- (Antes había alertas Bootstrap y un alert() nativo: se reemplazó por SweetAlert2 abajo) -->

        <?php if (!empty($usuarioEditar)): ?>
            <div class="card my-3">
                <div class="card-header">Editar rol de: <strong><?= e($usuarioEditar['nombre'] ?? 'Usuario') ?></strong></div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>src/index.php?action=gestionar_usuarios" class="row g-3">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id" value="<?= (int)($usuarioEditar['id'] ?? 0) ?>">

                        <div class="col-12 col-md-6">
                            <label class="form-label">Rol</label>
                            <select name="id_rol" class="form-select" required>
                                <option value="">Selecciona un rol</option>
                                <?php foreach (($roles ?? []) as $r): ?>
                                    <?php
                                        $rid = (int)$r['id'];
                                        $sel = ($rid === (int)($usuarioEditar['id_rol'] ?? 0)) ? 'selected' : '';
                                    ?>
                                    <option value="<?= $rid ?>" <?= $sel ?>><?= e($r['nombre_rol']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <a class="btn btn-secondary" href="<?= BASE_URL ?>src/index.php?action=gestionar_usuarios">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <form method="GET" action="<?= BASE_URL ?>src/index.php" class="filters d-flex flex-wrap align-items-end gap-2 mb-3">
            <input type="hidden" name="action" value="gestionar_usuarios">

            <div>
                <label class="form-label mb-1">Búsqueda</label>
                <input class="form-control" type="text" name="buscar" placeholder="Buscar por nombre o email" value="<?= e($busqueda) ?>">
            </div>

            <div>
                <label class="form-label mb-1">Género</label>
                <select class="form-select" name="genero">
                    <option value="">Filtrar por género</option>
                    <option value="M" <?= $genero === 'M' ? 'selected' : '' ?>>Masculino</option>
                    <option value="F" <?= $genero === 'F' ? 'selected' : '' ?>>Femenino</option>
                </select>
            </div>
            
            <div id="estado-filter">
                <label class="form-label mb-1">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Filtrar por estado</option>
                    <option value="activo"    <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="bloqueado" <?= $estado === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-outline-primary">Filtrar</button>
            </div>
        </form>

        <!-- Tabla -->
        <div class="table-wrapper">
            <table class="table table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Género</th>
                        <th>País</th>
                        <th>Ciudad</th>
                        <th>HDV</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= e($u['nombre'] ?? '') ?></td>
                            <td><?= e($u['email'] ?? '') ?></td>
                            <td><?= e($u['genero'] ?? '') ?></td>
                            <td><?= e($u['pais'] ?? '') ?></td>
                            <td><?= e($u['ciudad'] ?? '') ?></td>
                            <td><?= e($u['ruta_hdv'] ?? '') ?></td>
                            <td><?= e($u['nombre_rol'] ?? '') ?></td>
                            <td><?= e($u['estado'] ?? '') ?></td>
                            <td class="d-flex gap-2">
                                <!-- Editar Rol -->
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="<?= BASE_URL ?>src/index.php?action=gestionar_usuarios&editar=<?= (int)$u['id'] ?>">
                                   Editar Rol
                                </a>

                                <?php
                                    // >>> PATCH: evitar que el admin pueda bloquearse a sí mismo desde la UI
                                    $uid      = (int)$u['id'];
                                    $estadoU  = (string)($u['estado'] ?? '');
                                ?>

                                <?php if ($uid === $yoId): ?>
                                    <!-- No permitimos bloquearme a mí mismo -->
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="No puedes bloquear tu propia cuenta">
                                        Bloquear
                                    </button>
                                <?php else: ?>
                                    <?php if ($estadoU !== 'Bloqueado'): ?>
                                        <!-- Bloquear (POST) a terceros -->
                                        <form method="POST" action="<?= BASE_URL ?>src/index.php?action=gestionar_usuarios"
                                            onclick="return confirmarBloqueo(this)" class="d-inline">
                                            <input type="hidden" name="accion" value="bloquear">
                                            <input type="hidden" name="id" value="<?= $uid ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Bloquear</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="bloqueado">Bloqueado</span>
                                        <!-- (Opcional) Desbloquear a terceros -->
                                        <form method="POST" action="<?= BASE_URL ?>src/index.php?action=gestionar_usuarios" class="d-inline">
                                            <input type="hidden" name="accion" value="desbloquear">
                                            <input type="hidden" name="id" value="<?= $uid ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Desbloquear</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <!-- Configurar Perfil -->
                                <a class="btn btn-sm btn-outline-info"
                                   href="<?= BASE_URL ?>src/index.php?action=configurar_perfil&id=<?= (int)$u['id'] ?>">
                                   Configurar Perfil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Confirmación previa al bloqueo (se mantiene igual)
function confirmarBloqueo(formElement) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Este usuario será bloqueado y expulsado del sistema.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, bloquear',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            formElement.closest('form').submit();
        }
    });
    return false; // Evita el submit inmediato
}

// >>> PATCH: Renderizar todos los flashes de servidor con SweetAlert2 (luego de cargar la librería)
(function() {
    const f = (window.__flash || {});
    const queue = [];

    // Éxito (toast)
    if (f.success) {
        queue.push({
            icon: 'success',
            title: f.success,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end'
        });
    }

    // Alerta genérica (toast)
    if (f.alert) {
        queue.push({
            icon: 'success',
            title: f.alert,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            toast: true,
            position: 'top-end'
        });
    }

    // Error (modal centrado)
    if (f.error) {
        queue.push({
            icon: 'error',
            title: 'Ups…',
            text: f.error,
            confirmButtonText: 'OK'
        });
    }

    function showNext() {
        const cfg = queue.shift();
        if (!cfg) return;
        Swal.fire(cfg).then(showNext);
    }

    if (queue.length) showNext();
})();
</script>
</body>
</html>