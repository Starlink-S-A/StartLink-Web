<?php
// src/controllers/DashboardController.php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/userModel/User.php'; // Asumiendo que existe
require_once __DIR__ . '/../ofertasController/ofertasController.php';

class DashboardController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User(); // Ajusta según tu modelo
    }

    public function showDashboard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL . "bienvenida.php");
            exit();
        }

        $userId = $_SESSION["user_id"];
        $userName = $_SESSION["user_name"] ?? 'Usuario';
        $userRoleGlobal = $_SESSION["id_rol"] ?? 2;
        $userEmpresaId = $_SESSION["id_empresa"] ?? null;
        $userRolEmpresa = $_SESSION["id_rol_empresa"] ?? null;

        // Recuperar mensajes de sesión
        $message = $_SESSION['message'] ?? '';
        $message_type = $_SESSION['message_type'] ?? '';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);

        $conexion = getDbConnection();

        $profileIsComplete = $this->isProfileComplete($userId);
        $showProfileIncompleteBanner = !$profileIsComplete;
        $_SESSION['profile_completed'] = $profileIsComplete;

        $esAdminEmpresa = false;
        $esTrabajadorActivo = false;
        $showPublishProfileLink = false;
        $dbFotoPerfil = null;

        try {
            $stmtRolTrabajador = $conexion->prepare("SELECT id FROM rol WHERE nombre_rol = 'TRABAJADOR'");
            $stmtRolTrabajador->execute();
            $rolTrabajadorId = $stmtRolTrabajador->fetchColumn();

            $stmtUser = $conexion->prepare("SELECT id_rol, foto_perfil FROM usuario WHERE id = ?");
            $stmtUser->execute([$userId]);
            $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $currentUserGlobalRole = $userRow['id_rol'] ?? $rolTrabajadorId;
            $dbFotoPerfil = $userRow['foto_perfil'] ?? null;
            
            // [Sincronización] Actualizar rol global en sesión
            $_SESSION['id_rol'] = (int)$currentUserGlobalRole;

            if ($currentUserGlobalRole != $rolTrabajadorId) {
                $showPublishProfileLink = true;
            }

            // [Sincronización] Siempre obtener el rol más reciente de la empresa desde la BD
            $stmtCompanyAssociation = $conexion->prepare("
                SELECT id_empresa, id_rol_empresa 
                FROM usuario_empresa 
                WHERE id_usuario = ? 
                AND (id_empresa = ? OR ? IS NULL)
                ORDER BY id_rol_empresa ASC 
                LIMIT 1
            ");
            $stmtCompanyAssociation->execute([$userId, $_SESSION['id_empresa'] ?? null, $_SESSION['id_empresa'] ?? null]);
            $userCompanyAssociation = $stmtCompanyAssociation->fetch(PDO::FETCH_ASSOC);

            if ($userCompanyAssociation) {
                $_SESSION['id_empresa'] = (int)$userCompanyAssociation['id_empresa'];
                $_SESSION['id_rol_empresa'] = (int)$userCompanyAssociation['id_rol_empresa'];
                $userCompanyRole = (int)$userCompanyAssociation['id_rol_empresa'];
            } else {
                $userCompanyRole = null;
            }

            if ($userCompanyRole !== null) {
                if (in_array($userCompanyRole, [1, 2])) {
                    $esAdminEmpresa = true;
                }
                if (in_array($userCompanyRole, [2, 3])) {
                    $esTrabajadorActivo = true;
                }
            }

            if ($userRoleGlobal == $rolTrabajadorId) {
                $esTrabajadorActivo = true;
            }

            $unreadNotificationsCount = 0;
            $latestNotifications = [];
            $stmtNotifications = $conexion->prepare("
                SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion, postulacion_id, solicitud_contratacion_id
                FROM notificaciones
                WHERE user_id = ?
                ORDER BY fecha_creacion DESC
                LIMIT 5
            ");
            $stmtNotifications->execute([$userId]);
            $latestNotifications = $stmtNotifications->fetchAll(PDO::FETCH_ASSOC);

            foreach ($latestNotifications as &$notification) {
                if (empty($notification['icono'])) {
                    switch ($notification['tipo']) {
                        case 'success': $notification['icono'] = 'fas fa-check-circle text-success'; break;
                        case 'warning': $notification['icono'] = 'fas fa-exclamation-triangle text-warning'; break;
                        case 'error': $notification['icono'] = 'fas fa-times-circle text-danger'; break;
                        default: $notification['icono'] = 'fas fa-info-circle text-info'; break;
                    }
                } elseif (strpos($notification['icono'], 'text-') === false) {
                    $notification['icono'] .= ' text-primary';
                }
                if (!$notification['leida']) {
                    $unreadNotificationsCount++;
                }
            }

            $rolDisplay = "";
            if ($userRoleGlobal == 1) $rolDisplay = "Administrador del Sistema";
            elseif ($userRolEmpresa == 1) $rolDisplay = "Administrador de Empresa";
            elseif ($userRolEmpresa == 2) $rolDisplay = "Contratador";
            elseif ($userRolEmpresa == 3) $rolDisplay = "Empleado";
            else $rolDisplay = "Usuario General";

        } catch (PDOException $e) {
            error_log("Error en DashboardController: " . $e->getMessage());
            $esTrabajadorActivo = ($userRoleGlobal == 3 || in_array($userRolEmpresa, [2, 3]));
        }

        // --- LOGICA DE RESOLUCION DE IMAGEN DE PERFIL (Consistente con misEmpresasController) ---
        $profileImage = 'https://static.thenounproject.com/png/4154905-200.png';

        // 1. Prioridad: Sesión | 2. Fallback: Base de Datos
        $fotoPath = !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : ($dbFotoPerfil ?? null);

        if (!empty($fotoPath)) {
            $nombreArchivo = basename($fotoPath);
            // Normalizar rutas usando DIRECTORY_SEPARATOR para file_exists
            $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $nombreArchivo;
            $rutaPublica  = rtrim(BASE_URL, '/') . '/assets/images/Uploads/profile_pictures/' . $nombreArchivo;

            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
                // Sincronizar sesión si vino de la BD
                if (empty($_SESSION['foto_perfil'])) {
                    $_SESSION['foto_perfil'] = 'assets/images/Uploads/profile_pictures/' . $nombreArchivo;
                }
            }
        }

        require_once __DIR__ . '/../../views/dashboardView/dashboard_view.php';
    }

    public function showOfertas() {
        // Delegar a OfertasController
        $ofertasController = new OfertasController();
        $ofertasController->showOfertas();
    }

    private function isProfileComplete($userId) {
        return isProfileComplete($userId); // Usa la función global si existe
    }
}
?>