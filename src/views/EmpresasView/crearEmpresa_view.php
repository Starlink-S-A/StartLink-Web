<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/mi_empresa.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .logo-preview-wrapper {
            width: 150px;
            height: 150px;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #f8fafc;
            margin: 0 auto 1rem;
        }
        .logo-preview-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../dashboardView/navbar_view.php'; ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="company-info-card">
                <div class="text-center mb-5">
                    <h2 class="fw-700">Registrar Nueva Empresa</h2>
                    <p class="text-muted">Completa los datos de tu organización para empezar.</p>
                </div>

                <?php if (!empty($_SESSION['mensaje_empresa'])): ?>
                    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_SESSION['mensaje_empresa']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_empresa']); ?>
                <?php elseif (!empty($errors)): ?>
                    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars(implode('<br>', $errors)) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="text-center">
                                <label class="form-label d-block text-center mb-3">Logo de la Empresa</label>
                                <div class="logo-preview-wrapper" id="previewContainer">
                                    <i class="fas fa-cloud-upload-alt text-muted fa-2x" id="placeholderIcon"></i>
                                    <img id="logoPreview" src="#" alt="Vista previa" style="display: none;">
                                </div>
                                <label for="logoInput" class="btn btn-sm btn-outline-primary mb-4">
                                    <i class="fas fa-image me-1"></i> Seleccionar Logo
                                </label>
                                <input type="file" name="logo" id="logoInput" class="d-none" accept="image/jpeg, image/png, image/gif">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_empresa" class="form-control" placeholder="Ej: Tech Solutions S.A." maxlength="255" required
                                   value="<?= htmlspecialchars($data['nombre_empresa'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Correo de Contacto <span class="text-danger">*</span></label>
                            <input type="email" name="email_contacto" class="form-control" placeholder="contacto@empresa.com" maxlength="255" required
                                   value="<?= htmlspecialchars($data['email_contacto'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono_contacto" class="form-control" placeholder="+123 456 789"
                                   value="<?= htmlspecialchars($data['telefono_contacto'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Sitio Web</label>
                            <input type="url" name="url_sitio_web" class="form-control" placeholder="https://www.empresa.com"
                                   value="<?= htmlspecialchars($data['url_sitio_web'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Descripción de la Empresa</label>
                            <textarea name="descripcion" class="form-control" rows="4" placeholder="Cuéntanos un poco sobre tu organización..."><?= htmlspecialchars($data['descripcion'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">País</label>
                            <input type="text" name="pais" class="form-control" 
                                   value="<?= htmlspecialchars($data['pais'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Departamento</label>
                            <input type="text" name="departamento" class="form-control"
                                   value="<?= htmlspecialchars($data['departamento'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control"
                                   value="<?= htmlspecialchars($data['ciudad'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mt-5 d-flex gap-3">
                        <button type="submit" class="btn-premium flex-grow-1 justify-content-center">
                            <i class="fas fa-check-circle me-1"></i> Finalizar Registro
                        </button>
                        <a href="<?= BASE_URL ?>index.php?action=mis_empresas" class="btn btn-light rounded-pill px-4 align-self-center">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('logoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('logoPreview');
                preview.src = event.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
