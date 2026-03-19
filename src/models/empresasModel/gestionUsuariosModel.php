<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';

class GestionUsuariosModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getUsuariosEmpresa(int $empresaId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                u.id AS id_usuario,
                u.nombre,
                u.dni,
                u.email,
                u.foto_perfil,
                ue.id_rol_empresa,
                re.nombre_rol_empresa,
                ue.fecha_union
            FROM usuario_empresa ue
            INNER JOIN usuario u ON u.id = ue.id_usuario
            INNER JOIN rol_empresa re ON re.id_rol_empresa = ue.id_rol_empresa
            WHERE ue.id_empresa = ?
            ORDER BY ue.id_rol_empresa ASC, u.nombre ASC
        ");
        $stmt->execute([$empresaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getRolEmpresaUsuario(int $usuarioId, int $empresaId): ?int {
        $stmt = $this->pdo->prepare("SELECT id_rol_empresa FROM usuario_empresa WHERE id_usuario = ? AND id_empresa = ? LIMIT 1");
        $stmt->execute([$usuarioId, $empresaId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int)$val : null;
    }

    public function removeUsuarioEmpresa(int $usuarioId, int $empresaId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM usuario_empresa WHERE id_usuario = ? AND id_empresa = ?");
        return $stmt->execute([$usuarioId, $empresaId]);
    }

    public function updateRolUsuarioEmpresa(int $usuarioId, int $empresaId, int $nuevoRolEmpresa): bool {
        $stmt = $this->pdo->prepare("UPDATE usuario_empresa SET id_rol_empresa = ? WHERE id_usuario = ? AND id_empresa = ?");
        return $stmt->execute([$nuevoRolEmpresa, $usuarioId, $empresaId]);
    }

    public function getRolesEmpresa(): array {
        $stmt = $this->pdo->query("SELECT id_rol_empresa, nombre_rol_empresa FROM rol_empresa ORDER BY id_rol_empresa ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getRolGlobalUsuario(int $usuarioId): ?int {
        $stmt = $this->pdo->prepare("SELECT id_rol FROM usuario WHERE id = ? LIMIT 1");
        $stmt->execute([$usuarioId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int)$val : null;
    }

    public function countEmpresasUsuario(int $usuarioId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuario_empresa WHERE id_usuario = ?");
        $stmt->execute([$usuarioId]);
        return (int)$stmt->fetchColumn();
    }

    public function updateRolGlobalUsuario(int $usuarioId, int $nuevoRolGlobal): bool {
        $stmt = $this->pdo->prepare("UPDATE usuario SET id_rol = ? WHERE id = ?");
        return $stmt->execute([$nuevoRolGlobal, $usuarioId]);
    }

    public function updateRolGlobalAfterUnlink(int $usuarioId): void {
        $rolActual = $this->getRolGlobalUsuario($usuarioId);
        if ($rolActual === 1) {
            return;
        }

        $tieneEmpresas = $this->countEmpresasUsuario($usuarioId) > 0;
        $nuevoRol = $tieneEmpresas ? 3 : 2;
        $this->updateRolGlobalUsuario($usuarioId, $nuevoRol);
    }

    public function addSeguimientoDesempeno(
        int $usuarioId,
        int $evaluadorId,
        string $fechaEvaluacion,
        string $tipoEvaluacion,
        ?float $puntuacion,
        ?string $comentarios,
        ?string $objetivosLogrados
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO `seguimiento_desempeño` (
                id_usuario,
                evaluador_id,
                fecha_evaluacion,
                tipo_evaluacion,
                puntuacion,
                comentarios,
                objetivos_logrados
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $usuarioId,
            $evaluadorId,
            $fechaEvaluacion,
            $tipoEvaluacion,
            $puntuacion,
            $comentarios,
            $objetivosLogrados,
        ]);
    }
}
