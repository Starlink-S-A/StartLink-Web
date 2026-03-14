<?php
// api/auth.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // ⚠️ Solo para pruebas, en producción usa tu dominio
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

require_once __DIR__ . '/../src/config/configuraciónInicial.php';
require_once __DIR__ . '/../src/controllers/AuthController.php';

$controller = new AuthController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

if ($method === 'OPTIONS') {
    // Preflight para CORS
    http_response_code(200);
    exit;
}

if ($action) {
    switch ($action) {
        case 'requestPasswordReset':
            $controller->forgotPassword();
            break;

        case 'verifyResetCode':
            $controller->verifyResetCode();
            break;

        case 'resetPassword':
            $controller->resetPassword();
            break;

        case 'user':
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $userId = (int) $_GET['id'];
                echo json_encode($controller->getUserById($userId));
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID de usuario requerido']);
            }
            break;

        case 'users':
            echo json_encode($controller->getAllUsers());
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se especificó ninguna acción']);
}
exit;
