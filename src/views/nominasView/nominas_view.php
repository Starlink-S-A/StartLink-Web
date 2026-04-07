<?php
// src/views/nominasView/nominas_view.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/configuracionInicial.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
    header("Location: " . BASE_URL . "bienvenida.php");
    exit();
}

// Variables del controlador:
// $nominas, $trabajadores, $puedeGenerar, $esAdminEmpresa, $esAdminGlobal
// $esTrabajador, $mensaje, $tipoMensaje, $rolGlobal, $rolEmpresa, $desempenos
// $pageNom, $pageDes, $totalPagesNom, $totalPagesDes, $totalNominas, $totalDesempenos

$baseHistorialUrl = BASE_URL . 'src/index.php?action=nominas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - StartLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .nav-tabs-historial { border: none; gap: 0.5rem; margin-bottom: 1.5rem; }
        .nav-tabs-historial .nav-link {
            border: none; border-radius: 12px; padding: 0.6rem 1.5rem;
            font-weight: 600; font-size: 0.88rem; color: #64748b;
            background: #f1f5f9; transition: all 0.2s ease;
        }
        .nav-tabs-historial .nav-link:hover { background: #e2e8f0; color: #334155; }
        .nav-tabs-historial .nav-link.active {
            background: linear-gradient(135deg, #10b981, #059669); color: #fff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .star-rating { color: #f59e0b; }
        .star-rating .empty { color: #e2e8f0; }
        /* Paginación premium */
        .pagination-premium .page-link {
            border: none; border-radius: 8px; margin: 0 2px; font-weight: 600;
            font-size: 0.82rem; padding: 6px 12px; color: #64748b; background: #f1f5f9;
            transition: all 0.15s ease;
        }
        .pagination-premium .page-link:hover { background: #e2e8f0; color: #334155; }
        .pagination-premium .page-item.active .page-link {
            background: linear-gradient(135deg, #10b981, #059669); color: #fff;
            box-shadow: 0 2px 8px rgba(16,185,129,0.3);
        }
        .pagination-premium .page-item.disabled .page-link { opacity: 0.4; }
        /* Modal detalle */
        .detail-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; margin-bottom: 2px; }
        .detail-value { font-size: 0.95rem; font-weight: 600; color: #1e293b; }
        .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; }
        tr.clickable-row { cursor: pointer; }
        tr.clickable-row:hover { background: #f0fdf4 !important; }
    </style>
</head>
<body>
<?php
$pageTitle = 'Historial';
include __DIR__ . '/../dashboardView/sidebar_View.php';
?>

<div class="main-content">
<?php include __DIR__ . '/../dashboardView/navbar_view.php'; ?>

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div></div>
        <?php if ($puedeGenerar && !empty($trabajadores)): ?>
        <button class="btn btn-dash-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalGenerarNomina">
            <i class="fas fa-plus me-2"></i>Generar Nómina
        </button>
        <?php endif; ?>
    </div>

    <!-- Alerta flash -->
    <?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?> alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
        <i class="fas fa-<?= $tipoMensaje === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php endif; ?>

    <!-- Pestañas -->
    <ul class="nav nav-tabs-historial" id="historialTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-nominas" data-bs-toggle="tab" data-bs-target="#panel-nominas" type="button" role="tab">
                <i class="fas fa-file-invoice-dollar me-2"></i>Nóminas
                <span class="badge bg-white bg-opacity-25 ms-1 rounded-pill"><?= $totalNominas ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-desempeno" data-bs-toggle="tab" data-bs-target="#panel-desempeno" type="button" role="tab">
                <i class="fas fa-chart-line me-2"></i>Desempeño
                <span class="badge bg-white bg-opacity-25 ms-1 rounded-pill"><?= $totalDesempenos ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="historialTabContent">

        <!-- ═══ TAB: NÓMINAS ═══ -->
        <div class="tab-pane fade show active" id="panel-nominas" role="tabpanel">
            <div class="dash-card p-0 overflow-hidden">
                <div class="dash-card-header px-4 py-3">
                    <h5 class="dash-card-title mb-0">
                        <i class="fas fa-list-alt text-success me-2"></i>
                        <?= $puedeGenerar ? 'Nóminas de Trabajadores' : 'Mis Nóminas' ?>
                    </h5>
                    <span class="badge bg-success-subtle text-success rounded-pill px-3">
                        <?= $totalNominas ?> registro<?= $totalNominas !== 1 ? 's' : '' ?>
                    </span>
                </div>

                <?php if (empty($nominas)): ?>
                <div class="text-center py-5 px-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm border border-success-subtle"
                         style="width:64px;height:64px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                        <i class="fas fa-file-invoice-dollar text-success fs-4"></i>
                    </div>
                    <h6 class="fw-600 text-muted mb-1">Sin nóminas registradas</h6>
                    <p class="text-muted small mb-0">
                        <?= $puedeGenerar ? 'Genera la primera nómina usando el botón de arriba.' : 'Aún no tienes nóminas generadas.' ?>
                    </p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <?php if ($puedeGenerar): ?><th class="px-4 py-3 fw-600 text-muted">Trabajador</th><?php endif; ?>
                                <th class="px-4 py-3 fw-600 text-muted">Empresa</th>
                                <th class="px-4 py-3 fw-600 text-muted">Período</th>
                                <th class="px-4 py-3 fw-600 text-muted">Horas</th>
                                <th class="px-4 py-3 fw-600 text-muted">Salario Bruto</th>
                                <th class="px-4 py-3 fw-600 text-muted">Deducciones</th>
                                <th class="px-4 py-3 fw-600 text-muted">Bonificaciones</th>
                                <th class="px-4 py-3 fw-600 text-muted text-success fw-700">Salario Neto</th>
                                <th class="px-4 py-3 fw-600 text-muted">Generado</th>
                                <th class="px-4 py-3 fw-600 text-muted text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nominas as $idx => $nomina): ?>
                            <tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#modalDetalle<?= $nomina['id'] ?>">
                                <?php if ($puedeGenerar): ?>
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                                             style="width:34px;height:34px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-size:0.75rem;font-weight:700;">
                                            <?= strtoupper(substr($nomina['nombre_trabajador'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-600"><?= htmlspecialchars($nomina['nombre_trabajador']) ?></div>
                                            <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($nomina['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <?php endif; ?>
                                <td class="px-4">
                                    <?= !empty($nomina['nombre_empresa']) ? htmlspecialchars($nomina['nombre_empresa']) : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="px-4">
                                    <span class="badge bg-light text-dark fw-500 border">
                                        <?= date('d/m/Y', strtotime($nomina['fecha_inicio_periodo'])) ?> – <?= date('d/m/Y', strtotime($nomina['fecha_fin_periodo'])) ?>
                                    </span>
                                </td>
                                <td class="px-4 fw-600"><?= number_format($nomina['horas_trabajadas'], 1) ?> h</td>
                                <td class="px-4">$<?= number_format($nomina['salario_bruto'], 2) ?></td>
                                <td class="px-4 text-danger">-$<?= number_format($nomina['deducciones'], 2) ?></td>
                                <td class="px-4 text-success">+$<?= number_format($nomina['bonificaciones'], 2) ?></td>
                                <td class="px-4"><span class="fw-700 text-success">$<?= number_format($nomina['salario_neto'], 2) ?></span></td>
                                <td class="px-4 text-muted small"><?= date('d/m/Y', strtotime($nomina['fecha_generacion'])) ?></td>
                                <td class="px-4 text-center" onclick="event.stopPropagation();">
                                    <a href="<?= BASE_URL ?>src/index.php?action=descargar_nomina&id=<?= $nomina['id'] ?>"
                                       class="btn btn-sm btn-outline-success rounded-pill px-3" target="_blank" title="Descargar PDF">
                                        <i class="fas fa-file-pdf me-1"></i>PDF
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal Detalle Nómina -->
                            <div class="modal fade" id="modalDetalle<?= $nomina['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-700">
                                                <i class="fas fa-receipt me-2 text-success"></i>Detalle de Nómina #<?= str_pad($nomina['id'], 6, '0', STR_PAD_LEFT) ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body pt-3">
                                            <!-- Info del trabajador -->
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-6">
                                                    <div class="detail-box">
                                                        <div class="detail-label">Trabajador</div>
                                                        <div class="detail-value"><?= htmlspecialchars($nomina['nombre_trabajador']) ?></div>
                                                        <div class="text-muted small"><?= htmlspecialchars($nomina['email']) ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="detail-box">
                                                        <div class="detail-label">Empresa</div>
                                                        <div class="detail-value" style="font-size:0.85rem;"><?= !empty($nomina['nombre_empresa']) ? htmlspecialchars($nomina['nombre_empresa']) : '—' ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="detail-box">
                                                        <div class="detail-label">Fecha Generación</div>
                                                        <div class="detail-value"><?= date('d/m/Y', strtotime($nomina['fecha_generacion'])) ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="detail-box">
                                                        <div class="detail-label">Período</div>
                                                        <div class="detail-value" style="font-size:0.82rem;">
                                                            <?= date('d/m/Y', strtotime($nomina['fecha_inicio_periodo'])) ?><br>
                                                            <?= date('d/m/Y', strtotime($nomina['fecha_fin_periodo'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Desglose de pago -->
                                            <table class="table table-sm mb-3" style="font-size:0.88rem;">
                                                <thead><tr style="background:#f8fafc;"><th>Concepto</th><th class="text-end">Monto</th></tr></thead>
                                                <tbody>
                                                    <tr>
                                                        <td><i class="fas fa-clock text-primary me-2"></i>Horas trabajadas (<?= number_format($nomina['horas_trabajadas'], 1) ?> h)</td>
                                                        <td class="text-end fw-600">$<?= number_format($nomina['salario_bruto'] - $nomina['bonificaciones'], 2) ?></td>
                                                    </tr>
                                                    <?php if ($nomina['horas_extras'] > 0): ?>
                                                    <tr>
                                                        <td><i class="fas fa-plus-circle text-info me-2"></i>Horas extras (<?= number_format($nomina['horas_extras'], 1) ?> h)</td>
                                                        <td class="text-end fw-600 text-info">Incluido</td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php if ($nomina['bonificaciones'] > 0): ?>
                                                    <tr>
                                                        <td><i class="fas fa-gift text-success me-2"></i>Bonificaciones</td>
                                                        <td class="text-end fw-600 text-success">+$<?= number_format($nomina['bonificaciones'], 2) ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <tr>
                                                        <td><i class="fas fa-minus-circle text-danger me-2"></i>Deducciones</td>
                                                        <td class="text-end fw-600 text-danger">-$<?= number_format($nomina['deducciones'], 2) ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <!-- Total -->
                                            <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #86efac;">
                                                <span class="fw-700 text-success" style="font-size:1rem;"><i class="fas fa-wallet me-2"></i>Salario Neto</span>
                                                <span class="fw-700 text-success" style="font-size:1.4rem;">$<?= number_format($nomina['salario_neto'], 2) ?></span>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <a href="<?= BASE_URL ?>src/index.php?action=descargar_nomina&id=<?= $nomina['id'] ?>"
                                               class="btn btn-success rounded-pill px-4 shadow-sm" target="_blank" style="background:#10b981;border:none;">
                                                <i class="fas fa-file-pdf me-2"></i>Descargar / Imprimir
                                            </a>
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación Nóminas -->
                <?php if ($totalPagesNom > 1): ?>
                <div class="d-flex justify-content-between align-items-center px-4 py-3" style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                    <span class="text-muted small">Página <?= $pageNom ?> de <?= $totalPagesNom ?></span>
                    <nav>
                        <ul class="pagination pagination-sm pagination-premium mb-0">
                            <li class="page-item <?= $pageNom <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $baseHistorialUrl ?>&page_nom=<?= $pageNom - 1 ?>&page_des=<?= $pageDes ?>#panel-nominas"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <?php for ($p = 1; $p <= $totalPagesNom; $p++): ?>
                            <li class="page-item <?= $p === $pageNom ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $baseHistorialUrl ?>&page_nom=<?= $p ?>&page_des=<?= $pageDes ?>#panel-nominas"><?= $p ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $pageNom >= $totalPagesNom ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $baseHistorialUrl ?>&page_nom=<?= $pageNom + 1 ?>&page_des=<?= $pageDes ?>#panel-nominas"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ═══ TAB: DESEMPEÑO ═══ -->
        <div class="tab-pane fade" id="panel-desempeno" role="tabpanel">
            <div class="dash-card p-0 overflow-hidden">
                <div class="dash-card-header px-4 py-3">
                    <h5 class="dash-card-title mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        <?= $puedeGenerar ? 'Desempeño de Trabajadores' : 'Mi Desempeño' ?>
                    </h5>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">
                        <?= $totalDesempenos ?> evaluación<?= $totalDesempenos !== 1 ? 'es' : '' ?>
                    </span>
                </div>

                <?php if (empty($desempenos)): ?>
                <div class="text-center py-5 px-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm border border-primary-subtle"
                         style="width:64px;height:64px;background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                        <i class="fas fa-chart-line text-primary fs-4"></i>
                    </div>
                    <h6 class="fw-600 text-muted mb-1">Sin evaluaciones registradas</h6>
                    <p class="text-muted small mb-0">
                        <?= $puedeGenerar ? 'Registra evaluaciones desde Gestionar Empresas → Usuarios.' : 'Aún no tienes evaluaciones.' ?>
                    </p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <?php if ($puedeGenerar): ?><th class="px-4 py-3 fw-600 text-muted">Trabajador</th><?php endif; ?>
                                <th class="px-4 py-3 fw-600 text-muted">Empresa</th>
                                <th class="px-4 py-3 fw-600 text-muted">Fecha</th>
                                <th class="px-4 py-3 fw-600 text-muted">Tipo</th>
                                <th class="px-4 py-3 fw-600 text-muted">Puntuación</th>
                                <th class="px-4 py-3 fw-600 text-muted">Evaluador</th>
                                <th class="px-4 py-3 fw-600 text-muted">Comentarios</th>
                                <th class="px-4 py-3 fw-600 text-muted">Objetivos Logrados</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($desempenos as $d): ?>
                            <tr>
                                <?php if ($puedeGenerar): ?>
                                <td class="px-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm"
                                             style="width:34px;height:34px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:white;font-size:0.75rem;font-weight:700;">
                                            <?= strtoupper(substr($d['nombre_trabajador'] ?? '?', 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-600"><?= htmlspecialchars($d['nombre_trabajador'] ?? '') ?></div>
                                            <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($d['email'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <?php endif; ?>
                                <td class="px-4">
                                    <?= !empty($d['nombre_empresa']) ? htmlspecialchars($d['nombre_empresa']) : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="px-4"><span class="badge bg-light text-dark fw-500 border"><?= date('d/m/Y', strtotime($d['fecha_evaluacion'])) ?></span></td>
                                <td class="px-4"><span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?= htmlspecialchars($d['tipo_evaluacion']) ?></span></td>
                                <td class="px-4">
                                    <?php if ($d['puntuacion'] !== null): ?>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="fw-700" style="color:#f59e0b;"><?= number_format((float)$d['puntuacion'], 1) ?></span>
                                        <span class="star-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?= $i <= round((float)$d['puntuacion']) ? '' : 'empty' ?>" style="font-size:0.7rem;"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <?php else: ?><span class="text-muted small">N/A</span><?php endif; ?>
                                </td>
                                <td class="px-4 text-muted small"><?= htmlspecialchars($d['nombre_evaluador'] ?? 'Sistema') ?></td>
                                <td class="px-4 small" style="max-width:200px;">
                                    <?= !empty($d['comentarios']) ? htmlspecialchars(mb_strimwidth($d['comentarios'], 0, 80, '...')) : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="px-4 small" style="max-width:200px;">
                                    <?= !empty($d['objetivos_logrados']) ? htmlspecialchars(mb_strimwidth($d['objetivos_logrados'], 0, 80, '...')) : '<span class="text-muted">—</span>' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación Desempeño -->
                <?php if ($totalPagesDes > 1): ?>
                <div class="d-flex justify-content-between align-items-center px-4 py-3" style="background:#f8fafc;border-top:1px solid #e2e8f0;">
                    <span class="text-muted small">Página <?= $pageDes ?> de <?= $totalPagesDes ?></span>
                    <nav>
                        <ul class="pagination pagination-sm pagination-premium mb-0">
                            <li class="page-item <?= $pageDes <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $baseHistorialUrl ?>&page_nom=<?= $pageNom ?>&page_des=<?= $pageDes - 1 ?>#panel-desempeno"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <?php for ($p = 1; $p <= $totalPagesDes; $p++): ?>
                            <li class="page-item <?= $p === $pageDes ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $baseHistorialUrl ?>&page_nom=<?= $pageNom ?>&page_des=<?= $p ?>#panel-desempeno"><?= $p ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $pageDes >= $totalPagesDes ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $baseHistorialUrl ?>&page_nom=<?= $pageNom ?>&page_des=<?= $pageDes + 1 ?>#panel-desempeno"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /tab-content -->

</div><!-- /main-content -->

<!-- ─── Modal: Generar Nómina ─────────────────── -->
<?php if ($puedeGenerar && !empty($trabajadores)): ?>
<div class="modal fade" id="modalGenerarNomina" tabindex="-1" aria-labelledby="modalGenerarNominaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-700" id="modalGenerarNominaLabel">
                        <i class="fas fa-file-invoice-dollar me-2 text-success"></i>Generar Nómina
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-4">
                    <form action="<?= BASE_URL ?>src/index.php?action=generar_nomina" method="POST" id="formGenerarNomina">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Trabajador <span class="text-danger">*</span></label>
                        <select name="id_usuario" class="form-select form-select-lg bg-light border-0 shadow-sm fs-6" id="selectTrabajador" required>
                            <option value="">— Selecciona un trabajador —</option>
                            <?php foreach ($trabajadores as $t): ?>
                            <option value="<?= $t['id'] ?>" data-salario="<?= $t['salario_base'] ?? 0 ?>" data-horas="<?= $t['horas_semanales_estandar'] ?? 0 ?>">
                                <?= htmlspecialchars($t['nombre']) ?> <?= $t['cargo'] ? '— ' . htmlspecialchars($t['cargo']) : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-4 g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Inicio Período <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio_periodo" class="form-control bg-light border-0 shadow-sm py-2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Fin Período <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin_periodo" class="form-control bg-light border-0 shadow-sm py-2" required>
                        </div>
                    </div>
                    <div class="row mb-4 g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Horas Trabajadas <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm rounded">
                                <input type="number" step="0.5" min="0" name="horas_trabajadas" id="inputHoras" class="form-control bg-light border-0 py-2" placeholder="Ej: 160" required>
                                <span class="input-group-text bg-light border-0 text-muted">h</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Tarifa por Hora <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm rounded">
                                <span class="input-group-text bg-light border-0 text-muted">$</span>
                                <input type="number" step="0.01" min="0" name="tarifa_hora" id="inputTarifa" class="form-control bg-light border-0 py-2" placeholder="Ej: 15.00" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Horas Extras</label>
                        <div class="input-group shadow-sm rounded">
                            <input type="number" step="0.5" min="0" name="horas_extras" id="inputHorasExtras" class="form-control bg-light border-0 py-2" value="0">
                            <span class="input-group-text bg-light border-0 text-muted">h</span>
                        </div>
                    </div>
                    <div class="row mb-4 g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Bonificaciones</label>
                            <div class="input-group shadow-sm rounded">
                                <span class="input-group-text bg-light border-0 text-success"><i class="fas fa-plus-circle"></i></span>
                                <input type="number" step="0.01" min="0" name="bonificaciones" id="inputBonificaciones" class="form-control bg-light border-0 py-2 text-success fw-bold" value="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small text-uppercase" style="letter-spacing: 0.5px;">Deducciones <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm rounded">
                                <span class="input-group-text bg-light border-0 text-danger"><i class="fas fa-minus-circle"></i></span>
                                <input type="number" step="0.01" min="0" name="deducciones" id="inputDeducciones" class="form-control bg-light border-0 py-2 text-danger fw-bold" value="0.00" required>
                            </div>
                        </div>
                    </div>
                    <!-- Preview -->
                    <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid #e2e8f0;" id="previewCalculo">
                        <div class="fw-600 mb-2 text-muted small text-uppercase" style="letter-spacing:0.5px;">Resumen de Cálculo</div>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="small text-muted">Salario Bruto</div>
                                <div class="fw-700 text-dark" id="prevBruto">$0.00</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Deducciones</div>
                                <div class="fw-700 text-danger" id="prevDeduc">-$0.00</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Salario Neto</div>
                                <div class="fw-700 text-success" id="prevNeto">$0.00</div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formGenerarNomina" class="btn btn-success px-4 rounded-pill shadow-sm" style="background:#10b981; border:none;">
                        <i class="fas fa-save me-2"></i>Generar Nómina
                    </button>
                </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Cálculo automático en tiempo real
function recalcular() {
    const horas = parseFloat(document.getElementById('inputHoras')?.value) || 0;
    const tarifa = parseFloat(document.getElementById('inputTarifa')?.value) || 0;
    const bonif = parseFloat(document.getElementById('inputBonificaciones')?.value) || 0;
    const deduc = parseFloat(document.getElementById('inputDeducciones')?.value) || 0;
    const bruto = (horas * tarifa) + bonif;
    const neto = bruto - deduc;
    const fmt = v => '$' + v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const el = id => document.getElementById(id);
    if (el('prevBruto')) el('prevBruto').textContent = fmt(Math.max(bruto, 0));
    if (el('prevDeduc')) el('prevDeduc').textContent = '-' + fmt(deduc);
    if (el('prevNeto'))  el('prevNeto').textContent  = fmt(Math.max(neto, 0));
}
document.querySelectorAll('#inputHoras, #inputTarifa, #inputBonificaciones, #inputDeducciones').forEach(el => {
    el?.addEventListener('input', recalcular);
});

// Si la URL tiene #panel-desempeno, activar esa pestaña
if (window.location.hash === '#panel-desempeno') {
    document.addEventListener('DOMContentLoaded', function() {
        const tab = document.getElementById('tab-desempeno');
        if (tab) new bootstrap.Tab(tab).show();
    });
}
</script>
</body>
</html>
