<?php
// src/controllers/authController/AuthController.php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/userController/User.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /* ============================
       📌 LOGIN
    ============================ */
    public function login() {
        header('Content-Type: application/json');
        $response = ['status' => 'error', 'message' => 'Ocurrió un error desconocido.'];

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $response['message'] = "Acceso no permitido.";
            echo json_encode($response);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $recaptchaToken = $data['recaptcha_token'] ?? '';

        if (!$this->validateRecaptcha($recaptchaToken)) {
            $response['message'] = "Error de verificación de seguridad.";
            echo json_encode($response);
            exit;
        }

        $errors = [];
        if (empty($email)) $errors[] = "Por favor ingresa tu email.";
        if (empty($password)) $errors[] = "Por favor ingresa tu contraseña.";

        if (empty($errors)) {
            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['contrasena_hash'])) {
                $_SESSION["user_id"] = $user['id'];
                $_SESSION["user_name"] = $user['nombre'];
                $_SESSION["loggedin"] = true;
                $_SESSION["id_rol"] = $user['id_rol'];
                $_SESSION["email"] = $user['email'] ?? null;

                session_write_close();

                $issuedAt = new DateTimeImmutable();
                $expireAt = $issuedAt->modify('+60 minutes')->getTimestamp();

                $token = JWT::encode([
                    'iat' => $issuedAt->getTimestamp(),
                    'exp' => $expireAt,
                    'uid' => $user['id'],
                    'name' => $user['nombre'],
                    'email' => $user['email'] ?? null,
                    'rol' => $user['id_rol']
                ], JWT_SECRET_KEY, 'HS256');

                $response = [
                    'status' => 'success',
                    'message' => "¡Bienvenido, " . htmlspecialchars($user['nombre']) . "!",
                    'token' => $token,
                    'data' => [
                        'redirect' => BASE_URL . 'src/views/dashboardView/dashboard.php'
                    ]
                ];
            } else {
                $errors[] = $user ? "La contraseña es incorrecta." : "No se encontró ninguna cuenta con ese email.";
            }
        }

        if (!empty($errors)) $response['message'] = implode(" ", $errors);

        echo json_encode($response);
        exit;
    }

    /* ============================
       📌 REGISTRO
    ============================ */
    public function register() {
        header('Content-Type: application/json');
        $response = ['status' => 'error'];

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $response['message'] = "Acceso no permitido.";
            echo json_encode($response);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $nombre = trim($data['nombre'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $confirm_password = trim($data['confirm_password'] ?? '');
        $recaptchaToken = trim($data['recaptcha_token'] ?? '');
        $errors = [];

        if (!$nombre) $errors[] = "Por favor ingresa tu nombre.";
        if (!$email) $errors[] = "Por favor ingresa tu email.";
        if (!$password) $errors[] = "Por favor ingresa tu contraseña.";
        if ($password !== $confirm_password) $errors[] = "Las contraseñas no coinciden.";
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El email no es válido.";

        if (!$this->validateRecaptcha($recaptchaToken)) {
            $errors[] = "No se pudo verificar el reCAPTCHA.";
        }

        if (empty($errors)) {
            try {
                $pdo = getDbConnection();

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
                        'message' => "Registro exitoso. Ahora puedes iniciar sesión."
                    ];
                }
            } catch (PDOException $e) {
                $errors[] = "Error al registrar el usuario.";
            }
        }

        if (!empty($errors)) $response['message'] = implode(" ", $errors);

        echo json_encode($response);
        exit;
    }

    /* ============================
       📌 RECUPERACIÓN DE CONTRASEÑA
    ============================ */
    public function requestPasswordReset() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $response = ['status' => 'error'];

        if (!$email) {
            $response['message'] = "Debes ingresar tu email.";
            echo json_encode($response); exit;
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $response['message'] = "No existe una cuenta con ese email.";
            echo json_encode($response); exit;
        }

        $code = random_int(100000, 999999);
        $expire = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $stmt = $pdo->prepare("UPDATE usuario SET reset_code=:code, reset_code_expire=:expire WHERE id=:id");
        $stmt->execute([':code' => $code, ':expire' => $expire, ':id' => $user['id']]);

        if ($this->sendResetEmail($email, $code)) {
            $response = ['status' => 'success', 'message' => "Se envió un código de recuperación a tu correo."];
        } else {
            $response['message'] = "Error al enviar el correo.";
        }

        echo json_encode($response);
        exit;
    }

    public function verifyResetCode() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $code = trim($data['code'] ?? '');
        $response = ['status' => 'error'];

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT reset_code, reset_code_expire FROM usuario WHERE email=:email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && $user['reset_code'] === $code && strtotime($user['reset_code_expire']) > time()) {
            $response = ['status' => 'success', 'message' => "Código válido."];
        } else {
            $response['message'] = "El código es incorrecto o expiró.";
        }

        echo json_encode($response);
        exit;
    }

    public function resetPassword() {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        $code = trim($data['code'] ?? '');
        $password = trim($data['password'] ?? '');
        $response = ['status' => 'error'];

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id, reset_code, reset_code_expire FROM usuario WHERE email=:email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && $user['reset_code'] === $code && strtotime($user['reset_code_expire']) > time()) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuario SET contrasena_hash=:pass, reset_code=NULL, reset_code_expire=NULL WHERE id=:id");
            $stmt->execute([':pass' => $hash, ':id' => $user['id']]);

            $response = ['status' => 'success', 'message' => "Contraseña actualizada correctamente."];
        } else {
            $response['message'] = "El código es inválido o expiró.";
        }

        echo json_encode($response);
        exit;
    }

    /* ============================
       📌 FUNCIONES AUXILIARES
    ============================ */
    private function validateRecaptcha($token) {
        if (empty($token)) return false;
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = ['secret' => RECAPTCHA_SECRET_KEY, 'response' => $token];
        $res = file_get_contents($url . '?' . http_build_query($data));
        $result = json_decode($res, true);
        return $result['success'] && $result['score'] >= 0.5;
    }

    private function sendResetEmail($to, $code) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // ⚡ Cambia por tu SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'tu_correo@gmail.com';
            $mail->Password = 'tu_contraseña_app';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('no-reply@tuapp.com', 'Soporte');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de contraseña';
            $mail->Body = "<p>Tu código de recuperación es: <b>$code</b></p><p>Válido por 15 minutos.</p>";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            return false;
        }
    }

    /* ============================
       📌 MÉTODOS DE CONSULTA
    ============================ */
    public function getUserById($id) {
        try {
            $user = $this->userModel->getUserById($id);
            return $user ? ['status' => 'success', 'data' => $user] : ['status' => 'error', 'message' => 'Usuario no encontrado'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getAllUsers() {
        try {
            $users = $this->userModel->getAllUsers();
            return ['status' => 'success', 'data' => $users];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
?>
