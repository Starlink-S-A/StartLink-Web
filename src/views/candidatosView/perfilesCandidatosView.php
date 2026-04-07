<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$endpoint = rtrim(BASE_URL, '/') . '/perfiles_candidatos';

$filters = $filters ?? ['name' => '', 'title' => '', 'skill' => ''];
$profiles = $profiles ?? [];
$userProfile = $userProfile ?? null;
$canPublishProfile = $canPublishProfile ?? false;
$canViewDetails = $canViewDetails ?? false;
$isWorker = $isWorker ?? false;
$userCompanies = $userCompanies ?? [];

$modalTituloBuscado = $userProfile['titulo_buscado'] ?? '';
$modalTipoContrato = $userProfile['tipo_contrato_preferido'] ?? '';
$modalModalidad = $userProfile['modalidad_preferida'] ?? '';
$modalExpectativaSalarial = $userProfile['expectativa_salarial'] ?? '';
$modalHabilidadesClave = $userProfile['habilidades_clave'] ?? '';
$modalEstaDisponible = (int)($userProfile['esta_disponible'] ?? 0) === 1;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfiles de Candidatos | StartLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/sidebar_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; }
        .profile-card-avatar { width: 72px; height: 72px; object-fit: cover; }
        .skill-chip { border: 1px solid #e5e7eb; border-radius: 999px; padding: 4px 10px; font-size: .75rem; background: #fff; }
    </style>
</head>
<body>

<?php
$pageTitle = 'Perfiles de Candidatos';
include __DIR__ . '/../dashboardView/sidebar_View.php';
?>

<div class="main-content">
<?php include __DIR__ . '/../dashboardView/navbar_view.php'; ?>
<div class="container mt-5 pt-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="dash-title mb-1">Perfiles de Candidatos</h2>
            <p class="text-muted small mb-0">Explora perfiles disponibles y filtra por nombre, título y habilidades.</p>
        </div>
        <button class="btn btn-dash-primary px-4" data-bs-toggle="modal" data-bs-target="#publishProfileModal">
            <i class="fas fa-paper-plane me-2"></i><?= $userProfile ? 'Actualizar mi perfil' : 'Publicar mi perfil' ?>
        </button>
    </div>

    <?php if ($isWorker && !isset($_SESSION['publish_profile_company_notice_shown'])): ?>
        <?php $_SESSION['publish_profile_company_notice_shown'] = true; ?>
        <div class="alert alert-info border-0 shadow-sm">
            Detectamos que estás vinculado a una empresa. Por defecto el perfil estará oculto, pero puedes activarlo o desactivarlo cuando quieras.
        </div>
    <?php endif; ?>

    <div class="dash-card p-4 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Nombre</label>
                <input id="filterName" type="text" class="form-control" value="<?= htmlspecialchars((string)($filters['name'] ?? '')) ?>" placeholder="Ej: Ana, Juan">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Título buscado</label>
                <input id="filterTitle" type="text" class="form-control" value="<?= htmlspecialchars((string)($filters['title'] ?? '')) ?>" placeholder="Ej: Desarrollador Frontend">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Habilidad</label>
                <input id="filterSkill" type="text" class="form-control" value="<?= htmlspecialchars((string)($filters['skill'] ?? '')) ?>" placeholder="Ej: React, SQL">
            </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <a class="btn btn-sm btn-outline-secondary" href="<?= htmlspecialchars($endpoint) ?>">Limpiar</a>
        </div>
    </div>

    <div id="profilesEmptyState" class="dash-card p-5 text-center <?= !empty($profiles) ? 'd-none' : '' ?>">
        <i class="fas fa-search text-muted mb-3" style="font-size: 2.5rem;"></i>
        <h5 class="mb-2">No hay resultados</h5>
        <p class="text-muted small mb-0">Ajusta los filtros para encontrar perfiles disponibles.</p>
    </div>

    <div id="profilesContainer" class="row g-4 <?= empty($profiles) ? 'd-none' : '' ?>">
        <?php foreach ($profiles as $p): ?>
            <div class="col-lg-6">
                <div class="dash-card h-100 p-4">
                    <div class="d-flex gap-3 align-items-start">
                        <img class="rounded-circle border profile-card-avatar" src="<?= htmlspecialchars((string)($p['foto_url'] ?? '')) ?>" alt="Foto">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <h5 class="fw-700 mb-1"><?= htmlspecialchars((string)$p['nombre']) ?></h5>
                                    <div class="text-primary fw-600 small mb-1"><?= htmlspecialchars((string)($p['titulo_buscado'] ?? '')) ?></div>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars((string)($p['ciudad'] ?? '')) ?>
                                        <?= (!empty($p['ciudad']) && !empty($p['departamento'])) ? ', ' : '' ?>
                                        <?= htmlspecialchars((string)($p['departamento'] ?? '')) ?>
                                        <?= ((!empty($p['ciudad']) || !empty($p['departamento'])) && !empty($p['pais'])) ? ', ' : '' ?>
                                        <?= htmlspecialchars((string)($p['pais'] ?? '')) ?>
                                    </div>
                                </div>
                                <?php if ($canViewDetails): ?>
                                    <button class="btn btn-sm btn-outline-primary view-profile-btn" data-candidate-id="<?= (int)$p['id_usuario'] ?>">
                                        Ver perfil
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php
                                $skillsRaw = (string)($p['habilidades'] ?? '');
                                $skills = array_values(array_filter(array_map('trim', explode(',', $skillsRaw))));
                                $skills = array_slice($skills, 0, 6);
                            ?>
                            <?php if (!empty($skills)): ?>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <?php foreach ($skills as $s): ?>
                                        <span class="skill-chip"><?= htmlspecialchars($s) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</div>

<div class="modal fade" id="publishProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $userProfile ? 'Actualizar perfil de búsqueda' : 'Publicar perfil de búsqueda' ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="publishProfileForm">
                    <input type="hidden" name="action" value="upsert_profile">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Título buscado <span class="text-danger">*</span></label>
                            <input class="form-control" name="titulo_buscado" value="<?= htmlspecialchars((string)$modalTituloBuscado) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de contrato preferido <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo_contrato_preferido" required>
                                <option value="">Selecciona...</option>
                                <option value="Tiempo Completo" <?= $modalTipoContrato === 'Tiempo Completo' ? 'selected' : '' ?>>Tiempo Completo</option>
                                <option value="Medio Tiempo" <?= $modalTipoContrato === 'Medio Tiempo' ? 'selected' : '' ?>>Medio Tiempo</option>
                                <option value="Contrato por Obra" <?= $modalTipoContrato === 'Contrato por Obra' ? 'selected' : '' ?>>Contrato por Obra</option>
                                <option value="Freelance" <?= $modalTipoContrato === 'Freelance' ? 'selected' : '' ?>>Freelance</option>
                                <option value="Temporal" <?= $modalTipoContrato === 'Temporal' ? 'selected' : '' ?>>Temporal</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modalidad preferida <span class="text-danger">*</span></label>
                            <select class="form-select" name="modalidad_preferida" required>
                                <option value="">Selecciona...</option>
                                <option value="Presencial" <?= $modalModalidad === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                                <option value="Remoto" <?= $modalModalidad === 'Remoto' ? 'selected' : '' ?>>Remoto</option>
                                <option value="Híbrido" <?= $modalModalidad === 'Híbrido' ? 'selected' : '' ?>>Híbrido</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expectativa salarial (opcional)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="expectativa_salarial" value="<?= htmlspecialchars((string)$modalExpectativaSalarial) ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="profileAvailabilitySwitch"
                                    name="esta_disponible"
                                    value="1"
                                    <?= $modalEstaDisponible ? 'checked' : '' ?>
                                >
                                <label class="form-check-label" for="profileAvailabilitySwitch">
                                    Perfil visible
                                </label>
                            </div>
                            <div class="text-muted small mt-1">Si desactivas esta opción, tu perfil no aparecerá en la lista pública de perfiles.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <?= $userProfile ? 'Guardar cambios' : 'Publicar' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perfil completo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="viewProfileModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="hireCandidateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contratar candidato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="hireCandidateForm">
                    <input type="hidden" name="candidate_id" id="hireCandidateId">

                    <div class="mb-3">
                        <div class="text-muted small mb-1">Candidato</div>
                        <div class="fw-600" id="hireCandidateName">—</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Empresa <span class="text-danger">*</span></label>
                        <select class="form-select" name="company_id" id="hireCompanySelect" required>
                            <option value="">Selecciona una empresa...</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Salario base (opcional)</label>
                            <input type="number" min="0" step="0.01" class="form-control" name="salario_base" placeholder="Ej: 1800">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Horas semanales (opcional)</label>
                            <input type="number" min="1" step="0.01" class="form-control" name="horas_semanales" placeholder="Ej: 40">
                        </div>
                    </div>

                    <div class="text-muted small mt-3">
                        Al contratar, el perfil se ocultará automáticamente de la lista pública.
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Contratar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="alertDialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mensaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="alertDialogBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.PERFILES_CANDIDATOS_ENDPOINT = <?= json_encode($endpoint) ?>;
    window.PERFILES_CANDIDATOS_CAN_VIEW_DETAILS = <?= json_encode((bool)$canViewDetails) ?>;
    window.PERFILES_USER_COMPANIES = <?= json_encode($userCompanies) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>src/public/js/perfiles_candidatos.js"></script>

</body>
</html>
