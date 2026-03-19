<?php

class DetallesOfertasModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getOfertaById($ofertaId) {
        $sql = "SELECT o.*, e.nombre_empresa, o.id_creador_oferta
                FROM oferta_trabajo o
                JOIN empresa e ON o.id_empresa = e.id_empresa
                WHERE o.id_oferta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ofertaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRolTrabajadorId() {
        // According to dataset.sql, 'Candidato' is id 2, 'Empresa' is id 3.
        // The original code looked for 'TRABAJADOR'. 
        // In dataset.sql, 'rol' has 'Administrador'(1), 'Candidato'(2), 'Empresa'(3).
        // Let's look for 'Candidato' or whatever is appropriate. 
        // Based on original code line 43, it searched for 'TRABAJADOR'.
        // I'll keep it flexible but use lowercase table name.
        $stmt = $this->db->prepare("SELECT id FROM rol WHERE nombre_rol = 'TRABAJADOR' OR nombre_rol = 'Candidato' LIMIT 1");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getPostulantesByOfertaId($ofertaId, $empresaId) {
        $sql = "SELECT u.id, u.nombre, u.email, u.foto_perfil, u.dni, u.cargo, u.fecha_ingreso, u.ruta_hdv, u.salario_base,
                       p.estado_postulacion, p.rechazo_permanente,
                       ue.horas_semanales_estandar,
                       GROUP_CONCAT(DISTINCT h.nombre_habilidad ORDER BY h.nombre_habilidad SEPARATOR ', ') AS habilidades,
                       GROUP_CONCAT(DISTINCT CONCAT(el.titulo_puesto, ' en ', el.empresa_nombre, ' (',
                                       DATE_FORMAT(el.fecha_inicio,'%Y-%m'), '–',
                                       IF(el.fecha_fin IS NULL,'Presente',DATE_FORMAT(el.fecha_fin,'%Y-%m')), ')') SEPARATOR '||') AS experiencias,
                       GROUP_CONCAT(DISTINCT CONCAT(es.titulo_grado, ' en ', es.institucion, ' (',
                                       DATE_FORMAT(es.fecha_inicio,'%Y-%m'), '–',
                                       IF(es.fecha_fin IS NULL,'Presente',DATE_FORMAT(es.fecha_fin,'%Y-%m')), ')') SEPARATOR '||') AS estudios
                FROM postulacion p
                JOIN usuario u ON p.id_usuario = u.id
                LEFT JOIN usuario_empresa ue ON u.id = ue.id_usuario AND ue.id_empresa = ?
                LEFT JOIN usuario_habilidad uh ON u.id = uh.id_usuario
                LEFT JOIN habilidad h ON uh.id_habilidad = h.id_habilidad
                LEFT JOIN experiencia_laboral el ON u.id = el.id_usuario
                LEFT JOIN estudio es ON u.id = es.id_usuario
                WHERE p.id_oferta = ?
                  AND (p.rechazo_permanente IS NULL OR p.rechazo_permanente = 0)
                GROUP BY u.id, u.nombre, u.email, u.foto_perfil, u.dni, u.cargo, u.fecha_ingreso, u.ruta_hdv, u.salario_base, 
                         p.estado_postulacion, p.rechazo_permanente, ue.horas_semanales_estandar";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId, $ofertaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contratarPostulante($ofertaId, $idPostulante, $idEmpresa, $salario, $horasSemanales, $rolTrabajadorId) {
        $this->db->beginTransaction();
        try {
            // 1. Actualizar estado de postulación
            $stmt = $this->db->prepare("UPDATE postulacion SET estado_postulacion = 'Contratado', fecha_actualizacion = NOW() 
                                    WHERE id_oferta = ? AND id_usuario = ?");
            $stmt->execute([$ofertaId, $idPostulante]);
            
            // 2. Actualizar salario base del usuario
            $stmt = $this->db->prepare("UPDATE usuario SET salario_base = ? WHERE id = ?");
            $stmt->execute([$salario, $idPostulante]);
            
            // 3. Vincular usuario a la empresa
            $stmt = $this->db->prepare("SELECT 1 FROM usuario_empresa WHERE id_usuario = ? AND id_empresa = ?");
            $stmt->execute([$idPostulante, $idEmpresa]);
            if ($stmt->fetch()) {
                $stmt = $this->db->prepare("UPDATE usuario_empresa SET horas_semanales_estandar = ? WHERE id_usuario = ? AND id_empresa = ?");
                $stmt->execute([$horasSemanales, $idPostulante, $idEmpresa]);
            } else {
                // Rol 3 = Empleado (según dataset.sql INSERT INTO rol_empresa)
                $stmt = $this->db->prepare("INSERT INTO usuario_empresa (id_usuario, id_empresa, id_rol_empresa, horas_semanales_estandar) 
                                        VALUES (?, ?, 3, ?)");
                $stmt->execute([$idPostulante, $idEmpresa, $horasSemanales]);
            }

            // 4. Actualizar el rol global del usuario
            if ($rolTrabajadorId) {
                $stmt = $this->db->prepare("UPDATE usuario SET id_rol = ? WHERE id = ?");
                $stmt->execute([$rolTrabajadorId, $idPostulante]);
            }

            // 5. Marcar el perfil de búsqueda de empleo como no disponible
            $stmt = $this->db->prepare("UPDATE perfil_busqueda_empleo SET esta_disponible = FALSE WHERE id_usuario = ?");
            $stmt->execute([$idPostulante]);
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function rechazarPostulante($ofertaId, $idPostulante) {
        $stmt = $this->db->prepare("UPDATE postulacion SET estado_postulacion = 'Rechazado', rechazo_permanente = 1, fecha_actualizacion = NOW() 
                                WHERE id_oferta = ? AND id_usuario = ?");
        return $stmt->execute([$ofertaId, $idPostulante]);
    }

    public function retirarPostulacion($ofertaId, $idUsuario) {
        $stmt = $this->db->prepare("DELETE FROM postulacion WHERE id_oferta = ? AND id_usuario = ?");
        return $stmt->execute([$ofertaId, $idUsuario]);
    }
}
