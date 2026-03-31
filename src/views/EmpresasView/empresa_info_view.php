<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Empresa | StartLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/sidebar_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/mi_empresa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include __DIR__ . '/../dashboardView/sidebar_View.php'; ?>

<?php $seccion = $seccion ?? 'informacion'; ?>
<?php $isUsuarios = $seccion === 'usuarios'; ?>

<div class="container-fluid mt-5 mb-5 pt-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xxl-11">
            <div class="company-info-card">
                <ul class="nav premium-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?= !$isUsuarios ? 'active' : '' ?>" href="<?= BASE_URL ?>mi_empresa?seccion=informacion">
                            Información
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $isUsuarios ? 'active' : '' ?>" href="<?= BASE_URL ?>mi_empresa?seccion=usuarios">
                            Gestión de Usuarios
                        </a>
                    </li>
                </ul>

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h2 class="fw-700 mb-1"><?= $isUsuarios ? 'Gestión de Usuarios' : 'Información de la Empresa' ?></h2>
                        <p class="text-muted small mb-0">
                            <?= $isUsuarios ? 'Administra los usuarios de tu empresa.' : 'Actualiza tu presencia institucional.' ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <?php if (!empty($empresa['ultima_actualizacion'])): ?>
                            <div class="text-muted small">
                                Última actualización:
                                <?= htmlspecialchars((new DateTime($empresa['ultima_actualizacion']))->format('d/m/Y H:i')) ?>
                                <?php if (!empty($empresa['ultimo_editor_nombre'])): ?>
                                    por <?= htmlspecialchars($empresa['ultimo_editor_nombre']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?> border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                        <?= nl2br(htmlspecialchars(implode("\n", $errors))) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($isUsuarios): ?>
                    <?php include __DIR__ . '/gestionUsuariosView.php'; ?>
                <?php else: ?>
                <form method="POST" action="" enctype="multipart/form-data" novalidate>
                    <div class="text-center mb-4">
                        <div class="company-logo-wrapper" id="logoWrapper" tabindex="0" role="button" aria-label="Cambiar logo">
                            <?php if (!empty($logoUrl)): ?>
                                <img id="logoPreview" src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo de la Empresa">
                            <?php else: ?>
                                <i class="fas fa-building company-logo-icon" id="logoIcon"></i>
                                <img id="logoPreview" src="#" alt="Logo de la Empresa" style="display:none;">
                            <?php endif; ?>
                        </div>
                        <?php if ($canEdit): ?>
                            <label for="logoInput" class="btn btn-sm btn-outline-primary rounded-pill px-4">
                                <i class="fas fa-image me-1"></i> Seleccionar Logo
                            </label>
                            <input type="file" name="logo" id="logoInput" class="d-none" accept="image/jpeg, image/png">
                        <?php else: ?>
                            <div class="text-muted small">No tienes permisos para cambiar el logo.</div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="nombre_empresa"
                                class="form-control <?= !empty($fieldErrors['nombre_empresa']) ? 'is-invalid' : '' ?>"
                                maxlength="255"
                                minlength="1"
                                autocomplete="organization"
                                required
                                value="<?= htmlspecialchars($data['nombre_empresa'] ?? '') ?>"
                                <?= $canEdit ? '' : 'readonly' ?>
                            >
                            <?php if (!empty($fieldErrors['nombre_empresa'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($fieldErrors['nombre_empresa']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Correo de Contacto <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                name="email_contacto"
                                class="form-control <?= !empty($fieldErrors['email_contacto']) ? 'is-invalid' : '' ?>"
                                inputmode="email"
                                autocomplete="email"
                                maxlength="255"
                                required
                                value="<?= htmlspecialchars($data['email_contacto'] ?? '') ?>"
                                <?= $canEdit ? '' : 'readonly' ?>
                            >
                            <?php if (!empty($fieldErrors['email_contacto'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($fieldErrors['email_contacto']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input
                                type="text"
                                name="telefono_contacto"
                                class="form-control <?= !empty($fieldErrors['telefono_contacto']) ? 'is-invalid' : '' ?>"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="50"
                                value="<?= htmlspecialchars($data['telefono_contacto'] ?? '') ?>"
                                <?= $canEdit ? '' : 'readonly' ?>
                            >
                            <?php if (!empty($fieldErrors['telefono_contacto'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($fieldErrors['telefono_contacto']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Sitio Web</label>
                            <input
                                type="url"
                                name="url_sitio_web"
                                class="form-control <?= !empty($fieldErrors['url_sitio_web']) ? 'is-invalid' : '' ?>"
                                inputmode="url"
                                maxlength="255"
                                value="<?= htmlspecialchars($data['url_sitio_web'] ?? '') ?>"
                                <?= $canEdit ? '' : 'readonly' ?>
                            >
                            <?php if (!empty($fieldErrors['url_sitio_web'])): ?>
                                <div class="invalid-feedback">
                                    <?= htmlspecialchars($fieldErrors['url_sitio_web']) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <textarea
                                name="descripcion"
                                class="form-control"
                                rows="4"
                                <?= $canEdit ? '' : 'readonly' ?>
                            ><?= htmlspecialchars($data['descripcion'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="mt-5 d-flex flex-wrap gap-3">
                        <?php if ($canEdit): ?>
                            <button type="submit" class="btn-premium flex-grow-1 justify-content-center">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>mis_empresas" class="btn btn-light rounded-pill px-4 align-self-center">
                            Volver
                        </a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('logoInput');
    const wrapper = document.getElementById('logoWrapper');
    const preview = document.getElementById('logoPreview');
    const icon = document.getElementById('logoIcon');

    if (wrapper && input) {
        wrapper.addEventListener('click', function () {
            input.click();
        });
        wrapper.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                input.click();
            }
        });
    }

    if (input && preview) {
        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;

            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.style.display = 'block';
            if (icon) icon.style.display = 'none';
        });
    }
});
</script>
</body>
</html>
