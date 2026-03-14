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
    define('ROOT_PATH', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}

// -------------------------------
// 🔹 Cargar variables de entorno (.env)
// -------------------------------
if (file_exists(ROOT_PATH . '.env')) {
    $lines = file(ROOT_PATH . '.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// -------------------------------
// 🔹 Configuración de reCAPTCHA
// -------------------------------
if (!defined('RECAPTCHA_SITE_KEY')) {
    define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: 'YOUR_RECAPTCHA_SITE_KEY'); 
}
if (!defined('RECAPTCHA_SECRET_KEY')) {
    define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: 'YOUR_RECAPTCHA_SECRET_KEY'); 
}

// -------------------------------
// 🔹 Configuración de JWT
// -------------------------------
if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', getenv('JWT_SECRET_KEY') ?: 'change_this_secret_key_in_production_environment');
}

// -------------------------------
// 🔹 Configuración de Base de Datos
// -------------------------------
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'defaultdb');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');

/**
 * 📌 Conexión PDO Singleton con Soporte SSL
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
                // 🔹 REQUERIDO PARA AIVEN: Habilita la conexión segura SSL
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
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
