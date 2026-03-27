<?php
// src/views/capacitacionesView/capacitaciones_view.php
// Vista del módulo de Capacitaciones - MVC
// Variables disponibles desde el controlador:
// $userId, $rolGlobal, $rolEmpresa, $puedeCrearCapacitacion
// $mensaje, $tipoMensaje, $capacitaciones
// $userName, $esAdminEmpresa, $profileImage
// $latestNotifications, $unreadNotificationsCount
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Capacitaciones - StartLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background-color: #f8fafc;">
<?php include __DIR__ . '/../dashboardView/sidebar_View.php'; ?>

<div class="container mt-5 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="dash-title mb-1">Capacitaciones</h2>
            <p class="text-muted small">Formaciones disponibles para impulsar tu desarrollo profesional.</p>
        </div>
        <?php if ($puedeCrearCapacitacion): ?>
            <button class="btn btn-dash-primary px-4" data-bs-toggle="modal" data-bs-target="#modalCrearCapacitacion">
                <i class="fas fa-plus me-2"></i>Nueva Capacitación
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?> border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($capacitaciones)): ?>
            <div class="col-12">
                <div class="dash-card p-5 text-center">
                    <i class="fas fa-chalkboard-teacher text-muted mb-3" style="font-size: 3rem;"></i>
                    <h4>No hay capacitaciones disponibles</h4>
                    <p class="text-muted">Vuelve más tarde para ver nuevas oportunidades de formación.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($capacitaciones as $capacitacion): ?>
                <div class="col-lg-6">
                    <div class="dash-card h-100 p-4">
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3 rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 52px; height: 52px; flex-shrink: 0; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white;">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-700 mb-1"><?= $capacitacion['nombre_capacitacion'] ?></h5>
                                <span class="badge bg-light text-dark border mb-1" style="font-size: 0.7rem;">
                                    <i class="fas fa-building me-1"></i><?= htmlspecialchars($capacitacion['nombre_empresa'] ?? 'Sin empresa') ?>
                                </span>
                                <p class="text-muted small mb-0 line-clamp-2"><?= $capacitacion['descripcion'] ?></p>
                            </div>
                            <?php if ($capacitacion['yaInscrito']): ?>
                                <div class="ms-2">
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                        <i class="fas fa-check me-1"></i>Inscrito
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <small class="text-muted d-block">Fecha Inicio</small>
                                <span class="fw-600 small"><?= $capacitacion['fecha_inicio_fmt'] ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Fecha Fin</small>
                                <span class="fw-600 small"><?= $capacitacion['fecha_fin_fmt'] ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Costo</small>
                                <span class="fw-600 small text-primary"><?= $capacitacion['costo_fmt'] ?></span>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-3 border-top mt-auto">
                            <?php if ($capacitacion['yaInscrito']): ?>
                                <?php 
                                    $fechaInicioObj = new DateTime($capacitacion['fecha_inicio']);
                                    $hoyObj = new DateTime();
                                    $hoyObj->setTime(0, 0, 0);
                                    $puedeCancelar = ($hoyObj < $fechaInicioObj);
                                ?>
                                <?php if ($puedeCancelar): ?>
                                <button class="btn btn-sm btn-outline-warning flex-grow-1" data-bs-toggle="modal" data-bs-target="#modalCancelarInscripcion<?= $capacitacion['id'] ?>">
                                    <i class="fas fa-times me-1"></i>Cancelar Inscripción
                                </button>
                                <?php else: ?>
                                <span class="btn btn-sm btn-outline-secondary flex-grow-1 disabled" title="No puedes cancelar después de la fecha de inicio">
                                    <i class="fas fa-lock me-1"></i>Inscripción confirmada
                                </span>
                                <?php endif; ?>

                            <?php elseif (!$capacitacion['puedeGestionarCapacitacion']): ?>
                                <form action="<?= BASE_URL ?>src/index.php?action=inscribir_capacitacion" method="POST" class="flex-grow-1">
                                    <input type="hidden" name="id_capacitacion" value="<?= $capacitacion['id'] ?>">
                                    <input type="hidden" name="accion" value="inscribir">
                                    <button type="submit" class="btn btn-sm btn-dash-primary w-100">
                                        <i class="fas fa-user-plus me-1"></i>Inscribirme
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($capacitacion['puedeGestionarCapacitacion']): ?>
                                <button class="btn btn-sm btn-outline-primary px-3 ver-inscritos-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalVerInscritos" 
                                        data-id-capacitacion="<?= $capacitacion['id'] ?>"
                                        data-nombre-capacitacion="<?= $capacitacion['nombre_capacitacion'] ?>"
                                        title="Ver Inscritos">
                                    <i class="fas fa-users"></i>
                                </button>

                                <button class="btn btn-sm btn-outline-warning px-3" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditarCapacitacion<?= $capacitacion['id'] ?>"
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button class="btn btn-sm btn-outline-danger px-3" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEliminarCapacitacion<?= $capacitacion['id'] ?>"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ═══ MODALS fuera de dash-card para evitar conflicto con CSS ═══ -->

                <?php if ($capacitacion['yaInscrito'] && $puedeCancelar): ?>
                <div class="modal fade" id="modalCancelarInscripcion<?= $capacitacion['id'] ?>" tabindex="-1" aria-labelledby="modalCancelarInscripcionLabel<?= $capacitacion['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <form action="<?= BASE_URL ?>src/index.php?action=inscribir_capacitacion" method="POST">
                                <input type="hidden" name="id_capacitacion" value="<?= $capacitacion['id'] ?>">
                                <input type="hidden" name="accion" value="cancelar">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-700" id="modalCancelarInscripcionLabel<?= $capacitacion['id'] ?>">¿Cancelar inscripción?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body py-4">
                                    Perderás tu lugar en <strong><?= htmlspecialchars($capacitacion['nombre_capacitacion']) ?></strong>.
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Volver</button>
                                    <button type="submit" class="btn btn-warning rounded-pill px-4">Sí, cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($capacitacion['puedeGestionarCapacitacion']): ?>
                <div class="modal fade" id="modalEliminarCapacitacion<?= $capacitacion['id'] ?>" tabindex="-1" aria-labelledby="modalEliminarCapacitacionLabel<?= $capacitacion['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <form action="<?= BASE_URL ?>src/index.php?action=eliminar_capacitacion" method="POST">
                                <input type="hidden" name="id_capacitacion" value="<?= $capacitacion['id'] ?>">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-700" id="modalEliminarCapacitacionLabel<?= $capacitacion['id'] ?>">¿Eliminar capacitación?</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body py-4">
                                    ¿Estás seguro de que deseas eliminar <strong><?= htmlspecialchars($capacitacion['nombre_capacitacion']) ?></strong>? <strong>Esta acción no se puede deshacer.</strong>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger rounded-pill px-4">Sí, eliminar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalEditarCapacitacion<?= $capacitacion['id'] ?>" tabindex="-1" aria-labelledby="modalEditarCapacitacionLabel<?= $capacitacion['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                            <form method="POST" action="<?= BASE_URL ?>src/index.php?action=editar_capacitacion">
                                <input type="hidden" name="editar_capacitacion" value="1">
                                <input type="hidden" name="id_capacitacion" value="<?= $capacitacion['id'] ?>">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-700" id="modalEditarCapacitacionLabel<?= $capacitacion['id'] ?>">
                                        <i class="fas fa-edit me-2"></i>Editar Capacitación
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre de la Capacitación:</label>
                                        <input type="text" name="nombre_capacitacion" class="form-control" value="<?= htmlspecialchars($capacitacion['nombre_capacitacion']) ?>" required minlength="5" maxlength="100">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Descripción:</label>
                                        <textarea name="descripcion" class="form-control" required minlength="20" style="min-height: 100px;"><?= htmlspecialchars($capacitacion['descripcion']) ?></textarea>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Fecha de Inicio:</label>
                                            <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($capacitacion['fecha_inicio']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Fecha de Fin:</label>
                                            <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($capacitacion['fecha_fin']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Costo (0 para gratis):</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" min="0" name="costo" class="form-control" value="<?= htmlspecialchars($capacitacion['costo']) ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-2"></i>Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Crear Capacitación (solo para usuarios con permiso) -->
<?php if ($puedeCrearCapacitacion): ?>
<div class="modal fade" id="modalCrearCapacitacion" tabindex="-1" aria-labelledby="modalCrearCapacitacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" action="<?= BASE_URL ?>src/index.php?action=crear_capacitacion" id="formCrearCapacitacion">
                <input type="hidden" name="crear_capacitacion" value="1">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-700" id="modalCrearCapacitacionLabel">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Crear Nueva Capacitación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Selector de Empresa -->
                    <?php if (!empty($empresasUsuario)): ?>
                    <div class="mb-3">
                        <label class="form-label">Empresa:</label>
                        <?php if (count($empresasUsuario) === 1): ?>
                            <input type="hidden" name="id_empresa" value="<?= $empresasUsuario[0]['id_empresa'] ?>">
                            <input type="text" class="form-control" value="<?= htmlspecialchars($empresasUsuario[0]['nombre_empresa']) ?>" readonly>
                        <?php else: ?>
                            <select name="id_empresa" class="form-select" required>
                                <option value="">Selecciona una empresa...</option>
                                <?php foreach ($empresasUsuario as $emp): ?>
                                    <option value="<?= $emp['id_empresa'] ?>"><?= htmlspecialchars($emp['nombre_empresa']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Capacitación:</label>
                        <input type="text" name="nombre_capacitacion" class="form-control" required minlength="5" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción:</label>
                        <textarea name="descripcion" class="form-control" required minlength="20" style="min-height: 100px;"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Inicio:</label>
                            <input type="date" name="fecha_inicio" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Fin:</label>
                            <input type="date" name="fecha_fin" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Costo (USD):</label>
                        <input type="number" name="costo" class="form-control" required min="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-dash-primary rounded-pill px-4">
                        <i class="fas fa-check me-1"></i>Publicar Capacitación
                    </button>
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal para ver inscritos en una capacitación -->
<div class="modal fade" id="modalVerInscritos" tabindex="-1" aria-labelledby="modalVerInscritosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-700" id="modalVerInscritosLabel">
                    <i class="fas fa-users me-2"></i>Inscritos en <span id="nombreCapacitacionInscritos"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="listaInscritos">
                    <p class="text-center text-muted" id="loadingInscritos">Cargando inscritos...</p>
                    <div id="totalInscritosContainer" class="alert alert-info border-0 d-flex align-items-center mb-3" style="display: none !important;">
                        <i class="fas fa-users me-2"></i>
                        <strong>Total de inscritos: <span id="totalInscritosCount">0</span></strong>
                    </div>
                    <ul class="list-group" id="ulInscritos">
                        <!-- Inscritos se añadirán aquí por JavaScript -->
                    </ul>
                    <p class="text-center text-muted" id="noInscritos" style="display: none;">No hay inscritos aún para esta capacitación.</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="btnImprimirInscritos" style="display: none;" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Validación adicional del formulario de creación de capacitación
document.getElementById('formCrearCapacitacion')?.addEventListener('submit', function(e) {
    const fechaInicio = new Date(this.elements['fecha_inicio'].value);
    const fechaFin = new Date(this.elements['fecha_fin'].value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    if (fechaInicio < hoy) {
        alert('La fecha de inicio no puede ser anterior a hoy.');
        e.preventDefault();
        return;
    }
    
    if (fechaFin < fechaInicio) {
        alert('La fecha de fin no puede ser anterior a la fecha de inicio.');
        e.preventDefault();
        return;
    }
});

// Lógica para el modal de "Ver Inscritos"
document.addEventListener('DOMContentLoaded', function() {
    const modalVerInscritos = document.getElementById('modalVerInscritos');
    const nombreCapacitacionInscritos = document.getElementById('nombreCapacitacionInscritos');
    const ulInscritos = document.getElementById('ulInscritos');
    const loadingInscritos = document.getElementById('loadingInscritos');
    const noInscritos = document.getElementById('noInscritos');

    modalVerInscritos.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; 
        const idCapacitacion = button.getAttribute('data-id-capacitacion');
        const nombreCapacitacion = button.getAttribute('data-nombre-capacitacion');

        nombreCapacitacionInscritos.textContent = nombreCapacitacion;
        ulInscritos.innerHTML = '';
        loadingInscritos.style.display = 'block';
        noInscritos.style.display = 'none';

        fetch('<?= BASE_URL ?>src/index.php?action=obtener_inscritos&id_capacitacion=' + idCapacitacion)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                loadingInscritos.style.display = 'none';
                const totalContainer = document.getElementById('totalInscritosContainer');
                const totalCount = document.getElementById('totalInscritosCount');
                const btnImprimir = document.getElementById('btnImprimirInscritos');
                if (data.success) {
                    if (data.inscritos.length > 0) {
                        totalCount.textContent = data.inscritos.length;
                        totalContainer.style.display = 'flex';
                        totalContainer.style.setProperty('display', 'flex', 'important');
                        btnImprimir.style.display = 'inline-block';
                        data.inscritos.forEach(inscrito => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item d-flex justify-content-between align-items-center';
                            li.innerHTML = `
                                <div>
                                    <strong>${inscrito.nombre}</strong> (${inscrito.email})
                                </div>
                                <span class="badge bg-primary rounded-pill">Inscrito desde: ${inscrito.fecha_inscripcion_fmt}</span>
                            `;
                            ulInscritos.appendChild(li);
                        });
                        ulInscritos.style.display = 'block';
                    } else {
                        totalContainer.style.display = 'none';
                        btnImprimir.style.display = 'none';
                        noInscritos.style.display = 'block';
                        ulInscritos.style.display = 'none';
                    }
                } else {
                    ulInscritos.innerHTML = `<li class="list-group-item text-danger">${data.message || 'No se pudo cargar la lista de inscritos.'}</li>`;
                    ulInscritos.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error al obtener inscritos:', error);
                loadingInscritos.style.display = 'none';
                ulInscritos.innerHTML = `<li class="list-group-item text-danger">Error al cargar los inscritos: ${error.message}.</li>`;
                ulInscritos.style.display = 'block';
            });
    });
});
</script>
</body>
</html>
