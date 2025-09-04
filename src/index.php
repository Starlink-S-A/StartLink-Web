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
    case 'dashboard':
        $controller = new DashboardController();
        $controller->showDashboard();
        break;
    default:
        $controller = new AuthController();
        $controller->showWelcomePage();
        break;
}
