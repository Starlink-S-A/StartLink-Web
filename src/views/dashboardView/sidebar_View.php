<?php
// views/dashboardView/sidebar_view.php
$currentPage = basename($_SERVER['PHP_SELF']);

$userName = $userName ?? ($_SESSION['user_name'] ?? 'Usuario');

if (!isset($esAdminEmpresa)) {
    $rolEmpresa = $_SESSION['id_rol_empresa'] ?? null;
    $esAdminEmpresa = in_array((int)$rolEmpresa, [1, 2], true);
}

if (!isset($showPublishProfileLink)) {
    $showPublishProfileLink = isset($_SESSION['user_id']) && (($_SESSION['loggedin'] ?? false) === true);
}

if (!isset($profileImage) && !empty($_SESSION['foto_perfil'])) {
    $profileImage = rtrim(BASE_URL, '/') . '/' . ltrim((string)$_SESSION['foto_perfil'], '/');
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/sidebar_styles.css">

<aside class="sidebar-premium" id="sidebarMenu">
    <div class="sidebar-header">
        <button class="sidebar-toggler" id="sidebarToggle" type="button" aria-label="Abrir o cerrar el menú lateral">
            <span class="sidebar-toggler-bar"></span>
            <span class="sidebar-toggler-bar"></span>
            <span class="sidebar-toggler-bar"></span>
        </button>
        <a class="sidebar-brand" href="<?= BASE_URL ?>dashboard">
            <i class="fas fa-rocket"></i> <span class="sidebar-brand-text">StartLink</span>
        </a>
    </div>

    <!-- User Section -->
    <div class="sidebar-user">
        <?php 
        // Lógica de bienvenida solo una vez por sesión
        if (!isset($_SESSION['welcome_msg_shown'])): 
            $_SESSION['welcome_msg_shown'] = true;
        ?>
            <span class="sidebar-greeting">¡Qué bueno verte!</span>
        <?php endif; ?>
        
        <div class="position-relative d-inline-block">
            <img src="<?= isset($profileImage) ? $profileImage : 'https://static.thenounproject.com/png/4154905-200.png' ?>" 
                 alt="Profile" class="user-avatar-large">
            <?php if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0): ?>
                <div class="notif-dot"></div>
            <?php endif; ?>
        </div>
        <span class="user-name"><?= htmlspecialchars($userName ?? 'Candidato') ?></span>
        <span class="user-role"><?= isset($esAdminEmpresa) && $esAdminEmpresa ? 'PRO Admin' : 'Candidato' ?></span>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav-container">
        <ul class="sidebar-nav">
            <li>
                <a class="sidebar-link <?= $currentPage == 'dashboard_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard">
                    <i class="fas fa-home"></i> <span class="sidebar-link-text">Home</span>
                </a>
            </li>
            <li class="position-relative">
                <a class="sidebar-link <?= $currentPage == 'ofertas_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>ofertas">
                    <i class="fas fa-briefcase"></i> <span class="sidebar-link-text">Ofertas</span>
                </a>
                <?php if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0): ?>
                     <span class="badge bg-danger rounded-pill position-absolute" style="top: 15px; right: 15px; font-size: 0.6rem;"><?= $unreadNotificationsCount ?></span>
                <?php endif; ?>
            </li>
            <?php if (isset($showPublishProfileLink) && $showPublishProfileLink): ?>
            <li>
                <a class="sidebar-link" href="<?= BASE_URL ?>perfiles_candidatos">
                    <i class="fas fa-paper-plane"></i> <span class="sidebar-link-text">Publicar Mi Perfil</span>
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a class="sidebar-link" href="<?= BASE_URL ?>index.php?action=crearEmpresa">
                    <i class="fas fa-plus-circle"></i> <span class="sidebar-link-text">Nueva Empresa</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link" href="<?= BASE_URL ?>index.php?action=mis_equipos">
                    <i class="fas fa-users"></i> <span class="sidebar-link-text">Mi Equipo</span>
                </a>
            </li>
            <?php if (isset($esAdminEmpresa) && $esAdminEmpresa): ?>
            <li>
                <a class="sidebar-link <?= $currentPage == 'mis_empresas_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php?action=mis_empresas">
                    <i class="fas fa-building"></i> <span class="sidebar-link-text">Gestionar Empresas</span>
                </a>
            </li>
            <?php endif; ?>
            
            <div class="sidebar-divider my-4 mx-3" style="border-top: 1px solid #f1f5f9;"></div>
            
            <li>
                <a class="sidebar-link <?= $currentPage == 'configurar_perfil_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil">
                    <i class="fas fa-user-cog"></i> <span class="sidebar-link-text">Mi Configuración</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link text-danger" href="<?= BASE_URL ?>logout" style="margin-top: 2rem;">
                    <i class="fas fa-power-off"></i> <span class="sidebar-link-text">Salir</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- App Version or Branding -->
    <div class="mt-auto px-3">
        <p class="text-center mt-3 text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">&copy; 2026 STARTLINK CLOUD</p>
    </div>
</aside>

<script src="<?= BASE_URL ?>src/public/js/sideBar.js"></script>
