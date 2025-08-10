<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/userController/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showWelcomePage($form = null, $status = null) {
        // Si no se pasa $form, mostrar 'welcome' por defecto
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
        $response = ['success' => false, 'message' => 'Ocurrió un error desconocido.'];

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $response['message'] = "Acceso no permitido. Este script solo acepta solicitudes POST.";
            echo json_encode($response);
            exit;
        }

        $email = trim($_POST["email"] ?? '');
        $password = trim($_POST["password"] ?? '');
        $errors = [];

        if (empty($email)) $errors[] = "Por favor ingresa tu email.";
        if (empty($password)) $errors[] = "Por favor ingresa tu contraseña.";

        if (empty($errors)) {
            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['contrasena_hash'])) {
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $user['id'];
                $_SESSION["user_name"] = $user['nombre'];
                $_SESSION["id_rol"] = $user['id_rol'];
                $_SESSION["foto_perfil"] = $user['foto_perfil'];
                $_SESSION["profile_completed"] = isProfileComplete($user['id']);

                $response['success'] = true;
                $response['message'] = "¡Bienvenido, " . htmlspecialchars($user['nombre']) . "!";
                $response['redirect'] = BASE_URL . "src/dashboard.php";
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

    public function register() {
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Ocurrió un error al registrarse.'];

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $response['message'] = "Acceso no permitido. Este script solo acepta solicitudes POST.";
            echo json_encode($response);
            exit;
        }

        $nombre = trim($_POST["nombre"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $password = trim($_POST["password"] ?? '');
        $errors = [];

        if (empty($nombre)) $errors[] = "Por favor ingresa tu nombre.";
        if (empty($email)) $errors[] = "Por favor ingresa tu email.";
        if (empty($password)) $errors[] = "Por favor ingresa tu contraseña.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El email no es válido.";

        if (empty($errors)) {
            try {
                $pdo = getDbConnection();
                $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = :email");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $errors[] = "El email ya está registrado.";
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO usuario (nombre, email, contrasena_hash, id_rol, fecha_creacion) VALUES (:nombre, :email, :contrasena_hash, 2, NOW())");
                    $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->bindParam(':contrasena_hash', $password_hash, PDO::PARAM_STR);
                    $stmt->execute();

                    $response['success'] = true;
                    $response['message'] = "Registro exitoso. Ahora puedes iniciar sesión.";
                }
            } catch (PDOException $e) {
                error_log('Error en el registro: ' . $e->getMessage());
                $errors[] = "Error al registrar el usuario. Inténtalo de nuevo.";
            }
        }

        if (!empty($errors)) {
            $response['message'] = implode(" ", $errors);
        }

        echo json_encode($response);
        exit;
    }
}