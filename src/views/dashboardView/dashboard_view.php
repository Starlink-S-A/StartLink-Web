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

// Fetch Dashboard Metrics
$countOfertas = 0;
$countPostulaciones = 0;
$countCapacitaciones = 0;
$ofertasRecomendadas = [];
$misPostulaciones = [];

if ($link instanceof PDO) {
    try {
        $stmtOfertas = $link->query("SELECT COUNT(*) FROM oferta_trabajo WHERE estado_oferta = 'Abierta'");
        $countOfertas = $stmtOfertas->fetchColumn();

        $stmtPostulaciones = $link->prepare("SELECT COUNT(*) FROM postulacion WHERE id_usuario = ?");
        $stmtPostulaciones->execute([$userId]);
        $countPostulaciones = $stmtPostulaciones->fetchColumn();

        $stmtCapacitaciones = $link->prepare("SELECT COUNT(*) FROM inscripcion WHERE id_usuario = ?");
        $stmtCapacitaciones->execute([$userId]);
        $countCapacitaciones = $stmtCapacitaciones->fetchColumn();

        $stmtRecomendadas = $link->query("
            SELECT id_oferta, titulo_oferta, empresa.nombre_empresa, oferta_trabajo.fecha_publicacion 
            FROM oferta_trabajo 
            JOIN empresa ON oferta_trabajo.id_empresa = empresa.id_empresa 
            WHERE estado_oferta = 'Abierta' 
            ORDER BY fecha_publicacion DESC LIMIT 3
        ");
        $ofertasRecomendadas = $stmtRecomendadas->fetchAll(PDO::FETCH_ASSOC);

        $stmtMisPostulaciones = $link->prepare("
            SELECT oferta_trabajo.id_oferta, oferta_trabajo.titulo_oferta, empresa.nombre_empresa, postulacion.estado_postulacion 
            FROM postulacion 
            JOIN oferta_trabajo ON postulacion.id_oferta = oferta_trabajo.id_oferta 
            JOIN empresa ON oferta_trabajo.id_empresa = empresa.id_empresa 
            WHERE postulacion.id_usuario = ? 
            ORDER BY fecha_postulacion DESC LIMIT 2
        ");
        $stmtMisPostulaciones->execute([$userId]);
        $misPostulaciones = $stmtMisPostulaciones->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { /* ignore */ }
}
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
<?php include __DIR__ . '/sidebar_View.php'; ?>

<div class="main-content-wrapper px-md-4">
    <?php 
    $pageTitle = 'Home';
    include __DIR__ . '/navbar_view.php'; 
    ?>


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
                <a href="<?= BASE_URL ?>src/index.php?action=configurar_perfil" class="btn-dash-primary rounded-pill px-4">
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
                <p><?= htmlspecialchars($countOfertas) ?></p>
            </div>
        </div>
        <div class="metric-box">
            <div class="metric-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="metric-data">
                <h5>Postulaciones</h5>
                <p><?= htmlspecialchars($countPostulaciones) ?></p>
                <?php if($countPostulaciones > 0): ?><span class="badge-status">En proceso</span><?php endif; ?>
            </div>
        </div>
        <div class="metric-box">
            <div class="metric-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="metric-data">
                <h5>Capacitaciones</h5>
                <p><?= htmlspecialchars($countCapacitaciones) ?></p>
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
                    <a href="<?= BASE_URL ?>src/index.php?action=ofertas" class="text-muted"><i class="fas fa-ellipsis-v"></i></a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($ofertasRecomendadas)): ?>
                        <div class="p-4 text-center text-muted">No hay ofertas recientes</div>
                    <?php else: ?>
                        <?php foreach($ofertasRecomendadas as $oferta): 
                            $fechaOf = "Hace poco";
                            if (!empty($oferta['fecha_publicacion'])) {
                                $creacion = new DateTime($oferta['fecha_publicacion']);
                                $ahora = new DateTime();
                                $diff = $ahora->diff($creacion);
                                if ($diff->d > 0) $fechaOf = "Hace {$diff->d} día" . ($diff->d > 1 ? 's' : '');
                                elseif ($diff->h > 0) $fechaOf = "Hace {$diff->h} hora" . ($diff->h > 1 ? 's' : '');
                                elseif ($diff->i > 0) $fechaOf = "Hace {$diff->i} min";
                            }
                        ?>
                        <a href="<?= BASE_URL ?>src/index.php?action=ofertas&id=<?= $oferta['id_oferta'] ?>" class="event-item text-decoration-none text-dark d-flex">
                            <div class="event-icon-circle">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="event-details">
                                <h6><?= htmlspecialchars($oferta['titulo_oferta']) ?></h6>
                                <p><?= htmlspecialchars($oferta['nombre_empresa']) ?> - <?= $fechaOf ?></p>
                            </div>
                            <div class="ms-auto event-action">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="mt-4 text-center">
                    <a href="<?= BASE_URL ?>src/index.php?action=ofertas" class="btn-dash-primary w-100">Ver todas las ofertas</a>
                </div>
            </div>
        </div>

    <div class="row g-4 mt-2 mb-5">
        <div class="col-md-4">
            <div class="course-card h-100">
                <span class="course-badge">Nuevo</span>
                <div class="course-image">
                    <img src="https://cdn-icons-png.flaticon.com/512/919/919830.png" alt="Capacitaciones">
                </div>
                <div class="course-content">
                    <div class="course-meta">DISEÑO Y DEV</div>
                    <h5>Domina React 18</h5>
                    <p>Aprende las últimas funcionalidades de React para crear interfaces modernas.</p>
                    <a href="<?= BASE_URL ?>src/index.php?action=capacitaciones" class="btn-dash-primary btn-course">Explorar Capacitaciones</a>
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
                            <a href="<?= BASE_URL ?>src/index.php?action=nominas" class="btn-dash-primary">Ver Historial Completo</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div> <!-- Cierra main-content-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
