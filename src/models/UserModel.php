<?php
// models/UserModel.php

// Se incluye el archivo de configuración para tener acceso a las constantes de la base de datos.
require_once ROOT_PATH . 'config/config.php';

class UserModel {
    private static $pdo = null;

    /**
     * Establece y devuelve una conexión a la base de datos usando PDO.
     * La conexión se guarda estáticamente para reutilizarla en futuras llamadas.
     *
     * @return PDO La instancia de conexión a la base de datos.
     */
    private static function getDbConnection() {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Loguear el error y detener la ejecución de manera segura.
                error_log('Error de conexión a la base de datos: ' . $e->getMessage());
                die('Error de conexión a la base de datos.');
            }
        }
        return self::$pdo;
    }

    /**
     * Busca un usuario en la base de datos por su dirección de email.
     *
     * @param string $email El email del usuario.
     * @return array|false Los datos del usuario si se encuentra, o false si no.
     */
    public function getUserByEmail($email) {
        $pdo = self::getDbConnection();
        $sql = "SELECT id, nombre, contrasena_hash, id_rol, foto_perfil FROM usuario WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Verifica si una contraseña coincide con un hash almacenado.
     *
     * @param string $password La contraseña en texto plano.
     * @param string $hashedPassword El hash almacenado en la base de datos.
     * @return bool True si la contraseña es correcta, false en caso contrario.
     */
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
}
