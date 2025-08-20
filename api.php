<?php
// api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Para pruebas, ajusta en producción
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
require_once __DIR__ . '/src/config/configuraciónInicial.php';
require_once __DIR__ . '/src/controllers/AuthController.php';

$controller = new AuthController();
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

if ($path[0] === 'api' && isset($path[1])) {
    switch ($path[1]) {
        case 'user':
            if (isset($path[2]) && is_numeric($path[2])) {
                $userId = $path[2];
                $user = $controller->getUserById($userId);
                echo json_encode($user);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'ID de usuario requerido']);
            }
            break;
        case 'users':
            $users = $controller->getAllUsers();
            echo json_encode($users);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Endpoint no válido']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ruta no encontrada']);
}
exit;