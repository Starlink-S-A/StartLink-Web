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
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/configurar_perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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

    <ul class="nav profile-nav mb-4 justify-content-center" id="profileTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'personal' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=personal">
                <i class="fas fa-user-circle"></i> <span>Personal</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'experience' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=experience">
                <i class="fas fa-briefcase"></i> <span>Experiencia</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'education' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=education">
                <i class="fas fa-graduation-cap"></i> <span>Estudios</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'skills' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=skills">
                <i class="fas fa-tools"></i> <span>Habilidades</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'cv' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=cv">
                <i class="fas fa-file-alt"></i> <span>CV</span>
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
                    
                    <div class="row g-4">
                        <!-- Foto de Perfil (Columna Izquierda o Superior) -->
                        <div class="col-12 text-center mb-2">
                            <div class="position-relative d-inline-block">
                                <img src="<?= isset($profileImage) ? $profileImage : 'https://static.thenounproject.com/png/4154905-200.png' ?>" alt="Foto de perfil" class="img-thumbnail" style="width: 140px; height: 140px; object-fit: cover;">
                                <div class="mt-2">
                                    <label for="foto_perfil" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-camera me-1"></i> Cambiar Foto
                                    </label>
                                    <input type="file" class="d-none" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/gif">
                                </div>
                            </div>
                        </div>

                        <!-- Datos Personales -->
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : htmlspecialchars($perfilData['nombre'] ?? '') ?>" required placeholder="Tu nombre completo">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($perfilData['email'] ?? '') ?>" required placeholder="ejemplo@correo.com">
                        </div>

                        <div class="col-md-6">
                            <label for="dni" class="form-label">DNI / Identificación <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dni" name="dni" value="<?= isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : htmlspecialchars($perfilData['dni'] ?? '') ?>" maxlength="12" pattern="[0-9]{8,12}" required placeholder="Solo números">
                        </div>

                        <div class="col-md-6">
                            <label for="genero" class="form-label">Género <span class="text-danger">*</span></label>
                            <select class="form-select" id="genero" name="genero" required>
                                <option value="">Selecciona...</option>
                                <option value="masculino" <?= (isset($_POST['genero']) ? $_POST['genero'] : ($perfilData['genero'] ?? '')) == 'masculino' ? 'selected' : '' ?>>Masculino</option>
                                <option value="femenino" <?= (isset($_POST['genero']) ? $_POST['genero'] : ($perfilData['genero'] ?? '')) == 'femenino' ? 'selected' : '' ?>>Femenino</option>
                                <option value="otro" <?= (isset($_POST['genero']) ? $_POST['genero'] : ($perfilData['genero'] ?? '')) == 'otro' ? 'selected' : '' ?>>Otro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="<?= isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : htmlspecialchars($perfilData['telefono'] ?? '') ?>" maxlength="15" placeholder="+57 300 000 0000">
                        </div>

                        <div class="col-md-6">
                            <label for="pais" class="form-label">País <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="pais" name="pais" value="<?= isset($_POST['pais']) ? htmlspecialchars($_POST['pais']) : htmlspecialchars($perfilData['pais'] ?? '') ?>" required placeholder="Tu país">
                        </div>

                        <div class="col-md-6">
                            <label for="departamento" class="form-label">Departamento / Estado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="departamento" name="departamento" value="<?= isset($_POST['departamento']) ? htmlspecialchars($_POST['departamento']) : htmlspecialchars($perfilData['departamento'] ?? '') ?>" required placeholder="Tu departamento">
                        </div>

                        <div class="col-md-6">
                            <label for="ciudad" class="form-label">Ciudad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?= isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : htmlspecialchars($perfilData['ciudad'] ?? '') ?>" required placeholder="Tu ciudad">
                        </div>
                    </div>

                    <div class="form-navigation mt-5 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-key me-2"></i>Seguridad
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
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
                    <p class="text-muted small">Cuéntanos sobre tu trayectoria profesional.</p>
                </div>
                <form action="<?= BASE_URL ?>configurar_perfil?step=experience" method="post">
                    <input type="hidden" name="form_type" value="add_experience">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cargo" name="cargo" required placeholder="Ej: Desarrollador Backend">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="empresa" class="form-label">Empresa <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="empresa" name="empresa" required placeholder="Nombre de la empresa">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="descripcion" class="form-label">Descripción de responsabilidades</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Describe brevemente tus logros y tareas..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                            <small class="text-muted">Deja en blanco si es tu empleo actual.</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-plus me-2"></i>Añadir Experiencia
                        </button>
                    </div>
                </form>

                <div class="mt-5">
                    <h5 class="mb-4 d-flex align-items-center">
                        <i class="fas fa-history me-2 text-primary"></i>Mi Trayectoria
                    </h5>
                    <?php if (!empty($experiencias)): ?>
                        <div class="list-group list-group-flush timeline-list">
                            <?php foreach ($experiencias as $exp): ?>
                                <div class="list-group-item border-0 ps-4 position-relative mb-4">
                                    <div class="timeline-dot"></div>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-700 mb-1"><?= htmlspecialchars($exp['titulo_puesto']) ?></h6>
                                            <div class="text-primary small fw-600 mb-2">
                                                <i class="fas fa-building me-1"></i><?= htmlspecialchars($exp['empresa_nombre']) ?>
                                            </div>
                                            <div class="text-muted small mb-2">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?= date('M Y', strtotime($exp['fecha_inicio'])) ?> — 
                                                <?= $exp['fecha_fin'] ? date('M Y', strtotime($exp['fecha_fin'])) : 'Actualidad' ?>
                                            </div>
                                            <?php if (!empty($exp['descripcion'])): ?>
                                                <p class="text-muted small mb-0"><?= nl2br(htmlspecialchars($exp['descripcion'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle p-2" style="width: 32px; height: 32px; line-height: 1;" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?= $exp['id_experiencia'] ?>">
                                            <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 bg-light rounded-4">
                            <i class="fas fa-briefcase text-muted mb-3" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">No has añadido experiencias laborales aún.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($currentStep == 'education'): ?>
            <!-- Formulario Estudios Académicos -->
            <div class="card p-4 layout-premium border-0 shadow-sm">
                <div class="section-header mb-4">
                    <h4 class="mb-0">Añadir Nuevo Estudio</h4>
                    <p class="text-muted small">Registra tus títulos y logros académicos.</p>
                </div>
                <form action="<?= BASE_URL ?>configurar_perfil?step=education" method="post">
                    <input type="hidden" name="form_type" value="add_education">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="titulo" class="form-label">Título / Grado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required placeholder="Ej: Ingeniero de Sistemas">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="institucion" class="form-label">Institución <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="institucion" name="institucion" required placeholder="Nombre de la universidad o colegio">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                            <small class="text-muted">Deja en blanco si aún estás estudiando.</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-plus me-2"></i>Añadir Estudio
                        </button>
                    </div>
                </form>

                <div class="mt-5">
                    <h5 class="mb-4 d-flex align-items-center">
                        <i class="fas fa-graduation-cap me-2 text-primary"></i>Mi Formación
                    </h5>
                    <?php if (!empty($estudios)): ?>
                        <div class="list-group list-group-flush timeline-list">
                            <?php foreach ($estudios as $edu): ?>
                                <div class="list-group-item border-0 ps-4 position-relative mb-4">
                                    <div class="timeline-dot education"></div>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-700 mb-1"><?= htmlspecialchars($edu['titulo_grado']) ?></h6>
                                            <div class="text-primary small fw-600 mb-2">
                                                <i class="fas fa-university me-1"></i><?= htmlspecialchars($edu['institucion']) ?>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?= date('M Y', strtotime($edu['fecha_inicio'])) ?> — 
                                                <?= $edu['fecha_fin'] ? date('M Y', strtotime($edu['fecha_fin'])) : 'En curso' ?>
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger rounded-circle p-2" style="width: 32px; height: 32px; line-height: 1;" data-bs-toggle="modal" data-bs-target="#confirmDeleteEstudioModal<?= $edu['id_estudio'] ?>">
                                            <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 bg-light rounded-4">
                            <i class="fas fa-graduation-cap text-muted mb-3" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">No has añadido estudios académicos aún.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($currentStep == 'skills'): ?>
            <div class="card p-4 layout-premium border-0 shadow-sm">
                <div class="section-header mb-4">
                    <h4 class="mb-0">Mis Habilidades</h4>
                    <p class="text-muted small">Agrega palabras clave sobre tus conocimientos técnicos o blandos.</p>
                </div>
                <form action="<?= BASE_URL ?>configurar_perfil?step=skills" method="post">
                    <input type="hidden" name="form_type" value="add_skill">
                    <div class="skill-input-container mb-4 d-flex gap-2">
                        <input type="text" class="form-control rounded-pill" name="new_skill" value="<?= isset($_POST['new_skill']) ? htmlspecialchars($_POST['new_skill']) : '' ?>" placeholder="Ej: PHP, React, Liderazgo" maxlength="100">
                        <button type="submit" name="add_skill" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-plus me-1"></i>Añadir
                        </button>
                    </div>
                </form>

                <div class="mt-4">
                    <h5 class="mb-3 d-flex align-items-center">
                        <i class="fas fa-tools me-2 text-primary"></i>Habilidades Registradas (<?= count($habilidades) ?>)
                    </h5>
                    <?php if (!empty($habilidades)): ?>
                        <div class="skills-container bg-light p-4 rounded-4">
                            <?php foreach ($habilidades as $skill): ?>
                                <div class="skill-badge px-3 py-2">
                                    <span><?= htmlspecialchars($skill) ?></span>
                                    <a href="<?= BASE_URL ?>configurar_perfil?step=skills&delete_skill=<?= urlencode($skill) ?>" class="remove-skill ms-2 text-decoration-none" onclick="return confirm('¿Eliminar <?= htmlspecialchars($skill) ?>?');">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 bg-light rounded-4">
                            <i class="fas fa-lightbulb text-muted mb-3" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">Aún no has registrado ninguna habilidad.</p>
                        </div>
                    <?php endif; ?>
                </div>
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
                    <p class="text-muted small">Sube tu CV para que las empresas puedan conocer mejor tu perfil profesional.</p>
                </div>

                <div class="mb-5">
                    <h5 class="mb-3">Documento Actual</h5>
                    <?php if ($rutaHdv !== ''): ?>
                        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center rounded-4 p-3">
                            <div class="bg-white rounded-circle p-2 me-3">
                                <i class="fas fa-file-pdf text-danger fa-2x"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-600 mb-1"><?= htmlspecialchars($cvFileName) ?></div>
                                <a href="<?= htmlspecialchars($cvUrl) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill mt-1" rel="noopener noreferrer">
                                    <i class="fas fa-eye me-1"></i>Ver Documento
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 bg-light rounded-4">
                            <i class="fas fa-file-upload text-muted mb-3" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">No has subido una hoja de vida todavía.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <h5 class="mb-3"><?= $rutaHdv !== '' ? 'Actualizar CV' : 'Subir CV' ?></h5>
                    <form action="<?= BASE_URL ?>configurar_perfil?step=cv" method="post" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="form_type" value="upload_cv">
                        <div class="mb-4">
                            <div class="input-group">
                                <input type="file" class="form-control" id="cv" name="cv" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                            </div>
                            <small class="form-text text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>Formatos permitidos: <strong>PDF, DOC, DOCX</strong>. Tamaño máximo: <strong>5MB</strong>.
                            </small>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-5">
                                <i class="fas fa-upload me-2"></i>Subir y Guardar
                            </button>
                        </div>
                    </form>
                </div>
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
<script src="<?= BASE_URL ?>src/public/js/main.js"></script>
<script src="<?= BASE_URL ?>src/public/js/configurar_perfil.js"></script>
</body>
</html>
