<?php
// src/models/adminGlobalModel/AdminGlobalModel.php

require_once __DIR__ . '/../../config/configuracionInicial.php';

class AdminGlobalModel {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getAllUsersMetrics() {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW() THEN 1 ELSE 0 END) as suspendidos,
                SUM(CASE WHEN (bloqueado_hasta IS NULL OR bloqueado_hasta <= NOW()) AND (nombre IS NULL OR nombre = '') THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN (bloqueado_hasta IS NULL OR bloqueado_hasta <= NOW()) AND nombre IS NOT NULL AND nombre != '' THEN 1 ELSE 0 END) as activos
            FROM usuario
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total' => (int)($row['total'] ?? 0),
            'suspendidos' => (int)($row['suspendidos'] ?? 0),
            'pendientes' => (int)($row['pendientes'] ?? 0),
            'activos' => (int)($row['activos'] ?? 0)
        ];
    }

    public function getAllUsersWithCompanyInfo() {
        // Hacemos LEFT JOIN con empresa y GROUP_CONCAT para agrupar usuarios
        $stmt = $this->pdo->query("
            SELECT 
                u.id, u.nombre, u.email, u.telefono, DATE(u.fecha_registro) as fecha_ingreso,
                u.bloqueado_hasta, u.id_rol,
                r.nombre_rol,
                GROUP_CONCAT(e.nombre_empresa SEPARATOR ', ') as empresas
            FROM usuario u
            JOIN rol r ON u.id_rol = r.id
            LEFT JOIN usuario_empresa ue ON ue.id_usuario = u.id
            LEFT JOIN empresa e ON ue.id_empresa = e.id_empresa
            GROUP BY u.id, u.nombre, u.email, u.telefono, u.fecha_registro, u.bloqueado_hasta, u.id_rol, r.nombre_rol
            ORDER BY u.id DESC
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Asignamos el campo lógico para el estado
        foreach ($results as &$row) {
            if (!empty($row['bloqueado_hasta']) && strtotime($row['bloqueado_hasta']) > time()) {
                $row['estado_logico'] = 'Suspendido';
            } elseif (empty($row['nombre'])) {
                $row['estado_logico'] = 'Pendiente';
            } else {
                $row['estado_logico'] = 'Activo';
            }

            // [Corrección de lectura Rol Global/Empresa]
            // Si el usuario es administrador de una o varias empresas, pero en DB su rol global
            // está estancado en 2 (Candidato), forzamos visualmente a que el administrador
            // global lo reconozca como un Contratador/Empresa
            if (!empty($row['empresas']) && $row['id_rol'] == 2) {
                $row['nombre_rol'] = 'Empresa/Contratador';
                $row['id_rol'] = 3;
            }

            // Normalizar nombres si están nulos
            if (empty($row['nombre'])) {
                $row['nombre'] = 'Usuario Sin Completar';
            }
        }
        return $results;
    }

    public function changeUserRole($userId, $newRoleId) {
        $stmt = $this->pdo->prepare("UPDATE usuario SET id_rol = ? WHERE id = ?");
        return $stmt->execute([(int)$newRoleId, (int)$userId]);
    }

    public function suspendAccount($userId) {
        $stmt = $this->pdo->prepare("UPDATE usuario SET bloqueado_hasta = '2099-12-31 23:59:59' WHERE id = ?");
        return $stmt->execute([(int)$userId]);
    }

    public function activateAccount($userId) {
        $stmt = $this->pdo->prepare("UPDATE usuario SET bloqueado_hasta = NULL WHERE id = ?");
        return $stmt->execute([(int)$userId]);
    }

    // --- MÉTODOS PARA EMPRESAS --- //
    public function getAllEmpresasMetrics() {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_empresas,
                SUM(CASE WHEN estado = 'Activa' THEN 1 ELSE 0 END) as activas,
                SUM(CASE WHEN estado = 'Suspendida' THEN 1 ELSE 0 END) as suspendidas
            FROM empresa
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total' => (int)($row['total_empresas'] ?? 0),
            'activos' => (int)($row['activas'] ?? 0),
            'suspendidos' => (int)($row['suspendidas'] ?? 0)
        ];
    }

    public function getAllEmpresas() {
        $stmt = $this->pdo->query("
            SELECT 
                e.id_empresa, e.nombre_empresa, e.descripcion, e.email_contacto, e.telefono_contacto,
                e.pais, e.departamento, e.ciudad, e.url_sitio_web, e.logo_ruta, DATE(e.fecha_registro) as fecha_registro, e.estado,
                COUNT(ue.id_usuario) as total_admin_candidato
            FROM empresa e
            LEFT JOIN usuario_empresa ue ON e.id_empresa = ue.id_empresa
            GROUP BY e.id_empresa
            ORDER BY e.id_empresa DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
