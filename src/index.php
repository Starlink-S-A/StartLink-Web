<?php
// src/index.php

require_once __DIR__ . '/controllers/authController/AuthController.php';
require_once __DIR__ . '/controllers/dashboardController/DashboardController.php';
require_once __DIR__ . '/controllers/configuracionusuarioController/UserController.php';

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
    case 'dashboard':
        $controller = new DashboardController();
        $controller->showDashboard();
        break;
    case 'configurar_perfil':
        $controller = new UserController();
        $controller->configureProfile();
        break;
    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;
    case 'gestionar_usuarios':
        require_once __DIR__ . '/controllers/controller_gest_u/controller_gest_u.php';
        break;
    case 'gestionar_usuarios':
    // Si quieres que el RolController también cargue la vista:
        (new RolController())->index();
        break;
    case 'actualizar_rol':
        (new RolController())->updateUserRole();
        break;
    default:
        $controller = new AuthController();
        $controller->showWelcomePage();
        break;
}
?>  