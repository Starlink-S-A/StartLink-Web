<?php
// views/dashboardView/sidebar_view.php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentAction = $_GET['action'] ?? '';

// --- Variables de rol y configuración principal ---
$userName = $userName ?? ($_SESSION['user_name'] ?? 'Usuario');
$rolGlobal  = $rolGlobal  ?? ($_SESSION['id_rol'] ?? null);
$rolEmpresa = $rolEmpresa ?? ($_SESSION['id_rol_empresa'] ?? null);

// Etiqueta de rol visible
$esAdminGlobal = ((int)$rolGlobal === 1);
if ($esAdminGlobal) {
    $rolLabel = 'Administrador';
} elseif ((int)$rolEmpresa === 1) {
    $rolLabel = 'Admin Empresa';
} elseif ((int)$rolEmpresa === 2) {
    $rolLabel = 'Contratador';
} else {
    $rolLabel = 'Candidato';
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
        <span class="user-name"><?= htmlspecialchars($userName) ?></span>
        <span class="user-role"><?= $rolLabel ?></span>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav-container">
        <ul class="sidebar-nav">
<<<<<<< HEAD
=======
            <!-- ═══ TODOS LOS MÓDULOS VISIBLES PARA TODOS LOS ROLES ═══ -->
>>>>>>> 7af304b (Refactorización de módulo nóminas a historial y ajustes de UI)
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
            <li>
                <a class="sidebar-link <?= $currentAction == 'capacitaciones' ? 'active' : '' ?>" href="<?= BASE_URL ?>src/index.php?action=capacitaciones">
                    <i class="fas fa-chalkboard-teacher"></i> <span class="sidebar-link-text">Capacitaciones</span>
                </a>
            </li>
            <li>
<<<<<<< HEAD
                <a class="sidebar-link" href="<?= BASE_URL ?>index.php?action=crearEmpresa">
=======
                <a class="sidebar-link <?= $currentAction == 'crearEmpresa' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php?action=crearEmpresa">
>>>>>>> 7af304b (Refactorización de módulo nóminas a historial y ajustes de UI)
                    <i class="fas fa-plus-circle"></i> <span class="sidebar-link-text">Nueva Empresa</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= $currentAction == 'mis_equipos' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php?action=mis_equipos">
                    <i class="fas fa-users"></i> <span class="sidebar-link-text">Mi Equipo</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= $currentAction == 'nominas' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php?action=nominas">
                    <i class="fas fa-history"></i> <span class="sidebar-link-text">Historial</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= $currentPage == 'mis_empresas_view.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php?action=mis_empresas">
                    <i class="fas fa-building"></i> <span class="sidebar-link-text">Gestionar Empresas</span>
                </a>
            </li>
            
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
