<?php
// models/userController/User.php

require_once __DIR__ . '/../../config/configuracionInicial.php';

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getUserByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nombre, email, contrasena_hash, id_rol, estado FROM usuario WHERE email = :email
                FROM usuario 
                WHERE email = :email
            ");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuario por email: " . $e->getMessage());
            return false;
        }
    }

    public function getUserById($id) {
        try {
            $stmt = $this->pdo->prepare("
               SELECT id, nombre, email, id_rol, fecha_registro, estado FROM usuario WHERE id = :id
                FROM usuario 
                WHERE id = :id
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuario por ID: " . $e->getMessage());
            return false;
        }
    }

    // Obtener todos los usuarios
    public function getAllUsers() {
        try {
            $stmt = $this->pdo->query("
                SELECT id, nombre, email, id_rol, fecha_registro, intentos_fallidos, bloqueado_hasta 
                FROM usuario 
                ORDER BY fecha_registro DESC
            ");

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todos los usuarios: " . $e->getMessage());
            return [];
        }
    }

    public function updateUser($id, $data) {
        try {
            $sql = "UPDATE usuario SET ";
            $params = [];
            
            foreach ($data as $key => $value) {
                $sql .= "$key = :$key, ";
                $params[":$key"] = $value;
            }
            
            $sql = rtrim($sql, ', ') . " WHERE id = :id";
            $params[':id'] = $id;
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    public function resetLoginAttempts($userId) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE usuario 
                SET intentos_fallidos = 0, bloqueado_hasta = NULL 
                WHERE id = :id
            ");
            return $stmt->execute([':id' => $userId]);
        } catch (PDOException $e) {
            error_log("Error al resetear intentos de login: " . $e->getMessage());
            return false;
        }
    }


    public function updateLoginAttempts($userId, $attempts) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE usuario 
                SET intentos_fallidos = :attempts 
                WHERE id = :id
            ");
            return $stmt->execute([':attempts' => $attempts, ':id' => $userId]);
        } catch (PDOException $e) {
            error_log("Error al actualizar intentos de login: " . $e->getMessage());
            return false;
        }
    }


    public function blockUser($userId, $bloqueadoHasta) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE usuario 
                SET intentos_fallidos = 0, bloqueado_hasta = :bloqueado_hasta 
                WHERE id = :id
            ");
            return $stmt->execute([':bloqueado_hasta' => $bloqueadoHasta, ':id' => $userId]);
        } catch (PDOException $e) {
            error_log("Error al bloquear usuario: " . $e->getMessage());
            return false;
        }
    }
}
?>
