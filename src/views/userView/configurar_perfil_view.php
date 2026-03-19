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
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/navbar_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/configurar_perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../dashboardView/navbar_view.php'; ?>

<div class="container mt-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show text-center" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h1 class="text-center mb-4">Configura tu Perfil</h1>

    <div class="progress mb-4">
        <div class="progress-bar" role="progressbar" style="width: <?= $currentStep == 'personal' ? '25' : ($currentStep == 'experience' ? '50' : ($currentStep == 'education' ? '75' : '100')) ?>%;" aria-valuenow="<?= $currentStep == 'personal' ? '25' : ($currentStep == 'experience' ? '50' : ($currentStep == 'education' ? '75' : '100')) ?>" aria-valuemin="0" aria-valuemax="100">Paso <?= $currentStep == 'personal' ? '1' : ($currentStep == 'experience' ? '2' : ($currentStep == 'education' ? '3' : '4')) ?> de 4</div>
    </div>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'personal' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=personal">Información Personal</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'experience' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=experience">Experiencia Laboral</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'education' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=education">Estudios Académicos</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentStep == 'skills' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil?step=skills">Habilidades</a>
        </li>
    </ul>

    <div class="tab-content">
        <?php if ($currentStep == 'personal'): ?>
            <!-- Formulario Información Personal -->
            <div class="card p-4">
                <form action="<?= BASE_URL ?>configurar_perfil?step=personal" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="form_type" value="personal_info">
                    <!-- Foto de Perfil -->
                    <div class="mb-3 text-center">
                        <label for="foto_perfil" class="form-label">Foto de Perfil</label>
                        <div class="profile-picture-container">
                            <img src="<?= $profileImage ?>" alt="Foto de perfil" class="profile-picture">
                            <div class="profile-picture-overlay">Cambiar Foto</div>
                            <input type="file" class="form-control" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/gif">
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 5MB. Haz clic en la imagen para cambiarla.</small>
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
                    <button type="submit" class="btn btn-primary">Guardar Información Personal</button>
                    <button type="button" class="btn btn-secondary ms-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Cambiar Contraseña</button>
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
            <div class="card p-4">
                <h5 class="mb-3">Añadir Nueva Experiencia Laboral</h5>
                <form action="<?= BASE_URL ?>configurar_perfil?step=experience" method="post">
                    <input type="hidden" name="form_type" value="add_experience">
                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cargo" name="cargo" value="<?= isset($_POST['cargo']) ? htmlspecialchars($_POST['cargo']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="empresa" class="form-label">Empresa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="empresa" name="empresa" value="<?= isset($_POST['empresa']) ? htmlspecialchars($_POST['empresa']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_fin" class="form-label">Fecha de Fin (dejar en blanco si es actual)</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= isset($_POST['fecha_fin']) ? htmlspecialchars($_POST['fecha_fin']) : '' ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Añadir Experiencia</button>
                </form>

                <h5 class="mt-4">Mis Experiencias Laborales</h5>
                <?php if (!empty($experiencias)): ?>
                    <div class="list-group">
                        <?php foreach ($experiencias as $exp): ?>
                            <div class="list-group-item">
                                <h6><?= htmlspecialchars($exp['titulo_puesto']) ?> en <?= htmlspecialchars($exp['empresa_nombre']) ?></h6>
                                <p class="mb-1"><?= htmlspecialchars($exp['descripcion'] ?? 'Sin descripción') ?></p>
                                <small class="text-muted">
                                    <?= date('M Y', strtotime($exp['fecha_inicio'])) ?> - 
                                    <?= $exp['fecha_fin'] ? date('M Y', strtotime($exp['fecha_fin'])) : 'Actual' ?>
                                </small>
                                <button class="btn btn-danger btn-sm float-end" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?= $exp['id_experiencia'] ?>">Eliminar</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No has añadido experiencias laborales aún.</p>
                <?php endif; ?>
            </div>
        <?php elseif ($currentStep == 'education'): ?>
            <!-- Formulario Estudios Académicos -->
            <div class="card p-4">
                <h5 class="mb-3">Añadir Nuevo Estudio Académico</h5>
                <form action="<?= BASE_URL ?>configurar_perfil?step=education" method="post">
                    <input type="hidden" name="form_type" value="add_education">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="titulo" name="titulo" value="<?= isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="institucion" class="form-label">Institución <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="institucion" name="institucion" value="<?= isset($_POST['institucion']) ? htmlspecialchars($_POST['institucion']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for 'fecha_fin' class="form-label">Fecha de Fin (dejar en blanco si es actual)</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= isset($_POST['fecha_fin']) ? htmlspecialchars($_POST['fecha_fin']) : '' ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Añadir Estudio</button>
                </form>

                <h5 class="mt-4">Mis Estudios Académicos</h5>
                <?php if (!empty($estudios)): ?>
                    <div class="list-group">
                        <?php foreach ($estudios as $edu): ?>
                            <div class="list-group-item">
                                <h6><?= htmlspecialchars($edu['titulo_grado']) ?> en <?= htmlspecialchars($edu['institucion']) ?></h6>
                                <small class="text-muted">
                                    <?= date('M Y', strtotime($edu['fecha_inicio'])) ?> - 
                                    <?= $edu['fecha_fin'] ? date('M Y', strtotime($edu['fecha_fin'])) : 'Actual' ?>
                                </small>
                                <button class="btn btn-danger btn-sm float-end" data-bs-toggle="modal" data-bs-target="#confirmDeleteEstudioModal<?= $edu['id_estudio'] ?>">Eliminar</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No has añadido estudios académicos aún.</p>
                <?php endif; ?>
            </div>
        <?php elseif ($currentStep == 'skills'): ?>
            <!-- Formulario Habilidades -->
            <div class="card p-4">
                <h5 class="mb-3">Añadir Nueva Habilidad</h5>
                <form action="<?= BASE_URL ?>configurar_perfil?step=skills" method="post">
                    <input type="hidden" name="form_type" value="add_skill">
                    <div class="skill-input-container mb-3 d-flex">
                        <input type="text" class="form-control me-2" name="new_skill" value="<?= isset($_POST['new_skill']) ? htmlspecialchars($_POST['new_skill']) : '' ?>" placeholder="Ej: PHP, MySQL, JavaScript" maxlength="100">
                        <button type="submit" name="add_skill" class="btn btn-primary btn-add-skill">
                            <i class="fas fa-plus"></i> Añadir
                        </button>
                    </div>
                    <small class="form-text text-muted">Escribe una habilidad y presiona añadir.</small>
                </form>

                <h5 class="mt-4">Mis Habilidades (<?= count($habilidades) ?>)</h5>
                <?php if (!empty($habilidades)): ?>
                    <div class="skills-container mb-3">
                        <?php foreach ($habilidades as $skill): ?>
                            <span class="skill-badge">
                                <?= htmlspecialchars($skill) ?>
                                <a href="<?= BASE_URL ?>configurar_perfil?step=skills&delete_skill=<?= urlencode($skill) ?>" class="skill-delete-btn" onclick="return confirm('¿Estás seguro de que deseas eliminar la habilidad: <?= htmlspecialchars($skill) ?>?');" title="Eliminar habilidad">
                                    &times;
                                </a>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No has agregado habilidades aún.</p>
                <?php endif; ?>
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