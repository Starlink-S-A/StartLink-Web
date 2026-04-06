<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName = $userName ?? 'Usuario';
$profileImage = $profileImage ?? 'https://static.thenounproject.com/png/4154905-200.png';
$empresas = $empresas ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Empresas | StartLink</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/mi_empresa.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Incluir el Navbar -->
    <?php include __DIR__ . '/../dashboardView/sidebar_View.php'; ?>

    <!-- Contenido Principal -->
    <div class="container mt-5 pt-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-700 mb-1">Mis Empresas</h2>
                <p class="text-muted small mb-0">Gestiona las organizaciones a las que tienes acceso.</p>
            </div>
            <a href="<?= BASE_URL ?>index.php?action=crearEmpresa" class="btn-premium">
                <i class="fas fa-plus"></i> Registrar Empresa
            </a>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-info border-0 shadow-sm alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i> <?= htmlspecialchars($_SESSION['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (empty($empresas)): ?>
            <div class="company-info-card text-center py-5">
                <div class="company-logo-wrapper mb-4">
                    <i class="fas fa-building company-logo-icon"></i>
                </div>
                <h4>No tienes empresas vinculadas</h4>
                <p class="text-muted mx-auto" style="max-width: 400px;">
                    Parece que aún no eres administrador de ninguna empresa. Registra una nueva organización para empezar a gestionar ofertas y talento.
                </p>
                <a href="<?= BASE_URL ?>index.php?action=crearEmpresa" class="btn-premium mt-3">
                    Crear mi primera empresa
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($empresas as $e): ?>
                    <?php 
                        $logo_url = null;
                        if (!empty($e['logo_ruta'])) {
                            $ruta_logo = 'assets/images/Uploads/logos_empresa/' . $e['logo_ruta'];
                            if (file_exists(ROOT_PATH . $ruta_logo)) {
                                $logo_url = BASE_URL . $ruta_logo;
                            }
                        }
                    ?>
                    
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="company-info-card h-100 p-4 d-flex flex-column align-items-center text-center" onclick="seleccionarEmpresa(<?= $e['id_empresa'] ?>)" style="cursor: pointer;">
                            <div class="company-logo-wrapper mb-3" style="width: 100px; height: 100px;">
                                <?php if ($logo_url): ?>
                                    <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo <?= htmlspecialchars($e['nombre_empresa']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-building company-logo-icon" style="font-size: 2rem;"></i>
                                <?php endif; ?>
                            </div>
                            <h5 class="fw-700 mb-2"><?= htmlspecialchars($e['nombre_empresa']) ?></h5>
                            <span class="badge bg-light text-primary border border-primary-subtle px-3 py-2 rounded-pill small">
                                <i class="fas fa-user-shield me-1"></i> Gestionar
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <form id="formSeleccionEmpresa" method="POST" action="<?= BASE_URL ?>index.php?action=mis_empresas" style="display: none;">
                <input type="hidden" name="empresa_id" id="empresa_id_input" value="">
            </form>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>src/public/js/mis_empresas.js"></script>
</body>
</html>
