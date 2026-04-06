document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM cargado - Iniciando form-logic.js');
    
    const showLoginFormBtn = document.getElementById('showLoginFormBtn');
    const showRegisterLink = document.getElementById('showRegisterLink');
    const showLoginLink = document.getElementById('showLoginLink');
    const showForgotPasswordLink = document.getElementById('showForgotPasswordLink');

    const welcomeSection = document.getElementById('welcomeSection');
    const loginFormSection = document.getElementById('loginFormSection');
    const registerFormSection = document.getElementById('registerFormSection');
    const forgotPasswordSection = document.getElementById('forgotPasswordSection');

    const loginForm = document.getElementById('loginForm');
    const registrationForm = document.getElementById('registrationForm');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const resetPasswordForm = document.getElementById('resetPasswordForm');

    const messageContainer = document.getElementById('alertMessageContainer');
    const transitionDuration = 600;

    // -------- Función para mostrar mensajes --------
    const displayMessage = (message, type) => {
        if (!messageContainer) {
            console.error('No se encontró el contenedor de mensajes');
            return;
        }
        messageContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    };

    // -------- Animar cambio de secciones --------
    const changeSection = (sectionToShow) => {
        const sections = [
            welcomeSection, loginFormSection,
            registerFormSection, forgotPasswordSection
        ];
        let currentActiveSection = null;

        for (const section of sections) {
            if (section && section.style.display === 'block') {
                currentActiveSection = section;
                break;
            }
        }

        if (currentActiveSection && currentActiveSection !== sectionToShow) {
            currentActiveSection.style.opacity = '0';
            currentActiveSection.style.transform = 'translateY(20px)';

            setTimeout(() => {
                currentActiveSection.style.display = 'none';
                sectionToShow.style.display = 'block';
                void sectionToShow.offsetWidth;
                sectionToShow.style.opacity = '1';
                sectionToShow.style.transform = 'translateY(0)';
            }, transitionDuration);
        } else if (!currentActiveSection) {
            sectionToShow.style.display = 'block';
            sectionToShow.style.opacity = '1';
            sectionToShow.style.transform = 'translateY(0)';
        }
    };

    // -------- Sección inicial --------
    let initialSection;
    if (typeof FORM_TO_SHOW !== "undefined" && FORM_TO_SHOW === 'login') {
        initialSection = loginFormSection;
    } else if (typeof FORM_TO_SHOW !== "undefined" && FORM_TO_SHOW === 'register') {
        initialSection = registerFormSection;
    } else {
        initialSection = welcomeSection;
    }

    if (welcomeSection) welcomeSection.style.display = 'none';
    if (loginFormSection) loginFormSection.style.display = 'none';
    if (registerFormSection) registerFormSection.style.display = 'none';
    if (forgotPasswordSection) forgotPasswordSection.style.display = 'none';

    if (initialSection) {
        initialSection.style.display = 'block';
        setTimeout(() => {
            initialSection.style.opacity = '1';
            initialSection.style.transform = 'translateY(0)';
        }, 50);
    }

    // -------- Registro --------
    if (registrationForm) {
        // Inicializar validación
        StartLink.setupValidation('registrationForm', {
            'registerName': ['required'],
            'registerEmail': ['required', 'email'],
            'registerPassword': ['required', { name: 'minLength', params: [8] }],
            'confirmPassword': ['required', { name: 'match', params: ['registerPassword'] }]
        });

        registrationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Si el formulario no es válido, detener
            if (registrationForm.classList.contains('is-invalid')) return;

            const nombreInput = document.getElementById('registerName');
            const emailInput = document.getElementById('registerEmail');
            const passwordInput = document.getElementById('registerPassword');
            const confirmPasswordInput = document.getElementById('confirmPassword');

            const nombre = nombreInput.value.trim();
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();
            const confirmPassword = confirmPasswordInput.value.trim();

            // Validar reCAPTCHA
            if (typeof grecaptcha === 'undefined') {
                StartLink.notify("Error: reCAPTCHA no cargado.", "danger");
                return;
            }

            const captchaResponse = grecaptcha.getResponse();
            if (!captchaResponse) {
                StartLink.notify("Por favor confirma el reCAPTCHA.", "danger");
                return;
            }

            // Mostrar loader en el botón
            const submitBtn = registrationForm.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registrando...';
            submitBtn.disabled = true;

            try {
                const response = await fetch(`${BASE_URL}src/index.php?action=register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nombre: nombre,
                        email: email,
                        password: password,
                        confirm_password: confirmPassword,
                        recaptcha_token: captchaResponse
                    })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    StartLink.notify(result.message, 'success');
                    registrationForm.reset();
                    grecaptcha.reset();
                    // Limpiar clases de validación
                    registrationForm.querySelectorAll('.is-valid').forEach(el => el.classList.remove('is-valid'));
                    setTimeout(() => {
                        changeSection(loginFormSection);
                    }, 1500);
                } else {
                    StartLink.notify(result.message, 'danger');
                    grecaptcha.reset();
                }
            } catch (error) {
                console.error('Error en registro:', error);
                StartLink.notify("Error de conexión al servidor.", "danger");
            } finally {
                submitBtn.innerHTML = originalBtnHtml;
                submitBtn.disabled = false;
            }
        });
    }

    // -------- Login --------
    if (loginForm) {
        // Inicializar validación
        StartLink.setupValidation('loginForm', {
            'loginEmail': ['required', 'email'],
            'loginPassword': ['required']
        });

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const emailInput = document.getElementById('loginEmail');
            const passwordInput = document.getElementById('loginPassword');
            
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();

            // Mostrar loader
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ingresando...';
            submitBtn.disabled = true;

            try {
                const response = await fetch(`${BASE_URL}src/index.php?action=login`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                const result = await response.json();

                if (result.status === 'success') {
                    window.location.href = result.redirect || `${BASE_URL}src/index.php?action=dashboard`;
                } else {
                    StartLink.notify(result.message, 'danger');
                }
            } catch (error) {
                StartLink.notify("Error al intentar iniciar sesión.", "danger");
            } finally {
                submitBtn.innerHTML = originalBtnHtml;
                submitBtn.disabled = false;
            }
        });
    }

    // -------- Forgot Password --------
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('forgotEmail').value.trim();
            if (!email) {
                displayMessage("Debes ingresar tu correo.", "danger");
                return;
            }
            displayMessage("Procesando solicitud...", "info");
            try {
                const response = await fetch(`${BASE_URL}src/index.php?action=forgotPassword`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const result = await response.json();
                displayMessage(result.message, result.status === 'success' ? 'success' : 'danger');
                if (result.status === 'success') forgotPasswordForm.reset();
            } catch (err) {
                console.error(err);
                displayMessage("Error al conectar con el servidor.", "danger");
            }
        });
    }

   

    // -------- Botones navegación --------
    if (showLoginFormBtn) {
        showLoginFormBtn.addEventListener('click', () => {
            changeSection(loginFormSection);
            if (messageContainer) messageContainer.innerHTML = '';
        });
    }

    if (showRegisterLink) {
        showRegisterLink.addEventListener('click', (e) => {
            e.preventDefault();
            changeSection(registerFormSection);
            if (messageContainer) messageContainer.innerHTML = '';
        });
    }

    if (showLoginLink) {
        showLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            changeSection(loginFormSection);
            if (messageContainer) messageContainer.innerHTML = '';
        });
    }

    if (showForgotPasswordLink) {
        showForgotPasswordLink.addEventListener('click', (e) => {
            e.preventDefault();
            changeSection(forgotPasswordSection);
            if (messageContainer) messageContainer.innerHTML = '';
        });
    }
});
