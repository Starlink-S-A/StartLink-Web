<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';
// Si $form_to_show no está definido, mostrar welcome
if (!isset($form_to_show)) $form_to_show = 'welcome';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TalentLink - ¡Encuentra tu próximo empleo!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/public/styles/estilos.css">
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const FORM_TO_SHOW = '<?php echo $form_to_show; ?>';
    </script>
</head>
<body>
    <div class="video-background">
        <video id="video1" class="video-layer active-video" autoplay loop muted playsinline>
            <source src="<?php echo BASE_URL; ?>assets/media/Fondo.mp4" type="video/mp4">
            Tu navegador no soporta la etiqueta de video.
        </video>
        <video id="video2" class="video-layer" loop muted playsinline>
            <source src="<?php echo BASE_URL; ?>assets/media/Fondo1.mp4" type="video/mp4">
            Tu navegador no soporta la etiqueta de video.
        </video>
        <div class="video-overlay"></div>
    </div>
    <div class="content-overlay">
        <div class="main-content">
            <div id="alertMessageContainer" class="position-absolute top-0 start-50 translate-middle-x mt-3" style="z-index: 1000; width: 80%; max-width: 500px;">
            </div>

            <div id="welcomeSection" class="section-container" style="display: <?php echo $form_to_show === 'welcome' ? 'block' : 'none'; ?>;">
                <h1>¡Bienvenido a TalentLink!</h1>
                <p class="lead">Conecta con tu futuro laboral ideal.</p>
                <button id="showLoginFormBtn" class="btn btn-primary btn-lg mt-3">Comenzar</button>
            </div>

            <div id="loginFormSection" class="section-container" style="display: <?php echo $form_to_show === 'login' ? 'block' : 'none'; ?>;">
                <h2>Iniciar Sesión</h2>
                <form id="loginForm" class="form-container mt-4" action="<?php echo BASE_URL; ?>src/index.php?action=login" method="POST">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block mt-4">Ingresar</button>
                    <p class="mt-3 text-center">
                        ¿No tienes cuenta? <a href="#" id="showRegisterLink" class="btn-link">Regístrate aquí</a>
                    </p>
                    <p>
                        ¿Olvidaste tu contraseña? <a href="<?php echo BASE_URL; ?>recuperar_contraseña.php" class="btn-link">Recuperarla</a>
                    </p>
                </form>
            </div>

            <div id="registerFormSection" class="section-container" style="display: <?php echo $form_to_show === 'register' ? 'block' : 'none'; ?>;">
                <h2>Crear una Cuenta</h2>
                <form id="registrationForm" class="form-container mt-4">
                    <div class="mb-3">
                        <label for="registerName" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="registerName" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="registerEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg btn-block mt-4">Registrarse</button>
                    <p class="mt-3 text-center">
                        ¿Ya tienes cuenta? <a href="#" id="showLoginLink" class="btn-link">Inicia sesión</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>src/public/js/video-crossfade.js"></script>
    <script src="<?php echo BASE_URL; ?>src/public/js/form-logic.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.search) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>