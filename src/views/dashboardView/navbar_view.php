<?php
// views/dashboardView/navbar_view.php

// Lógica ya manejada en DashboardController o UserController; variables pasadas globalmente
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>dashboard">StartLink</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage == 'dashboard_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard">Dashboard</a>
                </li>
                <li>
                    <a class="nav-link <?= $currentPage == 'ofertas_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>ofertas">Ofertas</a>
                </li>
                <?php if (isset($showPublishProfileLink) && $showPublishProfileLink): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>perfiles_candidatos.php">Publicar Mi Perfil</a>
                </li>
                <li>
                    <a href="nav_link"></a>
                </li>
                <?php endif; ?>
                <?php if (isset($esAdminEmpresa) && $esAdminEmpresa): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>mi_empresa.php">Mi Empresa</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>index.php?action=crearEmpresa">Crear Empresa</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <!-- Dropdown de Notificaciones (estático, sin AJAX) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount">
                                <?= $unreadNotificationsCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="max-height: 400px; overflow-y: auto;">
                        <?php if (empty($latestNotifications)): ?>
                            <li><span class="dropdown-item text-center">No hay notificaciones</span></li>
                        <?php else: ?>
                            <?php foreach ($latestNotifications as $notification): ?>
                                <li class="dropdown-item notification-item <?= !$notification['leida'] ? 'unread' : '' ?>" data-notification-id="<?= $notification['id'] ?>">
                                    <div class="d-flex align-items-center">
                                        <i class="<?= htmlspecialchars($notification['icono']) ?> me-2"></i>
                                        <div class="flex-grow-1">
                                            <small class="text-muted"><?= date('d M H:i', strtotime($notification['fecha_creacion'])) ?></small>
                                            <p class="mb-0"><?= htmlspecialchars($notification['mensaje']) ?></p>
                                            <?php if (!empty($notification['url_redireccion'])): ?>
                                                <a href="<?= htmlspecialchars($notification['url_redireccion']) ?>" class="small text-primary">Ver más</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="<?= BASE_URL ?>notificaciones.php">Ver todas</a></li>
                    </ul>
                </li>
                <!-- Dropdown de Perfil Usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= isset($profileImage) ? $profileImage : 'https://static.thenounproject.com/png/4154905-200.png' ?>" alt="Profile" class="rounded-circle" width="30" height="30">
                        <?= htmlspecialchars(isset($userName) ? $userName : 'Usuario') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userProfileDropdown">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>configurar_perfil">
                                <i class="fas fa-user me-2"></i>Configurar Perfil
                            </a>
                        </li>
                        <?php if (isset($esAdminEmpresa) && $esAdminEmpresa): ?>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>mi_empresa.php">
                                <i class="fas fa-building me-2"></i>Mi Empresa
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Script JS para inicializar dropdowns con manejo de errores -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Verificar si Bootstrap está cargado
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap no está cargado. Verifica la inclusión del script de Bootstrap.');
            // Fallback: Toggle manual para dropdowns
            const dropdownToggles = document.querySelectorAll('[data-bs-toggle="dropdown"]');
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const dropdownMenu = toggle.nextElementSibling;
                    if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                        dropdownMenu.classList.toggle('show');
                    }
                });
            });
            return;
        }

        // Inicialización de Dropdowns de Bootstrap
        const dropdownToggleElements = document.querySelectorAll('[data-bs-toggle="dropdown"]');
        dropdownToggleElements.forEach(function(dropdownToggle) {
            new bootstrap.Dropdown(dropdownToggle);
        });

        // Logs para depuración
        const notificationsDropdownElement = document.getElementById('notificationsDropdown');
        const userProfileDropdownElement = document.getElementById('userProfileDropdown');
        if (notificationsDropdownElement) {
            notificationsDropdownElement.addEventListener('click', function() {
                console.log('Notifications dropdown clicked!');
            });
        }
        if (userProfileDropdownElement) {
            userProfileDropdownElement.addEventListener('click', function() {
                console.log('User profile dropdown clicked!');
            });
        }
    } catch (error) {
        console.error('Error al inicializar dropdowns:', error);
    }
});

// Cerrar dropdowns al hacer clic fuera
document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('.dropdown-menu.show');
    dropdowns.forEach(dropdown => {
        if (!dropdown.parentElement.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
});
</script>