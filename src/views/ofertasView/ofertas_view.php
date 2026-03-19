<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ofertas de Empleo - StartLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background-color: #f8fafc;">
<?php include __DIR__ . '/../dashboardView/navbar_view.php'; ?>

<div class="container mt-5 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="dash-title mb-1">Ofertas de Empleo</h2>
            <p class="text-muted small">Encuentra tu próximo paso profesional o gestiona tus vacantes.</p>
        </div>
        <?php if ($esContratador): ?>
            <button class="btn btn-dash-primary px-4" data-bs-toggle="modal" data-bs-target="#modalCrearOferta">
                <i class="fas fa-plus me-2"></i> Nueva Oferta
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['mensaje'])): ?>
        <div class="alert alert-profile border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-info-circle me-2"></i> <?= htmlspecialchars($_SESSION['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($ofertas)): ?>
            <div class="col-12">
                <div class="dash-card p-5 text-center">
                    <i class="fas fa-search text-muted mb-3" style="font-size: 3rem;"></i>
                    <h4>No hay ofertas disponibles</h4>
                    <p class="text-muted">Vuelve más tarde para ver nuevas oportunidades.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($ofertas as $oferta): ?>
                <div class="col-lg-6">
                    <div class="dash-card h-100 p-4">
                        <div class="d-flex align-items-start mb-3">
                            <?php if (!empty($oferta['logoEmpresa'])):?>
                                <div class="me-3" style="width: 64px; height: 64px; flex-shrink: 0;">
                                    <div class="rounded-circle border overflow-hidden p-1 bg-white h-100 w-100">
                                        <img src="<?= htmlspecialchars($oferta['logoEmpresa']) ?>" alt="Logo"
                                             class="w-100 h-100" style="object-fit: contain;">
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="me-3 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; flex-shrink: 0;">
                                    <i class="fas fa-building text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex-grow-1">
                                <h5 class="fw-700 mb-1"><?= $oferta['titulo_oferta'] ?></h5>
                                <p class="text-primary fw-600 small mb-0"><?= $oferta['nombre_empresa'] ?></p>
                            </div>

                            <?php if ($oferta['yaPostulado']): ?>
                                <div class="ms-2">
                                    <?php
                                    switch ($oferta['estadoPostulacion']) {
                                        case 'Contratado':
                                            echo '<span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="fas fa-check me-1"></i> Contratado</span>';
                                            break;
                                        case 'Rechazado':
                                            echo '<span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="fas fa-times me-1"></i> Rechazado</span>';
                                            break;
                                        default:
                                            echo '<span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill"><i class="fas fa-hourglass-half me-1"></i> Postulado</span>';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <p class="text-muted small mb-3 line-clamp-2"><?= htmlspecialchars($oferta['descripcion_oferta']) ?></p>
                        
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <small class="text-muted d-block">Salario</small>
                                <span class="fw-600 small">
                                    <?php if (!empty($oferta['presupuesto_min']) || !empty($oferta['presupuesto_max'])): ?>
                                        $<?= number_format($oferta['presupuesto_min'], 0, ',', '.') ?> - $<?= number_format($oferta['presupuesto_max'], 0, ',', '.') ?>
                                    <?php else: ?>
                                        A convenir
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Ubicación</small>
                                <span class="fw-600 small"><?= $oferta['ubicacion'] ?? 'Remoto' ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Modalidad</small>
                                <span class="badge bg-light text-dark border px-2 py-1"><?= $oferta['modalidad'] ?? 'Full-time' ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Postulantes</small>
                                <span class="fw-600 small text-primary"><?= $oferta['numParticipantes'] ?> convocados</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top mt-auto">
                            <?php if ($oferta['yaPostulado']): ?>
                                <a href="<?= BASE_URL ?>index.php?action=chat&id_oferta=<?= $oferta['id_oferta'] ?>" 
                                   class="btn btn-sm btn-outline-success flex-grow-1">
                                    <i class="fas fa-comments me-1"></i> Chat
                                </a>
                                <button class="btn btn-sm btn-link text-danger text-decoration-none" data-bs-toggle="modal" 
                                        data-bs-target="#modalSalirOferta<?= $oferta['id_oferta'] ?>">
                                    Abandonar
                                </button>
                                
                                <div class="modal fade" id="modalSalirOferta<?= $oferta['id_oferta'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg">
                                            <form action="<?= BASE_URL ?>salir_oferta.php" method="POST">
                                                <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-700">¿Retirar postulación?</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body py-4">
                                                    Si abandonas esta oferta, perderás tu lugar en el proceso de selección de <strong><?= htmlspecialchars($oferta['nombre_empresa']) ?></strong>.
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4">Sí, retirar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php elseif ($esUsuario): ?>
                                <form action="<?= BASE_URL ?>postular_oferta.php" method="POST" class="flex-grow-1">
                                    <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                    <button type="submit" class="btn btn-sm btn-dash-primary w-100">
                                        <i class="fas fa-paper-plane me-1"></i> Postularme ahora
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($oferta['esCreador'] && $esContratador): ?>
                                <a href="<?= BASE_URL ?>detalle_oferta.php?id=<?= $oferta['id_oferta'] ?>" 
                                   class="btn btn-sm btn-outline-primary px-3">
                                    <i class="fas fa-users"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" 
                                        data-bs-target="#modalEliminarOferta<?= $oferta['id_oferta'] ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalEliminarOferta<?= $oferta['id_oferta'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST" action="<?= BASE_URL ?>index.php?action=eliminar_oferta">
                                <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title">¿Eliminar esta oferta?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que deseas eliminar esta oferta? <strong>Esta acción no se puede deshacer.</strong>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para crear nueva oferta -->
<div class="modal fade" id="modalCrearOferta" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>index.php?action=crear_oferta" id="formCrearOferta">
                <input type="hidden" name="crear_oferta" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-briefcase"></i> Crear Nueva Oferta de Empleo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label">Título de la oferta:</label>
                        <input type="text" name="titulo" class="form-control" required minlength="5" maxlength="255"
                               placeholder="Ej: Desarrollador Full Stack">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción:</label>
                        <textarea name="descripcion" class="form-control" required minlength="20" 
                                  style="min-height: 100px;" placeholder="Describe la oferta en detalle..."></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Presupuesto Mínimo:</label>
                            <input type="number" name="presupuesto_min" class="form-control" required min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Presupuesto Máximo:</label>
                            <input type="number" name="presupuesto_max" class="form-control" required min="0" step="0.01">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ubicación:</label>
                            <input type="text" name="ubicacion" class="form-control" required
                                   placeholder="Ej: Madrid, España">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modalidad:</label>
                            <select name="modalidad" class="form-select" required>
                                <option value="">Selecciona una modalidad</option>
                                <option value="Presencial">Presencial</option>
                                <option value="Remoto">Remoto</option>
                                <option value="Híbrido">Híbrido</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de Cierre:</label>
                        <input type="date" name="fecha_cierre" class="form-control" required 
                               min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Requisitos:</label>
                        <textarea name="requisitos" class="form-control" required 
                                  style="min-height: 100px;" placeholder="Detalla los requisitos necesarios..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Límite de Postulantes (0 = ilimitado):</label>
                        <input type="number" name="limite_postulantes" class="form-control" min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Publicar Oferta
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar oferta -->
<div class="modal fade" id="modalEditarOferta" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>index.php?action=editar_oferta" id="formEditarOferta">
                <input type="hidden" name="id_oferta" id="edit_id_oferta">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Editar Oferta de Empleo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label">Título de la oferta:</label>
                        <input type="text" name="titulo" id="edit_titulo" class="form-control" required minlength="5" maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="edit_descripcion" class="form-control" required minlength="20" 
                                  style="min-height: 100px;"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Presupuesto Mínimo:</label>
                            <input type="number" name="presupuesto_min" id="edit_presupuesto_min" class="form-control" required min="0" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Presupuesto Máximo:</label>
                            <input type="number" name="presupuesto_max" id="edit_presupuesto_max" class="form-control" required min="0" step="0.01">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ubicación:</label>
                            <input type="text" name="ubicacion" id="edit_ubicacion" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modalidad:</label>
                            <select name="modalidad" id="edit_modalidad" class="form-select" required>
                                <option value="">Selecciona una modalidad</option>
                                <option value="Presencial">Presencial</option>
                                <option value="Remoto">Remoto</option>
                                <option value="Híbrido">Híbrido</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de Cierre:</label>
                        <input type="date" name="fecha_cierre" id="edit_fecha_cierre" class="form-control" required 
                               min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Requisitos:</label>
                        <textarea name="requisitos" id="edit_requisitos" class="form-control" required 
                                  style="min-height: 100px;"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Límite de Postulantes (0 = ilimitado):</label>
                        <input type="number" name="limite_postulantes" id="edit_limite_postulantes" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>src/public/js/ofertas.js"></script>
</body>
</html>