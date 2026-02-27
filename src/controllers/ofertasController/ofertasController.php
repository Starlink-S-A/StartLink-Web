<?php
// src/controllers/ofertasController/ofertasController.php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/ofertasModel/ofertasModel.php';

class OfertasController {
    private $ofertasModel;

    public function __construct() {
        try {
            $this->ofertasModel = new OfertasModel();
        } catch (Exception $e) {
            error_log("Error inicializando OfertasController: " . $e->getMessage());
        }
    }

    /**
     * Mostrar página de ofertas
     */
    public function showOfertas() {
        // Validar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL);
            exit();
        }

        // Obtener datos de sesión
        $userId = $_SESSION["user_id"];
        $idEmpresaActual = $_SESSION['id_empresa'] ?? null;
        $rolEmpresa = $_SESSION['id_rol_empresa'] ?? null;
        $rolGlobal = $_SESSION['id_rol'] ?? null;
        $esContratador = in_array($rolEmpresa, [1, 2]);
        $esUsuario = ($rolGlobal == 2);

        $mensaje = '';
        $ofertas = [];
        $ofertasConInfo = [];

        try {
            // Obtener todas las ofertas activas
            $ofertas = $this->ofertasModel->getAllActiveOfertas();

            // Enriquecer datos de ofertas con información de postulación
            foreach ($ofertas as $oferta) {
                $ofertaEnriquecida = $oferta;
                
                // Obtener estado de postulación del usuario actual
                $estadoPostulacion = $this->ofertasModel->getUserApplicationStatus($userId, $oferta['id_oferta']);
                $ofertaEnriquecida['yaPostulado'] = !empty($estadoPostulacion);
                $ofertaEnriquecida['estadoPostulacion'] = $estadoPostulacion;

                // Obtener número de participantes
                $ofertaEnriquecida['numParticipantes'] = $this->ofertasModel->getNumParticipantes($oferta['id_oferta']);

                // Validar si es creador
                $ofertaEnriquecida['esCreador'] = ($userId == ($oferta['id_creador_oferta'] ?? null));

                // Preparar URL del logo
                $ofertaEnriquecida['logoEmpresa'] = !empty($oferta['logo_ruta']) 
                    ? BASE_URL . 'assets/images/uploads/logos_empresa/' . htmlspecialchars($oferta['logo_ruta']) 
                    : BASE_URL . 'assets/images/uploads/logos_empresa/default_logo.png';

                $ofertasConInfo[] = $ofertaEnriquecida;
            }

        } catch (Exception $e) {
            error_log("Error al cargar ofertas: " . $e->getMessage());
            $mensaje = "Error al cargar las ofertas. Por favor intenta más tarde.";
            $ofertasConInfo = [];
        }

        // Variables para la vista
        $ofertas = $ofertasConInfo;

        // Cargar la vista
        require_once __DIR__ . '/../../views/ofertasView/ofertas_view.php';
    }

    /**
     * Crear nueva oferta (AJAX o formulario)
     */
    public function createOferta() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            $this->respondJson(['status' => 'error', 'message' => 'No autorizado']);
            exit();
        }

        // Validar que sea contratador
        $rolEmpresa = $_SESSION['id_rol_empresa'] ?? null;
        if (!in_array($rolEmpresa, [1, 2])) {
            $this->respondJson(['status' => 'error', 'message' => 'No tienes permisos para crear ofertas']);
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->respondJson(['status' => 'error', 'message' => 'Método HTTP no permitido']);
            exit();
        }

        try {
            $data = [
                'titulo' => trim($_POST['titulo'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'presupuesto_min' => floatval($_POST['presupuesto_min'] ?? 0),
                'presupuesto_max' => floatval($_POST['presupuesto_max'] ?? 0),
                'ubicacion' => trim($_POST['ubicacion'] ?? ''),
                'modalidad' => trim($_POST['modalidad'] ?? ''),
                'fecha_cierre' => $_POST['fecha_cierre'] ?? '',
                'requisitos' => trim($_POST['requisitos'] ?? ''),
                'limite_postulantes' => intval($_POST['limite_postulantes'] ?? 0),
                'id_empresa' => $_SESSION['id_empresa'] ?? null,
                'id_creador_oferta' => $_SESSION['user_id']
            ];

            // Validaciones
            $errores = $this->validarOferta($data);
            if (!empty($errores)) {
                $this->respondJson(['status' => 'error', 'message' => implode(', ', $errores)]);
                exit();
            }

            // Crear oferta
            $id_oferta = $this->ofertasModel->createOferta($data);
            
            if (!$id_oferta) {
                throw new Exception("No se pudo crear la oferta");
            }

            $_SESSION['mensaje'] = "Oferta creada exitosamente";
            $this->respondJson([
                'status' => 'success',
                'message' => 'Oferta creada exitosamente',
                'id_oferta' => $id_oferta
            ]);

        } catch (Exception $e) {
            error_log("Error al crear oferta: " . $e->getMessage());
            $this->respondJson(['status' => 'error', 'message' => 'Error al crear la oferta']);
        }
    }

    /**
     * Eliminar oferta
     */
    public function deleteOferta() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            $this->respondJson(['status' => 'error', 'message' => 'No autorizado']);
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $this->respondJson(['status' => 'error', 'message' => 'Método HTTP no permitido']);
            exit();
        }

        try {
            $id_oferta = intval($_POST['id_oferta'] ?? 0);
            
            if ($id_oferta <= 0) {
                throw new Exception("ID de oferta inválido");
            }

            $resultado = $this->ofertasModel->deleteOferta($id_oferta, $_SESSION['user_id']);
            
            if (!$resultado) {
                throw new Exception("No se pudo eliminar la oferta o no tienes permiso");
            }

            $_SESSION['mensaje'] = "Oferta eliminada exitosamente";
            $this->respondJson(['status' => 'success', 'message' => 'Oferta eliminada exitosamente']);

        } catch (Exception $e) {
            error_log("Error al eliminar oferta: " . $e->getMessage());
            $this->respondJson(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Validar datos de oferta
     * @param array $data
     * @return array Errores encontrados
     */
    private function validarOferta($data) {
        $errores = [];

        if (empty($data['titulo']) || strlen($data['titulo']) < 5) {
            $errores[] = "El título debe tener al menos 5 caracteres";
        }

        if (empty($data['descripcion']) || strlen($data['descripcion']) < 20) {
            $errores[] = "La descripción debe tener al menos 20 caracteres";
        }

        if ($data['presupuesto_min'] < 0) {
            $errores[] = "El presupuesto mínimo no puede ser negativo";
        }

        if ($data['presupuesto_max'] < $data['presupuesto_min']) {
            $errores[] = "El presupuesto máximo debe ser mayor o igual al mínimo";
        }

        if (empty($data['ubicacion'])) {
            $errores[] = "La ubicación es requerida";
        }

        if (!in_array($data['modalidad'], ['Presencial', 'Remoto', 'Híbrido'])) {
            $errores[] = "Modalidad inválida";
        }

        if (empty($data['fecha_cierre'])) {
            $errores[] = "La fecha de cierre es requerida";
        } else {
            try {
                $fechaCierre = new DateTime($data['fecha_cierre']);
                $hoy = new DateTime();
                if ($fechaCierre <= $hoy) {
                    $errores[] = "La fecha de cierre debe ser futura";
                }
            } catch (Exception $e) {
                $errores[] = "Formato de fecha inválido";
            }
        }

        if (empty($data['requisitos'])) {
            $errores[] = "Los requisitos son requeridos";
        }

        return $errores;
    }

    /**
     * Responder con JSON
     * @param array $data
     */
    private function respondJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
?>
