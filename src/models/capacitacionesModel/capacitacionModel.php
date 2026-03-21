<?php
// src/models/capacitacionesModel/capacitacionModel.php

require_once __DIR__ . '/../../config/configuracionInicial.php';

class CapacitacionModel
{
    private $conexion;

    public function __construct()
    {
        try {
            $this->conexion = getDbConnection();
            if (!$this->conexion) {
                throw new Exception("No se pudo establecer conexión con la base de datos");
            }
        }
        catch (Exception $e) {
            error_log("Error al conectar a la BD en CapacitacionModel: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener todas las capacitaciones activas (fecha_fin >= hoy)
     * @return array
     */
    public function getAllActiveCapacitaciones()
    {
        try {
            $sql = "SELECT id, nombre_capacitacion, descripcion, fecha_inicio, fecha_fin, costo, creador_id 
                    FROM capacitacion 
                    WHERE fecha_fin >= CURDATE()
                    ORDER BY fecha_inicio DESC";
            $stmt = $this->conexion->query($sql);

            if (!$stmt) {
                throw new Exception("Error en la consulta de capacitaciones: " . implode(" - ", $this->conexion->errorInfo()));
            }

            $capacitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear fechas y asegurar datos
            foreach ($capacitaciones as &$cap) {
                $cap['nombre_capacitacion'] = htmlspecialchars($cap['nombre_capacitacion'] ?? 'Sin nombre');
                $cap['descripcion'] = htmlspecialchars($cap['descripcion'] ?? 'Descripción no disponible');
                $cap['fecha_inicio_fmt'] = (new DateTime($cap['fecha_inicio']))->format('d/m/Y');
                $cap['fecha_fin_fmt'] = (new DateTime($cap['fecha_fin']))->format('d/m/Y');
                $cap['costo_fmt'] = number_format($cap['costo'], 2) . ' USD';
            }
            unset($cap);

            return $capacitaciones;

        }
        catch (PDOException $e) {
            error_log("Error PDO al cargar capacitaciones: " . $e->getMessage());
            throw new Exception("Error al cargar las capacitaciones");
        }
        catch (Exception $e) {
            error_log("Error general al cargar capacitaciones: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verificar estado de inscripción de un usuario en una capacitación
     * @param int $userId
     * @param int $capId
     * @return string|false
     */
    public function checkInscripcion($userId, $capId)
    {
        try {
            $stmt = $this->conexion->prepare("SELECT estado_inscripcion FROM inscripcion WHERE id_usuario = ? AND id_capacitacion = ?");
            $stmt->execute([$userId, $capId]);
            $estado = $stmt->fetchColumn();
            return $estado !== false ? $estado : false;
        }
        catch (PDOException $e) {
            error_log("Error al verificar inscripción: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear una nueva capacitación con notificaciones a usuarios
     * @param array $data [nombre_capacitacion, descripcion, fecha_inicio, fecha_fin, costo, creador_id]
     * @return bool
     */
    public function crearCapacitacion($data)
    {
        try {
            $this->conexion->beginTransaction();

            $sql = "INSERT INTO capacitacion (nombre_capacitacion, descripcion, fecha_inicio, fecha_fin, costo, creador_id) 
                    VALUES (:nombre_capacitacion, :descripcion, :fecha_inicio, :fecha_fin, :costo, :creador_id)";
            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':nombre_capacitacion', $data['nombre_capacitacion']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':fecha_inicio', $data['fecha_inicio']);
            $stmt->bindParam(':fecha_fin', $data['fecha_fin']);
            $stmt->bindParam(':costo', $data['costo']);
            $stmt->bindParam(':creador_id', $data['creador_id']);

            if ($stmt->execute()) {
                $this->conexion->commit();
                return true;
            }
            else {
                $this->conexion->rollBack();
                return false;
            }

        }
        catch (PDOException $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error PDO al crear capacitación: " . $e->getMessage());
            throw new Exception("Error de base de datos al crear la capacitación.");
        }
        catch (Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error general al crear capacitación: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Actualiza una capacitación existente
     * @param int $capId
     * @param array $data
     * @return bool
     */
    public function actualizarCapacitacion($capId, $data) {
        try {
            $sql = "UPDATE capacitacion 
                    SET nombre_capacitacion = :nombre_capacitacion, 
                        descripcion = :descripcion, 
                        fecha_inicio = :fecha_inicio, 
                        fecha_fin = :fecha_fin, 
                        costo = :costo 
                    WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(':nombre_capacitacion', $data['nombre_capacitacion']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':fecha_inicio', $data['fecha_inicio']);
            $stmt->bindParam(':fecha_fin', $data['fecha_fin']);
            $stmt->bindParam(':costo', $data['costo']);
            $stmt->bindParam(':id', $capId, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error PDO al actualizar capacitación: " . $e->getMessage());
            throw new Exception("Error de base de datos al actualizar la capacitación.");
        }
    }


    /**
     * Obtener el creador_id de una capacitación
     * @param int $capId
     * @return array|false
     */
    public function getCreadorId($capId)
    {
        try {
            $stmt = $this->conexion->prepare("SELECT creador_id FROM capacitacion WHERE id = ?");
            $stmt->execute([$capId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            error_log("Error al obtener creador de capacitación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una capacitación por ID
     * @param int $capId
     * @return bool
     */
    public function eliminarCapacitacion($capId)
    {
        try {
            $stmt = $this->conexion->prepare("DELETE FROM capacitacion WHERE id = ?");
            return $stmt->execute([$capId]);
        }
        catch (PDOException $e) {
            error_log("Error PDO al eliminar capacitación: " . $e->getMessage());
            throw new Exception("Error de base de datos al eliminar la capacitación.");
        }
    }

    /**
     * Verificar si una capacitación existe
     * @param int $capId
     * @return bool
     */
    public function existeCapacitacion($capId)
    {
        try {
            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM capacitacion WHERE id = ?");
            $stmt->execute([$capId]);
            return $stmt->fetchColumn() > 0;
        }
        catch (PDOException $e) {
            error_log("Error al verificar existencia de capacitación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un usuario ya está inscrito
     * @param int $userId
     * @param int $capId
     * @return bool
     */
    public function estaInscrito($userId, $capId)
    {
        try {
            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM inscripcion WHERE id_usuario = ? AND id_capacitacion = ?");
            $stmt->execute([$userId, $capId]);
            return $stmt->fetchColumn() > 0;
        }
        catch (PDOException $e) {
            error_log("Error al verificar inscripción: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inscribir un usuario en una capacitación
     * @param int $userId
     * @param int $capId
     * @return bool
     */
    public function inscribir($userId, $capId)
    {
        try {
            $stmt = $this->conexion->prepare("INSERT INTO inscripcion (id_usuario, id_capacitacion, fecha_inscripcion, estado_inscripcion) VALUES (?, ?, NOW(), 'Inscrito')");
            return $stmt->execute([$userId, $capId]);
        }
        catch (PDOException $e) {
            error_log("Error al inscribir en capacitación: " . $e->getMessage());
            throw new Exception("Error al procesar la inscripción.");
        }
    }

    /**
     * Cancelar inscripción de un usuario
     * @param int $userId
     * @param int $capId
     * @return int Número de filas afectadas
     */
    public function cancelarInscripcion($userId, $capId)
    {
        try {
            $stmt = $this->conexion->prepare("DELETE FROM inscripcion WHERE id_usuario = ? AND id_capacitacion = ?");
            $stmt->execute([$userId, $capId]);
            return $stmt->rowCount();
        }
        catch (PDOException $e) {
            error_log("Error al cancelar inscripción: " . $e->getMessage());
            throw new Exception("Error al cancelar la inscripción.");
        }
    }

    /**
     * Obtener lista de inscritos en una capacitación
     * HU-16: nombre completo, correo y fecha/hora de inscripción
     * @param int $capId
     * @return array
     */
    public function getInscritos($capId)
    {
        try {
            $sql = "
                SELECT 
                    U.nombre, 
                    U.email, 
                    I.fecha_inscripcion,
                    I.estado_inscripcion
                FROM inscripcion I
                JOIN usuario U ON I.id_usuario = U.id
                WHERE I.id_capacitacion = ?
                ORDER BY I.fecha_inscripcion DESC
            ";
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$capId]);
            $inscritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear la fecha de inscripción
            foreach ($inscritos as &$inscrito) {
                $inscrito['fecha_inscripcion_fmt'] = (new DateTime($inscrito['fecha_inscripcion']))->format('d/m/Y H:i');
            }
            unset($inscrito);

            return $inscritos;

        }
        catch (PDOException $e) {
            error_log("Error PDO al obtener inscritos: " . $e->getMessage());
            throw new Exception("Error de base de datos al cargar inscritos.");
        }
    }
}
?>
