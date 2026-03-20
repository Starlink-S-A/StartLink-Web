<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/empresasModel/misEquiposModel.php';

class MisEquiposController {
    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['loggedin'] ?? false) !== true) {
            header('Location: ' . BASE_URL . 'index.php');
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $modelo = new MisEquiposModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empresa_id']) && is_numeric($_POST['empresa_id'])) {
            $empresaId = (int)$_POST['empresa_id'];
            $relacion = $modelo->getRelacionUsuarioEmpresa($userId, $empresaId);

            if (!$relacion) {
                $_SESSION['mensaje'] = 'No tienes acceso a esa empresa.';
                header('Location: ' . BASE_URL . 'index.php?action=mis_equipos');
                exit();
            }

            $_SESSION['id_empresa'] = (int)$relacion['id_empresa'];
            $_SESSION['id_rol_empresa'] = $relacion['id_rol_empresa'] !== null ? (int)$relacion['id_rol_empresa'] : null;

            header('Location: ' . BASE_URL . 'index.php?action=mi_equipo');
            exit();
        }

        $empresasRaw = $modelo->getEmpresasUsuario($userId);
        $empresas = [];

        $defaultCompanyImage = 'https://cdn-icons-png.flaticon.com/512/3061/3061341.png';
        foreach ($empresasRaw as $e) {
            $e['logo_url'] = $defaultCompanyImage;
            if (!empty($e['logo_ruta'])) {
                $nombreArchivo = basename($e['logo_ruta']);
                $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'logos_empresa' . DIRECTORY_SEPARATOR . $nombreArchivo;
                $rutaPublica = rtrim(BASE_URL, '/') . '/assets/images/Uploads/logos_empresa/' . $nombreArchivo;
                if (file_exists($rutaAbsoluta)) {
                    $e['logo_url'] = $rutaPublica;
                }
            }
            $empresas[] = $e;
        }

        $esAdminEmpresa = false;
        $showPublishProfileLink = false;
        $unreadNotificationsCount = 0;
        $latestNotifications = [];

        $conexion = getDbConnection();
        $dbFotoPerfil = null;
        try {
            $stmtRolTrabajador = $conexion->prepare("SELECT id FROM rol WHERE nombre_rol = 'TRABAJADOR'");
            $stmtRolTrabajador->execute();
            $rolTrabajadorId = (int)$stmtRolTrabajador->fetchColumn();

            $stmtUser = $conexion->prepare("SELECT id_rol, foto_perfil FROM usuario WHERE id = ?");
            $stmtUser->execute([$userId]);
            $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $currentUserGlobalRole = (int)($userRow['id_rol'] ?? $rolTrabajadorId);
            $dbFotoPerfil = $userRow['foto_perfil'] ?? null;

            if ($currentUserGlobalRole !== $rolTrabajadorId) {
                $showPublishProfileLink = true;
            }

            $userRolEmpresa = $_SESSION['id_rol_empresa'] ?? null;
            if (in_array((int)$userRolEmpresa, [1, 2], true)) {
                $esAdminEmpresa = true;
            }

            $stmtUnread = $conexion->prepare("SELECT COUNT(*) FROM notificaciones WHERE user_id = ? AND leida = 0");
            $stmtUnread->execute([$userId]);
            $unreadNotificationsCount = (int)$stmtUnread->fetchColumn();
        } catch (Throwable $e) {
            $userRolEmpresa = $_SESSION['id_rol_empresa'] ?? null;
            if (in_array((int)$userRolEmpresa, [1, 2], true)) {
                $esAdminEmpresa = true;
            }
        }

        $profileImage = 'https://static.thenounproject.com/png/4154905-200.png';
        $fotoPath = !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : $dbFotoPerfil;
        if (!empty($fotoPath)) {
            $nombreArchivo = basename($fotoPath);
            $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $nombreArchivo;
            $rutaPublica = rtrim(BASE_URL, '/') . '/assets/images/Uploads/profile_pictures/' . $nombreArchivo;
            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
                if (empty($_SESSION['foto_perfil'])) {
                    $_SESSION['foto_perfil'] = 'assets/images/Uploads/profile_pictures/' . $nombreArchivo;
                }
            }
        }

        require_once __DIR__ . '/../../views/EmpresasView/misEquiposView.php';
    }
}
