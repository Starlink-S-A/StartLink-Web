<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ofertas de Empleo - StartLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/navbar_styles.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../dashboardView/navbar_view.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Ofertas de Empleo</h2>

    <?php if (!empty($_SESSION['mensaje'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($esContratador): ?>
        <div class="mb-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearOferta">
                <i class="fas fa-plus"></i> Nueva Oferta
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($ofertas)): ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle"></i> No hay ofertas disponibles en este momento.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($ofertas as $oferta): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <?php if (!empty($oferta['logoEmpresa'])):?>
                                <div class="mb-3 d-flex justify-content-center">
                                    <div style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; background-color: #f0f0f0;">
                                        <img src="<?= htmlspecialchars($oferta['logoEmpresa']) ?>" alt="Logo Empresa"
                                             class="w-100 h-100" style="object-fit: contain;">
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <h5 class="card-title"><?= $oferta['titulo_oferta'] ?></h5>
                            <p class="card-text text-muted"><?= $oferta['descripcion_oferta'] ?></p>
                            
                            <div class="mb-3">
                                <p class="mb-1"><strong>Empresa:</strong> <?= $oferta['nombre_empresa'] ?></p>
                                
                                <?php if (!empty($oferta['presupuesto_min']) || !empty($oferta['presupuesto_max'])): ?>
                                    <p class="mb-1"><strong>Salario:</strong> 
                                        <?= isset($oferta['presupuesto_min']) ? number_format($oferta['presupuesto_min'], 2, ',', '.') : 'No especificado' ?> - 
                                        <?= isset($oferta['presupuesto_max']) ? number_format($oferta['presupuesto_max'], 2, ',', '.') : 'No especificado' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="mb-1">
                                    <strong>Ubicación:</strong> <?= $oferta['ubicacion'] ?? 'No especificada' ?> | 
                                    <strong>Modalidad:</strong> <span class="badge bg-secondary"><?= $oferta['modalidad'] ?? 'No especificada' ?></span>
                                </p>
                                
                                <p class="mb-1"><strong>Fecha de cierre:</strong> <?= $oferta['fecha_cierre'] ?? 'No especificada' ?></p>
                                
                                <p class="mb-0"><strong>Postulantes:</strong> <?= $oferta['numParticipantes'] ?>
                                    <?php if (!empty($oferta['limite_participantes']) && $oferta['limite_participantes'] > 0): ?>
                                        / <?= $oferta['limite_participantes'] ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <?php if ($oferta['yaPostulado']): ?>
                                <p class="mb-3"><strong>Tu estado:</strong>
                                    <?php
                                    switch ($oferta['estadoPostulacion']) {
                                        case 'Contratado':
                                            echo '<span class="badge bg-success"><i class="fas fa-check"></i> Contratado</span>';
                                            break;
                                        case 'Rechazado':
                                            echo '<span class="badge bg-danger"><i class="fas fa-times"></i> Rechazado</span>';
                                            break;
                                        default:
                                            echo '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half"></i> Postulado</span>';
                                    }
                                    ?>
                                </p>
                            <?php endif; ?>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <?php if ($oferta['yaPostulado']): ?>
                                    <?php if ($esUsuario): ?>
                                        <a href="<?= BASE_URL ?>index.php?action=chat&id_oferta=<?= $oferta['id_oferta'] ?>" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-comments"></i> Chat Grupal
                                        </a>
                                        <?php if ($userId != $oferta['id_creador_oferta']): ?>
                                            <a href="<?= BASE_URL ?>index.php?action=chat&id_oferta=<?= $oferta['id_oferta'] ?>&privado=1" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-envelope"></i> Chat Privado
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                            data-bs-target="#modalSalirOferta<?= $oferta['id_oferta'] ?>">
                                        <i class="fas fa-sign-out-alt"></i> Salir
                                    </button>
                                    
                                    <div class="modal fade" id="modalSalirOferta<?= $oferta['id_oferta'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="<?= BASE_URL ?>salir_oferta.php" method="POST">
                                                    <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">¿Salir de esta oferta?</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        ¿Estás seguro de que deseas salir de esta oferta? Tu postulación será eliminada.
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-warning">Sí, salir</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                <?php elseif ($esUsuario): ?>
                                    <form action="<?= BASE_URL ?>postular_oferta.php" method="POST" class="d-inline">
                                        <input type="hidden" name="id_oferta" value="<?= $oferta['id_oferta'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-paper-plane"></i> Postularme
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($oferta['esCreador'] && $esContratador): ?>
                                    <a href="<?= BASE_URL ?>detalle_oferta.php?id=<?= $oferta['id_oferta'] ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-users"></i> Ver Postulados
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" 
                                            data-bs-target="#modalEliminarOferta<?= $oferta['id_oferta'] ?>">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalEliminarOferta<?= $oferta['id_oferta'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST" action="eliminar_oferta.php">
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
            <form method="POST" action="<?= BASE_URL ?>crear_oferta.php" id="formCrearOferta">
                <input type="hidden" name="crear_oferta" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-briefcase"></i> Crear Nueva Oferta de Empleo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Validación del formulario de creación
document.getElementById('formCrearOferta')?.addEventListener('submit', function(e) {
    const presupuestoMin = parseFloat(this.elements['presupuesto_min'].value);
    const presupuestoMax = parseFloat(this.elements['presupuesto_max'].value);
    
    if (presupuestoMin > presupuestoMax) {
        alert('El presupuesto mínimo no puede ser mayor que el máximo');
        e.preventDefault();
        return;
    }
    
    const fechaCierre = new Date(this.elements['fecha_cierre'].value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    if (fechaCierre < hoy) {
        alert('La fecha de cierre no puede ser anterior a hoy');
        e.preventDefault();
    }
});
</script>
</body>
</html>