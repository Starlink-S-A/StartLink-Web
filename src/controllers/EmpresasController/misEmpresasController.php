<?php
// src/controllers/EmpresasController/misEmpresasController.php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/empresasModel/MisEmpresasModel.php';

class MisEmpresasController {
    
    /**
     * Muestra y gestiona la página de Mis Empresas.
     */
    public function misEmpresas() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL . "index.php");
            exit();
        }

        $userId = $_SESSION["user_id"];
        
        $modelo = new MisEmpresasModel();
        $empresasRaw = $modelo->getEmpresasUsuario($userId);
        $empresas = [];

        // Imagen por defecto para empresas (un edificio/compañía)
        $defaultCompanyImage = 'https://cdn-icons-png.flaticon.com/512/3061/3061341.png';

        foreach ($empresasRaw as $e) {
            $e['logo_url'] = $defaultCompanyImage;
            
            if (!empty($e['logo_ruta'])) {
                $nombreArchivo = basename($e['logo_ruta']);
                $rutaRelativa = 'assets/images/Uploads/logos_empresa/' . $nombreArchivo;
                $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'logos_empresa/' . $nombreArchivo;
                $rutaPublica  = rtrim(BASE_URL, '/') . '/assets/images/Uploads/logos_empresa/' . $nombreArchivo;
                
                if (file_exists($rutaAbsoluta)) {
                    $e['logo_url'] = $rutaPublica;
                }
            }
            $empresas[] = $e;
        }

        // Si se envió el formulario de selección de empresa (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empresa_id'])) {
            $empresaId = (int)$_POST['empresa_id'];
            
            // Validar que realmente pertenece a esa empresa
            $pertence = false;
            foreach ($empresas as $e) {
                if ($e['id_empresa'] == $empresaId) {
                    $pertence = true;
                    break;
                }
            }

            if ($pertence) {
                $_SESSION['id_empresa'] = $empresaId;
                
                // Cargar rol específico en la empresa seleccionada
                $db = getDbConnection();
                $stmt2 = $db->prepare("SELECT id_rol_empresa FROM usuario_empresa WHERE id_usuario = ? AND id_empresa = ?");
                $stmt2->execute([$userId, $empresaId]);
                $_SESSION['id_rol_empresa'] = (int)$stmt2->fetchColumn();

                header("Location: " . BASE_URL . "mi_empresa");
                exit();
            } else {
                $_SESSION['mensaje'] = "Error: no perteneces a la empresa seleccionada.";
            }
        }

        // Variables para el navbar
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRolEmpresa = $_SESSION['id_rol_empresa'] ?? null;
        $esAdminEmpresa = in_array($userRolEmpresa, [1, 2]);

        $defaultImage = 'https://static.thenounproject.com/png/4154905-200.png';
        $profileImage = $defaultImage;
        if (!empty($_SESSION['foto_perfil'])) {
            $rutaAbsoluta = ROOT_PATH . 'assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
            $rutaPublica  = BASE_URL . 'assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
            }
        }
        $latestNotifications = [];
        $unreadNotificationsCount = 0;

        require_once __DIR__ . '/../../views/EmpresasView/mis_empresas_view.php';
    }
}
?>
