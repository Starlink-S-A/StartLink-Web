<?php
// src/controllers/rol/rolController.php
declare(strict_types=1);

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/rolModel/rolModel.php';
require_once __DIR__ . '/../authController/SessionController.php';

SessionController::enforceActive();

class RolController {
    private RolModel $model;

    public function __construct() {
        $this->model = new RolModel();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Guard reutilizable: exige que el usuario actual sea ADMIN global.
     * Úsalo al inicio de cualquier endpoint sensible (gestión de usuarios, etc.).
     */
    public static function requireAdmin(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $model   = new RolModel();
        $adminId = $model->getAdminRoleId();

        // 1) Verifica rol en sesión (lo setea AuthController en el login)
        $roleInSession = $_SESSION['id_rol'] ?? null;
        $isAdmin = ($roleInSession && (int)$roleInSession === (int)$adminId);

        // 2) Fallback: verifica en DB por si la sesión estuviera desactualizada
        if (!$isAdmin && !empty($_SESSION['user_id'])) {
            $userRole = $model->userRoleId((int)$_SESSION['user_id']);
            $isAdmin  = ((int)$userRole === (int)$adminId);
        }

        if (!$isAdmin) {
            $_SESSION['flash_error'] = 'No tienes permisos para gestionar usuarios.';
            header('Location: ' . BASE_URL . 'dashboard');
            exit();
        }
    }

    /** Ejemplo de listado de usuarios + roles (si quieres centralizar aquí la vista) */
    public function index(): void {
        self::requireAdmin();

        $pdo   = getDbConnection();
        $users = $pdo->query("SELECT id, nombre, email, id_rol, estado FROM USUARIO ORDER BY id DESC")
                     ->fetchAll(PDO::FETCH_ASSOC);
        $roles = $this->model->getAll();

        // Pasa $users y $roles a tu vista (puedes reutilizar tu gest_view.php)
        require __DIR__ . '/../../views/Gestion_usuarios/gest_view.php';
    }

    /** Endpoint para actualizar rol (POST) */
    public function updateUserRole(): void {
        self::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        $userId = (int)($_POST['id_usuario'] ?? 0);
        $roleId = (int)($_POST['id_rol'] ?? 0);

        if ($userId <= 0 || $roleId <= 0) {
            $_SESSION['flash_error'] = 'Datos incompletos.';
            header('Location: ' . BASE_URL . 'gestionar_usuarios');
            exit;
        }

        try {
            $ok = $this->model->assignToUser($userId, $roleId);
            $_SESSION['flash_success'] = $ok ? 'Rol actualizado correctamente.' : 'No se pudo actualizar el rol.';
        } catch (Throwable $e) {
            error_log('updateUserRole error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . 'gestionar_usuarios');
        exit;
    }
}
