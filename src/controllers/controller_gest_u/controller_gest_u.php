<?php
require_once __DIR__ . '/../../models/gestion_usuarios_model/gest_u_model.php';
require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../authController/SessionController.php';
SessionController::enforceActive();
require_once __DIR__ . '/../rol/rolController.php';
RolController::requireAdmin(); // <- bloquea a quien no sea admin

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Variables necesarias para la navbar: SIEMPRE desde la sesión del LOGUEADO (no del usuario editado)
$userName = $_SESSION['user_name'] ?? 'Usuario';

// Foto de perfil: si guardas en sesión la ruta relativa 'foto_perfil', normalízala a URL pública
$profileImage = 'https://static.thenounproject.com/png/4154905-200.png';
if (!empty($_SESSION['foto_perfil'])) {
    $rutaAbsoluta = ROOT_PATH . '/assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
    $rutaPublica  = BASE_URL . 'assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
    if (file_exists($rutaAbsoluta)) {
        $profileImage = $rutaPublica;
    }
}

// Mantén estas si ya las usas para la UI
$esAdminEmpresa = $_SESSION['es_admin_empresa'] ?? false;
$unreadNotificationsCount = $_SESSION['notificaciones_no_leidas'] ?? 0;
$latestNotifications = $_SESSION['notificaciones_recientes'] ?? [];


$model = new UsuarioModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (($_POST['accion'] ?? '') === 'registrar') {
        $model->registrar($_POST);
        $_SESSION['flash_success'] = 'Usuario registrado correctamente.';
        header('Location: ' . BASE_URL . 'src/index.php?action=gestionar_usuarios');
        exit;
    }

    if (($_POST['accion'] ?? '') === 'editar') {
        $id    = (int)$_POST['id'];
        $idRol = $_POST['id_rol'] ?? $_POST['rol_id'] ?? null;
        if ($idRol === null && !empty($_POST['rol'])) {
            $idRol = $model->rolIdPorNombre($_POST['rol']);
        }
        $model->editarRol($id, (int)$idRol);
        $_SESSION['flash_success'] = 'Rol actualizado correctamente.';
        header('Location: ' . BASE_URL . 'src/index.php?action=gestionar_usuarios');
        exit;
    }

    // >>> PATCH: impedir que el admin se bloquee a sí mismo
    if (($_POST['accion'] ?? '') === 'bloquear') {
        $bloqueadoId   = (int)$_POST['id'];

        // Tomamos el ID del usuario logueado de forma robusta (soporta tus dos esquemas de sesión)
        $currentUserId = (int)($_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? 0));

        // 1) Si intenta bloquearse a sí mismo, NO permitimos la operación
        if ($bloqueadoId === $currentUserId) {
            $_SESSION['flash_error'] = 'No puedes bloquear tu propia cuenta.';
            header('Location: ' . BASE_URL . 'src/index.php?action=gestionar_usuarios');
            exit;
        }

        // 2) Procede con el bloqueo normal para terceros
        $model->bloquear($bloqueadoId);

        // Mensajería para el admin
        $_SESSION['flash_success'] = 'El usuario fue bloqueado exitosamente.';
        $_SESSION['flash_alert']   = 'Bloqueo exitoso.';

        header('Location: ' . BASE_URL . 'src/index.php?action=gestionar_usuarios');
        exit;
    }

    if (($_POST['accion'] ?? '') === 'desbloquear') {
        $model->desbloquear((int)$_POST['id']);
        $_SESSION['flash_success'] = 'El usuario fue desbloqueado.';
        header('Location: ' . BASE_URL . 'src/index.php?action=gestionar_usuarios');
        exit;
    }
}

$busqueda = $_GET['buscar'] ?? '';
$genero   = $_GET['genero'] ?? '';
$estado   = strtolower(trim($_GET['estado'] ?? ''));

$estadoDb = '';
if ($estado === 'activo')     { $estadoDb = 'Activo'; }
if ($estado === 'bloqueado')  { $estadoDb = 'Bloqueado'; }

// ahora SÍ pásalo al modelo
$usuarios = $model->obtenerUsuarios($busqueda, $genero, $estadoDb);

$modoEdicion   = $_GET['editar'] ?? null;
$usuarioEditar = $modoEdicion ? $model->obtenerPorId((int)$modoEdicion) : null;

$roles = $model->obtenerRoles();

$currentPage = 'gestionar_usuarios';

require_once __DIR__ . '/../../views/Gestion_usuarios/gest_view.php';