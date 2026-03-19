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
        
        // Determinar si es administrador de empresa para el navbar
        $esAdminEmpresa = in_array($rolEmpresa, [1, 2]);

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

                // --- PROCESAMIENTO DEL LOGO DE LA EMPRESA ---
                $defaultCompanyImage = 'https://cdn-icons-png.flaticon.com/512/3061/3061341.png';
                $ofertaEnriquecida['logoEmpresa'] = $defaultCompanyImage;

                if (!empty($oferta['logo_ruta'])) {
                    $nombreArchivo = basename($oferta['logo_ruta']);
                    $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'logos_empresa' . DIRECTORY_SEPARATOR . $nombreArchivo;
                    $rutaPublica  = rtrim(BASE_URL, '/') . '/assets/images/Uploads/logos_empresa/' . $nombreArchivo;

                    if (file_exists($rutaAbsoluta)) {
                        $ofertaEnriquecida['logoEmpresa'] = $rutaPublica;
                    }
                }
                // --- FIN PROCESAMIENTO LOGO ---

                $ofertasConInfo[] = $ofertaEnriquecida;
            }

        } catch (Exception $e) {
            error_log("Error al cargar ofertas: " . $e->getMessage());
            $mensaje = "Error al cargar las ofertas. Por favor intenta más tarde.";
            $ofertasConInfo = [];
        }

        // Variables para la vista
        $ofertas = $ofertasConInfo;

        // Variables para el navbar
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $dbFotoPerfil = null;
        $latestNotifications = [];
        $unreadNotificationsCount = 0;

        try {
            $db = getDbConnection();
            if ($db instanceof PDO) {
                // Obtener datos del usuario (para el navbar)
                $stmtUser = $db->prepare("SELECT foto_perfil FROM usuario WHERE id = ?");
                $stmtUser->execute([$userId]);
                $dbFotoPerfil = $stmtUser->fetchColumn();

                // Notificaciones
                $stmtNotif = $db->prepare(
                    "SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion
                     FROM notificaciones
                     WHERE user_id = ?
                     ORDER BY fecha_creacion DESC
                     LIMIT 10"
                );
                $stmtNotif->execute([$userId]);
                while ($row = $stmtNotif->fetch(PDO::FETCH_ASSOC)) {
                    if (empty($row['icono'])) {
                        switch ($row['tipo']) {
                            case 'success': $row['icono'] = 'fas fa-check-circle text-success'; break;
                            case 'warning': $row['icono'] = 'fas fa-exclamation-triangle text-warning'; break;
                            case 'error':   $row['icono'] = 'fas fa-times-circle text-danger'; break;
                            default:        $row['icono'] = 'fas fa-info-circle text-info'; break;
                        }
                    } elseif (strpos($row['icono'], 'text-') === false) {
                        $row['icono'] .= ' text-primary';
                    }
                    $latestNotifications[] = $row;
                    if (!$row['leida']) {
                        $unreadNotificationsCount++;
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error al obtener notificaciones en ofertas: " . $e->getMessage());
        }

        // --- LOGICA DE RESOLUCION DE IMAGEN DE PERFIL (Consistente con misEmpresasController) ---
        $profileImage = 'https://static.thenounproject.com/png/4154905-200.png';

        // 1. Prioridad: Sesión | 2. Fallback: Base de Datos
        $fotoPath = !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : ($dbFotoPerfil ?? null);

        if (!empty($fotoPath)) {
            $nombreArchivo = basename($fotoPath);
            // Normalizar rutas usando DIRECTORY_SEPARATOR para file_exists
            $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $nombreArchivo;
            $rutaPublica  = rtrim(BASE_URL, '/') . '/assets/images/Uploads/profile_pictures/' . $nombreArchivo;

            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
                // Sincronizar sesión si vino de la BD
                if (empty($_SESSION['foto_perfil'])) {
                    $_SESSION['foto_perfil'] = 'assets/images/Uploads/profile_pictures/' . $nombreArchivo;
                }
            }
        }

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
            $_SESSION['mensaje'] = 'No tienes permisos para crear ofertas';
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $_SESSION['mensaje'] = 'Método no permitido';
            header("Location: " . BASE_URL . "index.php?action=ofertas");
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

            $errores = $this->validarOferta($data);
            if (!empty($errores)) {
                $_SESSION['mensaje'] = implode(', ', $errores);
                header("Location: " . BASE_URL . "index.php?action=ofertas");
                exit();
            }

            $id_oferta = $this->ofertasModel->createOferta($data);
            
            if (!$id_oferta) {
                throw new Exception("No se pudo crear la oferta");
            }

            $_SESSION['mensaje'] = "Oferta creada exitosamente";
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();

        } catch (Exception $e) {
            error_log("Error al crear oferta: " . $e->getMessage());
            $_SESSION['mensaje'] = 'Error al crear la oferta: ' . escapeshellarg($e->getMessage());
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();
        }
    }

    /**
     * Actualizar oferta existente
     */
    public function updateOferta() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL);
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            $_SESSION['mensaje'] = 'Método no permitido';
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();
        }

        try {
            $data = [
                'id_oferta' => intval($_POST['id_oferta'] ?? 0),
                'titulo' => trim($_POST['titulo'] ?? ''),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'presupuesto_min' => floatval($_POST['presupuesto_min'] ?? 0),
                'presupuesto_max' => floatval($_POST['presupuesto_max'] ?? 0),
                'ubicacion' => trim($_POST['ubicacion'] ?? ''),
                'modalidad' => trim($_POST['modalidad'] ?? ''),
                'fecha_cierre' => $_POST['fecha_cierre'] ?? '',
                'requisitos' => trim($_POST['requisitos'] ?? ''),
                'limite_postulantes' => intval($_POST['limite_postulantes'] ?? 0),
                'id_creador_oferta' => $_SESSION['user_id']
            ];

            if ($data['id_oferta'] <= 0) {
                throw new Exception("ID de oferta inválido");
            }

            $errores = $this->validarOferta($data);
            if (!empty($errores)) {
                $_SESSION['mensaje'] = implode(', ', $errores);
                header("Location: " . BASE_URL . "index.php?action=ofertas");
                exit();
            }

            $resultado = $this->ofertasModel->updateOferta($data);
            
            if (!$resultado) {
                throw new Exception("No se pudo actualizar la oferta o no tienes permiso");
            }

            $_SESSION['mensaje'] = "Oferta actualizada exitosamente";
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();

        } catch (Exception $e) {
            error_log("Error al actualizar oferta: " . $e->getMessage());
            $_SESSION['mensaje'] = 'Error: ' . $e->getMessage();
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();
        }
    }

    /**
     * Salir / retirar postulación propia
     */
    public function salirOferta() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL . "bienvenida.php");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();
        }

        $userId   = $_SESSION["user_id"];
        $ofertaId = intval($_POST['id_oferta'] ?? 0);

        if ($ofertaId <= 0) {
            $_SESSION['mensaje'] = "Oferta inválida.";
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();
        }

        try {
            $db    = getDbConnection();
            $model = new \DetallesOfertasModel($db);

            // Verificar si el usuario fue rechazado permanentemente
            $stmtCheck = $db->prepare(
                "SELECT rechazo_permanente FROM postulacion WHERE id_oferta = ? AND id_usuario = ? LIMIT 1"
            );
            $stmtCheck->execute([$ofertaId, $userId]);
            $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($row && $row['rechazo_permanente'] == 1) {
                $_SESSION['mensaje'] = "No puedes retirarte de esta oferta porque fuiste rechazado permanentemente.";
                header("Location: " . BASE_URL . "index.php?action=ofertas");
                exit();
            }

            $ok = $model->retirarPostulacion($ofertaId, $userId);

            $_SESSION['mensaje'] = $ok
                ? "Has salido de la postulación correctamente."
                : "No se pudo procesar tu solicitud.";

        } catch (Exception $e) {
            error_log("Error en salirOferta: " . $e->getMessage());
            $_SESSION['mensaje'] = "Error al procesar la solicitud.";
        }

        header("Location: " . BASE_URL . "index.php?action=ofertas");
        exit();
    }

    /**
     * Eliminar oferta
     */
    public function deleteOferta() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL . "bienvenida.php");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: " . BASE_URL . "index.php?action=ofertas");
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

        } catch (Exception $e) {
            error_log("Error al eliminar oferta: " . $e->getMessage());
            $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        }

        header("Location: " . BASE_URL . "index.php?action=ofertas");
        exit();
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
     * Postular a una oferta
     */
    public function postular() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL . "bienvenida.php");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();
        }

        $userId = $_SESSION["user_id"];
        $ofertaId = intval($_POST['id_oferta'] ?? 0);

        if ($ofertaId <= 0) {
            $_SESSION['mensaje'] = "Oferta inválida";
            header("Location: " . BASE_URL . "index.php?action=ofertas");
            exit();
        }

        try {
            // Verificar si el usuario tiene rol de TRABAJADOR (id_rol = 2)
            if ($_SESSION['id_rol'] != 2) {
                $_SESSION['mensaje'] = "Solo los candidatos pueden postularse a ofertas.";
                header("Location: " . BASE_URL . "index.php?action=ofertas");
                exit();
            }

            $resultado = $this->ofertasModel->postular($userId, $ofertaId);

            if ($resultado) {
                $_SESSION['mensaje'] = "¡Postulación exitosa! Ya formas parte de esta oferta.";
            } else {
                $_SESSION['mensaje'] = "No se pudo realizar la postulación o ya estás postulado.";
            }

        } catch (Exception $e) {
            error_log("Error en postular: " . $e->getMessage());
            $_SESSION['mensaje'] = "Error al procesar la postulación.";
        }

        header("Location: " . BASE_URL . "index.php?action=ofertas");
        exit();
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
