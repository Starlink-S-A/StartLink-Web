<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Notificaciones - TalentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/notificacioens.css">
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body>
<?php include __DIR__ . '/../dashboardView/sidebar_View.php'; ?>

<div class="main-content">
<div class="notifications-container">
    <div class="notifications-card">
        <div class="notifications-header">
            <h4><i class="fas fa-bell me-2 text-primary"></i>Tus Notificaciones</h4>
            <?php if (!empty($notifications)): ?>
                <button id="mark-all-read-btn" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-check-double me-1"></i> Marcar todo como leído
                </button>
            <?php endif; ?>
        </div>

        <div class="notifications-list">
            <?php if (empty($notifications) && $totalNotifications == 0): ?>
                <div class="empty-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>No tienes notificaciones pendientes.</p>
                </div>
            <?php elseif (empty($notifications) && $totalNotifications > 0): ?>
                <div class="empty-notifications">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>No hay más notificaciones en esta página.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <a href="<?= !empty($n['url_redireccion']) ? htmlspecialchars($n['url_redireccion']) : '#' ?>" 
                       class="notification-item <?= !$n['leida'] ? 'unread' : '' ?>" 
                       data-id="<?= $n['id'] ?>">
                        <div class="notification-icon-wrapper <?= htmlspecialchars($n['tipo']) ?>">
                            <i class="<?= htmlspecialchars($n['icono']) ?>"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-message"><?= htmlspecialchars($n['mensaje']) ?></div>
                            <div class="notification-time">
                                <i class="far fa-clock"></i>
                                <?= (new DateTime($n['fecha_creacion']))->format('d/m/Y H:i') ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $currentPage - 1 ?>">&laquo;</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $currentPage + 1 ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow-lg">
            <div class="modal-header bg-light p-3 border-bottom-0">
                <h5 class="modal-title fs-5 text-dark">Confirmar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 text-secondary" id="confirmModalBody"></div>
            <div class="modal-footer bg-light p-3 border-top-0 d-flex justify-content-end">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="confirmActionButton">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="alertDialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow-lg">
            <div class="modal-header bg-light p-3 border-bottom-0">
                <h5 class="modal-title fs-5 text-dark">Mensaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 text-secondary" id="alertDialogBody"></div>
            <div class="modal-footer bg-light p-3 border-top-0 d-flex justify-content-end">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>src/public/js/notificaiones.js"></script>
</body>
</html>
