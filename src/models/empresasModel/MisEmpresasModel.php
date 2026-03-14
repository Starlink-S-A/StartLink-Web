<?php
// src/models/empresasModel/misEmpresasModel/MisEmpresasModel.php

require_once __DIR__ . '/../../config/configuracionInicial.php';

class MisEmpresasModel {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    /**
     * Obtiene las empresas en las que el usuario es Administrador (1) o Contratador (2).
     * @param int $userId
     * @return array
     */
    public function getEmpresasUsuario($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT E.id_empresa, E.nombre_empresa, E.logo_ruta
                FROM empresa E
                JOIN usuario_empresa UE ON E.id_empresa = UE.id_empresa
                WHERE UE.id_usuario = ? AND UE.id_rol_empresa IN (1,2)
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener empresas del usuario: " . $e->getMessage());
            return [];
        }
    }
}
?>
