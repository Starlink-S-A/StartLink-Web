<?php
// src/controllers/DashboardController.php

require_once __DIR__ . '/../config/configuracionInicial.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class DashboardController {
    public function showDashboard() {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            try {
                $token = str_replace('Bearer ', '', $headers['Authorization']);
                $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
                
                // Establecer datos de sesión
                $_SESSION["user_id"] = $decoded->uid;
                $_SESSION["user_name"] = $decoded->name;
                $_SESSION["id_rol"] = $decoded->rol;
                $_SESSION["loggedin"] = true;
                
                require_once __DIR__ . '/../../views/dashboardView/dashboard.php';
            } catch (Exception $e) {
                error_log('Error en JWT: ' . $e->getMessage());
                header('Location: ' . BASE_URL . 'index.php');
                exit;
            }
        } else {
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }
    }
}
?>