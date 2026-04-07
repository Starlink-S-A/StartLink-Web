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
        .btn-hover-light-green {
            border-color: #00a680;
            color: #00a680;
            background-color: transparent;
            transition: all 0.2s ease-in-out;
        }
        .btn-hover-light-green:hover {
            background-color: #d1fae5 !important;
            color: #1e293b !important;
            border-color: #00a680;
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
        <a href="<?= BASE_URL ?>src/index.php?action=ofertas" class="text-decoration-none text-dark fw-600">
            <i class="fas fa-arrow-left me-2"></i>Volver a ofertas
        </a>
        <div class="badge border rounded-2 px-3 py-2 fw-600 d-flex align-items-center shadow-sm" style="color: #0f766e; background-color: #d1fae5; border-color: #059669 !important; font-size: 0.95rem;">
            Presupuesto: 
            <?php if (isset($oferta['presupuesto_min'], $oferta['presupuesto_max']) && $oferta['presupuesto_min'] > 0): ?>
                $<?= number_format($oferta['presupuesto_min'], 2) ?> - $<?= number_format($oferta['presupuesto_max'], 2) ?>
            <?php else: ?>
                A convenir
            <?php endif; ?>
        </div>
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

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="card border border-secondary-subtle rounded-4 bg-white shadow-sm mb-5 overflow-hidden">
        
        <!-- CABECERA DE LA OFERTA -->
        <div class="p-4 p-md-5 d-flex align-items-center gap-4 flex-wrap border-bottom border-light" style="background-color: #e6fcf5;">
            <?php 
            $logoUrl = null;
            if (!empty($oferta['logo_ruta'])) {
                $nombreArchivo = basename((string)$oferta['logo_ruta']);
                $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'logos_empresa' . DIRECTORY_SEPARATOR . $nombreArchivo;
                $rutaPublica = rtrim(BASE_URL, '/') . '/assets/images/Uploads/logos_empresa/' . $nombreArchivo;
                if (file_exists($rutaAbsoluta)) {
                    $logoUrl = $rutaPublica;
                }
            }
            ?>
            <?php $bgClass = $logoUrl ? 'bg-transparent' : 'bg-warning'; ?>
            <div class="rounded-4 <?= $bgClass ?> border border-secondary d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm overflow-hidden" style="width: 70px; height: 70px;">
                <?php if ($logoUrl): ?>
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Empresa" class="w-100 h-100" style="object-fit: contain;">
                <?php else: ?>
                    <i class="fas fa-shield-alt" style="color: #3b82f6; font-size: 2.2rem;"></i>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <h2 class="fw-bold mb-2 text-dark" style="font-size: 1.5rem;"><?= htmlspecialchars($oferta['titulo_oferta'] ?? '') ?></h2>
                        <div class="d-flex align-items-center flex-wrap gap-3 text-muted small fw-500">
                            <span style="color: #10b981;"><i class="fas fa-building me-1"></i> <?= htmlspecialchars($oferta['nombre_empresa'] ?? '') ?></span>
                            <span><i class="fas fa-map-marker-alt me-1 text-secondary"></i> <?= htmlspecialchars($oferta['ubicacion'] ?? 'No especificada') ?></span>
                            <span class="badge border border-primary text-primary px-3 py-1 rounded-2 fw-600 bg-transparent">
                                <i class="fas fa-desktop me-1"></i> <?= htmlspecialchars($oferta['modalidad'] ?? 'No especificada') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4 p-md-5">
            <!-- DETALLES OFERTA -->
            <div class="d-flex align-items-center gap-2 mb-4">
                <div class="bg-info bg-opacity-10 text-info rounded d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;"><i class="fas fa-info-circle small" style="color: #3b82f6;"></i></div>
                <h6 class="fw-bold text-dark m-0" style="font-size: 1.1rem;">Detalles de la Oferta</h6>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="bg-light rounded-4 p-4 h-100 border border-light">
                        <div class="fw-600 text-secondary mb-3 small text-uppercase tracking-wider">DESCRIPCIÓN</div>
                        <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6;"><?= nl2br(htmlspecialchars($oferta['descripcion_oferta'] ?? '')) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light rounded-4 p-4 h-100 border border-light">
                        <div class="fw-600 text-secondary mb-3 small text-uppercase tracking-wider">REQUISITOS</div>
                        <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6;"><?= nl2br(htmlspecialchars($oferta['requisitos'] ?? '')) ?></p>
                    </div>
                </div>
            </div>

            <hr class="text-secondary opacity-10 my-5">

    <!-- LISTA POSTULANTES -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px; font-size: 2.2rem; background-color: #dbeafe; color: #3b82f6;">
                <i class="fas fa-user-friends"></i>
            </div>
            <h3 class="fw-700 m-0" style="font-size: 1.5rem;">Postulantes Activos <span class="badge rounded-pill fs-6 ms-2 text-white" style="background-color: #64748b;"><?= count($postulantes) ?></span></h3>
        </div>
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
                    <div class="card border border-light shadow-sm mb-4 rounded-4 bg-white">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                                <div class="d-flex align-items-center gap-4">
                                    <?php
                                    $img = 'https://static.thenounproject.com/png/4154905-200.png';
                                    if (!empty($u['foto_perfil']) && file_exists(ROOT_PATH . $u['foto_perfil'])) {
                                        $img = BASE_URL . $u['foto_perfil'];
                                    }
                                    ?>
                                    <div class="rounded-circle overflow-hidden shadow-sm position-relative" style="width: 80px; height: 80px;">
                                        <img src="<?= htmlspecialchars($img) ?>" alt="Foto" class="w-100 h-100" style="object-fit: cover;">
                                        <div class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width: 20px; height: 20px; right: 5px; bottom: 5px;">
                                            <i class="fas fa-check text-white d-flex align-items-center justify-content-center h-100" style="font-size: 0.6rem;"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark" style="font-size: 1.25rem;"><?= htmlspecialchars($u['nombre'] ?? '') ?></h5>
                                        <div class="badge bg-light text-secondary px-3 py-1 fw-500 rounded-pill">
                                            <?= htmlspecialchars($u['cargo'] ?? 'Sin cargo') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2 align-items-md-end">
                                    <button type="button" class="btn btn-hover-light-green rounded-2 px-4 fw-600" style="border-width: 1px;" data-bs-toggle="modal" data-bs-target="#modalPerfilInfo<?= $u['id'] ?>">
                                        Ver perfil completo
                                    </button>
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 d-flex align-items-center gap-3">
                                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-envelope"></i></div>
                                        <div>
                                            <div class="small text-muted mb-1">Email</div>
                                            <div class="fw-500 text-dark"><?= htmlspecialchars($u['email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 d-flex align-items-center gap-3">
                                        <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-phone"></i></div>
                                        <div>
                                            <div class="small text-muted mb-1">Teléfono</div>
                                            <!-- Asumiendo dnI guarda el telefono o algo, el wireframe dice telefono. -->
                                            <div class="fw-500 text-dark"><?= !empty($u['telefono']) ? htmlspecialchars($u['telefono']) : 'Sin teléfono' ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3 flex-wrap mb-5 pb-3">
                                <a href="index.php?action=mis_chats&id_oferta=<?= $oferta['id_oferta'] ?>" class="btn btn-primary rounded-2 px-4 flex-grow-1 py-2 fw-600 pt-2 pb-2" style="background-color: #2563eb; border: 1px solid #2563eb;">
                                    <i class="far fa-comments me-2"></i> Chat de la Oferta
                                </a>
                                <?php if ($esCreador): ?>
                                    <a href="index.php?action=mis_chats&sub_action=create_private_chat&candidate_id=<?= $u['id'] ?>" class="btn btn-outline-secondary rounded-2 px-4 flex-grow-1 py-2 fw-600 pt-2 pb-2 text-dark" style="border-width: 1px;">
                                        <i class="far fa-comment-dots me-2"></i> Chat Privado con Postulante
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="row g-4 pt-4 border-top">
                                <?php if (!empty($u['experiencias'])): ?>
                                <div class="col-md-6">
                                    <div class="fw-600 text-dark mb-3 small"><i class="fas fa-briefcase text-secondary me-2"></i> Experiencia Laboral</div>
                                    <div class="bg-light rounded-4 p-3 border border-light">
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                                            <?php 
                                            $exps = explode('||', $u['experiencias']); 
                                            foreach ($exps as $exp): ?>
                                                <li class="text-dark small d-flex align-items-start">
                                                    <span class="text-primary me-2 mt-1" style="font-size: 0.5rem;"><i class="fas fa-circle"></i></span>
                                                    <span><?= htmlspecialchars($exp) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($u['estudios'])): ?>
                                <div class="col-md-6">
                                    <div class="fw-600 text-dark mb-3 small"><i class="fas fa-graduation-cap text-secondary me-2"></i> Formación Académica</div>
                                    <div class="bg-light rounded-4 p-3 border border-light">
                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                                            <?php 
                                            $ests = explode('||', $u['estudios']); 
                                            foreach ($ests as $est): ?>
                                                <li class="text-dark small d-flex align-items-start">
                                                    <span class="text-success me-2 mt-1" style="font-size: 0.5rem;"><i class="fas fa-circle"></i></span>
                                                    <span><?= htmlspecialchars($est) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODALES AFUERA DE LA TARJETA (Evita bugs de z-index y fixed positioning) -->
                
                <!-- MODAL PERFIL COMPLETO -->
                <div class="modal fade" id="modalPerfilInfo<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-id-badge text-primary me-2"></i>Perfil del Postulante</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body px-4 py-4">
                                <div class="d-flex align-items-center flex-wrap gap-4 mb-4 pb-4 border-bottom border-light">
                                    <div class="rounded-circle overflow-hidden shadow-sm flex-shrink-0" style="width: 80px; height: 80px;">
                                        <img src="<?= htmlspecialchars($img) ?>" alt="Foto" class="w-100 h-100" style="object-fit: cover;">
                                    </div>
                                    <div>
                                        <h4 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($u['nombre'] ?? '') ?></h4>
                                        <div class="text-secondary fw-500 mb-2"><?= htmlspecialchars($u['cargo'] ?? 'Sin cargo') ?></div>
                                        <div class="small text-muted d-flex gap-3 flex-wrap">
                                            <span><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($u['email'] ?? '') ?></span>
                                            <span><i class="fas fa-phone me-1"></i><?= !empty($u['telefono']) ? htmlspecialchars($u['telefono']) : (!empty($u['dni']) ? htmlspecialchars($u['dni']) : 'Sin teléfono') ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($u['habilidades'])): ?>
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-star text-warning me-2"></i>Habilidades Registradas</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php 
                                        $habs = explode(',', $u['habilidades']);
                                        foreach($habs as $h): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 px-3 py-1"><?= htmlspecialchars(trim($h)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <div class="mb-4">
                                        <div class="text-muted fst-italic small">No hay habilidades registradas públicamente.</div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($u['ruta_hdv']) && file_exists(ROOT_PATH . $u['ruta_hdv'])): ?>
                                <div class="mb-4 bg-light rounded-4 p-3 border border-light d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-danger-subtle text-danger rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; font-size: 1.25rem;"><i class="fas fa-file-pdf"></i></div>
                                        <div>
                                            <h6 class="fw-bold m-0 text-dark">Currículum Vitae adjunto</h6>
                                            <div class="small text-muted">Archivo PDF disponible</div>
                                        </div>
                                    </div>
                                    <a href="<?= BASE_URL . $u['ruta_hdv'] ?>" target="_blank" class="btn btn-outline-danger rounded-2 px-4 shadow-sm fw-600">
                                        Abrir CV
                                    </a>
                                </div>
                                <?php else: ?>
                                <div class="mb-4 bg-light rounded-4 p-3 border border-light d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-secondary-subtle text-secondary rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; font-size: 1.25rem;"><i class="fas fa-file-excel"></i></div>
                                        <div>
                                            <h6 class="fw-bold m-0 text-muted">Sin Currículum en PDF</h6>
                                            <div class="small text-muted">Aún no ha subido su archivo</div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-end gap-3 flex-wrap">
                                <?php if ($esCreador && ($u['estado_postulacion'] ?? '') !== 'Contratado'): ?>
                                    <button type="button" class="btn btn-outline-danger rounded-2 shadow-sm fw-600 px-4" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalRechazar<?= $u['id'] ?>">
                                        <i class="fas fa-user-times me-2"></i>Rechazar
                                    </button>
                                    <button type="button" class="btn btn-success rounded-2 shadow-sm fw-600 px-4" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalContratar<?= $u['id'] ?>">
                                        <i class="fas fa-handshake me-2"></i>Contratar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
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
    </div> <!-- /row g-4 mb-5 (Postulantes) -->
    
        </div> <!-- /p-4 p-md-5 (Contenido interior grande) -->
    </div> <!-- /CONTENEDOR PRINCIPAL -->
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
