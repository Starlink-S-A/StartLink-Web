<?php
// src/models/rolModel/rolModel.php
declare(strict_types=1);

require_once __DIR__ . '/../../config/configuracionInicial.php';

class RolModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    /** Devuelve todos los roles */
    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT id, nombre_rol FROM ROL ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Rol por ID */
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT id, nombre_rol FROM ROL WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Rol por nombre (case-insensitive) */
    public function getByName(string $name): ?array {
        $stmt = $this->pdo->prepare("SELECT id, nombre_rol FROM ROL WHERE UPPER(nombre_rol) = UPPER(?) LIMIT 1");
        $stmt->execute([$name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Determina el ID del rol administrador.
     * 1) Busca por nombre común ('ADMINISTRADOR', 'ADMIN', 'Administrador del Sistema')
     * 2) Si no lo encuentra, hace fallback a ID=1 (habitual en tu proyecto).
     */
    public function getAdminRoleId(): int {
        foreach (['ADMINISTRADOR', 'ADMIN', 'Administrador del Sistema'] as $n) {
            $row = $this->getByName($n);
            if ($row) return (int)$row['id'];
        }
        return 1; // Fallback conservador
    }

    /** Retorna el id_rol del usuario (o null si no existe) */
    public function userRoleId(int $userId): ?int {
        $stmt = $this->pdo->prepare("SELECT id_rol FROM USUARIO WHERE id = ?");
        $stmt->execute([$userId]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? (int)$val : null;
    }

    /** ¿El usuario tiene ese rol? (acepta id o nombre) */
    public function userHasRole(int $userId, int|string $role): bool {
        $roleId = is_numeric($role) ? (int)$role : ($this->getByName((string)$role)['id'] ?? null);
        if (!$roleId) return false;
        $current = $this->userRoleId($userId);
        return $current === $roleId;
    }

    /** Asigna (actualiza) el rol global de un usuario */
    public function assignToUser(int $userId, int $roleId): bool {
        if (!$this->getById($roleId)) {
            throw new InvalidArgumentException("El rol seleccionado no existe.");
        }
        $stmt = $this->pdo->prepare("UPDATE USUARIO SET id_rol = ? WHERE id = ?");
        return $stmt->execute([$roleId, $userId]);
    }
}
