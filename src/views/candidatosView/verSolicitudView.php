<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Contrato - TalentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <style>
        .request-card { max-width: 600px; margin: 50px auto; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background-color: white; padding:0; }
        .request-header { background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: white; padding: 25px; text-align: center; }
        .request-header img { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 3px solid white; margin-bottom: 15px; }
        .request-body { padding: 30px; }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-weight: 600; color: #555; }
        .info-value { color: #333; font-weight: 500; }
        .actions { display: flex; gap: 15px; margin-top: 30px; }
        .btn-action { flex: 1; padding: 12px; font-weight: bold; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">
<?php include __DIR__ . '/../dashboardView/sidebar_View.php'; ?>
<div class="main-content">
    <div class="container">
        <div class="request-card">
            <?php 
                $logoUrl = 'https://static.thenounproject.com/png/4154905-200.png';
                if (!empty($solicitud['logo_ruta'])) {
                    $logoUrl = str_starts_with($solicitud['logo_ruta'], 'http') ? $solicitud['logo_ruta'] : BASE_URL . $solicitud['logo_ruta'];
                }
            ?>
            <div class="request-header">
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo">
                <h3 class="mb-0">¡Tienes una solicitud de contrato!</h3>
                <p class="mb-0 mt-2 opacity-75">La empresa <strong><?= htmlspecialchars($solicitud['nombre_empresa']) ?></strong> quiere contratarte.</p>
            </div>
            <div class="request-body">
                <h5 class="mb-4 text-center">Detalles de la Oferta</h5>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-building me-2 text-primary"></i>Empresa</span>
                    <span class="info-value"><?= htmlspecialchars($solicitud['nombre_empresa']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-money-bill-wave me-2 text-success"></i>Salario Base propuesto</span>
                    <span class="info-value">
                        <?= $solicitud['salario_base'] ? '$' . number_format($solicitud['salario_base'], 2) : 'No especificado' ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-clock me-2 text-warning"></i>Horas Semanales</span>
                    <span class="info-value">
                        <?= $solicitud['horas_semanales_estandar'] ? htmlspecialchars($solicitud['horas_semanales_estandar']) . ' h' : 'No especificadas' ?>
                    </span>
                </div>
                
                <?php if ($solicitud['estado'] === 'pendiente'): ?>
                    <div class="actions">
                        <button class="btn btn-outline-danger btn-action rounded-pill" onclick="responderSolicitud('rechazada')">
                            <i class="fas fa-times me-2"></i> Rechazar
                        </button>
                        <button class="btn btn-primary btn-action rounded-pill" onclick="responderSolicitud('aceptada')">
                            <i class="fas fa-check me-2"></i> Aceptar Contrato
                        </button>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center mt-4 mb-0">
                        Esta solicitud ya ha sido <strong><?= htmlspecialchars($solicitud['estado']) ?></strong>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function responderSolicitud(respuesta) {
    if (!confirm('¿Estás seguro de que deseas ' + (respuesta === 'aceptada' ? 'aceptar' : 'rechazar') + ' este contrato?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', <?= (int)$solicitudId ?>);
    formData.append('respuesta', respuesta);
    
    fetch(BASE_URL + 'index.php?action=responder_solicitud', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Ocurrió un error de red.');
    });
}
</script>
</body>
</html>
