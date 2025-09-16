<?php
// ===========================================
// 📌 src/config/configuracionInicial.php
// ===========================================

// -------------------------------
// 🔹 Funciones auxiliares de sesión
// -------------------------------
if (!function_exists('setTempSessionData')) {
    function setTempSessionData($key, $data) {
        $_SESSION['temp_data'][$key] = $data;
    }
}

if (!function_exists('getTempSessionData')) {
    function getTempSessionData($key, $clear = true) {
        $data = $_SESSION['temp_data'][$key] ?? null;
        if ($clear) {
            unset($_SESSION['temp_data'][$key]);
        }
        return $data;
    }
}

// -------------------------------
// 🔹 Iniciar sesión
// -------------------------------
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------
// 🔹 Configuración de errores (solo desarrollo)
// -------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// -------------------------------
// 🔹 Rutas y constantes globales
// -------------------------------
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/StartLink-Web/');
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/StartLink-Web/');
}

// -------------------------------
// 🔹 Configuración de reCAPTCHA
// -------------------------------
if (!defined('RECAPTCHA_SITE_KEY')) {
    define('RECAPTCHA_SITE_KEY', '6LdobLYrAAAAABPXnbLFCmYrU1Mz7A_0hJCkltyQ'); 
}
if (!defined('RECAPTCHA_SECRET_KEY')) {
    define('RECAPTCHA_SECRET_KEY', '6LdobLYrAAAAAJAFYgyEN4QIyYK20cVLHDqjjsNH'); 
}

// -------------------------------
// 🔹 Configuración de JWT
// -------------------------------
if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', '123456789'); // cámbiala por una más segura
}

// -------------------------------
// 🔹 Configuración de Base de Datos
// -------------------------------
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'hrms_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '2525Guaza');
if (!defined('DB_PORT')) define('DB_PORT', '3307');

/**
 * 📌 Conexión PDO Singleton
 */
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('❌ Error de conexión: ' . $e->getMessage());
                die(json_encode([
                    "Error" => "No se pudo conectar a la base de datos",
                    "Detalles" => $e->getMessage()
                ]));
            }
        }
        return $pdo;
    }
}

// -------------------------------
// 🔹 Verificar si perfil de usuario está completo
// -------------------------------
if (!function_exists('isProfileComplete')) {
    function isProfileComplete($userId) {
        $pdo = getDbConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM usuario
                WHERE id = :id_usuario
                  AND nombre IS NOT NULL AND nombre != ''
                  AND email IS NOT NULL AND email != ''
                  AND genero IS NOT NULL
                  AND pais IS NOT NULL AND pais != ''
                  AND ciudad IS NOT NULL AND ciudad != ''
                  AND ruta_hdv IS NOT NULL AND ruta_hdv != ''
            ");
            $stmt->bindParam(':id_usuario', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $perfilCompleto = $stmt->fetchColumn() > 0;

            if (!$perfilCompleto) return false;

            $stmtEstudios = $pdo->prepare("SELECT COUNT(*) FROM estudio WHERE id_usuario = :id_usuario");
            $stmtEstudios->execute([':id_usuario' => $userId]);
            if ($stmtEstudios->fetchColumn() == 0) return false;

            $stmtExperiencia = $pdo->prepare("SELECT COUNT(*) FROM experiencia_laboral WHERE id_usuario = :id_usuario");
            $stmtExperiencia->execute([':id_usuario' => $userId]);
            if ($stmtExperiencia->fetchColumn() == 0) return false;

            return true;
        } catch (PDOException $e) {
            error_log("❌ Error perfil usuario ($userId): " . $e->getMessage());
            return false;
        }
    }
}

// -------------------------------
// 🔹 Configuración PHPMailer
// -------------------------------
require_once ROOT_PATH . "vendor/autoload.php";

if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USER')) define('SMTP_USER', 'tu_correo@gmail.com'); // 👉 cámbialo
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'clave_app_google');   // 👉 cámbialo
if (!defined('SMTP_FROM')) define('SMTP_FROM', 'tu_correo@gmail.com');
if (!defined('SMTP_NAME')) define('SMTP_NAME', 'TalentLink');

?>
