<?php
// views/dashboardView/navbar_view.php (Now as Sidebar)
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/sidebar_styles.css">

<!-- Toggle Button for Mobile -->
<button class="sidebar-toggler d-lg-none" id="mobileSidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<aside class="sidebar-premium" id="sidebarMenu">
    <div class="sidebar-header">
        <a class="sidebar-brand" href="<?= BASE_URL ?>dashboard">
            <i class="fas fa-rocket"></i> StartLink
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
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <li class="position-relative">
                <a class="sidebar-link <?= $currentPage == 'ofertas_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>ofertas">
                    <i class="fas fa-briefcase"></i> Ofertas
                </a>
                <?php if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0): ?>
                     <span class="badge bg-danger rounded-pill position-absolute" style="top: 15px; right: 15px; font-size: 0.6rem;"><?= $unreadNotificationsCount ?></span>
                <?php endif; ?>
            </li>
            <?php if (isset($showPublishProfileLink) && $showPublishProfileLink): ?>
            <li>
                <a class="sidebar-link" href="<?= BASE_URL ?>perfiles_candidatos.php">
                    <i class="fas fa-paper-plane"></i> Publicar Mi Perfil
                </a>
            </li>
            <?php endif; ?>
            <li>
                <a class="sidebar-link" href="<?= BASE_URL ?>index.php?action=crearEmpresa">
                    <i class="fas fa-plus-circle"></i> Nueva Empresa
                </a>
            </li>
            <?php if (isset($esAdminEmpresa) && $esAdminEmpresa): ?>
            <li>
                <a class="sidebar-link <?= $currentPage == 'mis_empresas_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php?action=mis_empresas">
                    <i class="fas fa-building"></i> Gestionar Empresas
                </a>
            </li>
            <?php endif; ?>
            
            <div class="sidebar-divider my-4 mx-3" style="border-top: 1px solid #f1f5f9;"></div>
            
            <li>
                <a class="sidebar-link <?= $currentPage == 'configurar_perfil_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>configurar_perfil">
                    <i class="fas fa-user-cog"></i> Mi Configuración
                </a>
            </li>
            <li>
                <a class="sidebar-link text-danger" href="<?= BASE_URL ?>logout" style="margin-top: 2rem;">
                    <i class="fas fa-power-off"></i> Salir
                </a>
            </li>
        </ul>
    </nav>

    <!-- App Version or Branding -->
    <div class="mt-auto px-3">
        <p class="text-center mt-3 text-muted" style="font-size: 0.65rem; letter-spacing: 1px;">&copy; 2026 STARTLINK CLOUD</p>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('mobileSidebarToggle');
    const sidebar = document.getElementById('sidebarMenu');
    
    if (toggle && sidebar) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
            toggle.querySelector('i').classList.toggle('fa-bars');
            toggle.querySelector('i').classList.toggle('fa-times');
        });

        // Close when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('active');
                toggle.querySelector('i').classList.add('fa-bars');
                toggle.querySelector('i').classList.remove('fa-times');
            }
        });
    }
});
</script>
</script>
