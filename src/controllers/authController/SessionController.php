<?php
// src/controllers/authController/SessionController.php
require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/userModel/User.php';

class SessionController {
    /** Expulsa al usuario si su estado en DB no es 'activo'. */
    public static function enforceActive(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        // Si no hay sesión, nada que validar
        if (empty($_SESSION['user_id'])) return;

        $userModel = new User();
        $userData = $userModel->getUserById((int)$_SESSION['user_id']);

        // Si no se encontró el usuario o está bloqueado
        if (!$userData || ($userData['estado'] ?? '') !== 'Activo') {
            session_regenerate_id(true);
            session_unset();
            session_destroy();
            session_start();

            $_SESSION['pending_logout'] = true;
            $_SESSION['flash_error'] = 'Tu cuenta fue bloqueada.';

            header('Location: ' . BASE_URL . 'src/index.php?form=login&status=error');
            exit;
        }
    }

    /** Cierra sesión y lleva al login con mensaje. */
    public function logout(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        session_regenerate_id(true);
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash_success'] = 'Sesión cerrada correctamente.';

        header('Location: ' . BASE_URL . 'src/index.php?form=login&status=success');
        exit;
    }
}