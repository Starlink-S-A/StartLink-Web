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
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/navbar_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/MisEmpresas.css">
</head>
<body>

    <!-- Incluir el Navbar -->
    <?php include __DIR__ . '/../dashboardView/navbar_view.php'; ?>

    <!-- Contenido Principal -->
    <div class="container mt-5 pt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-building text-primary me-2"></i> Mis Empresas</h2>
            <a href="<?= BASE_URL ?>index.php?action=crearEmpresa" class="btn btn-outline-primary shadow-sm">
                <i class="fas fa-plus"></i> Crear Nueva Empresa
            </a>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['mensaje']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <?php if (empty($empresas)): ?>
            <div class="alert alert-info shadow-sm">
                <i class="fas fa-info-circle me-2"></i> No estás vinculado a ninguna empresa como Administrador o Contratador.
            </div>
        <?php else: ?>
            <p class="text-muted mb-4">Selecciona una empresa para acceder al panel de administración (gestionar usuarios, información, logo, ofertas, etc.).</p>
            
            <div class="row g-4">
                <?php foreach ($empresas as $e): ?>
                    <?php 
                        // Resolver ruta del logo
                        $logo_url = null;
                        if (!empty($e['logo_ruta'])) {
                            // El controlador ya guarda los logos en assets/images/Uploads/logos_empresa/
                            $ruta_logo = 'assets/images/Uploads/logos_empresa/' . $e['logo_ruta'];
                            if (file_exists(ROOT_PATH . $ruta_logo)) {
                                $logo_url = BASE_URL . $ruta_logo;
                            }
                        }
                    ?>
                    
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="empresa-card" onclick="seleccionarEmpresa(<?= $e['id_empresa'] ?>)">
                            <div class="empresa-logo-container">
                                <?php if ($logo_url): ?>
                                    <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo <?= htmlspecialchars($e['nombre_empresa']) ?>" class="empresa-logo">
                                <?php else: ?>
                                    <i class="fas fa-building empresa-icon-placeholder"></i>
                                <?php endif; ?>
                            </div>
                            <div class="empresa-card-body">
                                <h5 class="empresa-title"><?= htmlspecialchars($e['nombre_empresa']) ?></h5>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Formulario oculto para enviar la selección mediante JS -->
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
