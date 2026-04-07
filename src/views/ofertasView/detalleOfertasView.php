<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Postulantes – <?= htmlspecialchars($oferta['titulo_oferta'] ?? '') ?> - TalentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>src/public/styles/navbar_styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .salario-actual {
            background-color: #e2e8f0;
            color: #334155;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .applicant-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px 0 0 12px;
        }
        @media (max-width: 768px) {
            .applicant-avatar { border-radius: 12px 12px 0 0; max-height: 250px; }
        }
        .skill-badge {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            color: #475569;
            font-weight: 500;
            font-size: 0.75rem;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid #cbd5e1;
            margin-right: 6px;
            margin-bottom: 6px;
            display: inline-block;
        }
        .timeline-item {
            position: relative;
            padding-left: 20px;
            margin-bottom: 10px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 6px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            font-weight: 700;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
<?php
$userName = $_SESSION["user_name"] ?? 'Usuario';
$pageTitle = 'Detalle de Oferta';
include ROOT_PATH . 'src/views/dashboardView/sidebar_View.php';
?>

<div class="main-content">
    <?php include ROOT_PATH . 'src/views/dashboardView/navbar_view.php'; ?>
    
    <!-- HEADER OFERTA -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-700 mb-1" style="font-size:1.6rem; color:#1e293b;">
                <i class="fas fa-briefcase me-2 text-success"></i> <?= htmlspecialchars($oferta['titulo_oferta'] ?? '') ?>
            </h2>
            <p class="text-muted mb-0 small">
                <i class="fas fa-building me-1"></i> <?= htmlspecialchars($oferta['nombre_empresa'] ?? '') ?> &nbsp; | &nbsp; 
                <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($oferta['ubicacion'] ?? 'No especificada') ?> (<?= htmlspecialchars($oferta['modalidad'] ?? 'No especificada') ?>)
            </p>
        </div>
        <a href="<?= BASE_URL ?>src/index.php?action=ofertas" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Volver a ofertas
        </a>
    </div>

    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($mensaje ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- RESUMEN OFERTA -->
    <div class="dash-card mb-5">
        <div class="dash-card-header">
            <h5 class="dash-card-title"><i class="fas fa-info-circle text-success me-2"></i>Detalles de la Oferta</h5>
            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 fw-600">
                Presupuesto: 
                <?php if (isset($oferta['presupuesto_min']) && isset($oferta['presupuesto_max'])): ?>
                    $<?= number_format($oferta['presupuesto_min'], 2) ?> – $<?= number_format($oferta['presupuesto_max'], 2) ?>
                <?php else: ?>
                    A convenir
                <?php endif; ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="info-label">Descripción</div>
                    <p class="text-secondary" style="font-size:0.9rem; line-height:1.6;"><?= nl2br(htmlspecialchars($oferta['descripcion_oferta'] ?? '')) ?></p>
                </div>
                <div class="col-md-6">
                    <div class="info-label">Requisitos</div>
                    <p class="text-secondary" style="font-size:0.9rem; line-height:1.6;"><?= nl2br(htmlspecialchars($oferta['requisitos'] ?? '')) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTA POSTULANTES -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h3 class="fw-700 m-0"><i class="fas fa-users text-success me-2"></i>Postulantes Activos <span class="badge bg-secondary rounded-pill fs-6 ms-2"><?= count($postulantes) ?></span></h3>
    </div>

    <div class="row g-4 mb-5">
        <?php if (empty($postulantes)): ?>
            <div class="col-12">
                <div class="dash-card text-center py-5">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;background:#f1f5f9;">
                        <i class="fas fa-folder-open text-muted fs-3"></i>
                    </div>
                    <h5 class="fw-600 text-slate-700">Aún no hay postulantes</h5>
                    <p class="text-muted">Parece que nadie se ha postulado a esta oferta todavía, o todos han sido contratados/rechazados.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($postulantes as $u): ?>
                <div class="col-12">
                    <div class="dash-card p-sm-4 p-3 shadow-sm hover-shadow transition-all mb-4">
                        <div class="d-flex flex-column flex-md-row gap-4 align-items-md-start">
                            
                            <!-- FOTO y SALARIO -->
                            <div class="d-flex flex-column align-items-center flex-shrink-0" style="width: 140px;">
                                <?php
                                $img = 'https://static.thenounproject.com/png/4154905-200.png';
                                if (!empty($u['foto_perfil']) && file_exists(ROOT_PATH . $u['foto_perfil'])) {
                                    $img = BASE_URL . $u['foto_perfil'];
                                }
                                ?>
                                <div class="rounded-circle overflow-hidden shadow-sm border border-3 border-white mb-3" style="width: 110px; height: 110px;">
                                    <img src="<?= htmlspecialchars($img) ?>" alt="Foto de perfil" class="w-100 h-100" style="object-fit: cover;">
                                </div>
                                
                                <?php if (!empty($u['salario_base'])): ?>
                                    <div class="badge bg-success-subtle text-success w-100 py-2 fw-bold shadow-sm" style="font-size: 0.8rem;">
                                        <i class="fas fa-money-bill-wave me-1"></i> $<?= number_format($u['salario_base'], 0, ',', '.') ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- INFO PRINCIPAL -->
                            <div class="flex-grow-1 w-100">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start mb-4 gap-3">
                                    <div>
                                        <h4 class="fw-bold mb-2 text-dark" style="font-size: 1.4rem;"><?= htmlspecialchars($u['nombre'] ?? '') ?></h4>
                                        <span class="badge bg-light text-secondary border px-3 py-2 fw-semibold mb-2" style="font-size:0.85rem;">
                                            <i class="fas fa-user-tag me-1 text-success"></i> <?= htmlspecialchars($u['cargo'] ?? 'Sin cargo') ?>
                                        </span>
                                        <div class="text-muted small d-flex flex-wrap gap-3 mt-2">
                                            <span><i class="fas fa-envelope me-1 text-secondary"></i> <?= htmlspecialchars($u['email'] ?? '') ?></span>
                                            <span><i class="fas fa-id-card me-1 text-secondary"></i> <?= htmlspecialchars($u['dni'] ?? 'Sin DNI') ?></span>
                                        </div>
                                    </div>
                                    
                                    <!-- BOTONES SUPERIORES (Acciones) -->
                                    <div class="d-flex gap-2 flex-wrap justify-content-md-end">
                                        <?php if (!empty($u['ruta_hdv'])): 
                                            $cvPath = 'assets/images/Uploads/cvs/' . basename($u['ruta_hdv']);
                                            if (file_exists(ROOT_PATH . $cvPath)): ?>
                                            <a href="<?= BASE_URL . $cvPath ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-600 shadow-sm" title="Ver CV">
                                                <i class="fas fa-file-pdf text-danger"></i>
                                            </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($esCreador): ?>
                                            <a href="chat_oferta.php?id_oferta=<?= $oferta['id_oferta'] ?>&id_usuario_privado=<?= $u['id'] ?>&tipo_chat=privado" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-600 shadow-sm" title="Chat Privado">
                                                <i class="fas fa-comment-dots text-info"></i>
                                            </a>
                                        <?php endif; ?>

                            <div class="d-flex gap-2 mt-3">
                                <a href="index.php?action=mis_chats&id_oferta=<?= $oferta['id_oferta'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-comments"></i> Chat de la Oferta
                                </a>

                                <?php if ($esCreador): ?>
                                    <a href="index.php?action=mis_chats&id_oferta=<?= $oferta['id_oferta'] ?>&id_usuario_privado=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-comment-dots"></i> Chat Privado con Postulante
                                    </a>
                                <?php endif; ?>

                                    <?php if (!empty($u['experiencias'])): ?>
                                    <div class="col-md-4">
                                        <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing:1px;">Experiencia Laboral</h6>
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                            <?php 
                                            $exps = explode('||', $u['experiencias']); 
                                            foreach ($exps as $exp): ?>
                                                <li class="text-secondary small d-flex align-items-start">
                                                    <i class="fas fa-briefcase text-success mt-1 me-2" style="font-size:0.75rem;"></i>
                                                    <span><?= htmlspecialchars($exp) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($u['estudios'])): ?>
                                    <div class="col-md-4">
                                        <h6 class="fw-bold text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing:1px;">Formación Académica</h6>
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                            <?php 
                                            $ests = explode('||', $u['estudios']); 
                                            foreach ($ests as $est): ?>
                                                <li class="text-secondary small d-flex align-items-start">
                                                    <i class="fas fa-graduation-cap text-success mt-1 me-2" style="font-size:0.75rem;"></i>
                                                    <span><?= htmlspecialchars($est) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODALES AFUERA DE LA TARJETA (Evita bugs de z-index y fixed positioning) -->
                
                <!-- MODAL CONTRATAR -->
                <div class="modal fade" id="modalContratar<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="POST">
                            <input type="hidden" name="id_postulante" value="<?= $u['id'] ?>">
                            <input type="hidden" name="accion" value="Contratado">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-700 text-success"><i class="fas fa-user-check me-2"></i>Contratar Candidato</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body py-4">
                                    <p class="mb-4">¿Confirmas que deseas contratar a <strong class="text-dark"><?= htmlspecialchars($u['nombre'] ?? '') ?></strong> para esta oferta?</p>
                                    
                                    <div class="mb-3">
                                        <label for="salario<?= $u['id'] ?>" class="form-label fw-600">Salario acordado (Base):</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">$</span>
                                            <input type="number" step="0.01" class="form-control" id="salario<?= $u['id'] ?>" 
                                                    name="salario_contratado" required
                                                    min="<?= $oferta['presupuesto_min'] ?>" 
                                                    max="<?= $oferta['presupuesto_max'] ?>"
                                                    placeholder="Ej: <?= number_format($oferta['presupuesto_min'], 2) ?>"
                                                    value="<?= !empty($u['salario_base']) ? number_format($u['salario_base'], 2, '.', '') : '' ?>">
                                        </div>
                                        <div class="form-text text-muted small"><i class="fas fa-info-circle"></i> Rango de la oferta: $<?= number_format($oferta['presupuesto_min'], 2) ?> - $<?= number_format($oferta['presupuesto_max'], 2) ?></div>
                                    </div>

                                    <div class="mb-2">
                                        <label for="horas_semanales<?= $u['id'] ?>" class="form-label fw-600">Horas Semanales Estándar:</label>
                                        <input type="number" step="0.5" class="form-control" id="horas_semanales<?= $u['id'] ?>"
                                                name="horas_semanales_estandar" required min="1" max="168"
                                                value="<?= !empty($u['horas_semanales_estandar']) ? htmlspecialchars($u['horas_semanales_estandar']) : 40 ?>">
                                        <div class="form-text text-muted small"><i class="fas fa-info-circle"></i> Define las horas base semanales (ej. 40).</div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-600 shadow-sm"><i class="fas fa-handshake me-2"></i>Confirmar Contrato</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- MODAL RECHAZAR -->
                <div class="modal fade" id="modalRechazar<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <form method="POST">
                            <input type="hidden" name="id_postulante" value="<?= $u['id'] ?>">
                            <input type="hidden" name="accion" value="Rechazado">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-700 text-danger"><i class="fas fa-user-times me-2"></i>Rechazar Candidato</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body py-4">
                                    <p>¿Estás seguro de que deseas rechazar permanentemente a <strong><?= htmlspecialchars($u['nombre'] ?? '') ?></strong>?</p>
                                    <div class="alert alert-warning mb-0 border-0 rounded-3" style="background-color:#fffbeb; color:#d97706;">
                                        <i class="fas fa-exclamation-triangle me-2"></i> El usuario será descartado de la oferta y no podrá volver a postularse.
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-600 shadow-sm"><i class="fas fa-ban me-2"></i>Descartar Permanentemente</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div><!-- /.main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (form.querySelector('input[name="accion"]')?.value === 'Contratado') {
                    const salarioInput = form.querySelector('input[name="salario_contratado"]');
                    const min = parseFloat(salarioInput.min);
                    const max = parseFloat(salarioInput.max);
                    const value = parseFloat(salarioInput.value);
                    
                    if (isNaN(value) || value < min || value > max) {
                        alert(`El salario acordado ($${value}) debe estar dentro del presupuesto de la oferta ($${min.toFixed(2)} - $${max.toFixed(2)}).`);
                        e.preventDefault();
                        return false;
                    }

                    const horasSemanalesInput = form.querySelector('input[name="horas_semanales_estandar"]');
                    const horasValue = parseFloat(horasSemanalesInput.value);
                    if (isNaN(horasValue) || horasValue <= 0 || horasValue > 168) {
                        alert('Las horas semanales registradas no son válidas.');
                        e.preventDefault();
                        return false;
                    }
                }
                return true;
            });
        });
    });
</script>
</body>
</html>
