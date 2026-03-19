<?php
// src/views/userView/configurar_perfil_view.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Perfil - TalentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/configurar_perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../dashboardView/sidebar_View.php'; ?>

<div class="container mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show text-center" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h1 class="text-center mb-4">Configura tu Perfil</h1>

    <?php
        $stepOrder = [
            'personal' => 1,
            'experience' => 2,
            'education' => 3,
            'skills' => 4,
            'cv' => 5,
        ];
        $stepNum = $stepOrder[$currentStep] ?? 1;
        $progress = (int)round(($stepNum / 5) * 100);
    ?>

    <div class="progress mb-4">
        <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">Paso <?= $stepNum ?> de 5</div>
    </div>

    <ul class="nav flex-column profile-nav mb-4" id="profileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'personal' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=personal">
                <i class="fas fa-user-circle me-2"></i> Información Personal
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'experience' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=experience">
                <i class="fas fa-briefcase me-2"></i> Experiencia Laboral
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'education' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=education">
                <i class="fas fa-graduation-cap me-2"></i> Estudios Académicos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'skills' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=skills">
                <i class="fas fa-tools me-2"></i> Habilidades
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'cv' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=cv">
                <i class="fas fa-file-alt me-2"></i> Hoja de Vida (CV)
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <?php if ($currentStep == 'personal'): ?>
            <div class="card p-4 layout-premium border-0 shadow-sm">
                <div class="section-header mb-4">
                    <h4 class="mb-0">Información de Perfil</h4>
                    <p class="text-muted small">Mantén tus datos actualizados para mejorar tu visibilidad.</p>
                </div>
                <form action="<?= BASE_URL ?>configurar_perfil?step=personal" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="personal_info">
                    <!-- Foto de Perfil -->
                    <div class="mb-4 text-center">
                        <div class="position-relative d-inline-block">
                            <img src="<?= isset($profileImage) ? $profileImage : 'https://static.thenounproject.com/png/4154905-200.png' ?>" alt="Foto de perfil" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                            <div class="mt-2">
                                <label for="foto_perfil" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-camera me-1"></i> Cambiar Foto
                                </label>
                                <input type="file" class="d-none" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/gif">
                            </div>
                        </div>
                    </div>
                    <!-- Nombre -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : htmlspecialchars($perfilData['nombre'] ?? '') ?>" required>
                    </div>
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($perfilData['email'] ?? '') ?>" required>
                        <small class="form-text text-muted">Ejemplo: usuario@dominio.com</small>
                    </div>
                    <!-- Género -->
                    <div class="mb-3">
                        <label for="genero" class="form-label">Género <span class="text-danger">*</span></label>
                        <select class="form-control" id="genero" name="genero" required>
                            <option value="">Selecciona tu género</option>
                            <option value="masculino" <?= (isset($_POST['genero']) ? $_POST['genero'] : ($perfilData['genero'] ?? '')) == 'masculino' ? 'selected' : '' ?>>Masculino</option>
                            <option value="femenino" <?= (isset($_POST['genero']) ? $_POST['genero'] : ($perfilData['genero'] ?? '')) == 'femenino' ? 'selected' : '' ?>>Femenino</option>
                            <option value="otro" <?= (isset($_POST['genero']) ? $_POST['genero'] : ($perfilData['genero'] ?? '')) == 'otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                    <!-- dni -->
                    <div class="mb-3">
                        <label for="dni" class="form-label">DNI <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="dni" name="dni" value="<?= isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : htmlspecialchars($perfilData['dni'] ?? '') ?>" maxlength="12" pattern="[0-9]{8,12}" title="El DNI debe contener entre 8 y 12 dígitos numéricos." required>
                        <small class="form-text text-muted">Debe contener entre 8 y 12 dígitos numéricos.</small>
                    </div>
                    <!-- Teléfono -->
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : htmlspecialchars($perfilData['telefono'] ?? '') ?>" maxlength="15" pattern="[0-9+\-\(\) ]{7,15}" title="El teléfono debe contener entre 7 y 15 caracteres (números, +, -, (), espacios).">
                        <small class="form-text text-muted">Ejemplo: +1234567890 o (123) 456-7890</small>
                    </div>
                    <!-- Ciudad -->
                    <div class="mb-3">
                        <label for="ciudad" class="form-label">Ciudad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?= isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : htmlspecialchars($perfilData['ciudad'] ?? '') ?>" required>
                    </div>
                    <!-- Departamento -->
                    <div class="mb-3">
                        <label for="departamento" class="form-label">Departamento <span class="text-danger">*</label>
                        <input type="text" class="form-control" id="departamento" name="departamento" value="<?= isset($_POST['departamento']) ? htmlspecialchars($_POST['departamento']) : htmlspecialchars($perfilData['departamento'] ?? '') ?>" required>
                    </div>
                    <!-- País -->
                    <div class="mb-3">
                        <label for="pais" class="form-label">País <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pais" name="pais" value="<?= isset($_POST['pais']) ? htmlspecialchars($_POST['pais']) : htmlspecialchars($perfilData['pais'] ?? '') ?>" required>
                    </div>
                    <div class="form-navigation mt-4">
                        <button type="submit" class="btn btn-primary px-4">Guardar Cambios</button>
                        <button type="button" class="btn btn-outline-secondary ms-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Cambiar Contraseña</button>
                    </div>
                </form>
            </div>

            <!-- Modal para Cambiar Contraseña -->
            <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="changePasswordLabel">Cambiar Contraseña</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="changePasswordForm" action="<?= BASE_URL ?>configurar_perfil?step=personal" method="post" novalidate>
                                <input type="hidden" name="form_type" value="change_password">

                                <!-- Nueva contraseña -->
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="new_password"
                                               minlength="8" required autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleNewPwd">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="progress mt-2" style="height:6px;">
                                        <div id="pwdStrengthBar" class="progress-bar" role="progressbar" style="width:0%"></div>
                                    </div>
                                    <small id="pwdStrengthLabel" class="text-muted"></small>
                                    <ul class="list-unstyled mt-2 small" id="pwdRequirements">
                                        <li id="req-length"  class="text-danger"><i class="fas fa-times-circle me-1"></i>Mínimo 8 caracteres</li>
                                        <li id="req-upper"   class="text-danger"><i class="fas fa-times-circle me-1"></i>Al menos una letra mayúscula</li>
                                        <li id="req-lower"   class="text-danger"><i class="fas fa-times-circle me-1"></i>Al menos una letra minúscula</li>
                                        <li id="req-number"  class="text-danger"><i class="fas fa-times-circle me-1"></i>Al menos un número</li>
                                        <li id="req-special" class="text-danger"><i class="fas fa-times-circle me-1"></i>Al menos un símbolo (!@#$%^&*...)</li>
                                    </ul>
                                </div>

                                <!-- Confirmar contraseña -->
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                               required autocomplete="new-password">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPwd">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small id="matchMsg" class="text-muted"></small>
                                </div>

                                <button type="submit" class="btn btn-primary w-100" id="btnCambiarContrasena">Cambiar Contraseña</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($currentStep == 'experience'): ?>
            <!-- Formulario Experiencia Laboral -->
            <div class="card p-4 layout-premium border-0 shadow-sm">
                <div class="section-header mb-4">
                    <h4 class="mb-0">Añadir Nueva Experiencia</h4>
                </div>
                <form action="<?= BASE_URL ?>configurar_perfil?step=experience" method="post">
                    <input type="hidden" name="form_type" value="add_experience">
                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cargo" name="cargo" required>
                    </div>
                    <div class="mb-3">
                        <label for="empresa" class="form-label">Empresa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="empresa" name="empresa" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">Añadir Experiencia</button>
                </form>

                <h5 class="mt-5 mb-3">Mis Experiencias Laborales</h5>
                <?php if (!empty($experiencias)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($experiencias as $exp): ?>
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($exp['titulo_puesto']) ?></h6>
                                        <p class="text-primary mb-1 small fw-600"><?= htmlspecialchars($exp['empresa_nombre']) ?></p>
                                        <small class="text-muted">
                                            <?= date('M Y', strtotime($exp['fecha_inicio'])) ?> - 
                                            <?= $exp['fecha_fin'] ? date('M Y', strtotime($exp['fecha_fin'])) : 'Actual' ?>
                                        </small>
                                    </div>
                                    <button class="delete-btn" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?= $exp['id_experiencia'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4">No has añadido experiencias laborales aún.</p>
                <?php endif; ?>
            </div>
        <?php elseif ($currentStep == 'education'): ?>
            <!-- Formulario Estudios Académicos -->
            <div class="card p-4 layout-premium border-0 shadow-sm">
                <div class="section-header mb-4">
                    <h4 class="mb-0">Añadir Nuevo Estudio</h4>
                </div>
                <form action="<?= BASE_URL ?>configurar_perfil?step=education" method="post">
                    <input type="hidden" name="form_type" value="add_education">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título / Grado <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="institucion" class="form-label">Institución <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="institucion" name="institucion" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">Añadir Estudio</button>
                </form>

                <h5 class="mt-5 mb-3">Mis Estudios Académicos</h5>
                <?php if (!empty($estudios)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($estudios as $edu): ?>
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($edu['titulo_grado']) ?></h6>
                                        <p class="text-primary mb-1 small fw-600"><?= htmlspecialchars($edu['institucion']) ?></p>
                                        <small class="text-muted">
                                            <?= date('M Y', strtotime($edu['fecha_inicio'])) ?> - 
                                            <?= $edu['fecha_fin'] ? date('M Y', strtotime($edu['fecha_fin'])) : 'Actual' ?>
                                        </small>
                                    </div>
                                    <button class="delete-btn" data-bs-toggle="modal" data-bs-target="#confirmDeleteEstudioModal<?= $edu['id_estudio'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4">No has añadido estudios académicos aún.</p>
                <?php endif; ?>
            </div>
        <?php elseif ($currentStep == 'skills'): ?>
            <div class="card p-4 layout-premium border-0 shadow-sm">
                <div class="section-header mb-4">
                    <h4 class="mb-0">Mis Habilidades</h4>
                    <p class="text-muted small">Agrega palabras clave sobre tus conocimientos técnicos o blandos.</p>
                </div>
                <form action="<?= BASE_URL ?>configurar_perfil?step=skills" method="post">
                    <input type="hidden" name="form_type" value="add_skill">
                    <div class="skill-input-container mb-3 d-flex gap-2">
                        <input type="text" class="form-control" name="new_skill" value="<?= isset($_POST['new_skill']) ? htmlspecialchars($_POST['new_skill']) : '' ?>" placeholder="Ej: PHP, React, Liderazgo" maxlength="100">
                        <button type="submit" name="add_skill" class="btn btn-primary px-4">
                            <i class="fas fa-plus me-1"></i> Añadir
                        </button>
                    </div>
                </form>

                <h5 class="mt-4 mb-3">Tus Skills (<?= count($habilidades) ?>)</h5>
                <?php if (!empty($habilidades)): ?>
                    <div class="skills-container">
                        <?php foreach ($habilidades as $skill): ?>
                            <div class="skill-badge">
                                <span><?= htmlspecialchars($skill) ?></span>
                                <a href="<?= BASE_URL ?>configurar_perfil?step=skills&delete_skill=<?= urlencode($skill) ?>" class="remove-skill" onclick="return confirm('¿Eliminar <?= htmlspecialchars($skill) ?>?');">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 bg-light rounded">
                        <p class="text-muted mb-0">No has agregado habilidades aún.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($currentStep == 'cv'): ?>
            <?php
                $rutaHdv = (string)($perfilData['ruta_hdv'] ?? '');
                $cvUrl = '';
                $cvFileName = '';
                if ($rutaHdv !== '') {
                    $cvFileName = basename($rutaHdv);
                    $cvUrl = rtrim(BASE_URL, '/') . '/' . ltrim($rutaHdv, '/');
                }
            ?>
            <div class="card p-4 layout-premium border-0 shadow-sm">
                <div class="section-header mb-4">
                    <h4 class="mb-0">Hoja de Vida (CV)</h4>
                    <p class="text-muted small">Sube tu CV para postularte más rápido a las ofertas.</p>
                </div>

                <?php if ($rutaHdv !== ''): ?>
                    <div class="alert alert-info border-0 shadow-sm">
                        CV actual: <a href="<?= htmlspecialchars($cvUrl) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($cvFileName) ?></a>
                    </div>
                <?php else: ?>
                    <div class="text-muted small mb-3">Aún no has subido una hoja de vida.</div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>configurar_perfil?step=cv" method="post" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="form_type" value="upload_cv">
                    <div class="mb-3">
                        <label for="cv" class="form-label">Subir hoja de vida</label>
                        <input type="file" class="form-control" id="cv" name="cv" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                        <small class="form-text text-muted">Formatos permitidos: PDF, DOC, DOCX. Máx. 5MB.</small>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-upload me-1"></i> Subir
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modals para confirmación de eliminación -->
<?php if (!empty($experiencias)): ?>
    <?php foreach ($experiencias as $exp): ?>
        <div class="modal fade" id="confirmDeleteModal<?= $exp['id_experiencia'] ?>" tabindex="-1" aria-labelledby="confirmDeleteLabel<?= $exp['id_experiencia'] ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="confirmDeleteLabel<?= $exp['id_experiencia'] ?>">Confirmar eliminación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que deseas eliminar esta experiencia laboral?
                    </div>
                    <div class="modal-footer">
                        <form method="post" action="<?= BASE_URL ?>configurar_perfil?step=experience">
                            <input type="hidden" name="form_type" value="delete_experience">
                            <input type="hidden" name="experience_id" value="<?= $exp['id_experiencia'] ?>">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($estudios)): ?>
    <?php foreach ($estudios as $edu): ?>
        <div class="modal fade" id="confirmDeleteEstudioModal<?= $edu['id_estudio'] ?>" tabindex="-1" aria-labelledby="confirmDeleteEstudioLabel<?= $edu['id_estudio'] ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="confirmDeleteEstudioLabel<?= $edu['id_estudio'] ?>">Confirmar eliminación</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        ¿Estás seguro de que deseas eliminar este estudio académico?
                    </div>
                    <div class="modal-footer">
                        <form method="post" action="<?= BASE_URL ?>configurar_perfil?step=education">
                            <input type="hidden" name="form_type" value="delete_education">
                            <input type="hidden" name="education_id" value="<?= $edu['id_estudio'] ?>">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="<?= BASE_URL ?>src/public/js/configurar_perfil.js"></script>
</body>
</html>
