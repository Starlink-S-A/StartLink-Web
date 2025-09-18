<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';

class UsuarioModel {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    /* ========================= CRUD/Acciones ========================= */

    public function registrar($data) {
        // Inserta usando id_rol (FK). Si te llega rol/nombre, el helper lo resuelve.
        $idRol = $this->resolverIdRol($data);
        $stmt = $this->pdo->prepare(
            "INSERT INTO USUARIO (nombre, email, genero, pais, ciudad, ruta_hdv, id_rol, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo')"
        );
        $stmt->execute([
            $data['nombre'], $data['email'], $data['genero'], $data['pais'],
            $data['ciudad'], $data['ruta_hdv'], $idRol
        ]);
    }

    public function editarRol($idUsuario, $idRol) {
        $stmt = $this->pdo->prepare("UPDATE USUARIO SET id_rol = ? WHERE id = ?");
        $stmt->execute([(int)$idRol, (int)$idUsuario]);
    }

    public function bloquear($id) {
        $stmt = $this->pdo->prepare("UPDATE USUARIO SET estado = 'Bloqueado' WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    public function desbloquear($id) {
        $stmt = $this->pdo->prepare("UPDATE USUARIO SET estado = 'Activo' WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    /* ========================= Listados/Consultas ========================= */

    public function obtenerUsuarios($busqueda = '', $genero = '', $estado = '') {
        $query = "SELECT u.*, r.nombre_rol
                FROM USUARIO u
                LEFT JOIN rol r ON r.id = u.id_rol
                WHERE 1";
        $params = [];

        if ($busqueda) {
            $query .= " AND (u.nombre LIKE ? OR u.email LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        if ($genero !== '') {
            // Normaliza input y permite ambas variantes: M/Masculino y F/Femenino
            $g = strtoupper(trim($genero));
            if ($g === 'M' || $g === 'MASCULINO') {
                $query .= " AND UPPER(TRIM(u.genero)) IN (?, ?)";
                $params[] = 'M';
                $params[] = 'MASCULINO';
            } elseif ($g === 'F' || $g === 'FEMENINO') {
                $query .= " AND UPPER(TRIM(u.genero)) IN (?, ?)";
                $params[] = 'F';
                $params[] = 'FEMENINO';
            } else {
                // Para cualquier otro valor (p.ej. 'NB', 'OTRO', etc.)
                $query .= " AND UPPER(TRIM(u.genero)) = ?";
                $params[] = $g;
            }
        }
        if ($estado) {
            $query .= " AND UPPER(TRIM(u.estado)) = ?";
            $params[] = strtoupper(trim($estado));
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerPorId($id) {
        // Incluye id_rol y nombre_rol
        $stmt = $this->pdo->prepare(
            "SELECT u.*, r.nombre_rol
             FROM USUARIO u
             LEFT JOIN rol r ON r.id = u.id_rol
             WHERE u.id = ?"
        );
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function obtenerRoles() {
        return $this->pdo->query("SELECT id, nombre_rol FROM rol ORDER BY nombre_rol")->fetchAll();
    }

    public function obtenerEstado(int $id): ?string {
        $stmt = $this->pdo->prepare("SELECT estado FROM USUARIO WHERE id = ?");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();
        return $row['estado'] ?? null;
    }

    public function obtenerEstadoPorEmail(string $email): ?string {
        $stmt = $this->pdo->prepare("SELECT estado FROM USUARIO WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row['estado'] ?? null;
    }

    /* ========================= Autenticación (por si la usas) ========================= */
    // Usa tu nombre real de columna de pass: contrasena_hash (me lo diste así)
    public function autenticar(string $email, string $password): array {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, email, contrasena_hash, id_rol, estado
            FROM USUARIO
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if (!$u) return [false, 'credenciales'];

        if (($u['estado'] ?? '') !== 'Activo') {
            return [false, 'Bloqueado'];
        }

        if (!password_verify($password, $u['contrasena_hash'])) {
            return [false, 'credenciales'];
        }

        return [true, $u];
    }

    /* ========================= Helpers ========================= */

    private function resolverIdRol(array $data) {
        if (isset($data['id_rol']))  return (int)$data['id_rol'];
        if (isset($data['rol_id']))  return (int)$data['rol_id'];
        if (!empty($data['rol'])) {
            $id = $this->rolIdPorNombre($data['rol']);
            if ($id !== null) return $id;
        }
        throw new \InvalidArgumentException("id_rol no proporcionado.");
    }

    public function rolIdPorNombre(?string $nombre) {
        if (!$nombre) return null;
        $stmt = $this->pdo->prepare("SELECT id FROM rol WHERE nombre_rol = ?");
        $stmt->execute([$nombre]);
        $row = $stmt->fetch();
        return $row ? (int)$row['id'] : null;
    }
}