<?php
// src/models/empresasModel/crearEmpresaModel/crearEmpresaModel.php

require_once __DIR__ . '/../../../config/configuracionInicial.php';
require_once __DIR__ . '/../../userModel/User.php';

class EmpresasModel {
    private $pdo;
    private $userModel;

    public function __construct() {
        $this->pdo = getDbConnection();
        $this->userModel = new User();
    }

    /**
     * Comprueba si ya existe una empresa con el mismo nombre.
     * @param string $nombre
     * @return bool
     */
    public function companyExistsByName($nombre) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM EMPRESA WHERE nombre_empresa = ?");
            $stmt->execute([$nombre]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en companyExistsByName: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserta nueva empresa en la base de datos.
     * @param array $data
     * @return int|false ID de la empresa creada o false en error
     */
    public function createCompany($data) {
        try {
            $sql = "INSERT INTO EMPRESA (
                        nombre_empresa, descripcion, email_contacto, telefono_contacto,
                        pais, departamento, ciudad, url_sitio_web, logo_ruta
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['nombre_empresa'],
                $data['descripcion'],
                $data['email_contacto'],
                $data['telefono_contacto'],
                $data['pais'],
                $data['departamento'],
                $data['ciudad'],
                $data['url_sitio_web'],
                $data['logo_ruta']
            ]);

            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear empresa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asocia un usuario con una empresa y rol dentro de ella.
     * @param int $userId
     * @param int $empresaId
     * @param int $rolEmpresa
     * @return bool
     */
    public function linkUserToCompany($userId, $empresaId, $rolEmpresa) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO USUARIO_EMPRESA (id_usuario, id_empresa, id_rol_empresa) VALUES (?, ?, ?)");
            return $stmt->execute([$userId, $empresaId, $rolEmpresa]);
        } catch (PDOException $e) {
            error_log("Error al enlazar usuario a empresa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el rol global del usuario.
     * @param int $userId
     * @param int $rolGlobal
     * @return bool
     */
    public function updateUserRoleGlobal($userId, $rolGlobal) {
        return $this->userModel->updateUser($userId, ['id_rol' => $rolGlobal]);
    }
}
?>
