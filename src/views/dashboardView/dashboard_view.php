<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/configuracionInicial.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
    header("Location: " . BASE_URL . "bienvenida.php");
    exit();
}

$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"] ?? 'Usuario';
$userRoleGlobal = $_SESSION["id_rol"] ?? 2; // 1 = ADMINISTRADOR, 2 = USUARIO
$userEmpresaId = $_SESSION["id_empresa"] ?? null;
$userRolEmpresa = $_SESSION["id_rol_empresa"] ?? null;

$profileIsComplete = isProfileComplete($userId);
$_SESSION['profile_completed'] = $profileIsComplete;
$showProfileIncompleteBanner = !$profileIsComplete;

$notificaciones = [];
$unread_notifications_count = 0;
$link = getDbConnection(); 

if ($link instanceof PDO) {
    try {
        $stmt_notif = $link->prepare("SELECT id, mensaje, tipo, icono, fecha_creacion, leida FROM notificaciones WHERE user_id = ? ORDER BY fecha_creacion DESC LIMIT 10");
        $stmt_notif->execute([$userId]);

        while ($row = $stmt_notif->fetch(PDO::FETCH_ASSOC)) {
            $row['tiempo'] = (new DateTime($row['fecha_creacion']))->format('d M H:i');

            if (empty($row['icono'])) {
                switch ($row['tipo']) {
                    case 'success': $row['icono'] = 'fas fa-check-circle text-success'; break;
                    case 'warning': $row['icono'] = 'fas fa-exclamation-triangle text-warning'; break;
                    case 'error': $row['icono'] = 'fas fa-times-circle text-danger'; break;
                    default: $row['icono'] = 'fas fa-info-circle text-info'; break;
                }
            } elseif (strpos($row['icono'], 'text-') === false) {
                $row['icono'] .= ' text-primary';
            }

            $notificaciones[] = $row;
            if (!$row['leida']) {
                $unread_notifications_count++;
            }
        }
    } catch (PDOException $e) {
        error_log("Error al obtener notificaciones: " . $e->getMessage());
    }
}

