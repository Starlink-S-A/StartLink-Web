<?php
// src/controllers/nominaController/nominaController.php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/nominaModel/nominaModel.php';

class NominaController
{
    private $nominaModel;

    public function __construct()
    {
        try {
            $this->nominaModel = new NominaModel();
        } catch (Exception $e) {
            error_log("Error inicializando NominaController: " . $e->getMessage());
        }
    }

    // ───────────────────────────────────────────
    // Helpers de sesión y roles
    // ───────────────────────────────────────────
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function requireLogin(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id']) || $_SESSION['loggedin'] !== true) {
            header("Location: " . BASE_URL);
            exit();
        }
    }

    private function getSessionData(): array
    {
        return [
            'userId'     => (int)$_SESSION['user_id'],
            'userName'   => $_SESSION['user_name'] ?? 'Usuario',
            'rolGlobal'  => (int)($_SESSION['id_rol'] ?? 0),
            'rolEmpresa' => (int)($_SESSION['id_rol_empresa'] ?? 0),
            'idEmpresa'  => (int)($_SESSION['id_empresa'] ?? 0),
        ];
    }

    /**
     * HU-25 / HU-26: Vista principal de Nóminas
     * Admin Global → todas las nóminas
     * Admin Empresa → nóminas de su empresa + formulario generar
     * Trabajador → solo sus propias nóminas
     */
    public function showNominas(): void
    {
        $this->requireLogin();
        $s = $this->getSessionData();

        $esAdminGlobal  = ($s['rolGlobal'] === 1);
        $esAdminEmpresa = ($s['rolEmpresa'] === 1);
        $esTrabajador   = ($s['rolGlobal'] === 3 || $s['rolEmpresa'] === 3);
        $puedeGenerar   = ($esAdminGlobal || $esAdminEmpresa);

        // Cargar mensaje flash de sesión
        $mensaje     = $_SESSION['mensaje_nomina'] ?? '';
        $tipoMensaje = $_SESSION['tipo_mensaje_nomina'] ?? 'success';
        unset($_SESSION['mensaje_nomina'], $_SESSION['tipo_mensaje_nomina']);

        // Obtener nóminas según rol
        $nominas      = [];
        $trabajadores = [];
        $desempenos   = [];

        try {
            if ($esAdminGlobal) {
                $nominas = $this->nominaModel->getAllNominas();
            } elseif ($esAdminEmpresa && $s['idEmpresa']) {
                $nominas      = $this->nominaModel->getNominasByEmpresa($s['idEmpresa']);
                $trabajadores = $this->nominaModel->getTrabajadoresDeEmpresa($s['idEmpresa']);
                $desempenos   = $this->nominaModel->getDesempenoByEmpresa($s['idEmpresa']);
            } else {
                // Trabajador: solo sus propias nóminas y desempeño
                $nominas    = $this->nominaModel->getNominasByUsuario($s['userId']);
                $desempenos = $this->nominaModel->getDesempenoByUsuario($s['userId']);
            }
        } catch (Exception $e) {
            error_log("Error al cargar historial: " . $e->getMessage());
            $mensaje     = "Error al cargar el historial. Inténtalo de nuevo.";
            $tipoMensaje = "danger";
        }

        // Pasar variables a la vista
        $rolGlobal    = $s['rolGlobal'];
        $rolEmpresa   = $s['rolEmpresa'];
        $userId       = $s['userId'];
        $userName     = $s['userName'];

        require_once __DIR__ . '/../../views/nominasView/nominas_view.php';
    }

    /**
     * HU-25: Generar nómina (POST)
     * Solo Admin Empresa o Admin Global
     */
    public function generarNomina(): void
    {
        $this->requireLogin();
        $s = $this->getSessionData();

        $esAdminGlobal  = ($s['rolGlobal'] === 1);
        $esAdminEmpresa = ($s['rolEmpresa'] === 1);

        if (!$esAdminGlobal && !$esAdminEmpresa) {
            $_SESSION['mensaje_nomina']      = "No tienes permisos para generar nóminas.";
            $_SESSION['tipo_mensaje_nomina'] = "danger";
            header("Location: " . BASE_URL . "src/index.php?action=nominas");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "src/index.php?action=nominas");
            exit();
        }

        // Validar y sanitizar entradas
        $idUsuario        = filter_input(INPUT_POST, 'id_usuario',          FILTER_VALIDATE_INT);
        $horasTrabajadas  = filter_input(INPUT_POST, 'horas_trabajadas',    FILTER_VALIDATE_FLOAT);
        $tarifaHora       = filter_input(INPUT_POST, 'tarifa_hora',         FILTER_VALIDATE_FLOAT);
        $deducciones      = filter_input(INPUT_POST, 'deducciones',         FILTER_VALIDATE_FLOAT);
        $bonificaciones   = filter_input(INPUT_POST, 'bonificaciones',      FILTER_VALIDATE_FLOAT) ?? 0.0;
        $horasExtras      = filter_input(INPUT_POST, 'horas_extras',        FILTER_VALIDATE_FLOAT) ?? 0.0;
        $fechaInicio      = filter_input(INPUT_POST, 'fecha_inicio_periodo', FILTER_SANITIZE_SPECIAL_CHARS);
        $fechaFin         = filter_input(INPUT_POST, 'fecha_fin_periodo',    FILTER_SANITIZE_SPECIAL_CHARS);

        // El admin no puede enviarse nómina a sí mismo
        if ($idUsuario === $s['userId']) {
            $_SESSION['mensaje_nomina']      = "No puedes generar una nómina para ti mismo.";
            $_SESSION['tipo_mensaje_nomina'] = "warning";
            header("Location: " . BASE_URL . "src/index.php?action=nominas");
            exit();
        }

        if (!$idUsuario || !$horasTrabajadas || !$tarifaHora || $deducciones === false || !$fechaInicio || !$fechaFin) {
            $_SESSION['mensaje_nomina']      = "Por favor completa todos los campos requeridos.";
            $_SESSION['tipo_mensaje_nomina'] = "warning";
            header("Location: " . BASE_URL . "src/index.php?action=nominas");
            exit();
        }

        if (strtotime($fechaFin) < strtotime($fechaInicio)) {
            $_SESSION['mensaje_nomina']      = "La fecha de fin no puede ser anterior a la fecha de inicio.";
            $_SESSION['tipo_mensaje_nomina'] = "warning";
            header("Location: " . BASE_URL . "src/index.php?action=nominas");
            exit();
        }

        try {
            $ok = $this->nominaModel->generarNomina([
                'id_usuario'          => $idUsuario,
                'horas_trabajadas'    => $horasTrabajadas,
                'tarifa_hora'         => $tarifaHora,
                'deducciones'         => $deducciones,
                'bonificaciones'      => $bonificaciones,
                'horas_extras'        => $horasExtras,
                'fecha_inicio_periodo'=> $fechaInicio,
                'fecha_fin_periodo'   => $fechaFin,
            ]);

            if ($ok) {
                $_SESSION['mensaje_nomina']      = "Nómina generada exitosamente.";
                $_SESSION['tipo_mensaje_nomina'] = "success";
            } else {
                $_SESSION['mensaje_nomina']      = "No se pudo generar la nómina. Inténtalo de nuevo.";
                $_SESSION['tipo_mensaje_nomina'] = "danger";
            }
        } catch (Exception $e) {
            error_log("Error al generar nómina: " . $e->getMessage());
            $_SESSION['mensaje_nomina']      = "Error interno al generar la nómina.";
            $_SESSION['tipo_mensaje_nomina'] = "danger";
        }

        header("Location: " . BASE_URL . "src/index.php?action=nominas");
        exit();
    }

    /**
     * HU-26: Descargar recibo de nómina en PDF
     * Admin Empresa puede descargar cualquier nómina de su empresa
     * Trabajador solo puede descargar las suyas
     */
    public function descargarPDF(): void
    {
        $this->requireLogin();
        $s = $this->getSessionData();

        $idNomina = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$idNomina) {
            http_response_code(400);
            exit("ID de nómina inválido.");
        }

        $esAdminGlobal  = ($s['rolGlobal'] === 1);
        $esAdminEmpresa = ($s['rolEmpresa'] === 1);
        $esTrabajador   = !$esAdminGlobal && !$esAdminEmpresa;

        // Verificar permiso de acceso
        if ($esTrabajador) {
            if (!$this->nominaModel->nominaPerteneceAUsuario($idNomina, $s['userId'])) {
                http_response_code(403);
                exit("Acceso denegado.");
            }
        } elseif ($esAdminEmpresa && $s['idEmpresa']) {
            if (!$this->nominaModel->nominaPerteneceAEmpresa($idNomina, $s['idEmpresa'])) {
                http_response_code(403);
                exit("Acceso denegado.");
            }
        }

        try {
            $nomina = $this->nominaModel->getNominaById($idNomina);
            if (!$nomina) {
                http_response_code(404);
                exit("Nómina no encontrada.");
            }

            // Nombre del archivo según HU-26
            $nombreTrabajador = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nomina['nombre_trabajador']);
            $periodo = substr($nomina['fecha_inicio_periodo'], 0, 7); // YYYY-MM
            $filename = "nomina_{$nombreTrabajador}_{$periodo}.html";

            // Renderizar vista de PDF (HTML imprimible)
            ob_start();
            require_once __DIR__ . '/../../views/nominasView/nomina_pdf_view.php';
            $html = ob_get_clean();

            // Enviar como descarga
            header("Content-Type: text/html; charset=utf-8");
            header("Content-Disposition: inline; filename=\"{$filename}\"");
            echo $html;
            exit();

        } catch (Exception $e) {
            error_log("Error al descargar PDF de nómina: " . $e->getMessage());
            http_response_code(500);
            exit("Error interno al generar el recibo.");
        }
    }
}
?>
