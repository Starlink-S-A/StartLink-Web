<?php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/userModel/User.php'; // Asumiendo que existe
require_once __DIR__ . '/../authController/SessionController.php';
SessionController::enforceActive();

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

        try {
            $stmtRolTrabajador = $conexion->prepare("SELECT id FROM ROL WHERE nombre_rol = 'TRABAJADOR'");
            $stmtRolTrabajador->execute();
            $rolTrabajadorId = $stmtRolTrabajador->fetchColumn();

            $stmtUserGlobalRole = $conexion->prepare("SELECT id_rol FROM USUARIO WHERE id = ?");
            $stmtUserGlobalRole->execute([$userId]);
            $currentUserGlobalRole = $stmtUserGlobalRole->fetchColumn();

            if ($currentUserGlobalRole != $rolTrabajadorId) {
                $showPublishProfileLink = true;
            }

            if (isset($_SESSION['id_empresa']) && isset($_SESSION['id_rol_empresa'])) {
                $userCompanyRole = $_SESSION['id_rol_empresa'];
                if (in_array($userCompanyRole, [1, 2])) {
                    $esAdminEmpresa = true;
                }
                if (in_array($userCompanyRole, [2, 3])) {
                    $esTrabajadorActivo = true;
                }
            } else {
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
                    $_SESSION['id_empresa'] = $userCompanyAssociation['id_empresa'];
                    $_SESSION['id_rol_empresa'] = $userCompanyAssociation['id_rol_empresa'];
                    $userCompanyRole = $userCompanyAssociation['id_rol_empresa'];
                    if (in_array($userCompanyRole, [1, 2])) {
                        $esAdminEmpresa = true;
                    }
                    if (in_array($userCompanyRole, [2, 3])) {
                        $esTrabajadorActivo = true;
                    }
                }
            }

            if ($userRoleGlobal == $rolTrabajadorId) {
                $esTrabajadorActivo = true;
            }

            $unreadNotificationsCount = 0;
            $latestNotifications = [];
            $stmtNotifications = $conexion->prepare("
                SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion, postulacion_id, solicitud_contratacion_id
                FROM NOTIFICACIONES
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
            elseif ($userRolEmpresa == 3) $rolDisplay = "Empleado Interno";
            else $rolDisplay = "Usuario General";

        } catch (PDOException $e) {
            error_log("Error en DashboardController: " . $e->getMessage());
            $esTrabajadorActivo = ($userRoleGlobal == 3 || in_array($userRolEmpresa, [2, 3]));
        }

        $defaultImage = 'https://static.thenounproject.com/png/4154905-200.png';
        $profileImage = $defaultImage;
        if (!empty($_SESSION['foto_perfil'])) {
            $rutaAbsoluta = ROOT_PATH . '/assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
            $rutaPublica = BASE_URL . 'assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
            }
        }

        require_once __DIR__ . '/../../views/dashboardView/dashboard_view.php';
    }

    private function isProfileComplete($userId) {
        return isProfileComplete($userId); // Usa la función global si existe
    }
}
?>