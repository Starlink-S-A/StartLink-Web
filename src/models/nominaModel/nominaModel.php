<?php
// src/models/nominaModel/nominaModel.php

require_once __DIR__ . '/../../config/configuracionInicial.php';

class NominaModel
{
    private $conexion;

    public function __construct()
    {
        try {
            $this->conexion = getDbConnection();
            if (!$this->conexion) {
                throw new Exception("No se pudo establecer conexión con la base de datos");
            }
        } catch (Exception $e) {
            error_log("Error al conectar a la BD en NominaModel: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * HU-25: Generar nómina para un trabajador
     * Calcula automáticamente salario_bruto y salario_neto
     */
    public function generarNomina(array $data): bool
    {
        try {
            $this->conexion->beginTransaction();

            // Calcular salario_bruto: (horas_trabajadas * tarifa_hora) + bonificaciones
            $salarioBruto = ($data['horas_trabajadas'] * $data['tarifa_hora']) + $data['bonificaciones'];
            $salarioNeto  = $salarioBruto - $data['deducciones'];

            // 1. Insertar Nómina
            $sql = "INSERT INTO nomina 
                        (id_usuario, horas_trabajadas, salario_bruto, deducciones, salario_neto,
                         fecha_generacion, fecha_inicio_periodo, fecha_fin_periodo, bonificaciones, horas_extras)
                    VALUES 
                        (:id_usuario, :horas_trabajadas, :salario_bruto, :deducciones, :salario_neto,
                         CURDATE(), :fecha_inicio, :fecha_fin, :bonificaciones, :horas_extras)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id_usuario',        $data['id_usuario'],       PDO::PARAM_INT);
            $stmt->bindParam(':horas_trabajadas',  $data['horas_trabajadas']);
            $stmt->bindParam(':salario_bruto',     $salarioBruto);
            $stmt->bindParam(':deducciones',       $data['deducciones']);
            $stmt->bindParam(':salario_neto',      $salarioNeto);
            $stmt->bindParam(':fecha_inicio',      $data['fecha_inicio_periodo']);
            $stmt->bindParam(':fecha_fin',         $data['fecha_fin_periodo']);
            $stmt->bindParam(':bonificaciones',    $data['bonificaciones']);
            $stmt->bindParam(':horas_extras',      $data['horas_extras']);

            $stmt->execute();

            // 2. Insertar Notificación Automática (HU-25)
            $fechaInicioFormat = date('d/m/Y', strtotime($data['fecha_inicio_periodo']));
            $fechaFinFormat    = date('d/m/Y', strtotime($data['fecha_fin_periodo']));
            $mensajeNotif      = "Se ha generado tu recibo de nómina para el período {$fechaInicioFormat} al {$fechaFinFormat}.";
            
            $sqlNotif = "INSERT INTO notificaciones (user_id, mensaje, tipo, icono) VALUES (?, ?, 'info', 'fas fa-file-invoice-dollar')";
            $stmtNotif = $this->conexion->prepare($sqlNotif);
            $stmtNotif->execute([$data['id_usuario'], $mensajeNotif]);

            $this->conexion->commit();
            return true;

        } catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error PDO al generar nómina: " . $e->getMessage());
            throw new Exception("Error de base de datos al generar la nómina.");
        }
    }

    /**
     * Obtener el ID de la última nómina insertada
     */
    public function getLastInsertId(): int
    {
        return (int)$this->conexion->lastInsertId();
    }

    /**
     * HU-25 / HU-26: Historial de nóminas de un trabajador
     */
    public function getNominasByUsuario(int $idUsuario): array
    {
        try {
            $sql = "SELECT n.*, u.nombre AS nombre_trabajador, u.email
                    FROM nomina n
                    JOIN usuario u ON n.id_usuario = u.id
                    WHERE n.id_usuario = ?
                    ORDER BY n.fecha_generacion DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idUsuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener nóminas del usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HU-25 / HU-16: Todas las nóminas de todos los trabajadores de una empresa
     */
    public function getNominasByEmpresa(int $idEmpresa): array
    {
        try {
            $sql = "SELECT n.*, u.nombre AS nombre_trabajador, u.email, u.cargo
                    FROM nomina n
                    JOIN usuario u ON n.id_usuario = u.id
                    JOIN usuario_empresa ue ON ue.id_usuario = u.id AND ue.id_empresa = ?
                    ORDER BY n.fecha_generacion DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idEmpresa]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener nóminas de empresa: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Todas las nóminas (solo Admin Global)
     */
    public function getAllNominas(): array
    {
        try {
            $sql = "SELECT n.*, u.nombre AS nombre_trabajador, u.email, u.cargo
                    FROM nomina n
                    JOIN usuario u ON n.id_usuario = u.id
                    ORDER BY n.fecha_generacion DESC";
            $stmt = $this->conexion->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todas las nóminas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * HU-26: Obtener una nómina por ID (para generar PDF)
     */
    public function getNominaById(int $idNomina): ?array
    {
        try {
            $sql = "SELECT n.*, u.nombre AS nombre_trabajador, u.email, u.dni, u.cargo,
                           u.salario_base, e.nombre_empresa
                    FROM nomina n
                    JOIN usuario u ON n.id_usuario = u.id
                    LEFT JOIN usuario_empresa ue ON ue.id_usuario = u.id
                    LEFT JOIN empresa e ON e.id_empresa = ue.id_empresa
                    WHERE n.id = ?
                    LIMIT 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idNomina]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener nómina por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * HU-25: Obtener trabajadores activos de una empresa para el select del formulario
     */
    public function getTrabajadoresDeEmpresa(int $idEmpresa): array
    {
        try {
            $sql = "SELECT u.id, u.nombre, u.email, u.cargo, u.salario_base,
                           ue.horas_semanales_estandar
                    FROM usuario u
                    JOIN usuario_empresa ue ON ue.id_usuario = u.id AND ue.id_empresa = ?
                    WHERE ue.id_rol_empresa = 3
                    ORDER BY u.nombre ASC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idEmpresa]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener trabajadores de empresa: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar que una nómina pertenece al usuario (para seguridad en descarga)
     */
    public function nominaPerteneceAUsuario(int $idNomina, int $idUsuario): bool
    {
        try {
            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM nomina WHERE id = ? AND id_usuario = ?");
            $stmt->execute([$idNomina, $idUsuario]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar pertenencia de nómina: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar que la nómina pertenece a un trabajador de la empresa del admin
     */
    public function nominaPerteneceAEmpresa(int $idNomina, int $idEmpresa): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM nomina n
                    JOIN usuario_empresa ue ON ue.id_usuario = n.id_usuario AND ue.id_empresa = ?
                    WHERE n.id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idEmpresa, $idNomina]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar pertenencia empresa de nómina: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Obtener historial de desempeño de un usuario específico
     */
    public function getDesempenoByUsuario(int $idUsuario): array
    {
        try {
            $sql = "SELECT sd.*, u.nombre AS nombre_evaluador
                    FROM `seguimiento_desempeño` sd
                    LEFT JOIN usuario u ON sd.evaluador_id = u.id
                    WHERE sd.id_usuario = ?
                    ORDER BY sd.fecha_evaluacion DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idUsuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener desempeño del usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener historial de desempeño de todos los empleados de una empresa
     */
    public function getDesempenoByEmpresa(int $idEmpresa): array
    {
        try {
            $sql = "SELECT sd.*, u.nombre AS nombre_trabajador, u.email,
                           ev.nombre AS nombre_evaluador
                    FROM `seguimiento_desempeño` sd
                    JOIN usuario u ON sd.id_usuario = u.id
                    JOIN usuario_empresa ue ON ue.id_usuario = u.id AND ue.id_empresa = ?
                    LEFT JOIN usuario ev ON sd.evaluador_id = ev.id
                    ORDER BY sd.fecha_evaluacion DESC";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$idEmpresa]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener desempeño de empresa: " . $e->getMessage());
            return [];
        }
    }
    // ═══════════════════════════════════════════
    // MÉTODOS PAGINADOS
    // ═══════════════════════════════════════════

    public function countNominasByUsuario(int $idUsuario): int
    {
        $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM nomina WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        return (int)$stmt->fetchColumn();
    }

    public function countNominasByEmpresa(int $idEmpresa): int
    {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM nomina n JOIN usuario_empresa ue ON ue.id_usuario = n.id_usuario AND ue.id_empresa = ?"
        );
        $stmt->execute([$idEmpresa]);
        return (int)$stmt->fetchColumn();
    }

    public function countAllNominas(): int
    {
        return (int)$this->conexion->query("SELECT COUNT(*) FROM nomina")->fetchColumn();
    }

    public function getNominasByUsuarioPag(int $idUsuario, int $limit, int $offset): array
    {
        $sql = "SELECT n.*, u.nombre AS nombre_trabajador, u.email, e.nombre_empresa
                FROM nomina n
                JOIN usuario u ON n.id_usuario = u.id
                LEFT JOIN (
                    SELECT id_usuario, MIN(id_empresa) AS id_empresa
                    FROM usuario_empresa
                    GROUP BY id_usuario
                ) ue ON ue.id_usuario = u.id
                LEFT JOIN empresa e ON e.id_empresa = ue.id_empresa
                WHERE n.id_usuario = :id
                ORDER BY n.fecha_generacion DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNominasByEmpresaPag(int $idEmpresa, int $limit, int $offset): array
    {
        $sql = "SELECT n.*, u.nombre AS nombre_trabajador, u.email, u.cargo, e.nombre_empresa
                FROM nomina n
                JOIN usuario u ON n.id_usuario = u.id
                JOIN usuario_empresa ue ON ue.id_usuario = u.id AND ue.id_empresa = :id
                JOIN empresa e ON e.id_empresa = ue.id_empresa
                ORDER BY n.fecha_generacion DESC LIMIT :lim OFFSET :off";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $idEmpresa, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllNominasPag(int $limit, int $offset): array
    {
        $sql = "SELECT n.*, u.nombre AS nombre_trabajador, u.email, u.cargo, e.nombre_empresa
                FROM nomina n
                JOIN usuario u ON n.id_usuario = u.id
                LEFT JOIN (
                    SELECT id_usuario, MIN(id_empresa) AS id_empresa
                    FROM usuario_empresa
                    GROUP BY id_usuario
                ) ue ON ue.id_usuario = u.id
                LEFT JOIN empresa e ON e.id_empresa = ue.id_empresa
                ORDER BY n.fecha_generacion DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countDesempenoByUsuario(int $idUsuario): int
    {
        $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM `seguimiento_desempeño` WHERE id_usuario = ?");
        $stmt->execute([$idUsuario]);
        return (int)$stmt->fetchColumn();
    }

    public function countDesempenoByEmpresa(int $idEmpresa): int
    {
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM `seguimiento_desempeño` sd JOIN usuario_empresa ue ON ue.id_usuario = sd.id_usuario AND ue.id_empresa = ?"
        );
        $stmt->execute([$idEmpresa]);
        return (int)$stmt->fetchColumn();
    }

    public function getDesempenoByUsuarioPag(int $idUsuario, int $limit, int $offset): array
    {
        $sql = "SELECT sd.*, u.nombre AS nombre_evaluador, e.nombre_empresa
                FROM `seguimiento_desempeño` sd
                LEFT JOIN usuario u ON sd.evaluador_id = u.id
                LEFT JOIN (
                    SELECT id_usuario, MIN(id_empresa) AS id_empresa
                    FROM usuario_empresa
                    GROUP BY id_usuario
                ) ue ON ue.id_usuario = sd.id_usuario
                LEFT JOIN empresa e ON e.id_empresa = ue.id_empresa
                WHERE sd.id_usuario = :id
                ORDER BY sd.fecha_evaluacion DESC
                LIMIT :lim OFFSET :off";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDesempenoByEmpresaPag(int $idEmpresa, int $limit, int $offset): array
    {
        $sql = "SELECT sd.*, u.nombre AS nombre_trabajador, u.email, ev.nombre AS nombre_evaluador, e.nombre_empresa
                FROM `seguimiento_desempeño` sd
                JOIN usuario u ON sd.id_usuario = u.id
                JOIN usuario_empresa ue ON ue.id_usuario = u.id AND ue.id_empresa = :id
                JOIN empresa e ON e.id_empresa = ue.id_empresa
                LEFT JOIN usuario ev ON sd.evaluador_id = ev.id
                ORDER BY sd.fecha_evaluacion DESC LIMIT :lim OFFSET :off";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':id', $idEmpresa, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
