<?php
// src/index.php

require_once __DIR__ . '/controllers/authController/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $controller = new AuthController();
        $controller->login();
        break;

    case 'register':
        $controller = new AuthController();
        $controller->register();
        break;

    case 'forgotPassword':   // ✅ Solicitar enlace de recuperación
        $controller = new AuthController();
        $controller->forgotPassword();
        break;

    case 'resetPassword':    // ✅ Restablecer la contraseña con token
        $controller = new AuthController();
        $controller->resetPassword();
        break;

    case 'dashboard':
        $controller = new DashboardController();
        $controller->showDashboard();
        break;

    default:
        $controller = new AuthController();
        $controller->showWelcomePage();
        break;

        case 'forgotPassword':
    $controller = new AuthController();
    $controller->forgotPassword();
    break;
case 'resetPassword':
    $controller = new AuthController();
    $controller->resetPassword();
    break;

} 