// Determinar si el usuario es un "TRABAJADOR" (rol global 3) o tiene un rol de empresa (Contratador o Empleado Interno)
$esTrabajadorActivo = ($userRoleGlobal == 3 || in_array($userRolEmpresa, [2, 3]));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - TalentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<?php include 'navbar_view.php'; ?>


    <?php 
    // Mock de porcentaje para demostración visual interactiva
    $completionPercent = $profileIsComplete ? 100 : 45;
    if ($showProfileIncompleteBanner): 
    ?>
        <div class="completion-container">
            <div class="progress-header">
                <div class="progress-title">
                    <h4>Nivel de Perfil: <?= $profileIsComplete ? 'Profesional Elite' : 'Candidato en Crecimiento' ?></h4>
                    <p class="text-muted mb-0">¡Completa tu perfil para desbloquear beneficios exclusivos!</p>
                </div>
                <div class="progress-percentage"><?= $completionPercent ?>%</div>
            </div>
            
            <div class="premium-progress">
                <div class="premium-progress-bar" style="width: <?= $completionPercent ?>%;"></div>
            </div>
            
            <div class="milestones">
                <div class="milestone active">
                    <div class="milestone-dot"></div>
                    <i class="fas fa-user"></i>
                    <span>Básico</span>
                </div>
                <div class="milestone <?= $completionPercent >= 50 ? 'active' : '' ?>">
                    <div class="milestone-dot"></div>
                    <i class="fas fa-briefcase"></i>
                    <span>Experiencia</span>
                </div>
                <div class="milestone <?= $completionPercent >= 75 ? 'active' : '' ?>">
                    <div class="milestone-dot"></div>
                    <i class="fas fa-graduation-cap"></i>
                    <span>Educación</span>
                </div>
                <div class="milestone <?= $completionPercent == 100 ? 'active' : '' ?>">
                    <div class="milestone-dot"></div>
                    <i class="fas fa-award"></i>
                    <span>Verificado</span>
                </div>
            </div>

            <div class="mt-4 text-end">
                <a href="<?= BASE_URL ?>configurar_perfil" class="btn-dash-primary rounded-pill px-4">
                    <i class="fas fa-rocket me-2"></i> Impulsar Perfil
                </a>
            </div>
        </div>
    <?php endif; ?>


    <!-- Metrics Summary Section -->
    <div class="metrics-container mb-4">
        <div class="metric-box">
            <div class="metric-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="metric-data">
                <h5>Ofertas Activas</h5>
                <p>12</p>
            </div>
        </div>
        <div class="metric-box">
            <div class="metric-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="metric-data">
                <h5>Postulaciones</h5>
                <p>5</p>
                <span class="badge-status">En proceso</span>
            </div>
        </div>
        <div class="metric-box">
            <div class="metric-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="metric-data">
                <h5>Cursos</h5>
                <p>3</p>
            </div>
        </div>
        <div class="metric-box">
            <div class="metric-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="metric-data">
                <h5>Notificaciones</h5>
                <p><?= $unread_notifications_count ?></p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h5 class="dash-card-title">
                        <i class="fas fa-search text-primary"></i> 
                        Ofertas Recomendadas
                    </h5>
                    <a href="#" class="text-muted"><i class="fas fa-ellipsis-v"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="event-item">
                        <div class="event-icon-circle">
                            <i class="fas fa-code"></i>
                        </div>
                        <div class="event-details">
                            <h6>Desarrollador Senior PHP</h6>
                            <p>Tech Solutions - Hace 2 horas</p>
                        </div>
                        <div class="ms-auto event-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                    <div class="event-item">
                        <div class="event-icon-circle">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <div class="event-details">
                            <h6>Diseñador UI/UX</h6>
                            <p>Creative Studio - Hace 5 horas</p>
                        </div>
                        <div class="ms-auto event-action">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <a href="#" class="btn-dash-primary w-100">Ver todas las ofertas</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h5 class="dash-card-title">
                        <i class="fas fa-tasks text-primary"></i> 
                        Mis Postulaciones
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">Consulta el estado actual de tus procesos de selección de forma rápida.</p>
                    <div class="event-item">
                        <div class="event-icon-circle" style="background-color: #fef3c7; color: #d97706;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div class="event-details">
                            <h6>Analista de Datos</h6>
                            <p>DataCorp - En revisión</p>
                        </div>
                    </div>
                    <div class="event-item">
                        <div class="event-icon-circle" style="background-color: #d1fae5; color: #059669;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="event-details">
                            <h6>Frontend Developer</h6>
                            <p>WebFlow - Entrevista programada</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <a href="#" class="btn-dash-primary w-100">Ver historial completo</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2 mb-5">
        <div class="col-md-4">
            <div class="course-card h-100">
                <span class="course-badge">Nuevo</span>
                <div class="course-image">
                    <img src="https://cdn-icons-png.flaticon.com/512/919/919830.png" alt="Cursos">
                </div>
                <div class="course-content">
                    <div class="course-meta">DISEÑO Y DEV</div>
                    <h5>Domina React 18</h5>
                    <p>Aprende las últimas funcionalidades de React para crear interfaces modernas.</p>
                    <a href="<?= BASE_URL ?>capacitaciones.php" class="btn-dash-primary btn-course">Explorar Cursos</a>
                </div>
            </div>
        </div>

        <?php if ($esTrabajadorActivo): ?>
            <div class="col-md-8">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h5 class="dash-card-title">
                            <i class="fas fa-history text-primary"></i> 
                            Mi Historial Laboral
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="empty-state">
                            <div class="circle-icon d-flex align-items-center justify-content-center">
                                <i class="fas fa-folder-open text-muted"></i>
                            </div>
                            <p>Consulta tu historial de empleo y descarga tus nóminas anteriores.</p>
                            <a href="<?= BASE_URL ?>historial_usuario.php" class="btn-dash-primary">Ver Historial Completo</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
