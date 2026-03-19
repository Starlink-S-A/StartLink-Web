<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';

class MisEquiposModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getEmpresasUsuario(int $userId): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    e.id_empresa,
                    e.nombre_empresa,
                    e.logo_ruta,
                    ue.id_rol_empresa
                FROM empresa e
                JOIN usuario_empresa ue ON ue.id_empresa = e.id_empresa
                WHERE ue.id_usuario = ?
                ORDER BY e.nombre_empresa ASC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener empresas del usuario (MisEquiposModel): ' . $e->getMessage());
            return [];
        }
    }

    public function getRelacionUsuarioEmpresa(int $userId, int $empresaId): ?array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id_empresa, id_rol_empresa
                FROM usuario_empresa
                WHERE id_usuario = ? AND id_empresa = ?
                LIMIT 1
            ");
            $stmt->execute([$userId, $empresaId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log('Error al obtener relación usuario_empresa (MisEquiposModel): ' . $e->getMessage());
            return null;
        }
    }
}
