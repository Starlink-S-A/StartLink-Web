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

        $defaultImage = 'https://static.thenounproject.com/png/4154905-200.png';
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

        require_once __DIR__ . '/../../views/EmpresasView/misEquiposView.php';
    }
}
