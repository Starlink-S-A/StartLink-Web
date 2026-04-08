<?php
// src/controllers/adminGlobalController/AdminGlobalController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/adminGlobalModel/AdminGlobalModel.php';

class AdminGlobalController {
    private $adminModel;

    public function __construct() {
        $this->adminModel = new AdminGlobalModel();
    }

    public function index() {
        // Validación estricta de Administrador Global (rol = 1)
        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true || (int)$_SESSION["id_rol"] !== 1) {
            header("Location: " . BASE_URL . "src/index.php?action=dashboard");
            exit();
        }

        $metrics = $this->adminModel->getAllUsersMetrics();
        $users = $this->adminModel->getAllUsersWithCompanyInfo();
        
        $empresasMetrics = $this->adminModel->getAllEmpresasMetrics();
        $empresas = $this->adminModel->getAllEmpresas();

        require_once __DIR__ . '/../../views/adminGlobalView/admin_view.php';
    }

    public function updateRole() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true || (int)$_SESSION["id_rol"] !== 1) {
            echo json_encode(["status" => "error", "message" => "Acceso no autorizado"]);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['userId']) || !isset($input['newRoleId'])) {
            echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
            exit();
        }

        // Evitar que el admin se quite su propio rol
        if ((int)$input['userId'] === (int)$_SESSION['user_id']) {
            echo json_encode(["status" => "error", "message" => "No puedes cambiar tu propio rol global directamente."]);
            exit();
        }

        $success = $this->adminModel->changeUserRole($input['userId'], $input['newRoleId']);

        if ($success) {
            // Generar Notificación al Usuario Afectado
            require_once __DIR__ . '/../../models/notifiacionesModel/notificacionesModel.php';
            $notifModel = new NotificacionesModel();
            
            $rolNombres = [1 => "Administrador Global", 2 => "Candidato", 3 => "Empresa / Contratador"];
            $nuevoRolTexto = $rolNombres[(int)$input['newRoleId']] ?? "Nuevo Rol Asignado";
            
            $mensaje = "Tu rol en StartLink ha sido actualizado a: " . $nuevoRolTexto . " por un Administrador del Sistema.";
            $notifModel->crearNotificacion(
                (int)$input['userId'],
                $mensaje,
                'info',
                'fas fa-user-shield',
                BASE_URL . "src/index.php?action=dashboard"
            );

            echo json_encode(["status" => "success", "message" => "Rol global actualizado correctamente. Se ha notificado al usuario."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar rol."]);
        }
        exit();
    }

    public function toggleSuspension() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true || (int)$_SESSION["id_rol"] !== 1) {
            echo json_encode(["status" => "error", "message" => "Acceso no autorizado"]);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['userId']) || !isset($input['action'])) {
            echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
            exit();
        }

        if ((int)$input['userId'] === (int)$_SESSION['user_id']) {
            echo json_encode(["status" => "error", "message" => "No puedes suspender tu propia cuenta."]);
            exit();
        }

        $success = false;
        if ($input['action'] === 'suspend') {
            $success = $this->adminModel->suspendAccount($input['userId']);
        } elseif ($input['action'] === 'activate') {
            $success = $this->adminModel->activateAccount($input['userId']);
        }

        if ($success) {
            echo json_encode(["status" => "success", "message" => "Estado de la cuenta actualizado correctamente."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al cambiar el estado de la cuenta."]);
        }
        exit();
    }
}
?>
