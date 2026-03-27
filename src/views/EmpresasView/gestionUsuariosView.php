<?php
$canManageUsers = $canManageUsers ?? false;
$canChangeRoles = $canChangeRoles ?? false;
$usuarios = $usuarios ?? [];
$rolesEmpresa = $rolesEmpresa ?? [];

$rolesById = [];
foreach ($rolesEmpresa as $r) {
    $rolesById[(int)$r['id_rol_empresa']] = $r['nombre_rol_empresa'];
}
?>

<?php if (!$canManageUsers): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-0">
        Acceso no autorizado.
    </div>
<?php else: ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="d-flex flex-column gap-2">
            <div class="text-muted small">
                <?= count($usuarios) ?> usuario(s) en esta empresa.
            </div>
            <div style="max-width: 420px;">
                <input type="text" id="usuariosFilterInput" class="form-control form-control-sm" placeholder="Filtrar por nombre o DNI">
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Unión</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="4" class="text-muted">No hay usuarios asociados a esta empresa.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($usuarios as $u): ?>
                    <?php
                        $idUsuario = (int)$u['id_usuario'];
                        $rolEmpresa = (int)$u['id_rol_empresa'];
                        $isOwner = $rolEmpresa === 1;
                        $fechaUnion = !empty($u['fecha_union']) ? (new DateTime($u['fecha_union']))->format('d/m/Y') : '';
                        $nombre = (string)($u['nombre'] ?? '');
                        $dni = (string)($u['dni'] ?? '');
                        $searchKey = mb_strtolower(trim($nombre . ' ' . $dni));

                        $defaultAvatar = 'https://static.thenounproject.com/png/4154905-200.png';
                        $avatarUrl = $defaultAvatar;
                        if (!empty($u['foto_perfil'])) {
                            $fileName = basename((string)$u['foto_perfil']);
                            $absPath = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $fileName;
                            $pubPath = rtrim(BASE_URL, '/') . '/assets/images/Uploads/profile_pictures/' . $fileName;
                            if (file_exists($absPath)) {
                                $avatarUrl = $pubPath;
                            }
                        }
                    ?>
                    <tr data-search="<?= htmlspecialchars($searchKey) ?>">
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle border bg-light" style="width: 44px; height: 44px; overflow: hidden; flex: 0 0 44px;">
                                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Foto de <?= htmlspecialchars($nombre) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($nombre) ?></div>
                                    <div class="text-muted small">
                                        <?= htmlspecialchars($u['email'] ?? '') ?>
                                        <?php if ($dni !== ''): ?>
                                            · DNI: <?= htmlspecialchars($dni) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?= htmlspecialchars($u['nombre_rol_empresa'] ?? ($rolesById[$rolEmpresa] ?? '')) ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($fechaUnion) ?></td>
                        <td class="text-end">
                            <div class="d-inline-flex flex-wrap gap-2 justify-content-end">
                                <?php if ($canChangeRoles && !$isOwner): ?>
                                    <form method="POST" action="<?= BASE_URL ?>mi_empresa?seccion=usuarios" class="d-inline-flex gap-2">
                                        <input type="hidden" name="form_type" value="cambiar_rol">
                                        <input type="hidden" name="usuario_id" value="<?= $idUsuario ?>">
                                        <select name="nuevo_rol_empresa" class="form-select form-select-sm" style="width: 170px;">
                                            <?php foreach ($rolesEmpresa as $r): ?>
                                                <?php
                                                    $rid = (int)$r['id_rol_empresa'];
                                                    if ($rid === 1) continue;
                                                ?>
                                                <option value="<?= $rid ?>" <?= $rid === $rolEmpresa ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($r['nombre_rol_empresa']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary px-2" title="Guardar rol" aria-label="Guardar rol" data-tooltip="true">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php
                                $currentUserId = (int)($_SESSION['user_id'] ?? 0);
                                if ($idUsuario !== $currentUserId): 
                                ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary px-2" data-bs-toggle="modal" data-bs-target="#modalNomina<?= $idUsuario ?>" title="Enviar nómina" aria-label="Enviar nómina" data-tooltip="true">
                                    <i class="fas fa-file-invoice"></i>
                                </button>
                                <?php endif; ?>

                                <button type="button" class="btn btn-sm btn-outline-success px-2" data-bs-toggle="modal" data-bs-target="#modalSeguimiento<?= $idUsuario ?>" title="Desempeño" aria-label="Desempeño" data-tooltip="true">
                                    <i class="fas fa-chart-line"></i>
                                </button>

                                <?php if (!$isOwner): ?>
                                    <form method="POST" action="<?= BASE_URL ?>mi_empresa?seccion=usuarios" class="d-inline">
                                        <input type="hidden" name="form_type" value="eliminar_usuario">
                                        <input type="hidden" name="eliminar_usuario_id" value="<?= $idUsuario ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Eliminar usuario" aria-label="Eliminar usuario" data-tooltip="true">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>

                            <div class="modal fade" id="modalSeguimiento<?= $idUsuario ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Seguimiento de desempeño</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="<?= BASE_URL ?>mi_empresa?seccion=usuarios" novalidate>
                                            <div class="modal-body">
                                                <input type="hidden" name="form_type" value="seguimiento_desempeno">
                                                <input type="hidden" name="id_usuario_evaluado" value="<?= $idUsuario ?>">

                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Fecha</label>
                                                        <input type="date" name="fecha_evaluacion" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Tipo</label>
                                                        <input type="text" name="tipo_evaluacion" class="form-control" maxlength="50" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Puntuación (0–5)</label>
                                                        <input type="number" name="puntuacion" class="form-control" min="0" max="5" step="0.1">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Comentarios</label>
                                                        <textarea name="comentarios" class="form-control" rows="3" maxlength="500"></textarea>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">Objetivos logrados</label>
                                                        <textarea name="objetivos_logrados" class="form-control" rows="3" maxlength="500"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn-premium">
                                                    <i class="fas fa-save"></i> Guardar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <?php if ($idUsuario !== $currentUserId): ?>
                            <div class="modal fade" id="modalNomina<?= $idUsuario ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-700">
                                                <i class="fas fa-file-invoice-dollar me-2 text-success"></i>Enviar Nómina — <?= htmlspecialchars($nombre) ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="<?= BASE_URL ?>src/index.php?action=generar_nomina">
                                            <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                                            <div class="modal-body py-3">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-uppercase text-secondary">Inicio Período <span class="text-danger">*</span></label>
                                                        <input type="date" name="fecha_inicio_periodo" class="form-control bg-light border-0 shadow-sm" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-uppercase text-secondary">Fin Período <span class="text-danger">*</span></label>
                                                        <input type="date" name="fecha_fin_periodo" class="form-control bg-light border-0 shadow-sm" required>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-uppercase text-secondary">Horas Trabajadas <span class="text-danger">*</span></label>
                                                        <div class="input-group shadow-sm rounded">
                                                            <input type="number" step="0.5" min="0" name="horas_trabajadas" class="form-control bg-light border-0 nomina-horas" placeholder="160" required>
                                                            <span class="input-group-text bg-light border-0 text-muted">h</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-uppercase text-secondary">Tarifa por Hora <span class="text-danger">*</span></label>
                                                        <div class="input-group shadow-sm rounded">
                                                            <span class="input-group-text bg-light border-0 text-muted">$</span>
                                                            <input type="number" step="0.01" min="0" name="tarifa_hora" class="form-control bg-light border-0 nomina-tarifa" placeholder="15.00" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase text-secondary">Horas Extras</label>
                                                    <div class="input-group shadow-sm rounded">
                                                        <input type="number" step="0.5" min="0" name="horas_extras" class="form-control bg-light border-0" value="0">
                                                        <span class="input-group-text bg-light border-0 text-muted">h</span>
                                                    </div>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-uppercase text-secondary">Bonificaciones</label>
                                                        <div class="input-group shadow-sm rounded">
                                                            <span class="input-group-text bg-light border-0 text-success"><i class="fas fa-plus-circle"></i></span>
                                                            <input type="number" step="0.01" min="0" name="bonificaciones" class="form-control bg-light border-0 text-success fw-bold nomina-bonif" value="0.00">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-uppercase text-secondary">Deducciones <span class="text-danger">*</span></label>
                                                        <div class="input-group shadow-sm rounded">
                                                            <span class="input-group-text bg-light border-0 text-danger"><i class="fas fa-minus-circle"></i></span>
                                                            <input type="number" step="0.01" min="0" name="deducciones" class="form-control bg-light border-0 text-danger fw-bold nomina-deduc" value="0.00" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success px-4 rounded-pill shadow-sm" style="background:#10b981; border:none;">
                                                    <i class="fas fa-paper-plane me-2"></i>Enviar Nómina
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('usuariosFilterInput');
        const rows = Array.from(document.querySelectorAll('tbody tr[data-search]'));
        if (input) {
            input.addEventListener('input', function () {
                const q = (input.value || '').trim().toLowerCase();
                rows.forEach(function (tr) {
                    const key = (tr.getAttribute('data-search') || '').toLowerCase();
                    tr.style.display = q === '' || key.includes(q) ? '' : 'none';
                });
            });
        }

        if (window.bootstrap && bootstrap.Tooltip) {
            document.querySelectorAll('[data-tooltip=\"true\"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        });
    });
    </script>
<?php endif; ?>
