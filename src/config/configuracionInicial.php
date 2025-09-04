<?php
// configuraciónInicial.php
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

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de errores para depuración (¡QUITAR O DESHABILITAR EN PRODUCCIÓN!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir la URL base de tu aplicación
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/StartLink-Web/');
}

// Configuración de Google reCAPTCHA
if (!defined('RECAPTCHA_SITE_KEY')) {
    define('RECAPTCHA_SITE_KEY', '6LdobLYrAAAAABPXnbLFCmYrU1Mz7A_0hJCkltyQ'); // la clave pública para el frontend
}

if (!defined('RECAPTCHA_SECRET_KEY')) {
    define('RECAPTCHA_SECRET_KEY', '6LdobLYrAAAAAJAFYgyEN4QIyYK20cVLHDqjjsNH'); // la clave privada para el backend
}

if (!defined('JWT_SECRET_KEY')) {
    define('JWT_SECRET_KEY', '123456789'); 
    // ⚠️ cámbiala por algo largo y único, al menos 32 caracteres
}

// Definir la ruta raíz física del proyecto
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/StartLink-Web/');
    // Alternativa manual si falla: define('ROOT_PATH', 'C:/xampp/htdocs/prueba_registro_inicio_de_sesión/StartLink-Web/');
}

// Configuración de la base de datos (PDO)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'hrms_db'); // Cambia si es necesario
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'caragors1');
}

/**
 * Función para obtener una conexión PDO a la base de datos.
 * Utiliza un patrón Singleton para evitar múltiples conexiones.
 * @return PDO La conexión PDO.
 * @throws PDOException Si la conexión a la base de datos falla.
 */
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        static $pdo = null;

        if ($pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('Error de conexión a la base de datos: ' . $e->getMessage());
                die('Error de conexión a la base de datos. Por favor, inténtalo más tarde. (Detalles en el log del servidor)');
            }
        }
        return $pdo;
    }
}

/**
 * Función para verificar si el perfil del usuario está completo.
 * @param int $userId El ID del usuario.
 * @return bool True si el perfil está completo, false en caso contrario.
 */
if (!function_exists('isProfileComplete')) {
    function isProfileComplete($userId) {
        $pdo = getDbConnection();
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM USUARIO
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

            if (!$perfilCompleto) {
                return false;
            }

            $stmtEstudios = $pdo->prepare("
                SELECT COUNT(*) FROM ESTUDIO WHERE id_usuario = :id_usuario
            ");
            $stmtEstudios->bindParam(':id_usuario', $userId, PDO::PARAM_INT);
            $stmtEstudios->execute();
            $tieneEstudios = $stmtEstudios->fetchColumn() > 0;

            if (!$tieneEstudios) {
                return false;
            }

            $stmtExperiencia = $pdo->prepare("
                SELECT COUNT(*) FROM EXPERIENCIA_LABORAL WHERE id_usuario = :id_usuario
            ");
            $stmtExperiencia->bindParam(':id_usuario', $userId, PDO::PARAM_INT);
            $stmtExperiencia->execute();
            $tieneExperiencia = $stmtExperiencia->fetchColumn() > 0;

            if (!$tieneExperiencia) {
                return false;
            }

            return true;
        } catch (PDOException $e) {
            error_log('Error al verificar perfil completo para usuario ' . $userId . ': ' . $e->getMessage());
            return false;
        }
    }
}
?>