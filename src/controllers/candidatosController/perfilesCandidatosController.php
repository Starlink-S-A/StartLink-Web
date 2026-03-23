<?php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/candidatosModel/perfilesCandidatosModel.php';

class PerfilesCandidatosController {
    private PerfilesCandidatosModel $model;

    public function __construct() {
        $this->model = new PerfilesCandidatosModel();
    }

    public function index(): void {
        if (!isset($_SESSION['user_id']) || ($_SESSION['loggedin'] ?? false) !== true) {
            header('Location: ' . BASE_URL . 'index.php');
            exit();
        }

        $ajax = $_GET['ajax'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $ajax === 'search') {
            $this->ajaxSearch();
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $ajax === 'profile') {
            $this->ajaxProfileDetail();
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upsert_profile') {
            $this->ajaxUpsertProfile();
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hire_candidate') {
            $this->ajaxHireCandidate();
            return;
        }

        $this->render();
    }

    private function render(): void {
        $userId = (int)$_SESSION['user_id'];
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $rolGlobal = (int)($_SESSION['id_rol'] ?? 2);

        $filters = [
            'name' => $_GET['search_name'] ?? '',
            'title' => $_GET['search_title'] ?? '',
            'skill' => $_GET['search_skill'] ?? '',
        ];

        $profiles = $this->model->searchAvailableProfiles($filters);
        foreach ($profiles as &$p) {
            $p['foto_url'] = $this->buildPhotoUrl($p['foto_perfil'] ?? null);
        }
        unset($p);

        $userProfile = $this->model->getUserProfile($userId);
        $isWorker = $this->model->isUserWorker($userId);
        $canPublishProfile = true;

        $esAdminEmpresa = $this->model->userIsRecruiterOrCompanyAdmin($userId);
        $canViewDetails = $esAdminEmpresa || $rolGlobal === 1;
        $userCompanies = $this->model->getCompaniesUserCanHireFrom($userId);

        $profileIsComplete = isProfileComplete($userId);
        $showPublishProfileLink = $profileIsComplete;

        $profileImage = $this->buildPhotoUrl($_SESSION['foto_perfil'] ?? null);
        $latestNotifications = [];
        $unreadNotificationsCount = 0;

        try {
            $db = getDbConnection();
            $stmtNotif = $db->prepare(
                "SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion
                 FROM notificaciones
                 WHERE user_id = ?
                 ORDER BY fecha_creacion DESC
                 LIMIT 10"
            );
            $stmtNotif->execute([$userId]);
            $latestNotifications = $stmtNotif->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($latestNotifications as &$row) {
                if (empty($row['icono'])) {
                    switch ($row['tipo']) {
                        case 'success': $row['icono'] = 'fas fa-check-circle text-success'; break;
                        case 'warning': $row['icono'] = 'fas fa-exclamation-triangle text-warning'; break;
                        case 'error':   $row['icono'] = 'fas fa-times-circle text-danger'; break;
                        default:        $row['icono'] = 'fas fa-info-circle text-info'; break;
                    }
                } elseif (strpos((string)$row['icono'], 'text-') === false) {
                    $row['icono'] .= ' text-primary';
                }
                if (empty($row['leida'])) {
                    $unreadNotificationsCount++;
                }
            }
            unset($row);
        } catch (Throwable $e) {
        }

        require_once __DIR__ . '/../../views/candidatosView/perfilesCandidatosView.php';
    }

