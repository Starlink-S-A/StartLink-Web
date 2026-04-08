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
    const resetPasswordSection = document.getElementById('resetPasswordSection');

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
            registerFormSection, forgotPasswordSection, resetPasswordSection
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
    if (resetPasswordSection) resetPasswordSection.style.display = 'none';

    if (initialSection) {
        initialSection.style.display = 'block';
        setTimeout(() => {
            initialSection.style.opacity = '1';
            initialSection.style.transform = 'translateY(0)';
        }, 50);
    }

    // -------- Registro --------
    if (registrationForm) {
        console.log('Formulario de registro encontrado');

        registrationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Submit del formulario de registro');

            const nombreInput = document.getElementById('registerName');
            const emailInput = document.getElementById('registerEmail');
            const passwordInput = document.getElementById('registerPassword');
            let confirmPasswordInput = document.getElementById('confirmPassword');

            console.log('Campos encontrados (por ID global):', {
                nombre: !!nombreInput,
                email: !!emailInput,
                password: !!passwordInput,
                confirmPassword: !!confirmPasswordInput
            });

            if (!nombreInput || !emailInput || !passwordInput || !confirmPasswordInput) {
                console.error("❌ Campos no encontrados:", {
                    nombre: nombreInput,
                    email: emailInput,
                    password: passwordInput,
                    confirmPassword: confirmPasswordInput
                });

                const allInputs = document.querySelectorAll('input');
                console.log('Todos los inputs en la página:');
                allInputs.forEach(input => {
                    console.log('ID:', input.id, 'Name:', input.name, 'Type:', input.type);
                });

                displayMessage("Error interno: faltan campos en el formulario.", "danger");
                return;
            }

            const nombre = nombreInput.value.trim();
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();
            const confirmPassword = confirmPasswordInput.value.trim();

            console.log('Valores:', { nombre, email, password, confirmPassword });

            // Validaciones básicas
            if (!nombre) {
                displayMessage("Debes ingresar tu nombre.", "danger");
                return;
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                displayMessage("Debes ingresar un email válido.", "danger");
                return;
            }
            if (!password) {
                displayMessage("Debes ingresar una contraseña.", "danger");
                return;
            }
            if (password !== confirmPassword) {
                displayMessage("Las contraseñas no coinciden.", "danger");
                return;
            }

            // Mostrar mensaje de proceso
            displayMessage("Validando seguridad, por favor espera...", "info");

            // Obtener token de reCAPTCHA v3
            let captchaResponse;
            try {
                captchaResponse = await grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'register' });
                console.log('Token reCAPTCHA (registro) generado:', captchaResponse);
            } catch (error) {
                console.error('Error al obtener token de reCAPTCHA (registro):', error);
                displayMessage('Error de seguridad. Por favor, recarga la página e intenta nuevamente.', 'danger');
                return;
            }

            // Mostrar mensaje de proceso
            displayMessage("Registrando, por favor espera...", "info");

            try {
                console.log('Enviando datos al servidor...');
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

                console.log('Respuesta recibida:', response.status);
                const result = await response.json();
                console.log('Resultado:', result);

                if (result.status === 'success') {
                    displayMessage(result.message, 'success');
                    registrationForm.reset();
                    setTimeout(() => {
                        changeSection(loginFormSection);
                    }, 1500);
                } else {
                    displayMessage(result.message, 'danger');
                }
            } catch (err) {
                console.error("Error en registro:", err);
                displayMessage("Error al conectar con el servidor.", "danger");
            }
        });
    }

    // -------- Login --------
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Obtener token de reCAPTCHA
            let recaptchaToken;
            try {
                recaptchaToken = await grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'login' });
                console.log('Token reCAPTCHA generado:', recaptchaToken);
            } catch (error) {
                console.error('Error al obtener token de reCAPTCHA:', error);
                displayMessage('Error de seguridad. Por favor, recarga la página e intenta nuevamente.', 'danger');
                return;
            }

            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            try {
                const response = await fetch("src/index.php?action=login", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password,
                        recaptcha_token: recaptchaToken
                    })
                });

                const text = await response.text();
                console.log('Respuesta del servidor (texto):', text);

                let result;
                try {
                    result = JSON.parse(text);
                    console.log('Respuesta del servidor (JSON):', result);
                } catch (e) {
                    console.error('Respuesta inválida:', text);
                    throw new Error('Respuesta del servidor no válida');
                }

                if (result.status === 'success') {
                    displayMessage(result.message, 'success');
                    loginForm.reset();
                    localStorage.setItem('token', result.token);

                    // Redirect to dashboard
                    window.location.href = (result.data && result.data.redirect)
                        ? result.data.redirect
                        : `${BASE_URL}src/views/dashboardView/dashboard.php`;
                } else {
                    displayMessage(result.message, 'danger');
                }
            } catch (error) {
                console.error('Error en el inicio de sesión:', error);
                displayMessage('Hubo un problema al intentar iniciar sesión. Inténtalo de nuevo.', 'danger');
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
                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error("Respuesta no es JSON:", text);
                    displayMessage("Error del servidor: la respuesta no es válida.", "danger");
                    return;
                }

                displayMessage(result.message, result.status === 'success' ? 'success' : 'danger');
                if (result.status === 'success') {
                    forgotPasswordForm.reset();
                    setTimeout(() => changeSection(resetPasswordSection), 1500);
                }
            } catch (err) {
                console.error("Error en recuperación:", err);
                displayMessage("Error al conectar con el servidor.", "danger");
            }
        });
    }

    // -------- Reset Password --------
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const token = document.getElementById('resetToken').value.trim();
            const newPassword = document.getElementById('resetNewPassword').value.trim();
            const confirmPassword = document.getElementById('confirmResetPassword').value.trim();

            if (!token || !newPassword || !confirmPassword) {
                displayMessage("Todos los campos son obligatorios.", "danger");
                return;
            }

            // Validaciones de contraseña 
            if (newPassword !== confirmPassword) {
                displayMessage("Las contraseñas no coinciden.", "danger");
                return;
            }
            if (newPassword.length < 8) {
                displayMessage("La contraseña debe tener al menos 8 caracteres.", "danger");
                return;
            }
            if (newPassword.length > 20) {
                displayMessage("La contraseña no debe superar los 20 caracteres.", "danger");
                return;
            }
            if (!/[0-9]/.test(newPassword)) {
                displayMessage("La contraseña debe contener al menos un número.", "danger");
                return;
            }
            if (!/[\W_]/.test(newPassword)) {
                displayMessage("La contraseña debe contener al menos un carácter especial.", "danger");
                return;
            }

            displayMessage("Procesando...", "info");
            try {
                const response = await fetch(`${BASE_URL}src/index.php?action=resetPassword`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ token: token, new_password: newPassword })
                });
                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error("Respuesta no es JSON:", text);
                    displayMessage("Error del servidor: la respuesta no es válida.", "danger");
                    return;
                }

                if (result.status === 'success') {
                    displayMessage(result.message, 'success');
                    resetPasswordForm.reset();
                    setTimeout(() => changeSection(loginFormSection), 1500);
                } else {
                    displayMessage(result.message, 'danger');
                }
            } catch (err) {
                console.error("Error en reseteo:", err);
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

    const backToLoginFromResetLink = document.getElementById('backToLoginFromResetLink');
    if (backToLoginFromResetLink) {
        backToLoginFromResetLink.addEventListener('click', (e) => {
            e.preventDefault();
            changeSection(loginFormSection);
            if (messageContainer) messageContainer.innerHTML = '';
        });
    }

    const backToLoginLink = document.getElementById('backToLoginLink');
    if (backToLoginLink) {
        backToLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            changeSection(loginFormSection);
            if (messageContainer) messageContainer.innerHTML = '';
        });
    }
});
