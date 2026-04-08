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
                    <a href="<?= BASE_URL ?>src/index.php?action=notificaciones&sub_action=redirect&notification_id=<?= (int)$n['id'] ?>" 
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
                            <a class="page-link" href="<?= BASE_URL ?>src/index.php?action=notificaciones&page=<?= $currentPage - 1 ?>">&laquo;</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>src/index.php?action=notificaciones&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>src/index.php?action=notificaciones&page=<?= $currentPage + 1 ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