    private function ajaxSearch(): void {
        $filters = [
            'name' => $_GET['name'] ?? '',
            'title' => $_GET['title'] ?? '',
            'skill' => $_GET['skill'] ?? '',
        ];

        try {
            $profiles = $this->model->searchAvailableProfiles($filters);
            foreach ($profiles as &$p) {
                $p['foto_url'] = $this->buildPhotoUrl($p['foto_perfil'] ?? null);
            }
            unset($p);
            $this->json(['success' => true, 'profiles' => $profiles]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => 'Error al cargar perfiles.'], 500);
        }
    }

    private function ajaxProfileDetail(): void {
        $userId = (int)$_SESSION['user_id'];
        $rolGlobal = (int)($_SESSION['id_rol'] ?? 2);
        $canViewDetails = $this->model->userIsRecruiterOrCompanyAdmin($userId) || $rolGlobal === 1;

        if (!$canViewDetails) {
            $this->json(['success' => false, 'message' => 'No tienes permisos para ver el perfil completo.'], 403);
            return;
        }

        $candidateId = isset($_GET['candidate_id']) ? (int)$_GET['candidate_id'] : 0;
        if ($candidateId <= 0) {
            $this->json(['success' => false, 'message' => 'ID inválido.'], 400);
            return;
        }

        $profile = $this->model->getProfileDetail($candidateId);
        if (!$profile) {
            $this->json(['success' => false, 'message' => 'Perfil no encontrado.'], 404);
            return;
        }

        $profile['foto_url'] = $this->buildPhotoUrl($profile['foto_perfil'] ?? null);
        $profile['cv_url'] = $this->buildPublicFileUrl($profile['ruta_hdv'] ?? null);
        $this->json(['success' => true, 'profile' => $profile]);
    }

    private function ajaxUpsertProfile(): void {
        $userId = (int)$_SESSION['user_id'];

        $tituloBuscado = trim((string)($_POST['titulo_buscado'] ?? ''));
        $tipoContratoPreferido = trim((string)($_POST['tipo_contrato_preferido'] ?? ''));
        $modalidadPreferida = trim((string)($_POST['modalidad_preferida'] ?? ''));

        $expectativaSalarial = null;
        if (isset($_POST['expectativa_salarial']) && $_POST['expectativa_salarial'] !== '') {
            $value = filter_var($_POST['expectativa_salarial'], FILTER_VALIDATE_FLOAT);
            if ($value === false || $value < 0) {
                $this->json(['success' => false, 'message' => 'La expectativa salarial debe ser un número válido mayor o igual a 0.'], 400);
                return;
            }
            $expectativaSalarial = (float)$value;
        }

        $estaDisponible = isset($_POST['esta_disponible']) && (string)$_POST['esta_disponible'] === '1';

        if ($tituloBuscado === '' || $tipoContratoPreferido === '' || $modalidadPreferida === '') {
            $this->json(['success' => false, 'message' => 'Completa los campos obligatorios: título buscado, tipo de contrato y modalidad.'], 400);
            return;
        }

        try {
            $this->model->upsertUserProfile($userId, [
                'titulo_buscado' => $tituloBuscado,
                'tipo_contrato_preferido' => $tipoContratoPreferido,
                'modalidad_preferida' => $modalidadPreferida,
                'expectativa_salarial' => $expectativaSalarial,
                'esta_disponible' => $estaDisponible ? 1 : 0,
            ]);

            $message = $estaDisponible
                ? 'Tu perfil quedó publicado y visible en la plataforma.'
                : 'Tu perfil fue guardado, pero quedó oculto (no disponible).';

            $this->json(['success' => true, 'message' => $message]);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => 'Error al guardar el perfil.'], 500);
        }
    }

    private function ajaxHireCandidate(): void {
        $userId = (int)$_SESSION['user_id'];
        $candidateId = isset($_POST['candidate_id']) ? (int)$_POST['candidate_id'] : 0;
        $companyId = isset($_POST['company_id']) ? (int)$_POST['company_id'] : 0;

        if ($candidateId <= 0 || $companyId <= 0) {
            $this->json(['success' => false, 'message' => 'Datos inválidos.'], 400);
            return;
        }

        if (!$this->model->userCanHireFromCompany($userId, $companyId)) {
            $this->json(['success' => false, 'message' => 'No tienes permisos para contratar en esa empresa.'], 403);
            return;
        }

        $candidateExistingRole = $this->model->getUserCompanyRole($candidateId, $companyId);
        if ($candidateExistingRole !== null) {
            $this->json(['success' => false, 'message' => 'Este usuario ya pertenece a esa empresa y no puede ser contratado nuevamente.'], 409);
            return;
        }

        $salaryBase = null;
        if (isset($_POST['salario_base']) && $_POST['salario_base'] !== '') {
            $value = filter_var($_POST['salario_base'], FILTER_VALIDATE_FLOAT);
            if ($value === false || $value < 0) {
                $this->json(['success' => false, 'message' => 'El salario debe ser un número válido mayor o igual a 0.'], 400);
                return;
            }
            $salaryBase = (float)$value;
        }

        $hoursWeekly = null;
        if (isset($_POST['horas_semanales']) && $_POST['horas_semanales'] !== '') {
            $value = filter_var($_POST['horas_semanales'], FILTER_VALIDATE_FLOAT);
            if ($value === false || $value <= 0) {
                $this->json(['success' => false, 'message' => 'Las horas semanales deben ser un número válido mayor que 0.'], 400);
                return;
            }
            $hoursWeekly = (float)$value;
        }

        try {
            $this->model->hireCandidateToCompany($candidateId, $companyId, $salaryBase, $hoursWeekly);
            $this->json(['success' => true, 'message' => 'Contratación registrada. El perfil quedó oculto automáticamente.']);
        } catch (RuntimeException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 409);
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => 'Error al contratar al usuario.'], 500);
        }
    }

    private function buildPhotoUrl(?string $dbPath): string {
        $fallback = 'https://static.thenounproject.com/png/4154905-200.png';
        $path = trim((string)$dbPath);
        if ($path === '') {
            return $fallback;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $absolutePath = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));
        if (!file_exists($absolutePath)) {
            return $fallback;
        }
        return rtrim(BASE_URL, '/') . '/' . ltrim(str_replace('\\', '/', $path), '/');
    }

    private function buildPublicFileUrl(?string $dbPath): ?string {
        $path = trim((string)$dbPath);
        if ($path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $absolutePath = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));
        if (!file_exists($absolutePath)) {
            return null;
        }
        return rtrim(BASE_URL, '/') . '/' . ltrim(str_replace('\\', '/', $path), '/');
    }

    private function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }
}
