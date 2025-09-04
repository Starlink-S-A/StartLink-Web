<?php
// src/controllers/authController/AuthController.php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/userController/User.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $userModel;
    const JWT_SECRET_KEY = 'tu_clave_secreta_super_segura_aqui';

    public function __construct() {
        $this->userModel = new User();
    }

    public function showWelcomePage($form = null, $status = null) {
        $form_to_show = $form ?: 'welcome';
        if ($status === 'success' && $form === 'register') {
            $form_to_show = 'login';
        } elseif ($status === 'error' && in_array($form, ['login', 'register'])) {
            $form_to_show = $form;
        }
        require_once __DIR__ . '/../../views/paginaprincipalView/bienvenida.php';
    }

    public function login() {
        header('Content-Type: application/json');
        $response = ['status' => 'error', 'message' => 'Ocurrió un error desconocido.'];

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $response['message'] = "Acceso no permitido. Este script solo acepta solicitudes POST.";
            echo json_encode($response);
            exit;
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $email = '';
        $password = '';
        $errors = [];

        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $email = trim($data['email'] ?? '');
            $password = trim($data['password'] ?? '');
        } else {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
        }

        if (empty($email)) $errors[] = "Por favor ingresa tu email.";
        if (empty($password)) $errors[] = "Por favor ingresa tu contraseña.";

        if (empty($errors)) {
            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['contrasena_hash'])) {
                $issuedAt = new DateTimeImmutable();
                $expireAt = $issuedAt->modify('+60 minutes')->getTimestamp();
                $serverName = 'http://localhost';

                $data = [
                    'iat'  => $issuedAt->getTimestamp(),
                    'iss'  => $serverName,
                    'nbf'  => $issuedAt->getTimestamp(),
                    'exp'  => $expireAt,
                    'uid'  => $user['id'],
                    'name' => $user['nombre'],
                    'email' => $user['email'] ?? null,
                    'rol' => $user['id_rol']
                ];

                $token = JWT::encode($data, self::JWT_SECRET_KEY, 'HS256');

                $response = [
                    'status' => 'success',
                    'message' => "¡Bienvenido, " . htmlspecialchars($user['nombre']) . "!",
                    'token' => $token,
                    'data' => [
                        'redirect' => BASE_URL . 'src/dashboard.php'
                    ]
                ];
            } else {
                $errors[] = $user ? "La contraseña es incorrecta." : "No se encontró ninguna cuenta con ese email.";
            }
        }

        if (!empty($errors)) {
            $response['message'] = implode(" ", $errors);
        }

        echo json_encode($response);
        exit;
    }

    public function validateToken() {
        header('Content-Type: application/json');
        $response = ['status' => 'error', 'message' => 'Token no proporcionado o inválido.'];
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            try {
                $decoded = JWT::decode($token, new Key(self::JWT_SECRET_KEY, 'HS256'));
                $response = [
                    'status' => 'success',
                    'message' => 'Token válido.',
                    'data' => (array) $decoded
                ];
            } catch (Exception $e) {
                $response['message'] = 'Token inválido: ' . $e->getMessage();
            }
        }
        echo json_encode($response);
        exit;
    }

public function register() {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Ocurrió un error al registrarse.'];

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        $response['message'] = "Acceso no permitido. Solo solicitudes POST.";
        echo json_encode($response);
        exit;
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $nombre = trim($data['nombre'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');
    $confirm_password = trim($data['confirm_password'] ?? '');
    $recaptchaToken = trim($data['recaptcha_token'] ?? '');
    $errors = [];

    // Validaciones básicas
    if (!$nombre) $errors[] = "Por favor ingresa tu nombre.";
    if (!$email) $errors[] = "Por favor ingresa tu email.";
    if (!$password) $errors[] = "Por favor ingresa tu contraseña.";
    if ($password !== $confirm_password) $errors[] = "Las contraseñas no coinciden.";
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El email no es válido.";

    // Validación reCAPTCHA
    if (!$recaptchaToken) {
        $errors[] = "Falta la validación de seguridad (reCAPTCHA).";
    } else {
        $secretKey = "6Ldq87srAAAAAOdTe2F8-lbhqYfYRp586foWy_MH"; 
        $verifyURL = "https://www.google.com/recaptcha/api/siteverify?" . http_build_query([
            'secret' => $secretKey,
            'response' => $recaptchaToken
        ]);

        $verifyResponse = @file_get_contents($verifyURL);
        $captchaResult = json_decode($verifyResponse, true);

        error_log('Token reCAPTCHA recibido: ' . $recaptchaToken);
        error_log('Resultado verificación reCAPTCHA: ' . json_encode($captchaResult));

        if (empty($captchaResult['success'])) {
            $errors[] = "No se pudo verificar el reCAPTCHA. Inténtalo de nuevo.";
        }
    }

    if (empty($errors)) {
        try {
            $pdo = getDbConnection();

            // Verificar email rápido
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuario WHERE email = :email");
            $stmt->execute([':email' => $email]);

            if ($stmt->fetchColumn() > 0) {
                $errors[] = "El email ya está registrado.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO usuario (nombre, email, contrasena_hash, id_rol, fecha_registro)
                    VALUES (:nombre, :email, :contrasena_hash, 2, NOW())
                ");
                $stmt->execute([
                    ':nombre' => $nombre,
                    ':email' => $email,
                    ':contrasena_hash' => $password_hash
                ]);

                $response = [
                    'status' => 'success',
                    'message' => "Registro exitoso. Ahora puedes iniciar sesión.",
                    'data' => ['email' => $email]
                ];
            }
        } catch (PDOException $e) {
            error_log('Error en el registro: ' . $e->getMessage());
            $errors[] = "Error al registrar el usuario.";
        }
    }

    if (!empty($errors)) {
        $response['message'] = implode(" ", $errors);
    }

    echo json_encode($response);
    exit;
}





    public function getUserById($id) {
        try {
            $user = $this->userModel->getUserById($id);
            if ($user) {
                return ['status' => 'success', 'data' => $user];
            } else {
                return ['status' => 'error', 'message' => 'Usuario no encontrado'];
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error al consultar el usuario: ' . $e->getMessage()];
        }
    }

    public function getAllUsers() {
        try {
            $users = $this->userModel->getAllUsers();
            return ['status' => 'success', 'data' => $users];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Error al consultar los usuarios: ' . $e->getMessage()];
        }
    }
}
