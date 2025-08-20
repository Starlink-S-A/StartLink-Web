<?php
// config/config.php

// Iniciar sesión si no está iniciada.
// Es crucial que esto esté al principio de cualquier script que use sesiones.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de errores para depuración (¡QUITAR O DESHABILITAR EN PRODUCCIÓN!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir la URL base de tu aplicación
if (!defined('BASE_URL')) {
    // ¡IMPORTANTE! Se ha cambiado esta ruta a la nueva ubicación del proyecto.
    define('BASE_URL', 'http://localhost/Proyecto-StartLink/StartLink-Web/src/');
}

// Definir la ruta física de la raíz del proyecto para incluir archivos.
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__) . '/');
}

// Configuración de la base de datos (PDO)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'hrms_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'caragors1');
}
