<?php
require_once __DIR__ . '/config/configuracionInicial.php';
require_once __DIR__ . '/controllers/authController/AuthController.php';

$controller = new AuthController();
$action = $_GET['action'] ?? 'welcome';
$status = $_GET['status'] ?? null;
$form = $_GET['form'] ?? null;

switch ($action) {
    case 'login':
        $controller->login();
        break;
    case 'register':
        $controller->register();
        break;
    case 'welcome':
    default:
        $controller->showWelcomePage($form, $status);
        break;
}