<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';

class MiEquipoModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getEmpresa(int $empresaId): ?array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id_empresa, nombre_empresa, descripcion, email_contacto, telefono_contacto, url_sitio_web, logo_ruta
                FROM empresa
                WHERE id_empresa = ?
                LIMIT 1
            ");
            $stmt->execute([$empresaId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log('Error al obtener empresa (MiEquipoModel): ' . $e->getMessage());
            return null;
        }
    }

    public function getMiembros(int $empresaId): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.nombre, u.email, u.foto_perfil, re.nombre_rol_empresa
                FROM usuario_empresa ue
                JOIN usuario u ON u.id = ue.id_usuario
                LEFT JOIN rol_empresa re ON re.id_rol_empresa = ue.id_rol_empresa
                WHERE ue.id_empresa = ?
                ORDER BY u.nombre ASC
            ");
            $stmt->execute([$empresaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener miembros (MiEquipoModel): ' . $e->getMessage());
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
            error_log('Error al validar relación usuario_empresa (MiEquipoModel): ' . $e->getMessage());
            return null;
        }
    }
}
