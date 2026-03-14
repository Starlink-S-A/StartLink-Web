<?php
// src/models/ofertasModel/ofertasModel.php

require_once __DIR__ . '/../../config/configuracionInicial.php';

class OfertasModel {
    private $conexion;

    public function __construct() {
        try {
            $this->conexion = getDbConnection();
            if (!$this->conexion) {
                throw new Exception("No se pudo establecer conexión con la base de datos");
            }
        } catch (Exception $e) {
            error_log("Error al conectar a la BD en OfertasModel: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener todas las ofertas activas con información de empresa
     * @return array Lista de ofertas
     */
    public function getAllActiveOfertas() {
        try {
            $sql = "
                SELECT 
                    O.id_oferta, O.titulo_oferta, O.descripcion_oferta, 
                    O.presupuesto_min, O.presupuesto_max, O.ubicacion, 
                    O.modalidad, O.fecha_cierre, O.limite_participantes,
                    O.id_empresa, O.id_creador_oferta,
                    E.nombre_empresa, E.logo_ruta
                FROM oferta_trabajo O
                LEFT JOIN empresa E ON O.id_empresa = E.id_empresa
                WHERE O.estado_oferta = 'Abierta'
                ORDER BY O.fecha_publicacion DESC
            ";

            $stmt = $this->conexion->query($sql);
            
            if (!$stmt) {
                throw new Exception("Error en la consulta: " . implode(" - ", $this->conexion->errorInfo()));
            }

            $ofertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Sanitizar datos
            foreach ($ofertas as &$oferta) {
                $oferta = $this->sanitizeOferta($oferta);
            }
            unset($oferta);

            return $ofertas;

        } catch (PDOException $e) {
            error_log("Error PDO en getAllActiveOfertas: " . $e->getMessage());
            throw new Exception("Error al cargar las ofertas");
        } catch (Exception $e) {
            error_log("Error general en getAllActiveOfertas: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener oferta por ID
     * @param int $id_oferta
     * @return array|null
     */
    public function getOfertaById($id_oferta) {
        try {
            $sql = "
                SELECT 
                    O.id_oferta, O.titulo_oferta, O.descripcion_oferta, 
                    O.presupuesto_min, O.presupuesto_max, O.ubicacion, 
                    O.modalidad, O.fecha_cierre, O.limite_participantes,
                    O.id_empresa, O.id_creador_oferta,
                    E.nombre_empresa, E.logo_ruta
                FROM oferta_trabajo O
                LEFT JOIN empresa E ON O.id_empresa = E.id_empresa
                WHERE O.id_oferta = ? AND O.estado_oferta = 'Abierta'
            ";

            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([$id_oferta]);
            
            $oferta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($oferta) {
                $oferta = $this->sanitizeOferta($oferta);
            }

            return $oferta;

        } catch (PDOException $e) {
            error_log("Error al obtener oferta por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si un usuario ya está postulado a una oferta
     * @param int $id_usuario
     * @param int $id_oferta
     * @return string|bool Estado de postulación o false si no existe
     */
    public function getUserApplicationStatus($id_usuario, $id_oferta) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT estado_postulacion 
                FROM postulacion 
                WHERE id_usuario = ? AND id_oferta = ?
            ");
            $stmt->execute([$id_usuario, $id_oferta]);
            
            $estado = $stmt->fetchColumn();
            return $estado !== false ? $estado : false;

        } catch (PDOException $e) {
            error_log("Error al verificar postulación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener cantidad de participantes en una oferta
     * @param int $id_oferta
     * @return int
     */
    public function getNumParticipantes($id_oferta) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT COUNT(*) as total 
                FROM postulacion 
                WHERE id_oferta = ?
            ");
            $stmt->execute([$id_oferta]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);

        } catch (PDOException $e) {
            error_log("Error al contar participantes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Sanitizar datos de una oferta
     * @param array $oferta
     * @return array
     */
    private function sanitizeOferta($oferta) {
        $oferta['titulo_oferta'] = htmlspecialchars($oferta['titulo_oferta'] ?? 'Sin título', ENT_QUOTES, 'UTF-8');
        $oferta['descripcion_oferta'] = htmlspecialchars($oferta['descripcion_oferta'] ?? 'Descripción no disponible', ENT_QUOTES, 'UTF-8');
        $oferta['nombre_empresa'] = htmlspecialchars($oferta['nombre_empresa'] ?? 'Empresa no especificada', ENT_QUOTES, 'UTF-8');
        
        // Validación de fechas
        if (!empty($oferta['fecha_cierre'])) {
            try {
                new DateTime($oferta['fecha_cierre']);
            } catch (Exception $e) {
                $oferta['fecha_cierre'] = 'Fecha inválida';
            }
        }

        return $oferta;
    }

    /**
     * Crear nueva oferta
     * @param array $data
     * @return bool|int ID de la oferta creada o false
     */
    public function createOferta($data) {
        try {
            $sql = "
                INSERT INTO oferta_trabajo 
                (titulo_oferta, descripcion_oferta, presupuesto_min, presupuesto_max, 
                 ubicacion, modalidad, fecha_cierre, requisitos, limite_participantes, 
                 id_empresa, id_creador_oferta, estado_oferta, fecha_publicacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Abierta', NOW())
            ";

            $stmt = $this->conexion->prepare($sql);
            
            $stmt->execute([
                $data['titulo'],
                $data['descripcion'],
                $data['presupuesto_min'],
                $data['presupuesto_max'],
                $data['ubicacion'],
                $data['modalidad'],
                $data['fecha_cierre'],
                $data['requisitos'] ?? '',
                $data['limite_postulantes'] ?? 0,
                $data['id_empresa'],
                $data['id_creador_oferta']
            ]);

            return $this->conexion->lastInsertId();

        } catch (PDOException $e) {
            error_log("Error al crear oferta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar oferta
     * @param int $id_oferta
     * @param int $id_usuario_propietario
     * @return bool
     */
    public function deleteOferta($id_oferta, $id_usuario_propietario) {
        try {
            // Verificar que el usuario sea el propietario
            $stmt = $this->conexion->prepare("
                SELECT id_creador_oferta 
                FROM oferta_trabajo 
                WHERE id_oferta = ?
            ");
            $stmt->execute([$id_oferta]);
            $oferta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$oferta || $oferta['id_creador_oferta'] != $id_usuario_propietario) {
                return false;
            }

            $stmtDelete = $this->conexion->prepare("
                DELETE FROM oferta_trabajo 
                WHERE id_oferta = ?
            ");

            return $stmtDelete->execute([$id_oferta]);

        } catch (PDOException $e) {
            error_log("Error al eliminar oferta: " . $e->getMessage());
            return false;
        }
    }
}
?>
