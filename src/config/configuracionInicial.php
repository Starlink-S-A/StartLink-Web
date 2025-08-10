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
    // Ajustado para reflejar la raíz del proyecto Proyecto-StartLink/StartLink-Web/
    define('BASE_URL', 'http://localhost/Proyecto-StartLink/StartLink-Web/');
}

// Definir la ruta raíz física del proyecto
if (!defined('ROOT_PATH')) {
    // Ajustado para que apunte a la carpeta 'StartLink-Web' dentro de Proyecto-StartLink
    define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/Proyecto-StartLink/StartLink-Web/');
    // Si necesitas una ruta manual, podrías usar algo como:
    // define('ROOT_PATH', 'C:/ruta/a/Proyecto-StartLink/StartLink-Web/');
}

// Configuración de la base de datos (PDO)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    // Asegúrate de que este sea el nombre correcto de tu base de datos
    define('DB_NAME', 'hrms_db'); // Cambia a 'talentlink' si es el nombre real
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