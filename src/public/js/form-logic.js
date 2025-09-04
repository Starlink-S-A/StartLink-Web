// js/form-logic.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM cargado - Iniciando form-logic.js'); // DEBUG
    
    const showLoginFormBtn = document.getElementById('showLoginFormBtn');
    const showRegisterLink = document.getElementById('showRegisterLink');
    const showLoginLink = document.getElementById('showLoginLink');

    const welcomeSection = document.getElementById('welcomeSection');
    const loginFormSection = document.getElementById('loginFormSection');
    const registerFormSection = document.getElementById('registerFormSection');

    const loginForm = document.getElementById('loginForm');
    const registrationForm = document.getElementById('registrationForm');

    const messageContainer = document.getElementById('alertMessageContainer');

    const transitionDuration = 600;

    // Función para mostrar mensajes
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

    // Cambiar sección con animación
    const changeSection = (sectionToShow) => {
        const sections = [welcomeSection, loginFormSection, registerFormSection];
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

    // ✅ VERIFICACIÓN DE ELEMENTOS CON DEBUG
    console.log('Elementos encontrados:', {
        showLoginFormBtn: !!showLoginFormBtn,
        showRegisterLink: !!showRegisterLink,
        showLoginLink: !!showLoginLink,
        welcomeSection: !!welcomeSection,
        loginFormSection: !!loginFormSection,
        registerFormSection: !!registerFormSection,
        loginForm: !!loginForm,
        registrationForm: !!registrationForm,
        messageContainer: !!messageContainer
    });

    // Sección inicial
    let initialSection;
    if (typeof FORM_TO_SHOW !== "undefined" && FORM_TO_SHOW === 'login') {
        initialSection = loginFormSection;
    } else if (typeof FORM_TO_SHOW !== "undefined" && FORM_TO_SHOW === 'register') {
        initialSection = registerFormSection;
    } else {
        initialSection = welcomeSection;
    }

    // Ocultar todas las secciones primero
    if (welcomeSection) welcomeSection.style.display = 'none';
    if (loginFormSection) loginFormSection.style.display = 'none';
    if (registerFormSection) registerFormSection.style.display = 'none';

    // Mostrar la sección inicial
    if (initialSection) {
        initialSection.style.display = 'block';
        setTimeout(() => {
            initialSection.style.opacity = '1';
            initialSection.style.transform = 'translateY(0)';
        }, 50);
    }

    // -------- Registro --------
// -------- Registro --------
if (registrationForm) {
    registrationForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const nombre = document.getElementById('registerName').value.trim();
        const email = document.getElementById('registerEmail').value.trim();
        const password = document.getElementById('registerPassword').value.trim();
        const confirmPassword = document.querySelector('[name="confirm_password"]').value.trim();

        // Validaciones básicas
        if (!nombre || !email || !password || !confirmPassword) {
            displayMessage("Por favor llena todos los campos.", "danger");
            return;
        }

        if (password !== confirmPassword) {
            displayMessage("Las contraseñas no coinciden.", "danger");
            return;
        }

        // Validar reCAPTCHA
        if (typeof grecaptcha === 'undefined') {
            displayMessage("Error: reCAPTCHA no cargado.", "danger");
            return;
        }

        const captchaResponse = grecaptcha.getResponse();
        if (!captchaResponse) {
            displayMessage("Por favor confirma el reCAPTCHA.", "danger");
            return;
        }

        // Mostrar mensaje de proceso
        displayMessage("Registrando, por favor espera...", "info");

        try {
            const res = await fetch(`${BASE_URL}src/index.php?action=register`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    nombre,
                    email,
                    password,
                    confirm_password: confirmPassword,
                    recaptcha_token: captchaResponse
                })
            });

            const result = await res.json();
            console.log('Respuesta del servidor:', result);

            if (result.status === 'success') {
                displayMessage(result.message, 'success');
                registrationForm.reset();
                grecaptcha.reset();
                setTimeout(() => changeSection(loginFormSection), 1500);
            } else {
                displayMessage(result.message || "Error en el registro.", 'danger');
                grecaptcha.reset();
            }
        } catch (err) {
            console.error("Error en registro:", err);
            displayMessage("Error al conectar con el servidor.", "danger");
            grecaptcha.reset();
        }
    });
}






    // -------- Login --------
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            try {
                const response = await fetch(`${BASE_URL}src/index.php?action=login`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Respuesta inválida:', text);
                    throw new Error('Respuesta del servidor no válida');
                }

                if (result.status === 'success') {
                    displayMessage(result.message, 'success');
                    loginForm.reset();
                    window.location.href = (result.data && result.data.redirect) 
                        ? result.data.redirect 
                        : `${BASE_URL}src/dashboard.php`;
                } else {
                    displayMessage(result.message, 'danger');
                }
            } catch (error) {
                console.error('Error en el inicio de sesión:', error);
                displayMessage('Hubo un problema al intentar iniciar sesión. Inténtalo de nuevo.', 'danger');
            }
        });
    }

    // -------- Botones de navegación --------
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

    // Verificación final
    const elements = {
        showLoginFormBtn, showRegisterLink, showLoginLink,
        welcomeSection, loginFormSection, registerFormSection,
        loginForm, registrationForm, messageContainer
    };
    
    const missingElements = Object.entries(elements)
        .filter(([key, value]) => !value)
        .map(([key]) => key);
    
    if (missingElements.length > 0) {
        console.warn("Advertencia: faltan elementos HTML:", missingElements);
    }
});