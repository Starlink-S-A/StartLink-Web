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
} elseif ((int)$rolEmpresa === 3) {
    $rolLabel = 'Empleado';
} else {
    $rolLabel = 'Candidato';
}

// Iniciales del usuario
$userInitials = strtoupper(substr($userName, 0, 2));
if (strpos($userName, ' ') !== false) {
    $parts = explode(' ', $userName);
    $userInitials = strtoupper(substr($parts[0], 0, 1) . substr($parts[1] ?? '', 0, 1));
}

if (!isset($profileImage) && !empty($_SESSION['foto_perfil'])) {
    $profileImage = rtrim(BASE_URL, '/') . '/' . ltrim((string)$_SESSION['foto_perfil'], '/');
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/sidebar_styles.css">

<aside class="sidebar-premium" id="sidebarMenu">
    <!-- Header: Toggler + Logo -->
    <div class="sidebar-header">
        <button class="sidebar-toggler" id="sidebarToggle" type="button" aria-label="Abrir o cerrar menú lateral">
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
        <div class="sidebar-user-avatar">
            <?php if (isset($profileImage) && $profileImage): ?>
                <img src="<?= $profileImage ?>" alt="Avatar">
            <?php else: ?>
                <?= $userInitials ?>
            <?php endif; ?>
        </div>
        <div class="sidebar-user-info">
            <span class="sidebar-user-name"><?= htmlspecialchars($userName) ?></span>
            <span class="sidebar-user-role"><?= $rolLabel ?></span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav-container">
        <ul class="sidebar-nav">
            <li>
                <a class="sidebar-link <?= ($currentAction == 'dashboard' || $currentPage == 'dashboard_view.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>dashboard">
                    <i class="fas fa-home"></i> <span class="sidebar-link-text">Home</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= ($currentAction == 'ofertas' || $currentPage == 'ofertas_view.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>ofertas">
                    <i class="fas fa-briefcase"></i> <span class="sidebar-link-text">Ofertas</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= ($currentAction == 'perfiles_candidatos' || $currentPage == 'perfilesCandidatosView.php') ? 'active' : '' ?>" href="<?= BASE_URL ?>perfiles_candidatos">
                    <i class="fas fa-user-tie"></i> <span class="sidebar-link-text">Candidatos</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= $currentAction == 'capacitaciones' ? 'active' : '' ?>" href="<?= BASE_URL ?>src/index.php?action=capacitaciones">
                    <i class="fas fa-chalkboard-teacher"></i> <span class="sidebar-link-text">Capacitaciones</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= $currentAction == 'mis_empresas' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php?action=mis_empresas">
                    <i class="fas fa-building"></i> <span class="sidebar-link-text">Mis Empresas</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= $currentAction == 'crearEmpresa' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php?action=crearEmpresa">
                    <i class="fas fa-plus-circle"></i> <span class="sidebar-link-text">Nueva Empresa</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= ($currentAction == 'mis_equipos' || $currentAction == 'mi_equipo') ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php?action=mis_equipos">
                    <i class="fas fa-users"></i> <span class="sidebar-link-text">Mi Equipo</span>
                </a>
            </li>
            <li>
                <a class="sidebar-link <?= $currentAction == 'nominas' ? 'active' : '' ?>" href="<?= BASE_URL ?>src/index.php?action=nominas">
                    <i class="fas fa-history"></i> <span class="sidebar-link-text">Historial</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <p>&copy; 2026 STARTLINK CLOUD</p>
    </div>
</aside>

<script src="<?= BASE_URL ?>src/public/js/sideBar.js"></script>
