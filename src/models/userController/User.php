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

    public function getUserById($id) {
        try {
            $sql = "SELECT id, nombre, email, id_rol, foto_perfil 
                    FROM usuario 
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            error_log("Error en getUserById: " . $e->getMessage());
            throw new Exception("Error al consultar el usuario: " . $e->getMessage());
        }
    }

    public function getAllUsers() {
        try {
            $sql = "SELECT id, nombre, email, id_rol, foto_perfil 
                    FROM usuario";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getAllUsers: " . $e->getMessage());
            throw new Exception("Error al consultar los usuarios: " . $e->getMessage());
        }
    }
}