<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$empresa = $empresa ?? null;
$miembros = $miembros ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $empresa ? htmlspecialchars($empresa['nombre_empresa']) : 'Mi Equipo' ?> | StartLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/mi_empresa.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/mis_Equipo.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background-color: #f8fafc;">
    <?php include __DIR__ . '/../dashboardView/sidebar_View.php'; ?>

    <div class="container mt-5 pt-4">
        <?php if (!$empresa): ?>
            <div class="company-info-card text-center py-5">
                <div class="company-logo-wrapper mb-4">
                    <i class="fas fa-users company-logo-icon"></i>
                </div>
                <h4>No se pudo cargar la empresa</h4>
                <p class="text-muted mx-auto" style="max-width: 440px;">
                    Vuelve a seleccionar una empresa para ver el equipo.
                </p>
                <a href="<?= BASE_URL ?>index.php?action=mis_equipos" class="btn-premium mt-3">
                    Seleccionar empresa
                </a>
            </div>
        <?php else: ?>
            <div class="company-info-card p-4 p-md-5 mb-4">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="company-logo-wrapper" style="width: 100px; height: 100px;">
                        <img src="<?= htmlspecialchars($empresa['logo_url'] ?? '') ?>" alt="Logo <?= htmlspecialchars($empresa['nombre_empresa']) ?>">
                    </div>
                    <div class="flex-grow-1">
                        <h2 class="fw-700 mb-1">Equipo de <?= htmlspecialchars($empresa['nombre_empresa']) ?></h2>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($empresa['descripcion'] ?? 'Sin descripción.') ?></p>
                        <div class="d-flex flex-wrap gap-3 mt-3 text-muted small">
                            <?php if (!empty($empresa['email_contacto'])): ?>
                                <span><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($empresa['email_contacto']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($empresa['telefono_contacto'])): ?>
                                <span><i class="fas fa-phone me-2"></i><?= htmlspecialchars($empresa['telefono_contacto']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($empresa['url_sitio_web'])): ?>
                                <span>
                                    <i class="fas fa-globe me-2"></i>
                                    <a href="<?= htmlspecialchars($empresa['url_sitio_web']) ?>" target="_blank" class="text-decoration-none">
                                        <?= htmlspecialchars($empresa['url_sitio_web']) ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="ms-auto">
                        <a href="<?= BASE_URL ?>index.php?action=mis_equipos" class="btn btn-outline-secondary">
                            Cambiar empresa
                        </a>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <h3 class="fw-700 mb-0">Miembros</h3>
                <span class="text-muted small"><?= count($miembros) ?> en total</span>
            </div>

            <?php if (empty($miembros)): ?>
                <div class="company-info-card text-center py-5">
                    <div class="company-logo-wrapper mb-4">
                        <i class="fas fa-user-friends company-logo-icon"></i>
                    </div>
                    <h4>No hay miembros para mostrar</h4>
                    <p class="text-muted mx-auto" style="max-width: 440px;">
                        Aún no hay usuarios asociados a esta empresa.
                    </p>
                </div>
            <?php else: ?>
                <div class="row g-4 team-users-grid">
                    <?php foreach ($miembros as $m): ?>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="user-card h-100 p-4 text-center">
                                <img class="team-user-avatar mb-3" src="<?= htmlspecialchars($m['profile_image_url']) ?>" alt="Foto de <?= htmlspecialchars($m['nombre']) ?>">
                                <h5 class="fw-700 mb-1"><?= htmlspecialchars($m['nombre']) ?></h5>
                                <div class="text-muted small mb-3"><?= htmlspecialchars($m['email']) ?></div>
                                <div class="team-user-role mx-auto">
                                    <i class="fas fa-id-badge"></i>
                                    <?= htmlspecialchars($m['nombre_rol_empresa'] ?? 'Miembro') ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>src/public/js/mis_equipos.js"></script>
</body>
</html>
