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

                                <button type="button" class="btn btn-sm btn-outline-secondary px-2" data-bs-toggle="modal" data-bs-target="#modalNomina<?= $idUsuario ?>" title="Enviar nómina" aria-label="Enviar nómina" data-tooltip="true">
                                    <i class="fas fa-file-invoice"></i>
                                </button>

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

                            <div class="modal fade" id="modalNomina<?= $idUsuario ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Enviar nómina</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info border-0 shadow-sm mb-0">
                                                Funcionalidad pendiente. Este modal es solo visual por ahora.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="button" class="btn-premium" disabled>
                                                <i class="fas fa-paper-plane"></i> Enviar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
