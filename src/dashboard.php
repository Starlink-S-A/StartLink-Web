<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/configuracionInicial.php';

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
        $stmt_notif = $link->prepare("SELECT id, mensaje, tipo, icono, fecha_creacion, leida FROM NOTIFICACIONES WHERE user_id = ? ORDER BY fecha_creacion DESC LIMIT 10");
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
    <link rel="stylesheet" href="<?= BASE_URL ?>styles/dashboard_styles.css"> 
    <link rel="stylesheet" href="<?= BASE_URL ?>styles/navbar_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>


<div class="container mt-4">
    <?php if ($showProfileIncompleteBanner): ?>
        <div class="alert alert-warning text-center">
            ¡Tu perfil está incompleto! <a href="<?= BASE_URL ?>configurar_perfil.php" class="alert-link">Configúralo ahora</a>.
        </div>
    <?php endif; ?>

    <h1 class="text-center">Bienvenido, <?= htmlspecialchars($userName) ?></h1>

    <p class="lead text-center">
        Tu rol:
        <strong>
            <?php
                if ($userRoleGlobal == 1) echo "Administrador del Sistema";
                elseif ($userRolEmpresa == 1) echo "Administrador de Empresa";
                elseif ($userRolEmpresa == 2) echo "Contratador";
                elseif ($userRolEmpresa == 3) echo "Empleado Interno";
                else echo "Usuario General";
            ?>
        </strong>
    </p>

    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">Ofertas de Empleo Recomendadas</div>
                <div class="card-body">
                    <p>Aquí verás ofertas que coincidan con tu perfil.</p>
                    <a href="#" class="btn btn-primary">Ver todas</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">Mis Postulaciones</div>
                <div class="card-body">
                    <p>Consulta el estado de tus postulaciones activas.</p>
                    <a href="#" class="btn btn-primary">Ver postulaciones</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">Capacitaciones Disponibles</div>
                <div class="card-body">
                    <p>Mejora tus habilidades profesionales con cursos.</p>
                    <a href="<?= BASE_URL ?>capacitaciones.php" class="btn btn-primary">Ver capacitaciones</a>
                </div>
            </div>
        </div>
        <?php if ($esTrabajadorActivo): // Mostrar solo si el usuario es "TRABAJADOR" o tiene un rol de empresa relevante ?>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">Mi Historial Laboral</div>
                    <div class="card-body">
                        <p>Consulta tu historial de empleo y nóminas.</p>
                        <a href="<?= BASE_URL ?>historial_usuario.php" class="btn btn-primary">Ver Historial</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
