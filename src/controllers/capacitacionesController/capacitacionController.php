<?php
// src/controllers/capacitacionesController/capacitacionController.php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/capacitacionesModel/capacitacionModel.php';
require_once __DIR__ . '/../../models/empresasModel/MisEmpresasModel.php';

class CapacitacionController {
    private $capacitacionModel;

    public function __construct() {
        try {
            $this->capacitacionModel = new CapacitacionModel();
        } catch (Exception $e) {
            error_log("Error inicializando CapacitacionController: " . $e->getMessage());
        }
    }

    /**
     * Mostrar página de capacitaciones disponibles
     * HU-14: Visible para todos los usuarios
     * HU-15: Ver detalle completo antes de inscribirse
     */
    public function showCapacitaciones() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validación de sesión
        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL);
            exit();
        }

        $userId = $_SESSION["user_id"];
        $rolGlobal = $_SESSION['id_rol'] ?? null;
        $rolEmpresa = $_SESSION['id_rol_empresa'] ?? null;

        // HU-14: Solo ADMINISTRADOR global, o Administrador/Contratador de empresa pueden crear
        $puedeCrearCapacitacion = ($rolGlobal == 1 || in_array($rolEmpresa, [1, 2]));

        $mensaje = '';
        $tipoMensaje = 'info';

        // Cargar mensajes de sesión
        if (isset($_SESSION['mensaje_capacitacion'])) {
            $mensaje = $_SESSION['mensaje_capacitacion'];
            $tipoMensaje = $_SESSION['tipo_mensaje_capacitacion'] ?? 'success';
            unset($_SESSION['mensaje_capacitacion']);
            unset($_SESSION['tipo_mensaje_capacitacion']);
        }

        $capacitaciones = [];

        try {
            $capacitaciones = $this->capacitacionModel->getAllActiveCapacitaciones();

            // Verificar inscripción y permisos para cada capacitación
            foreach ($capacitaciones as &$cap) {
                // HU-15: Verificar si el usuario ya está inscrito
                $estadoInscripcion = $this->capacitacionModel->checkInscripcion($userId, $cap['id']);
                $cap['yaInscrito'] = !empty($estadoInscripcion);
                $cap['estadoInscripcion'] = $estadoInscripcion;

                // HU-14/HU-16: Determinar permisos de gestión
                // Creador, ADMINISTRADOR global, o Admin de Empresa
                $cap['puedeGestionarCapacitacion'] = ($cap['creador_id'] == $userId || $rolGlobal == 1 || $rolEmpresa == 1);
            }
            unset($cap);

        } catch (Exception $e) {
            error_log("Error al cargar capacitaciones: " . $e->getMessage());
            $mensaje = "Error al cargar las capacitaciones. Por favor intenta más tarde.";
            $tipoMensaje = 'danger';
        }

        // Variables para el navbar (consistente con ofertasController)
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $esAdminEmpresa = in_array($rolEmpresa, [1, 2]);

        // Cargar empresas del usuario para el selector de crear capacitación
        $empresasUsuario = [];
        try {
            $misEmpresasModel = new MisEmpresasModel();
            $empresasUsuario = $misEmpresasModel->getEmpresasUsuario($userId);
        } catch (Exception $e) {
            error_log("Error al cargar empresas del usuario: " . $e->getMessage());
        }
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
            error_log("Error al obtener notificaciones en capacitaciones: " . $e->getMessage());
        }

        // Resolución de imagen de perfil (consistente con ofertasController)
        $profileImage = 'https://static.thenounproject.com/png/4154905-200.png';
        $fotoPath = !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : ($dbFotoPerfil ?? null);

        if (!empty($fotoPath)) {
            $nombreArchivo = basename($fotoPath);
            $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $nombreArchivo;
            $rutaPublica  = rtrim(BASE_URL, '/') . '/assets/images/Uploads/profile_pictures/' . $nombreArchivo;

            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
                if (empty($_SESSION['foto_perfil'])) {
                    $_SESSION['foto_perfil'] = 'assets/images/Uploads/profile_pictures/' . $nombreArchivo;
                }
            }
        }

        // Cargar la vista
        require_once __DIR__ . '/../../views/capacitacionesView/capacitaciones_view.php';
    }

    /**
     * Crear una nueva capacitación
     * HU-14: Formulario con nombre, descripción, fechas inicio/fin y costo
     * HU-14: Vinculada al creador y su empresa
     */
    public function crearCapacitacion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Redireccionar si no hay sesión activa
        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL);
            exit();
        }

        $userId = $_SESSION["user_id"];
        $rolGlobal = $_SESSION['id_rol'] ?? null;
        $rolEmpresa = $_SESSION['id_rol_empresa'] ?? null;

        // Inicializar mensaje y tipo de mensaje
        $_SESSION['mensaje_capacitacion'] = '';
        $_SESSION['tipo_mensaje_capacitacion'] = 'danger';

        // HU-14: Solo ADMINISTRADOR global, o Administrador/Contratador de empresa
        $puedeCrearCapacitacion = ($rolGlobal == 1 || in_array($rolEmpresa, [1, 2]));

        if (!$puedeCrearCapacitacion) {
            $_SESSION['mensaje_capacitacion'] = 'No tienes permiso para crear capacitaciones.';
            header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
            exit();
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_capacitacion'])) {
            // Recopilar y validar datos del formulario
            $nombre_capacitacion = filter_input(INPUT_POST, 'nombre_capacitacion', FILTER_SANITIZE_STRING);
            $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
            $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_STRING);
            $fecha_fin = filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_STRING);
            $costo = filter_input(INPUT_POST, 'costo', FILTER_VALIDATE_FLOAT);

            // Validaciones básicas
            if (empty($nombre_capacitacion) || empty($descripcion) || empty($fecha_inicio) || empty($fecha_fin) || $costo === false || $costo < 0) {
                $_SESSION['mensaje_capacitacion'] = 'Todos los campos son obligatorios y el costo debe ser un número válido.';
                header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
                exit();
            }

            // Validar fechas
            $dateInicio = new DateTime($fecha_inicio);
            $dateFin = new DateTime($fecha_fin);
            $hoy = new DateTime();
            $hoy->setTime(0, 0, 0);

            if ($dateInicio < $hoy) {
                $_SESSION['mensaje_capacitacion'] = 'La fecha de inicio de la capacitación no puede ser anterior a hoy.';
                header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
                exit();
            }

            if ($dateFin < $dateInicio) {
                $_SESSION['mensaje_capacitacion'] = 'La fecha de fin de la capacitación no puede ser anterior a la fecha de inicio.';
                header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
                exit();
            }

            try {
                $data = [
                    'nombre_capacitacion' => $nombre_capacitacion,
                    'descripcion' => $descripcion,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'costo' => $costo,
                    'creador_id' => $userId,
                    'id_empresa' => filter_input(INPUT_POST, 'id_empresa', FILTER_VALIDATE_INT) ?: null
                ];

                $resultado = $this->capacitacionModel->crearCapacitacion($data);

                if ($resultado) {
                    $_SESSION['mensaje_capacitacion'] = 'Capacitación publicada exitosamente.';
                    $_SESSION['tipo_mensaje_capacitacion'] = 'success';
                } else {
                    $_SESSION['mensaje_capacitacion'] = 'Error al publicar la capacitación.';
                    $_SESSION['tipo_mensaje_capacitacion'] = 'danger';
                }

            } catch (Exception $e) {
                error_log("Error al crear capacitación: " . $e->getMessage());
                $_SESSION['mensaje_capacitacion'] = $e->getMessage();
                $_SESSION['tipo_mensaje_capacitacion'] = 'danger';
            }
        } else {
            $_SESSION['mensaje_capacitacion'] = 'Solicitud no válida para crear capacitación.';
            $_SESSION['tipo_mensaje_capacitacion'] = 'danger';
        }

        header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
        exit();
    }

    /**
     * Editar una capacitación existente
     */
    public function editarCapacitacion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL);
            exit();
        }

        $userId = $_SESSION["user_id"];
        $rolGlobal = $_SESSION['id_rol'] ?? null;
        $rolEmpresa = $_SESSION['id_rol_empresa'] ?? null;

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_capacitacion'])) {
            $id_capacitacion = filter_input(INPUT_POST, 'id_capacitacion', FILTER_VALIDATE_INT);
            $nombre_capacitacion = filter_input(INPUT_POST, 'nombre_capacitacion', FILTER_SANITIZE_STRING);
            $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
            $fecha_inicio = filter_input(INPUT_POST, 'fecha_inicio', FILTER_SANITIZE_STRING);
            $fecha_fin = filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_STRING);
            $costo = filter_input(INPUT_POST, 'costo', FILTER_VALIDATE_FLOAT);

            if (!$id_capacitacion || empty($nombre_capacitacion) || empty($descripcion) || empty($fecha_inicio) || empty($fecha_fin) || $costo === false || $costo < 0) {
                $_SESSION['mensaje_capacitacion'] = 'Validación fallida. Revisa los datos.';
                $_SESSION['tipo_mensaje_capacitacion'] = 'danger';
                header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
                exit();
            }

            // Verificar permisos para editar
            $creadorData = $this->capacitacionModel->getCreadorId($id_capacitacion);
            if (!$creadorData || ($creadorData['creador_id'] != $userId && $rolGlobal != 1 && $rolEmpresa != 1)) {
                $_SESSION['mensaje_capacitacion'] = 'No tienes permiso para editar esta capacitación.';
                $_SESSION['tipo_mensaje_capacitacion'] = 'danger';
                header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
                exit();
            }

            try {
                $data = [
                    'nombre_capacitacion' => $nombre_capacitacion,
                    'descripcion' => $descripcion,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'costo' => $costo
                ];

                if ($this->capacitacionModel->actualizarCapacitacion($id_capacitacion, $data)) {
                    $_SESSION['mensaje_capacitacion'] = 'Capacitación actualizada exitosamente.';
                    $_SESSION['tipo_mensaje_capacitacion'] = 'success';
                } else {
                    $_SESSION['mensaje_capacitacion'] = 'No se pudo actualizar la capacitación.';
                    $_SESSION['tipo_mensaje_capacitacion'] = 'danger';
                }
            } catch (Exception $e) {
                $_SESSION['mensaje_capacitacion'] = $e->getMessage();
                $_SESSION['tipo_mensaje_capacitacion'] = 'danger';
            }
        }
        
        header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
        exit();
    }

    /**
     * Eliminar una capacitación
     * HU-14: Solo creador, ADMINISTRADOR global o Admin de Empresa pueden eliminarla
     */
    public function eliminarCapacitacion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            header("Location: " . BASE_URL);
            exit();
        }

        $userId = $_SESSION["user_id"];
        $rolGlobal = $_SESSION['id_rol'] ?? null;
        $rolEmpresa = $_SESSION['id_rol_empresa'] ?? null;

        $_SESSION['mensaje_capacitacion'] = '';
        $_SESSION['tipo_mensaje_capacitacion'] = 'danger';

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_capacitacion'])) {
            $idCapacitacion = filter_input(INPUT_POST, 'id_capacitacion', FILTER_VALIDATE_INT);

            if (!$idCapacitacion) {
                $_SESSION['mensaje_capacitacion'] = 'ID de capacitación no válido para eliminar.';
                header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
                exit();
            }

            try {
                // Obtener el creador_id de la capacitación
                $capacitacion = $this->capacitacionModel->getCreadorId($idCapacitacion);

                if (!$capacitacion) {
                    $_SESSION['mensaje_capacitacion'] = 'Capacitación no encontrada.';
                    header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
                    exit();
                }

                $creadorIdCapacitacion = $capacitacion['creador_id'];

                // HU-14: Verificar permisos para eliminar
                $puedeEliminar = ($creadorIdCapacitacion == $userId || $rolGlobal == 1 || $rolEmpresa == 1);

                if (!$puedeEliminar) {
                    $_SESSION['mensaje_capacitacion'] = 'No tienes permiso para eliminar esta capacitación.';
                    header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
                    exit();
                }

                if ($this->capacitacionModel->eliminarCapacitacion($idCapacitacion)) {
                    $_SESSION['mensaje_capacitacion'] = 'Capacitación eliminada exitosamente.';
                    $_SESSION['tipo_mensaje_capacitacion'] = 'success';
                } else {
                    $_SESSION['mensaje_capacitacion'] = 'Error al eliminar la capacitación.';
                }

            } catch (Exception $e) {
                error_log("Error al eliminar capacitación: " . $e->getMessage());
                $_SESSION['mensaje_capacitacion'] = $e->getMessage();
            }
        } else {
            $_SESSION['mensaje_capacitacion'] = 'Solicitud no válida para eliminar capacitación.';
        }

        header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
        exit();
    }

    /**
     * Gestionar inscripción (inscribir o cancelar)
     * HU-15: Todos los usuarios pueden inscribirse
     * HU-15: Puede cancelar inscripción
     */
    public function gestionarInscripcion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            $_SESSION['mensaje_capacitacion'] = "Debes iniciar sesión para inscribirte.";
            $_SESSION['tipo_mensaje_capacitacion'] = "danger";
            header("Location: " . BASE_URL);
            exit();
        }

        $userId = $_SESSION["user_id"];
        $capacitacionId = isset($_POST['id_capacitacion']) ? (int)$_POST['id_capacitacion'] : 0;
        $accion = $_POST['accion'] ?? '';

        if (!$capacitacionId) {
            $_SESSION['mensaje_capacitacion'] = "Capacitación no especificada.";
            $_SESSION['tipo_mensaje_capacitacion'] = "danger";
            header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
            exit();
        }

        try {
            // Verificar si la capacitación existe
            if (!$this->capacitacionModel->existeCapacitacion($capacitacionId)) {
                throw new Exception("La capacitación seleccionada no existe.");
            }

            if ($accion === 'inscribir') {
                // HU-15: Verificar si ya está inscrito
                if ($this->capacitacionModel->estaInscrito($userId, $capacitacionId)) {
                    $_SESSION['mensaje_capacitacion'] = "Ya estás inscrito en esta capacitación.";
                    $_SESSION['tipo_mensaje_capacitacion'] = "warning";
                } else {
                    // HU-15: Realizar inscripción
                    $this->capacitacionModel->inscribir($userId, $capacitacionId);
                    $_SESSION['mensaje_capacitacion'] = "¡Te has inscrito correctamente en la capacitación!";
                    $_SESSION['tipo_mensaje_capacitacion'] = "success";
                }
            } elseif ($accion === 'cancelar') {
                // HU-15: Cancelar inscripción
                $filasAfectadas = $this->capacitacionModel->cancelarInscripcion($userId, $capacitacionId);

                if ($filasAfectadas > 0) {
                    $_SESSION['mensaje_capacitacion'] = "Tu inscripción ha sido cancelada.";
                    $_SESSION['tipo_mensaje_capacitacion'] = "info";
                } else {
                    $_SESSION['mensaje_capacitacion'] = "No se encontró una inscripción para cancelar.";
                    $_SESSION['tipo_mensaje_capacitacion'] = "warning";
                }
            } else {
                $_SESSION['mensaje_capacitacion'] = "Acción no válida.";
                $_SESSION['tipo_mensaje_capacitacion'] = "danger";
            }

        } catch (PDOException $e) {
            error_log("Error en base de datos al gestionar inscripción: " . $e->getMessage());
            $_SESSION['mensaje_capacitacion'] = "Error al procesar tu solicitud. Por favor intenta nuevamente.";
            $_SESSION['tipo_mensaje_capacitacion'] = "danger";
        } catch (Exception $e) {
            error_log("Error general al gestionar inscripción: " . $e->getMessage());
            $_SESSION['mensaje_capacitacion'] = $e->getMessage();
            $_SESSION['tipo_mensaje_capacitacion'] = "danger";
        }

        header("Location: " . BASE_URL . "src/index.php?action=capacitaciones");
        exit();
    }

    /**
     * Obtener inscritos de una capacitación (respuesta JSON para AJAX)
     * HU-16: Solo creador, ADMINISTRADOR global o Admin de Empresa pueden ver la lista
     * HU-16: Muestra nombre completo, correo y fecha/hora de inscripción
     */
    public function obtenerInscritos() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => ''];

        // 1. Validar sesión
        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            $response['message'] = 'Acceso no autorizado. Por favor, inicia sesión.';
            echo json_encode($response);
            exit();
        }

        $userId = $_SESSION["user_id"];
        $rolGlobal = $_SESSION['id_rol'] ?? null;
        $rolEmpresa = $_SESSION['id_rol_empresa'] ?? null;

        // 2. Obtener el ID de la capacitación
        $idCapacitacion = filter_input(INPUT_GET, 'id_capacitacion', FILTER_VALIDATE_INT);

        if (!$idCapacitacion) {
            $response['message'] = 'ID de capacitación no válido.';
            echo json_encode($response);
            exit();
        }

        try {
            // 3. HU-16: Verificar permisos
            $capacitacion = $this->capacitacionModel->getCreadorId($idCapacitacion);

            if (!$capacitacion) {
                $response['message'] = 'Capacitación no encontrada.';
                echo json_encode($response);
                exit();
            }

            $creadorIdCapacitacion = $capacitacion['creador_id'];
            $puedeVerInscritos = ($creadorIdCapacitacion == $userId || $rolGlobal == 1 || $rolEmpresa == 1);

            if (!$puedeVerInscritos) {
                $response['message'] = 'No tienes permiso para ver los inscritos de esta capacitación.';
                echo json_encode($response);
                exit();
            }

            // 4. HU-16: Obtener la lista de inscritos
            $inscritos = $this->capacitacionModel->getInscritos($idCapacitacion);

            $response['success'] = true;
            $response['inscritos'] = $inscritos;

        } catch (PDOException $e) {
            error_log("Error PDO al obtener inscritos: " . $e->getMessage());
            $response['message'] = "Error de base de datos al cargar inscritos.";
        } catch (Exception $e) {
            error_log("Error general al obtener inscritos: " . $e->getMessage());
            $response['message'] = "Ocurrió un error inesperado al cargar inscritos.";
        }

        echo json_encode($response);
        exit();
    }
}
?>
