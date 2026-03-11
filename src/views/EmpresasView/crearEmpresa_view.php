<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/navbar_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .logo-preview {
            max-width: 200px;
            max-height: 200px;
            margin-bottom: 15px;
            display: none;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../dashboardView/navbar_view.php'; ?>

<div class="container mt-5">
    <div class="form-container bg-light p-4 rounded shadow">
        <h2 class="mb-4 text-center">Registrar Nueva Empresa</h2>

        <?php if (!empty(
            
            
            
            $_SESSION['mensaje_empresa'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['mensaje_empresa']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['mensaje_empresa']); ?>
        <?php elseif (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars(implode('<br>', $errors)) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Nombre de la Empresa *</label>
                <input type="text" name="nombre_empresa" class="form-control" maxlength="255" required
                       value="<?= htmlspecialchars($data['nombre_empresa'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Logo de la Empresa</label>
                <input type="file" name="logo" id="logoInput" class="form-control" accept="image/jpeg, image/png, image/gif">
                <img id="logoPreview" class="logo-preview img-thumbnail mt-2" src="#" alt="Vista previa del logo">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($data['descripcion'] ?? '') ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Correo de Contacto *</label>
                <input type="email" name="email_contacto" class="form-control" maxlength="255" required
                       value="<?= htmlspecialchars($data['email_contacto'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono_contacto" class="form-control" 
                       value="<?= htmlspecialchars($data['telefono_contacto'] ?? '') ?>">
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">País</label>
                    <input type="text" name="pais" class="form-control" 
                           value="<?= htmlspecialchars($data['pais'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Departamento</label>
                    <input type="text" name="departamento" class="form-control"
                           value="<?= htmlspecialchars($data['departamento'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control"
                           value="<?= htmlspecialchars($data['ciudad'] ?? '') ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Sitio Web</label>
                <input type="url" name="url_sitio_web" class="form-control" 
                       value="<?= htmlspecialchars($data['url_sitio_web'] ?? '') ?>">
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Registrar Empresa</button>
                <a href="<?= BASE_URL ?>index.php?action=dashboard" class="btn btn-secondary ms-2">Cancelar</a>
            </div>
        </form>
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
