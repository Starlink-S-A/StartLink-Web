<?php

require_once __DIR__ . '/../../config/configuracionInicial.php';

class PerfilesCandidatosModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getRoleIdByName(string $roleName): ?int {
        $stmt = $this->pdo->prepare("SELECT id FROM rol WHERE nombre_rol = ? LIMIT 1");
        $stmt->execute([$roleName]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }

    public function getUserRoleId(int $userId): ?int {
        $stmt = $this->pdo->prepare("SELECT id_rol FROM usuario WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }

    public function isUserCandidate(int $userId): bool {
        $candidateRoleId = $this->getRoleIdByName('Candidato') ?? 2;
        $userRoleId = $this->getUserRoleId($userId);
        return $userRoleId !== null && $userRoleId === (int)$candidateRoleId;
    }

    public function isUserWorker(int $userId): bool {
        $trabajadorRoleId = $this->getRoleIdByName('TRABAJADOR');
        if ($trabajadorRoleId !== null) {
            $userRoleId = $this->getUserRoleId($userId);
            if ($userRoleId !== null && $userRoleId === (int)$trabajadorRoleId) {
                return true;
            }
        }

        $stmt = $this->pdo->prepare("SELECT 1 FROM usuario_empresa WHERE id_usuario = ? AND id_rol_empresa IN (1,2,3) LIMIT 1");
        $stmt->execute([$userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function userIsRecruiterOrCompanyAdmin(int $userId): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM usuario_empresa WHERE id_usuario = ? AND id_rol_empresa IN (1,2) LIMIT 1");
        $stmt->execute([$userId]);
        return (bool)$stmt->fetchColumn();
    }

    public function getCompaniesUserCanHireFrom(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT e.id_empresa, e.nombre_empresa
            FROM usuario_empresa ue
            JOIN empresa e ON e.id_empresa = ue.id_empresa
            WHERE ue.id_usuario = ? AND ue.id_rol_empresa IN (1,2)
            ORDER BY e.nombre_empresa
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function userCanHireFromCompany(int $userId, int $companyId): bool {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM usuario_empresa
            WHERE id_usuario = ? AND id_empresa = ? AND id_rol_empresa IN (1,2)
            LIMIT 1
        ");
        $stmt->execute([$userId, $companyId]);
        return (bool)$stmt->fetchColumn();
    }

    public function getUserCompanyRole(int $userId, int $companyId): ?int {
        $stmt = $this->pdo->prepare("
            SELECT id_rol_empresa
            FROM usuario_empresa
            WHERE id_usuario = ? AND id_empresa = ?
            LIMIT 1
        ");
        $stmt->execute([$userId, $companyId]);
        $role = $stmt->fetchColumn();
        return $role !== false && $role !== null ? (int)$role : null;
    }

    public function hireCandidateToCompany(int $candidateId, int $companyId, ?float $salaryBase, ?float $hoursWeekly): void {
        $this->pdo->beginTransaction();
        try {
            $existingRole = $this->getUserCompanyRole($candidateId, $companyId);
            if ($existingRole !== null) {
                throw new RuntimeException('El usuario ya pertenece a esta empresa y no puede ser contratado nuevamente.');
            }

            $stmtInsert = $this->pdo->prepare("
                INSERT INTO usuario_empresa (id_usuario, id_empresa, id_rol_empresa, horas_semanales_estandar)
                VALUES (?, ?, 3, ?)
            ");
            $stmtInsert->execute([$candidateId, $companyId, $hoursWeekly]);

            if ($salaryBase !== null) {
                $stmtSalary = $this->pdo->prepare("UPDATE usuario SET salario_base = ? WHERE id = ?");
                $stmtSalary->execute([$salaryBase, $candidateId]);
            }

            $stmtStart = $this->pdo->prepare("UPDATE usuario SET fecha_ingreso = COALESCE(fecha_ingreso, CURDATE()) WHERE id = ?");
            $stmtStart->execute([$candidateId]);

            $stmtHide = $this->pdo->prepare("UPDATE perfil_busqueda_empleo SET esta_disponible = 0 WHERE id_usuario = ?");
            $stmtHide->execute([$candidateId]);

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getUserProfile(int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM perfil_busqueda_empleo WHERE id_usuario = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function upsertUserProfile(int $userId, array $data): void {
        $existing = $this->getUserProfile($userId);
        $estaDisponible = (int)($data['esta_disponible'] ?? 0);

        if ($existing) {
            $stmt = $this->pdo->prepare("
                UPDATE perfil_busqueda_empleo
                SET titulo_buscado = ?,
                    tipo_contrato_preferido = ?,
                    modalidad_preferida = ?,
                    expectativa_salarial = ?,
                    esta_disponible = ?,
                    fecha_activacion = NOW()
                WHERE id_usuario = ?
            ");
            $stmt->execute([
                $data['titulo_buscado'],
                $data['tipo_contrato_preferido'],
                $data['modalidad_preferida'],
                $data['expectativa_salarial'],
                $estaDisponible,
                $userId,
            ]);
            return;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO perfil_busqueda_empleo
                (id_usuario, titulo_buscado, tipo_contrato_preferido, modalidad_preferida, expectativa_salarial, esta_disponible, fecha_activacion)
            VALUES
                (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $data['titulo_buscado'],
            $data['tipo_contrato_preferido'],
            $data['modalidad_preferida'],
            $data['expectativa_salarial'],
            $estaDisponible,
        ]);
    }

    public function searchAvailableProfiles(array $filters, int $limit = 60): array {
        $limit = max(1, min(200, (int)$limit));

        $sql = "
            SELECT
                pbe.id_usuario,
                u.nombre,
                u.foto_perfil,
                u.pais,
                u.departamento,
                u.ciudad,
                pbe.titulo_buscado,
                GROUP_CONCAT(DISTINCT h.nombre_habilidad ORDER BY h.nombre_habilidad SEPARATOR ', ') AS habilidades
            FROM perfil_busqueda_empleo pbe
            JOIN usuario u ON u.id = pbe.id_usuario
            LEFT JOIN usuario_habilidad uh ON uh.id_usuario = u.id
            LEFT JOIN habilidad h ON h.id_habilidad = uh.id_habilidad
            WHERE pbe.esta_disponible = 1
        ";
        $params = [];

        $name = trim((string)($filters['name'] ?? ''));
        if ($name !== '') {
            $sql .= " AND u.nombre LIKE ?";
            $params[] = '%' . $name . '%';
        }

        $title = trim((string)($filters['title'] ?? ''));
        if ($title !== '') {
            $sql .= " AND pbe.titulo_buscado LIKE ?";
            $params[] = '%' . $title . '%';
        }

        $skill = trim((string)($filters['skill'] ?? ''));
        if ($skill !== '') {
            $sql .= " AND h.nombre_habilidad LIKE ?";
            $params[] = '%' . $skill . '%';
        }

        $sql .= "
            GROUP BY
                pbe.id_usuario,
                u.nombre,
                u.foto_perfil,
                u.pais,
                u.departamento,
                u.ciudad,
                pbe.titulo_buscado
            ORDER BY pbe.fecha_activacion DESC
            LIMIT " . $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getProfileDetail(int $candidateId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT
                u.id,
                u.nombre,
                u.email,
                u.telefono,
                u.cargo,
                u.foto_perfil,
                u.ruta_hdv,
                u.pais,
                u.departamento,
                u.ciudad,
                pbe.titulo_buscado,
                pbe.tipo_contrato_preferido,
                pbe.modalidad_preferida,
                pbe.expectativa_salarial
            FROM perfil_busqueda_empleo pbe
            JOIN usuario u ON u.id = pbe.id_usuario
            WHERE pbe.id_usuario = ?
            LIMIT 1
        ");
        $stmt->execute([$candidateId]);
        $base = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$base) {
            return null;
        }

        $stmtSkills = $this->pdo->prepare("
            SELECT h.nombre_habilidad
            FROM usuario_habilidad uh
            JOIN habilidad h ON h.id_habilidad = uh.id_habilidad
            WHERE uh.id_usuario = ?
            ORDER BY h.nombre_habilidad
        ");
        $stmtSkills->execute([$candidateId]);
        $skills = $stmtSkills->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $stmtExp = $this->pdo->prepare("
            SELECT titulo_puesto, empresa_nombre, fecha_inicio, fecha_fin, descripcion
            FROM experiencia_laboral
            WHERE id_usuario = ?
            ORDER BY fecha_inicio DESC
        ");
        $stmtExp->execute([$candidateId]);
        $experiences = $stmtExp->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stmtStudies = $this->pdo->prepare("
            SELECT titulo_grado, institucion, fecha_inicio, fecha_fin, descripcion
            FROM estudio
            WHERE id_usuario = ?
            ORDER BY fecha_inicio DESC
        ");
        $stmtStudies->execute([$candidateId]);
        $studies = $stmtStudies->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $base['skills'] = $skills;
        $base['experiences'] = $experiences;
        $base['studies'] = $studies;

        return $base;
    }
}
