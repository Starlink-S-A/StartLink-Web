<?php
require_once ROOT_PATH . 'src/models/ofertasModel/detallesOfertasModel.php';

class DetallesOfertasController {
    private $model;
    private $userId;
    private $rolEmpresa;
    private $empresaId;

    public function __construct($db) {
        $this->model = new DetallesOfertasModel($db);
        $this->userId = $_SESSION["user_id"] ?? null;
        $this->rolEmpresa = $_SESSION['id_rol_empresa'] ?? null;
        $this->empresaId = $_SESSION['id_empresa'] ?? null;
    }

    public function index() {
        if (!$this->userId) {
            header("Location: bienvenida.php");
            exit();
        }

        $ofertaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$ofertaId) {
            die("Oferta no especificada.");
        }

        $oferta = $this->model->getOfertaById($ofertaId);
        if (!$oferta) {
            die("Oferta no encontrada.");
        }

        // Validar acceso
        $esCreador = $this->userId == $oferta['id_creador_oferta'];
        $esAdminEmpresa = in_array($this->rolEmpresa, [1, 2]);
        if (!$esCreador && !$esAdminEmpresa) {
            die("No tienes permisos para ver esta oferta.");
        }

        // Procesar POST
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $this->handlePost($ofertaId, $oferta);
        }

        $mensaje = $_SESSION['mensaje'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['mensaje'], $_SESSION['error']);

        $postulantes = $this->model->getPostulantesByOfertaId($ofertaId, $oferta['id_empresa']);

        // --- NAVBAR DATA (igual que OfertasController) ---
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $dbFotoPerfil = null;
        $latestNotifications = [];
        $unreadNotificationsCount = 0;
        $esAdminEmpresa = in_array($this->rolEmpresa, [1, 2]);
        $showPublishProfileLink = false;

        try {
            $db = getDbConnection();
            if ($db instanceof PDO) {
                // Foto de perfil del usuario
                $stmtUser = $db->prepare("SELECT foto_perfil FROM usuario WHERE id = ?");
                $stmtUser->execute([$this->userId]);
                $dbFotoPerfil = $stmtUser->fetchColumn();

                // Notificaciones
                $stmtNotif = $db->prepare(
                    "SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion
                     FROM notificaciones
                     WHERE user_id = ?
                     ORDER BY fecha_creacion DESC
                     LIMIT 10"
                );
                $stmtNotif->execute([$this->userId]);
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
            error_log("Error al obtener datos navbar en DetallesOfertasController: " . $e->getMessage());
        }

        // --- RESOLUCIÓN IMAGEN DE PERFIL (igual que OfertasController) ---
        $profileImage = 'https://static.thenounproject.com/png/4154905-200.png';

        $fotoPath = !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : ($dbFotoPerfil ?? null);
        if (!empty($fotoPath)) {
            $nombreArchivo = basename($fotoPath);
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
        // --- FIN NAVBAR DATA ---

        // Incluir la vista
        require_once ROOT_PATH . 'src/views/ofertasView/detalleOfertasView.php';
    }

    private function handlePost($ofertaId, $oferta) {
        if (isset($_POST['accion'], $_POST['id_postulante'])) {
            $accion = $_POST['accion'];
            $idPostulante = (int)$_POST['id_postulante'];

            if ($accion === 'Contratado') {
                $salario = (float)$_POST['salario_contratado'];
                $horasSemanales = (float)$_POST['horas_semanales_estandar'];

                // Validar salario
                if ($salario < $oferta['presupuesto_min'] || $salario > $oferta['presupuesto_max']) {
                    $_SESSION['error'] = "El salario debe estar entre $" . number_format($oferta['presupuesto_min'], 2) . 
                                        " y $" . number_format($oferta['presupuesto_max'], 2);
                    header("Location: index.php?action=detalle_oferta&id=$ofertaId");
                    exit();
                }

                // Validar horas
                if ($horasSemanales <= 0 || $horasSemanales > 168) {
                    $_SESSION['error'] = "Las horas semanales deben ser un valor positivo y razonable.";
                    header("Location: index.php?action=detalle_oferta&id=$ofertaId");
                    exit();
                }

                try {
                    $rolTrabajadorId = $this->model->getRolTrabajadorId();
                    $this->model->contratarPostulante($ofertaId, $idPostulante, $oferta['id_empresa'], $salario, $horasSemanales, $rolTrabajadorId);
                    $_SESSION['mensaje'] = "Usuario contratado con éxito.";
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error al contratar: " . $e->getMessage();
                }
            } elseif ($accion === 'Rechazado') {
                $this->model->rechazarPostulante($ofertaId, $idPostulante);
                $_SESSION['mensaje'] = "Usuario rechazado permanentemente.";
            }

            header("Location: index.php?action=detalle_oferta&id=$ofertaId");
            exit();
        }
    }
}
