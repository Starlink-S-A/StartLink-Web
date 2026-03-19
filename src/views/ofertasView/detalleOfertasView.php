<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle Oferta – <?= htmlspecialchars($oferta['titulo_oferta'] ?? '') ?> - TalentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>styles/dashboard_styles.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>styles/navbar_styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .salario-actual {
            background-color: #e7f5ff;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php
// Variables para el navbar (vienen desde DetallesOfertasController)
$userName     = $_SESSION["user_name"] ?? 'Usuario';

// Incluir el navbar después de las variables base (o si ya vienen del controlador)
include ROOT_PATH . 'src/views/dashboardView/navbar_view.php';
?>

<div class="container mt-4">
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje ?? '') ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error ?? '') ?></div>
    <?php endif; ?>

    <h2><?= htmlspecialchars($oferta['titulo_oferta'] ?? '') ?></h2>
    <p><strong>Empresa:</strong> <?= htmlspecialchars($oferta['nombre_empresa'] ?? '') ?></p>
    <p><strong>Rango salarial:</strong> 
        <?php if (isset($oferta['presupuesto_min']) && isset($oferta['presupuesto_max'])): ?>
            $<?= number_format($oferta['presupuesto_min'], 2) ?> – $<?= number_format($oferta['presupuesto_max'], 2) ?>
        <?php else: ?>
            No especificado
        <?php endif; ?>
    </p>
    <p><strong>Ubicación/Modalidad:</strong> 
        <?= htmlspecialchars($oferta['ubicacion'] ?? 'No especificada') ?> / <?= htmlspecialchars($oferta['modalidad'] ?? 'No especificada') ?>
    </p>
    <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($oferta['descripcion_oferta'] ?? '')) ?></p>
    <p><strong>Requisitos:</strong> <?= nl2br(htmlspecialchars($oferta['requisitos'] ?? '')) ?></p>

    <hr>
    <h3>Postulantes (<?= count($postulantes) ?>)</h3>

    <?php if (empty($postulantes)): ?>
        <div class="alert alert-info">No hay candidatos aún.</div>
    <?php else: ?>
        <?php foreach ($postulantes as $u): ?>
            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-md-2">
                        <?php
                        // Prioridad a la foto de perfil en la BD (que ya incluye el path relativo)
                        $img = 'https://static.thenounproject.com/png/4154905-200.png';
                        if (!empty($u['foto_perfil']) && file_exists(ROOT_PATH . $u['foto_perfil'])) {
                            $img = BASE_URL . $u['foto_perfil'];
                        }
                        ?>
                        <img src="<?= htmlspecialchars($img) ?>" class="img-fluid rounded-start" alt="Foto de perfil" style="height: 100%; object-fit: cover;">
                    </div>
                    <div class="col-md-10">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($u['nombre'] ?? '') ?> – <?= htmlspecialchars($u['cargo'] ?? 'Sin cargo especificado') ?></h5>
                            <p><strong>Email:</strong> <?= htmlspecialchars($u['email'] ?? '') ?> | <strong>DNI:</strong> <?= htmlspecialchars($u['dni'] ?? '') ?></p>
                            
                            <?php if (!empty($u['salario_base'])): ?>
                                <p><strong>Salario actual:</strong> <span class="salario-actual">$<?= number_format($u['salario_base'], 2) ?></span></p>
                            <?php endif; ?>
                            
                            <?php if (!empty($u['horas_semanales_estandar'])): ?>
                                <p><strong>Horas Semanales Estándar:</strong> <?= htmlspecialchars($u['horas_semanales_estandar']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($u['fecha_ingreso'])): ?>
                                <p><strong>Ingreso:</strong> <?= date("Y-m", strtotime($u['fecha_ingreso'])) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($u['habilidades'])): ?>
                                <p><strong>Habilidades:</strong> <?= htmlspecialchars($u['habilidades']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($u['experiencias'])):
                                $exps = explode('||', $u['experiencias']); ?>
                                <p><strong>Experiencia laboral:</strong>
                                <?php foreach ($exps as $exp): ?>
                                    <br>– <?= htmlspecialchars($exp) ?>
                                <?php endforeach; ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($u['estudios'])):
                                $ests = explode('||', $u['estudios']); ?>
                                <p><strong>Estudios:</strong>
                                <?php foreach ($ests as $est): ?>
                                    <br>– <?= htmlspecialchars($est) ?>
                                <?php endforeach; ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($u['ruta_hdv'])): 
                                // Asumiendo que ruta_hdv también incluye el path relativo o está en uploads/cvs
                                $cvPath = 'assets/images/Uploads/cvs/' . basename($u['ruta_hdv']);
                                if (file_exists(ROOT_PATH . $cvPath)): ?>
                                <p><strong>CV:</strong>
                                    <a href="<?= BASE_URL . $cvPath ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-file-pdf"></i> Ver / Descargar
                                    </a>
                                </p>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="d-flex gap-2 mt-3">
                                <a href="chat_oferta.php?id_oferta=<?= $oferta['id_oferta'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-comments"></i> Chat de la Oferta
                                </a>

                                <?php if ($esCreador): ?>
                                    <a href="chat_oferta.php?id_oferta=<?= $oferta['id_oferta'] ?>&id_usuario_privado=<?= $u['id'] ?>&tipo_chat=privado" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-comment-dots"></i> Chat Privado con Postulante
                                    </a>
                                <?php endif; ?>

                                <?php if (($u['estado_postulacion'] ?? '') != 'Contratado'): ?>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalContratar<?= $u['id'] ?>">
                                        <i class="fas fa-user-check"></i> Contratar
                                    </button>

                                    <div class="modal fade" id="modalContratar<?= $u['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="POST">
                                                <input type="hidden" name="id_postulante" value="<?= $u['id'] ?>">
                                                <input type="hidden" name="accion" value="Contratado">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Contratar postulante</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Confirmas que deseas contratar a <strong><?= htmlspecialchars($u['nombre'] ?? '') ?></strong>?</p>
                                                        
                                                        <div class="mb-3">
                                                            <label for="salario<?= $u['id'] ?>" class="form-label">Salario acordado:</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">$</span>
                                                                <input type="number" step="0.01" class="form-control" id="salario<?= $u['id'] ?>" 
                                                                        name="salario_contratado" required
                                                                        min="<?= $oferta['presupuesto_min'] ?>" 
                                                                        max="<?= $oferta['presupuesto_max'] ?>"
                                                                        placeholder="Ej: <?= number_format($oferta['presupuesto_min'], 2) ?>"
                                                                        value="<?= !empty($u['salario_base']) ? number_format($u['salario_base'], 2, '.', '') : '' ?>">
                                                            </div>
                                                            <div class="form-text">
                                                                Rango permitido: $<?= number_format($oferta['presupuesto_min'], 2) ?> 
                                                                - $<?= number_format($oferta['presupuesto_max'], 2) ?>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="horas_semanales<?= $u['id'] ?>" class="form-label">Horas Semanales Estándar:</label>
                                                            <input type="number" step="0.01" class="form-control" id="horas_semanales<?= $u['id'] ?>"
                                                                    name="horas_semanales_estandar" required min="1" max="168"
                                                                    value="<?= !empty($u['horas_semanales_estandar']) ? htmlspecialchars($u['horas_semanales_estandar']) : 40 ?>">
                                                            <div class="form-text">
                                                                Define las horas que el empleado trabajará semanalmente (ej. 40).
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-success">Confirmar Contrato</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (($u['estado_postulacion'] ?? '') != 'Rechazado'): ?>
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalRechazar<?= $u['id'] ?>">
                                        <i class="fas fa-user-times"></i> Rechazar
                                    </button>

                                    <div class="modal fade" id="modalRechazar<?= $u['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="POST">
                                                <input type="hidden" name="id_postulante" value="<?= $u['id'] ?>">
                                                <input type="hidden" name="accion" value="Rechazado">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Rechazar postulante</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Confirmas que deseas rechazar permanentemente a <strong><?= htmlspecialchars($u['nombre'] ?? '') ?></strong>?</p>
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle"></i> El usuario no podrá volver a postularse a esta oferta.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-danger">Rechazar Permanentemente</button>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (form.querySelector('input[name="accion"]')?.value === 'Contratado') {
                    const salarioInput = form.querySelector('input[name="salario_contratado"]');
                    const min = parseFloat(salarioInput.min);
                    const max = parseFloat(salarioInput.max);
                    const value = parseFloat(salarioInput.value);
                    
                    if (isNaN(value) || value < min || value > max) {
                        alert(`El salario debe estar entre $${min.toFixed(2)} y $${max.toFixed(2)}`);
                        e.preventDefault();
                        return false;
                    }

                    const horasSemanalesInput = form.querySelector('input[name="horas_semanales_estandar"]');
                    const horasValue = parseFloat(horasSemanalesInput.value);
                    if (isNaN(horasValue) || horasValue <= 0 || horasValue > 168) {
                        alert('Las horas semanales deben ser un valor positivo y razonable.');
                        e.preventDefault();
                        return false;
                    }
                }
                return true;
            });
        });
    });
</script>
</body>
</html>