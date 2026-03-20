<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/empresasModel/miEquipoModel.php';

class MiEquipoController {
    public function show(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['loggedin'] ?? false) !== true) {
            header('Location: ' . BASE_URL . 'index.php');
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $isGlobalAdmin = ((int)($_SESSION['id_rol'] ?? 0) === 1);

        $empresaId = null;
        if (isset($_GET['id_empresa']) && is_numeric($_GET['id_empresa'])) {
            $empresaId = (int)$_GET['id_empresa'];
        } elseif (!empty($_SESSION['id_empresa'])) {
            $empresaId = (int)$_SESSION['id_empresa'];
        }

        if (!$empresaId) {
            header('Location: ' . BASE_URL . 'index.php?action=mis_equipos');
            exit();
        }

        $modelo = new MiEquipoModel();

        $esAdminEmpresa = false;
        $showPublishProfileLink = false;
        $unreadNotificationsCount = 0;
        $latestNotifications = [];

        if (!$isGlobalAdmin) {
            $relacion = $modelo->getRelacionUsuarioEmpresa($userId, $empresaId);
            if (!$relacion) {
                $_SESSION['mensaje'] = 'No tienes acceso al equipo de esa empresa.';
                header('Location: ' . BASE_URL . 'index.php?action=mis_equipos');
                exit();
            }

            $_SESSION['id_empresa'] = (int)$relacion['id_empresa'];
            $_SESSION['id_rol_empresa'] = $relacion['id_rol_empresa'] !== null ? (int)$relacion['id_rol_empresa'] : null;
        } else {
            $_SESSION['id_empresa'] = $empresaId;
        }

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

        $empresa = $modelo->getEmpresa($empresaId);
        $miembrosRaw = $modelo->getMiembros($empresaId);
        $miembros = [];

        $defaultCompanyImage = 'https://cdn-icons-png.flaticon.com/512/3061/3061341.png';
        if ($empresa) {
            $empresa['logo_url'] = $defaultCompanyImage;
            if (!empty($empresa['logo_ruta'])) {
                $nombreArchivo = basename($empresa['logo_ruta']);
                $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'logos_empresa' . DIRECTORY_SEPARATOR . $nombreArchivo;
                $rutaPublica = rtrim(BASE_URL, '/') . '/assets/images/Uploads/logos_empresa/' . $nombreArchivo;
                if (file_exists($rutaAbsoluta)) {
                    $empresa['logo_url'] = $rutaPublica;
                }
            }
        }

        $defaultProfile = 'https://static.thenounproject.com/png/4154905-200.png';
        foreach ($miembrosRaw as $m) {
            $m['profile_image_url'] = $defaultProfile;
            if (!empty($m['foto_perfil'])) {
                $nombreArchivo = basename($m['foto_perfil']);
                $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $nombreArchivo;
                $rutaPublica = rtrim(BASE_URL, '/') . '/assets/images/Uploads/profile_pictures/' . $nombreArchivo;
                if (file_exists($rutaAbsoluta)) {
                    $m['profile_image_url'] = $rutaPublica;
                }
            }
            $miembros[] = $m;
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

        require_once __DIR__ . '/../../views/EmpresasView/miEquipoView.php';
    }
}
