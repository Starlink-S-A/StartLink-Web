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

        $defaultImage = $defaultProfile;
        $profileImage = $defaultImage;
        if (!empty($_SESSION['foto_perfil'])) {
            $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . basename($_SESSION['foto_perfil']);
            $rutaPublica = rtrim(BASE_URL, '/') . '/assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
            }
        }

        $latestNotifications = [];
        $unreadNotificationsCount = 0;

        require_once __DIR__ . '/../../views/EmpresasView/miEquipoView.php';
    }
}
