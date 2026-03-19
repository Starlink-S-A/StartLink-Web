<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';

class EmpresaInfoModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getEmpresaWithAuditById(int $empresaId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT 
                e.*,
                u.nombre AS ultimo_editor_nombre
            FROM empresa e
            LEFT JOIN usuario u ON u.id = e.ultimo_editor_id
            WHERE e.id_empresa = ?
            LIMIT 1
        ");
        $stmt->execute([$empresaId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function companyExistsByNameExcludingId(string $nombreEmpresa, int $empresaId): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM empresa
            WHERE nombre_empresa = ? AND id_empresa <> ?
        ");
        $stmt->execute([$nombreEmpresa, $empresaId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function updateEmpresa(int $empresaId, array $data, ?string $newLogoFileName, int $editorUserId): bool {
        $fields = [
            'nombre_empresa' => $data['nombre_empresa'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'email_contacto' => $data['email_contacto'] ?? null,
            'telefono_contacto' => $data['telefono_contacto'] ?? null,
            'url_sitio_web' => $data['url_sitio_web'] ?? null,
        ];

        $setParts = [];
        $params = [];
        foreach ($fields as $col => $val) {
            $setParts[] = $col . ' = ?';
            $params[] = $val;
        }

        if ($newLogoFileName !== null) {
            $setParts[] = 'logo_ruta = ?';
            $params[] = $newLogoFileName;
        }

        $setParts[] = 'ultima_actualizacion = NOW()';
        $setParts[] = 'ultimo_editor_id = ?';
        $params[] = $editorUserId;

        $params[] = $empresaId;

        $sql = "UPDATE empresa SET " . implode(', ', $setParts) . " WHERE id_empresa = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

