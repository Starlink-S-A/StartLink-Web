<?php
// ===========================================
// 📌 src/config/configuracionInicial.php
// ===========================================

// -------------------------------
// 🔹 Funciones auxiliares de sesión
// -------------------------------
if (!function_exists('setTempSessionData')) {
    function setTempSessionData($key, $data)
    {
        $_SESSION['temp_data'][$key] = $data;
    }
}

if (!function_exists('getTempSessionData')) {
    function getTempSessionData($key, $clear = true)
    {
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
// 🔹 Zona horaria (Colombia)
// -------------------------------
date_default_timezone_set('America/Bogota');

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
    // Detectamos si estamos en Render usando la variable RENDER_EXTERNAL_URL
    $externalUrl = getenv('RENDER_EXTERNAL_URL');
    if ($externalUrl) {
        define('BASE_URL', rtrim($externalUrl, '/') . '/');
    } else {
        define('BASE_URL', 'http://localhost/StartLink-Web/');
    }
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
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') === false)
            continue;
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
// Intentamos obtener de las variables de entorno de Render
$envSiteKey = getenv('RECAPTCHA_SITE_KEY');
$envSecretKey = getenv('RECAPTCHA_SECRET_KEY');

if (!defined('RECAPTCHA_SITE_KEY')) {
    define('RECAPTCHA_SITE_KEY', $envSiteKey ?: '6Ldq87srAAAAAGGOrfyjsXqp7rfPFvaIjhr3KHA2');
}
if (!defined('RECAPTCHA_SECRET_KEY')) {
    define('RECAPTCHA_SECRET_KEY', $envSecretKey ?: '6Ldq87srAAAAAOdTe2F8-lbhqYfYRp586foWy_MH');
}

// -------------------------------
// 🔹 Configuración de JWT
// -------------------------------
if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', getenv('JWT_SECRET_KEY') ?: 'change_this_secret_key_in_production_environment');
}

// -------------------------------
// 🔹 Configuración de Base de Datos (Aiven Cloud)
// -------------------------------
if (!defined('DB_HOST'))
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_NAME'))
    define('DB_NAME', getenv('DB_NAME') ?: 'defaultdb');
if (!defined('DB_USER'))
    define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS'))
    define('DB_PASS', getenv('DB_PASS') ?: '');
if (!defined('DB_PORT'))
    define('DB_PORT', getenv('DB_PORT') ?: '3306');

/**
 * 📌 Conexión PDO Singleton con Soporte SSL
 */
if (!function_exists('getDbConnection')) {
    function getDbConnection()
    {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4;connect_timeout=5';

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,

                // 🔹 CAMBIO AQUÍ: Configuración específica para TiDB Cloud
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                // Forzamos el uso de SSL/TLS (esto resuelve el error 1105)
                PDO::MYSQL_ATTR_SSL_CA => '',
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4; SET time_zone='-05:00'"
            ];

            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Logueamos el error internamente
                error_log('❌ Error de conexión: ' . $e->getMessage());

                // Enviamos JSON limpio para que el Frontend lo entienda
                header('Content-Type: application/json');
                die(json_encode([
                    "status" => "error",
                    "message" => "No se pudo conectar a la base de datos remota.",
                    "debug" => $e->getMessage()
                ]));
            }
        }
        return $pdo;
    }
}

if (
    isset($_SESSION['loggedin'], $_SESSION['user_id']) &&
    $_SESSION['loggedin'] === true &&
    !empty($_SESSION['id_empresa'])
) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT u.id_rol, ue.id_rol_empresa
            FROM usuario u
            LEFT JOIN usuario_empresa ue
                ON ue.id_usuario = u.id AND ue.id_empresa = ?
            WHERE u.id = ?
            LIMIT 1
        ");
        $stmt->execute([(int) $_SESSION['id_empresa'], (int) $_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (isset($row['id_rol'])) {
                $_SESSION['id_rol'] = (int) $row['id_rol'];
            }
            $_SESSION['id_rol_empresa'] = $row['id_rol_empresa'] !== null ? (int) $row['id_rol_empresa'] : null;
        }
    } catch (Throwable $e) {
    }
}

// -------------------------------
// 🔹 Verificar si perfil de usuario está completo
// -------------------------------
if (!function_exists('isProfileComplete')) {
    function isProfileComplete($userId)
    {
        $pdo = getDbConnection();
        try {
            // Nota: Verifica que los nombres de las tablas coincidan (minúsculas/mayúsculas)
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

            if (!$perfilCompleto)
                return false;

            $stmtEstudios = $pdo->prepare("SELECT COUNT(*) FROM estudio WHERE id_usuario = :id_usuario");
            $stmtEstudios->execute([':id_usuario' => $userId]);
            if ($stmtEstudios->fetchColumn() == 0)
                return false;

            $stmtExperiencia = $pdo->prepare("SELECT COUNT(*) FROM experiencia_laboral WHERE id_usuario = :id_usuario");
            $stmtExperiencia->execute([':id_usuario' => $userId]);
            if ($stmtExperiencia->fetchColumn() == 0)
                return false;

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
// Asegúrate de que la ruta sea correcta según tu estructura
if (file_exists(ROOT_PATH . "vendor/autoload.php")) {
    require_once ROOT_PATH . "vendor/autoload.php";
}

if (!defined('SMTP_HOST'))
    define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT'))
    define('SMTP_PORT', 587);
if (!defined('SMTP_USER'))
    define('SMTP_USER', 'tu_correo@gmail.com');
if (!defined('SMTP_PASS'))
    define('SMTP_PASS', 'clave_app_google');
if (!defined('SMTP_FROM'))
    define('SMTP_FROM', 'tu_correo@gmail.com');
if (!defined('SMTP_NAME'))
    define('SMTP_NAME', 'TalentLink');
?>