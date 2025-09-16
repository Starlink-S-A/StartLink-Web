<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/userModel/User.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Página de bienvenida
    public function showWelcomePage($form = null, $status = null) {
        $form_to_show = $form ?: 'welcome';
        if ($status === 'success' && $form === 'register') {
            $form_to_show = 'login';
        } elseif ($status === 'error' && in_array($form, ['login', 'register'])) {
            $form_to_show = $form;
        }
        require_once __DIR__ . '/../../views/paginaprincipalView/bienvenida.php';
    }

    // ---------------------------
    // 🔹 LOGIN (con JWT y reCAPTCHA)
    // ---------------------------
    public function login() {
        header('Content-Type: application/json');
        $response = ['status' => 'error', 'message' => 'Ocurrió un error desconocido.'];

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $response['message'] = "Acceso no permitido. Solo POST.";
            echo json_encode($response);
            exit;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $recaptchaToken = $data['recaptcha_token'] ?? '';

        // Validar reCAPTCHA
        if (!$this->validateRecaptcha($recaptchaToken)) {
            $response['message'] = "Error de seguridad. Intenta de nuevo.";
            echo json_encode($response);
            exit;
        }

        $errors = [];
        if (empty($email)) $errors[] = "Por favor ingresa tu email.";
        if (empty($password)) $errors[] = "Por favor ingresa tu contraseña.";

        if (empty($errors)) {
            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['contrasena_hash'])) {
                // Establecer variables de sesión
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION["user_id"] = $user['id'];
                $_SESSION["user_name"] = $user['nombre'];
                $_SESSION["email"] = $user['email'];
                $_SESSION["loggedin"] = true;
                $_SESSION["id_rol"] = $user['id_rol'];
                $_SESSION["id_empresa"] = $user['id_empresa'] ?? null;
                $_SESSION["id_rol_empresa"] = $user['id_rol_empresa'] ?? null;

                // Debug: Log session data to verify
                error_log("Session after login: " . print_r($_SESSION, true));

                // Generar token JWT
                $issuedAt = new DateTimeImmutable();
                $expireAt = $issuedAt->modify('+60 minutes')->getTimestamp();
                $serverName = BASE_URL;

                $payload = [
                    'iat'  => $issuedAt->getTimestamp(),
                    'iss'  => $serverName,
                    'nbf'  => $issuedAt->getTimestamp(),
                    'exp'  => $expireAt,
                    'uid'  => $user['id'],
                    'name' => $user['nombre'],
                    'email' => $user['email'],
                    'rol'  => $user['id_rol']
                ];

                $token = JWT::encode($payload, JWT_SECRET_KEY, 'HS256');

                $response = [
                    'status' => 'success',
                    'message' => "¡Bienvenido, " . htmlspecialchars($user['nombre']) . "!",
                    'token' => $token,
                    'data' => [
                        'redirect' => BASE_URL . 'dashboard'
                    ]
                ];
            } else {
                $errors[] = $user ? "La contraseña es incorrecta." : "No existe una cuenta con ese correo.";
            }
        }

        if (!empty($errors)) {
            $response['message'] = implode(" ", $errors);
        }

        echo json_encode($response);
        exit;
    }

    // ---------------------------
    // 🔹 Logout
    // ---------------------------
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "bienvenida.php");
        exit();
    }

    // ---------------------------
    // 🔹 Validar token
    // ---------------------------
    public function validateToken() {
        header('Content-Type: application/json');
        $response = ['status' => 'error', 'message' => 'Token no proporcionado o inválido.'];
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);
            try {
                $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
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

    // ---------------------------
    // 🔹 Registro
    // ---------------------------
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

            if (empty($captchaResult['success']) || $captchaResult['score'] < 0.5) {
                $errors[] = "No se pudo verificar el reCAPTCHA. Inténtalo de nuevo.";
            }
        }

        if (empty($errors)) {
            try {
                $pdo = getDbConnection();

                // Verificar email único
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM USUARIO WHERE email = :email");
                $stmt->execute([':email' => $email]);

                if ($stmt->fetchColumn() > 0) {
                    $errors[] = "El email ya está registrado.";
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO USUARIO (nombre, email, contrasena_hash, id_rol, fecha_registro)
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

    // ---------------------------
    // 🔹 Recuperar contraseña
    // ---------------------------
    public function forgotPassword() {
        header('Content-Type: application/json');
        $response = ['status' => 'error', 'message' => 'Ocurrió un error.'];

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $email = trim($data['email'] ?? '');

        if (!$email) {
            echo json_encode(['status' => 'error', 'message' => 'Debes ingresar tu correo.']);
            exit;
        }

        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT id, nombre FROM USUARIO WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                echo json_encode(['status' => 'error', 'message' => 'Correo no registrado.']);
                exit;
            }

            // Generar código de verificación (6 dígitos)
            $resetCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expire = date("Y-m-d H:i:s", strtotime("+15 minutes"));

            // Guardar en la base de datos
            $update = $pdo->prepare("UPDATE USUARIO 
                                     SET reset_code = :code, reset_code_expire = :expire 
                                     WHERE id = :id");
            $update->execute([
                ':code' => $resetCode,
                ':expire' => $expire,
                ':id' => $user['id']
            ]);

            // Enviar correo con PHPMailer
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jguaza999@gmail.com';
            $mail->Password = 'vitq cwse yphr ssce';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Remitente y destinatario
            $mail->setFrom('jguaza999@gmail.com', 'TalentLink');
            $mail->addAddress($email, $user['nombre']);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Recuperar contraseña - TalentLink';
            $mail->Body = "
                <p>Hola <b>" . htmlspecialchars($user['nombre']) . "</b>,</p>
                <p>Tu código de recuperación es: <b>$resetCode</b></p>
                <p>Este código expira en 15 minutos.</p>
            ";

            if ($mail->send()) {
                echo json_encode(['status' => 'success', 'message' => 'Se envió un código de recuperación a tu correo.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo enviar el correo: ' . $mail->ErrorInfo]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
            exit;
        }
    }

    // ---------------------------
    // 🔹 Resetear contraseña
    // ---------------------------
    public function resetPassword() {
        header('Content-Type: application/json');
        $response = ['status' => 'error', 'message' => 'Error al resetear.'];

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $token = trim($data['token'] ?? '');
        $newPassword = trim($data['new_password'] ?? '');

        if (!$token || !$newPassword) {
            $response['message'] = "Datos incompletos.";
            echo json_encode($response);
            exit;
        }

        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT id, reset_code_expire FROM USUARIO WHERE reset_code = :code LIMIT 1");
            $stmt->execute([':code' => $token]);
            $user = $stmt->fetch();

            if (!$user) {
                $response['message'] = "El código no es válido.";
                echo json_encode($response);
                exit;
            }

            if (strtotime($user['reset_code_expire']) < time()) {
                $response['message'] = "El código ha expirado.";
                echo json_encode($response);
                exit;
            }

            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE USUARIO 
                                     SET contrasena_hash = :password, reset_code = NULL, reset_code_expire = NULL 
                                     WHERE id = :id");
            $update->execute([
                ':password' => $passwordHash,
                ':id' => $user['id']
            ]);

            $response = [
                'status' => 'success',
                'message' => "Contraseña actualizada."
            ];
        } catch (Exception $e) {
            error_log("Error resetPassword: " . $e->getMessage());
            $response['message'] = "Error interno: " . $e->getMessage();
        }

        echo json_encode($response);
        exit;
    }

    // ---------------------------
    // 🔹 Verificación reCAPTCHA
    // ---------------------------
    private function validateRecaptcha($token) {
        if (empty($token)) {
            error_log('Token reCAPTCHA vacío');
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $token
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        if ($result === FALSE) {
            error_log('Error al conectar con el servicio reCAPTCHA: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        $response = json_decode($result, true);
        error_log('Respuesta de reCAPTCHA: ' . print_r($response, true));

        $isValid = $response['success'] && $response['score'] >= 0.5;

        if (!$isValid) {
            error_log('Validación reCAPTCHA fallida: ' . print_r($response, true));
        }

        return $isValid;
    }
}
?>