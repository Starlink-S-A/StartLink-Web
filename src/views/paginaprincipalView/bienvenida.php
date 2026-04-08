<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';
// Si $form_to_show no está definido, mostrar login por defecto
if (!isset($form_to_show)) $form_to_show = 'login';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StartLink - ¡Encuentra tu próximo empleo!</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos base y el nuevo diseño -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/public/styles/estilos.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>src/public/styles/login_redesign.css">
    
    <!-- Scripts de reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LdobLYrAAAAABPXnbLFCmYrU1Mz7A_0hJCkltyQ" async defer></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const FORM_TO_SHOW = '<?php echo $form_to_show; ?>';
    </script>
</head>
<body>
    <div class="login-wrapper">
        <!-- Parte Izquierda: Bienvenida -->
        <div class="login-left">
            <div class="bg-pattern"></div>
            <div class="glass-circle circle-1"></div>
            <div class="glass-circle circle-2"></div>
            <div class="glass-circle circle-3"></div>
            <div class="logo">
                <i class="fas fa-rocket"></i> StartLink
            </div>
            <div class="welcome-content">
                <h1>Tu próximo gran paso profesional comienza aquí</h1>
                <p>Conectamos tu talento con las mejores oportunidades del mercado laboral.</p>
                <div class="rocket-illustration">
                    <i class="fas fa-rocket"></i>
                </div>
            </div>
        </div>

        <!-- Parte Derecha: Formularios -->
        <div class="login-right">
            <!-- Contenedor para mensajes de alerta -->
            <div id="alertMessageContainer" class="position-absolute top-0 start-50 translate-middle-x mt-3" 
                 style="z-index: 1000; width: 90%; max-width: 400px;">
            </div>

            <!-- Sección Bienvenida (Mantenida por compatibilidad con JS) -->
            <div id="welcomeSection" class="form-section <?php echo $form_to_show === 'welcome' ? 'active' : ''; ?> welcome-professional">
                <h1>¡Bienvenido a StartLink!</h1>
                <p class="lead">Conecta con tu futuro laboral ideal.</p>
                <button id="showLoginFormBtn" class="btn-action">Comenzar</button>
            </div>

            <!-- Login -->
            <div id="loginFormSection" class="form-section <?php echo ($form_to_show === 'login') ? 'active' : ''; ?>">
                <h2>Iniciar Sesión</h2>
                <form id="loginForm">
                    <div class="form-group-custom">
                        <label for="loginEmail">Correo electrónico</label>
                        <input type="email" id="loginEmail" name="email" placeholder="Ingresa tu correo" required>
                    </div>
                    <div class="form-group-custom">
                        <label for="loginPassword">Contraseña</label>
                        <input type="password" id="loginPassword" name="password" placeholder="••••••••" required>
                    </div>
                    
                    <div class="checkbox-group">
                        <a href="#" id="showForgotPasswordLink" class="ms-auto">¿Olvidaste tu contraseña?</a>
                    </div>

                    <!-- Campo oculto para el token de reCAPTCHA v3 -->
                    <input type="hidden" id="recaptchaToken" name="recaptcha_token">

                    <button type="submit" class="btn-action">Ingresar</button>
                    
                    <div class="link-footer">
                        ¿No tienes una cuenta? <a href="#" id="showRegisterLink">Regístrate aquí</a>
                    </div>
                </form>
            </div>

            <!-- Registro -->
            <div id="registerFormSection" class="form-section <?php echo $form_to_show === 'register' ? 'active' : ''; ?>">
                <h2>Crea tu cuenta</h2>
                <form id="registrationForm">
                    <div class="form-group-custom">
                        <label for="registerName">Nombre completo</label>
                        <input type="text" id="registerName" name="nombre" placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div class="form-group-custom">
                        <label for="registerEmail">Correo electrónico</label>
                        <input type="email" id="registerEmail" name="email" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="form-group-custom">
                        <label for="registerPassword">Contraseña</label>
                        <input type="password" id="registerPassword" name="password" placeholder="Mín. 8 caracteres" required>
                    </div>
                    <div class="form-group-custom">
                        <label for="confirmPassword">Confirmar contraseña</label>
                        <input type="password" id="confirmPassword" name="confirm_password" placeholder="Repite tu clave" required>
                    </div>

                    <!-- reCAPTCHA v2 para registro -->
                    <div class="g-recaptcha" data-sitekey="6Ldq87srAAAAAGGOrfyjsXqp7rfPFvaIjhr3KHA2" data-theme="dark"></div>

                    <button type="submit" class="btn-action">Registrarme ahora</button>
                    
                    <div class="link-footer">
                        ¿Ya eres miembro? <a href="#" id="showLoginLink">Inicia sesión aquí</a>
                    </div>
                </form>
            </div>

            <!-- Recuperar contraseña -->
            <div id="forgotPasswordSection" class="form-section">
                <h2>Recuperar Contraseña</h2>
                <form id="forgotPasswordForm">
                    <div class="form-group-custom">
                        <label for="forgotEmail">Correo Electrónico</label>
                        <input type="email" id="forgotEmail" name="forgotEmail" placeholder="Ingresa tu correo" required>
                    </div>
                    <button type="submit" class="btn-action w-100">Enviar Enlace</button>
                    <div class="link-footer">
                        <a href="#" id="backToLoginLink">Volver al Inicio</a>
                    </div>
                </form>
            </div>

            <!-- Restablecer contraseña -->
            <div id="resetPasswordSection" class="form-section">
                <h2>Nueva Contraseña</h2>
                <form id="resetPasswordForm">
                    <div class="form-group-custom">
                        <label for="resetToken">Código de Verificación</label>
                        <input type="text" id="resetToken" name="resetToken" placeholder="Código enviado al correo" required>
                    </div>
                    <div class="form-group-custom">
                        <label for="resetNewPassword">Nueva Contraseña</label>
                        <input type="password" id="resetNewPassword" name="resetNewPassword" required>
                    </div>
                    <div class="form-group-custom">
                        <label for="confirmResetPassword">Confirmar Contraseña</label>
                        <input type="password" id="confirmResetPassword" name="confirmResetPassword" required>
                    </div>
                    <button type="submit" class="btn-action w-100">Restablecer Contraseña</button>
                    <div class="link-footer">
                        <a href="#" id="backToLoginFromResetLink">Volver al Inicio</a>
                    </div>
                </form>
            </div>

        </div> <!-- /.login-right -->
    </div> <!-- /.login-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>src/public/js/form-logic.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const navLoginBtn = document.getElementById('navLoginBtn');
            const navSignUpBtn = document.getElementById('navSignUpBtn');
            
            const setActiveNav = (btn) => {
                navLoginBtn.classList.remove('active');
                navSignUpBtn.classList.remove('active');
                btn.classList.add('active');
            };

            navLoginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                setActiveNav(navLoginBtn);
                document.getElementById('showLoginLink').click();
            });

            navSignUpBtn.addEventListener('click', (e) => {
                e.preventDefault();
                setActiveNav(navSignUpBtn);
                document.getElementById('showRegisterLink').click();
            });

            // Sincronizar botones superiores cuando se cambia de sección vía links internos
            const showRegisterLink = document.getElementById('showRegisterLink');
            const showLoginLink = document.getElementById('showLoginLink');
            
            if(showRegisterLink) showRegisterLink.addEventListener('click', () => setActiveNav(navSignUpBtn));
            if(showLoginLink) showLoginLink.addEventListener('click', () => setActiveNav(navLoginBtn));
            
            const showLoginFormBtn = document.getElementById('showLoginFormBtn');
            if(showLoginFormBtn) {
                showLoginFormBtn.addEventListener('click', () => setActiveNav(navLoginBtn));
            }
        });
    </script>
</body>
</html>
