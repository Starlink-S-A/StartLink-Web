<?php
// src/views/adminGlobalView/admin_view.php
// Asumimos que $metrics y $users vienen del controlador AdminGlobalController
$metrics = $metrics ?? ['total' => 0, 'activos' => 0, 'suspendidos' => 0, 'pendientes' => 0];
$users = $users ?? [];

function getInitials($name) {
    if (empty($name)) return 'U';
    $parts = explode(' ', trim($name));
    if (count($parts) > 1) return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
    return strtoupper(substr($parts[0], 0, 1));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración de Usuarios - StartLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/adminGlobal.css">
</head>
<body class="admin-dashboard">
    <?php include __DIR__ . '/../dashboardView/sidebar_View.php'; ?>

    <div class="main-content-wrapper px-md-4">
        <?php 
        $pageTitle = 'Administración de Usuarios';
        include __DIR__ . '/../dashboardView/navbar_view.php'; 
        ?>

        <!-- Tabs Navigation -->
        <ul class="nav nav-pills mt-4 mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="nav-usuarios-tab" data-bs-toggle="pill" data-bs-target="#nav-usuarios" type="button" role="tab" style="border-radius: 8px; font-weight: 600;"><i class="fas fa-users"></i> Usuarios</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nav-empresas-tab" data-bs-toggle="pill" data-bs-target="#nav-empresas" type="button" role="tab" style="border-radius: 8px; font-weight: 600; margin-left: 10px;"><i class="fas fa-building"></i> Empresas</button>
            </li>
        </ul>

        <div class="tab-content" id="nav-tabContent">
            <!-- TAB USUARIOS -->
            <div class="tab-pane fade show active" id="nav-usuarios" role="tabpanel">

        <!-- Top Metrics Cards -->
        <div class="metrics-row mb-4">
            <div class="metric-card metric-purple">
                <div class="metric-icon-box"><i class="fas fa-users"></i></div>
                <div class="metric-content">
                    <span class="metric-number"><?= htmlspecialchars($metrics['total']) ?></span>
                    <span class="metric-label">Total Usuarios</span>
                </div>
            </div>
            <div class="metric-card metric-green">
                <div class="metric-icon-box"><i class="fas fa-check-circle"></i></div>
                <div class="metric-content">
                    <span class="metric-number"><?= htmlspecialchars($metrics['activos']) ?></span>
                    <span class="metric-label">Activos</span>
                </div>
            </div>
            <div class="metric-card metric-red">
                <div class="metric-icon-box"><i class="fas fa-ban"></i></div>
                <div class="metric-content">
                    <span class="metric-number"><?= htmlspecialchars($metrics['suspendidos']) ?></span>
                    <span class="metric-label">Suspendidos</span>
                </div>
            </div>
            <div class="metric-card metric-yellow">
                <div class="metric-icon-box"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="metric-content">
                    <span class="metric-number"><?= htmlspecialchars($metrics['pendientes']) ?></span>
                    <span class="metric-label">Pendientes</span>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="search-input-group">
                <i class="fas fa-search"></i>
                <input type="text" id="searchUsers" placeholder="Buscar por nombre o correo...">
            </div>
            <div class="filter-controls d-flex gap-3">
                <i class="fas fa-filter text-muted d-flex align-items-center"></i>
                <select id="filterRole" class="filter-select">
                    <option value="all">Todos los roles</option>
                    <option value="1">Administrador</option>
                    <option value="2">Candidato</option>
                    <option value="3">Empresa</option>
                </select>
                <select id="filterStatus" class="filter-select">
                    <option value="all">Todos los estados</option>
                    <option value="Activo">Activos</option>
                    <option value="Suspendido">Suspendidos</option>
                    <option value="Pendiente">Pendientes</option>
                </select>
            </div>
        </div>

        <!-- Data Table -->
        <div class="users-table-container mb-5">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Contacto</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Empresa</th>
                        <th>Fecha Ingreso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): 
                        $isSelf = ($user['id'] == $_SESSION['user_id']);
                        $payload = ['user' => $user, 'isSelf' => $isSelf];
                        $jsonUser = htmlspecialchars(json_encode($payload), ENT_QUOTES, 'UTF-8');
                        $roleClass = 'role-candidato';
                        if ($user['id_rol'] == 1) $roleClass = 'role-admin';
                        elseif ($user['id_rol'] == 3) $roleClass = 'role-empresa';

                        $statusClass = 'status-pendiente';
                        $statusIcon = 'fa-exclamation-triangle';
                        if ($user['estado_logico'] === 'Activo') {
                            $statusClass = 'status-activo';
                            $statusIcon = 'fa-check-circle';
                        } elseif ($user['estado_logico'] === 'Suspendido') {
                            $statusClass = 'status-suspendido';
                            $statusIcon = 'fa-ban';
                        }

                        $initials = getInitials($user['nombre']);
                        $avatarClass = "avatar-bg-" . ($user['id'] % 4);
                    ?>
                    <tr data-rol="<?= $user['id_rol'] ?>" data-status="<?= $user['estado_logico'] ?>">
                        <td>
                            <div class="user-td-info">
                                <div class="user-avatar <?= $avatarClass ?>"><?= $initials ?></div>
                                <div>
                                    <div class="user-name"><?= htmlspecialchars($user['nombre']) ?></div>
                                    <div class="user-id text-muted"><?= $user['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></div>
                                <?php if(!empty($user['telefono'])): ?>
                                <div><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($user['telefono']) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge <?= $roleClass ?>"><?= htmlspecialchars($user['nombre_rol']) ?></span>
                        </td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>"><i class="fas <?= $statusIcon ?>"></i> <?= htmlspecialchars($user['estado_logico']) ?></span>
                        </td>
                        <td>
                            <div class="company-info">
                                <?php if(!empty($user['empresas'])): ?>
                                    <?php 
                                    $empresasArr = explode(', ', $user['empresas']);
                                    if (count($empresasArr) > 1) {
                                        $empText = count($empresasArr) . ' Empresas';
                                    } else {
                                        $empText = htmlspecialchars($empresasArr[0]);
                                    }
                                    ?>
                                    <i class="far fa-building"></i> <?= $empText ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin empresa</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="text-muted"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($user['fecha_ingreso']) ?></div>
                        </td>
                        <td>
                            <div class="action-icons">
                                <?php $safePayload = htmlspecialchars(json_encode($payload), ENT_QUOTES, 'UTF-8'); ?>
                                <button class="action-icon view-btn" data-payload="<?= $safePayload ?>" onclick="openUserModal(this, 'view')" title="Ver Información"><i class="fas fa-eye"></i></button>
                                <?php if (!$isSelf): ?>
                                <button class="action-icon edit-btn" data-payload="<?= $safePayload ?>" onclick="openUserModal(this, 'edit')" title="Editar Rol Global"><i class="fas fa-edit"></i></button>
                                
                                <?php if ($user['estado_logico'] === 'Suspendido'): ?>
                                    <button class="action-icon suspend-btn" onclick="directToggleSuspension(<?= $user['id'] ?>, 'activate')" title="Activar Cuenta"><i class="fas fa-check-circle text-success"></i></button>
                                <?php else: ?>
                                    <button class="action-icon suspend-btn" onclick="directToggleSuspension(<?= $user['id'] ?>, 'suspend')" title="Suspender Cuenta"><i class="fas fa-ban"></i></button>
                                <?php endif; ?>
                                
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
            </div> <!-- Cierra TAB USUARIOS -->

            <!-- TAB EMPRESAS -->
            <div class="tab-pane fade" id="nav-empresas" role="tabpanel">
                <!-- Top Metrics Cards EMPRESAS -->
                <div class="metrics-row mb-4">
                    <div class="metric-card metric-purple">
                        <div class="metric-icon-box"><i class="fas fa-building"></i></div>
                        <div class="metric-content">
                            <span class="metric-number"><?= htmlspecialchars($empresasMetrics['total'] ?? 0) ?></span>
                            <span class="metric-label">Total Empresas</span>
                        </div>
                    </div>
                    <div class="metric-card metric-green">
                        <div class="metric-icon-box"><i class="fas fa-check-circle"></i></div>
                        <div class="metric-content">
                            <span class="metric-number"><?= htmlspecialchars($empresasMetrics['activos'] ?? 0) ?></span>
                            <span class="metric-label">Activas</span>
                        </div>
                    </div>
                    <div class="metric-card metric-red">
                        <div class="metric-icon-box"><i class="fas fa-ban"></i></div>
                        <div class="metric-content">
                            <span class="metric-number"><?= htmlspecialchars($empresasMetrics['suspendidos'] ?? 0) ?></span>
                            <span class="metric-label">Suspendidas</span>
                        </div>
                    </div>
                </div>

                <!-- Data Table EMPRESAS-->
                <div class="users-table-container mb-5">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Contacto</th>
                                <th>Ubicación</th>
                                <th>Estado</th>
                                <th>Afiliados</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($empresas as $emp): 
                                $payloadEmp = ['empresa' => $emp];
                                $jsonEmp = htmlspecialchars(json_encode($payloadEmp), ENT_QUOTES, 'UTF-8');
                                
                                $statusClass = 'status-pendiente';
                                $statusIcon = 'fa-exclamation-triangle';
                                if ($emp['estado'] === 'Activa') {
                                    $statusClass = 'status-activo';
                                    $statusIcon = 'fa-check-circle';
                                } elseif ($emp['estado'] === 'Suspendida') {
                                    $statusClass = 'status-suspendido';
                                    $statusIcon = 'fa-ban';
                                }
                                
                                $initials = getInitials($emp['nombre_empresa']);
                                $avatarClass = "avatar-bg-" . ($emp['id_empresa'] % 4);
                                
                                $logoUrl = $emp['logo_ruta'] ? BASE_URL . "assets/images/Uploads/logos_empresa/" . $emp['logo_ruta'] : '';
                            ?>
                            <tr>
                                <td>
                                    <div class="user-td-info">
                                        <?php if($logoUrl): ?>
                                            <div class="user-avatar" style="background-image: url('<?= $logoUrl ?>'); background-size: cover; background-position: center; border-radius: 8px; width: 40px; height: 40px;"></div>
                                        <?php else: ?>
                                            <div class="user-avatar <?= $avatarClass ?>" style="border-radius: 8px;"><?= $initials ?></div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($emp['nombre_empresa']) ?></div>
                                            <div class="user-id text-muted">ID: <?= $emp['id_empresa'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($emp['email_contacto']) ?></div>
                                        <?php if(!empty($emp['telefono_contacto'])): ?>
                                        <div><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($emp['telefono_contacto']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="company-info">
                                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($emp['ciudad'] . ', ' . $emp['pais']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?= $statusClass ?>"><i class="fas <?= $statusIcon ?>"></i> <?= htmlspecialchars($emp['estado']) ?></span>
                                </td>
                                <td>
                                    <div class="text-muted"><i class="fas fa-users"></i> <?= $emp['total_admin_candidato'] ?> Miembros</div>
                                </td>
                                <td>
                                    <div class="text-muted"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($emp['fecha_registro']) ?></div>
                                </td>
                                <td>
                                    <div class="action-icons">
                                        <button class="action-icon view-btn" data-payload="<?= $jsonEmp ?>" onclick="openCompanyModal(this)" title="Ver Empresa"><i class="fas fa-eye"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div> <!-- Cierra TAB EMPRESAS -->
        </div> <!-- Cierra tab-content -->

    </div> <!-- Cierra main-content-wrapper -->

    <!-- Admin Company Modal -->
    <div class="modal fade" id="adminCompanyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered admin-modal">
            <div class="modal-content">
                <div class="admin-modal-header">
                    <div id="modalCompanyAvatar" class="admin-modal-avatar" style="border-radius: 8px; background-size: cover; background-position: center;">E</div>
                    <div>
                        <h4 class="m-0 fw-bold" id="modalCompanyName">Nombre Empresa</h4>
                        <div class="d-flex gap-2 mt-2">
                            <span id="modalCompanyStatus" class="status-badge">Estado</span>
                        </div>
                    </div>
                    <button type="button" class="admin-modal-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
                </div>
                <div class="admin-modal-body">
                    
                    <div class="info-section">
                        <div class="info-section-title">Información de Contacto</div>
                        <div class="contact-cards">
                            <div class="contact-card">
                                <div class="icon-wrap-blue"><i class="fas fa-envelope"></i></div>
                                <div class="contact-card-text">
                                    <small>Email</small>
                                    <span id="modalCompanyEmail">email@ejemplo.com</span>
                                </div>
                            </div>
                            <div class="contact-card">
                                <div class="icon-wrap-green"><i class="fas fa-phone-alt"></i></div>
                                <div class="contact-card-text">
                                    <small>Teléfono</small>
                                    <span id="modalCompanyPhone">3000000000</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="info-section-title">Ubicación</div>
                        <div class="company-card">
                            <div class="icon-wrap-purple"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="contact-card-text">
                                <span id="modalCompanyLocation">Colombia</span>
                                <small>País y Ciudad</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <div class="info-section-title">Descripción</div>
                        <p class="text-muted" id="modalCompanyDesc" style="font-size: 0.95rem; margin-bottom: 0;">Descripción detallada aquí.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- User Action Modal -->
    <div class="modal fade" id="adminUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered admin-modal">
            <div class="modal-content">
                <div class="admin-modal-header">
                    <div id="modalAvatar" class="admin-modal-avatar">U</div>
                    <div>
                        <h4 class="m-0 fw-bold" id="modalUserName">Nombre Usuario</h4>
                        <div class="d-flex gap-2 mt-2">
                            <span id="modalUserRole" class="role-badge">Rol</span>
                            <span id="modalUserStatus" class="status-badge">Estado</span>
                        </div>
                    </div>
                    <button type="button" class="admin-modal-close" data-bs-dismiss="modal"><i class="fas fa-times"></i></button>
                </div>
                <div class="admin-modal-body">
                    <input type="hidden" id="currentActionUserId">
                    
                    <div class="info-section">
                        <div class="info-section-title">Información de Contacto</div>
                        <div class="contact-cards">
                            <div class="contact-card">
                                <div class="icon-wrap-blue"><i class="fas fa-envelope"></i></div>
                                <div class="contact-card-text">
                                    <small>Email</small>
                                    <span id="modalUserEmail">email@ejemplo.com</span>
                                </div>
                            </div>
                            <div class="contact-card">
                                <div class="icon-wrap-green"><i class="fas fa-phone-alt"></i></div>
                                <div class="contact-card-text">
                                    <small>Teléfono</small>
                                    <span id="modalUserPhone">3000000000</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <div class="info-section-title">Empresa</div>
                        <div class="company-card">
                            <div class="icon-wrap-purple"><i class="fas fa-building"></i></div>
                            <div class="contact-card-text">
                                <span id="modalUserCompany">Startup Inc.</span>
                                <small>Organización actual</small>
                            </div>
                        </div>
                    </div>

                    <div id="adminActionsPanel" class="info-section border-top pt-4 mt-4">
                        <div class="info-section-title">Acciones Administrativas</div>
                        
                        <div id="roleEditContainer" class="hidden">
                            <select id="editRoleSelect">
                                <option value="1">Administrador</option>
                                <option value="2">Candidato</option>
                                <option value="3">Empresa</option>
                            </select>
                            <button class="btn-admin btn-admin-primary mb-3" onclick="saveRole()"><i class="fas fa-save"></i> Guardar Rol</button>
                        </div>

                        <div class="admin-actions">
                            <button class="btn-admin btn-admin-primary" onclick="toggleRoleEdit()">
                                <i class="fas fa-edit"></i> Editar Rol Global
                            </button>
                            <button id="btnSuspend" class="btn-admin btn-admin-outline-red" onclick="toggleSuspension('suspend')">
                                <i class="fas fa-ban"></i> Suspender Cuenta
                            </button>
                            <button id="btnActivate" class="btn-admin btn-admin-outline-green hidden" onclick="toggleSuspension('activate')">
                                <i class="fas fa-check"></i> Activar Cuenta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Dialog Modal -->
    <div class="modal fade" id="customDialogModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <div class="modal-body text-center p-4">
                    <div id="dialogIcon" class="mb-3" style="font-size: 3.5rem; color: #4361ee;">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h5 id="dialogTitle" class="fw-bold mb-2">Confirmación</h5>
                    <p id="dialogMessage" class="text-muted mb-4" style="font-size: 0.95rem;">¿Estás seguro de continuar?</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button id="dialogBtnCancel" type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal" style="font-weight: 600; border: 1px solid #e0e0e0;">Cancelar</button>
                        <button id="dialogBtnConfirm" type="button" class="btn btn-primary rounded-pill px-4" style="font-weight: 600; background: #4361ee; border: none;">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>src/public/js/adminGlobal.js"></script>
</body>
</html>
