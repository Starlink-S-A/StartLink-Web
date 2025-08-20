<?php
// controllers/LoginController.php

// Se incluye el modelo de usuario para poder interactuar con los datos.
require_once ROOT_PATH . 'models/UserModel.php';

class LoginController {
    /**
     * Procesa la solicitud de login que viene por POST.
     * Valida los datos del formulario, verifica las credenciales con el modelo
     * y gestiona la respuesta (éxito o error).
     *
     * @return void Envía una respuesta JSON.
     */
    public function login() {
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => 'Ocurrió un error desconocido.'];

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = trim($_POST["email"] ?? '');
            $password = trim($_POST["password"] ?? '');
            $errors = [];

            if (empty($email)) $errors[] = "Por favor ingresa tu email.";
            if (empty($password)) $errors[] = "Por favor ingresa tu contraseña.";

            if (empty($errors)) {
                $userModel = new UserModel();
                $user = $userModel->getUserByEmail($email);

                if ($user && $userModel->verifyPassword($password, $user['contrasena_hash'])) {
                    // Si las credenciales son correctas, se establecen las variables de sesión.
                    $_SESSION["loggedin"] = true;
                    $_SESSION["user_id"] = $user['id'];
                    $_SESSION["user_name"] = $user['nombre'];
                    $_SESSION["id_rol"] = $user['id_rol'];
                    $_SESSION["foto_perfil"] = $user['foto_perfil'];
                    
                    $response['success'] = true;
                    $response['message'] = "¡Bienvenido, " . htmlspecialchars($user['nombre']) . "!";
                    $response['redirect'] = BASE_URL . "dashboard.php";
                } else {
                    $errors[] = "Email o contraseña incorrectos.";
                }
            }
            if (!empty($errors)) {
                $response['message'] = implode(" ", $errors);
            }
        } else {
            $response['message'] = "Acceso no permitido.";
        }

        echo json_encode($response);
    }
}
