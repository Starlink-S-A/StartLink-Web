<?php
class User {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
        if (!$this->pdo) {
            throw new Exception("No se pudo conectar a la base de datos.");
        }
    }

    public function getUserByEmail($email) {
        try {
            $sql = "SELECT id, nombre, contrasena_hash, id_rol, foto_perfil 
                    FROM usuario 
                    WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error en getUserByEmail: " . $e->getMessage());
            throw new Exception("Error al intentar iniciar sesión: " . $e->getMessage());
        }
    }
}