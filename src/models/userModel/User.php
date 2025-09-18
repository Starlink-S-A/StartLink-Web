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
            $stmt = $this->pdo->prepare("SELECT id, nombre, email, contrasena_hash, id_rol, estado FROM usuario WHERE email = :email");
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
            $stmt = $this->pdo->prepare("SELECT id, nombre, email, id_rol, fecha_registro, estado FROM usuario WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuario por ID: " . $e->getMessage());
            return false;
        }
    }

    public function getAllUsers() {
        try {
            $stmt = $this->pdo->query("SELECT id, nombre, email, id_rol, fecha_registro FROM usuario ORDER BY fecha_registro DESC");
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
}


?>