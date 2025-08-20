document.addEventListener('DOMContentLoaded', () => {
    // Elementos clave
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

    // 🔁 Función para mostrar mensajes
    const displayMessage = (message, type) => {
        if (!messageContainer) return;
        messageContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    };

    // 🔄 Transición entre secciones
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

    // 🧭 Mostrar sección inicial
    const formToShowOnLoad = typeof FORM_TO_SHOW !== 'undefined' ? FORM_TO_SHOW : 'welcome';
    let initialSection = welcomeSection;
    if (formToShowOnLoad === 'login') initialSection = loginFormSection;
    if (formToShowOnLoad === 'register') initialSection = registerFormSection;

    [welcomeSection, loginFormSection, registerFormSection].forEach(section => {
        if (section) section.style.display = 'none';
    });

    if (initialSection) {
        initialSection.style.display = 'block';
        initialSection.style.opacity = '1';
        initialSection.style.transform = 'translateY(0)';
    }

    // 🧹 Limpiar URL
    if (window.location.search) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // 📝 Registro
    if (registrationForm) {
        registrationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registrationForm);

            if (formData.get('password') !== formData.get('confirm_password')) {
                displayMessage('Las contraseñas no coinciden.', 'danger');
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}src/index.php?action=register`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (result.success) {
                    displayMessage(result.message, 'success');
                    registrationForm.reset();
                    changeSection(loginFormSection);
                } else {
                    displayMessage(result.message, 'danger');
                    if (result.redirect === 'login') {
                        changeSection(loginFormSection); // ← Redirige al login si el correo ya existe
                    }
}
            } catch (error) {
                console.error('Error en el registro:', error);
                displayMessage('Hubo un problema al intentar registrarte. Inténtalo de nuevo.', 'danger');
            }
        });
    }

    // 🔐 Login
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
                    console.error('Respuesta no válida:', text);
                    throw new Error('Respuesta del servidor no válida');
                }

                if (result.success) {
                    displayMessage(result.message, 'success');
                    loginForm.reset();
                    window.location.href = result.redirect || `${BASE_URL}src/dashboard.php`;
                } else {
                    displayMessage(result.message, 'danger');
                }
            } catch (error) {
                console.error('Error en el login:', error);
                displayMessage('Hubo un problema al intentar iniciar sesión. Inténtalo de nuevo.', 'danger');
            }
        });
    }

    // 🎯 Eventos de navegación
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

    // 🗑️ Confirmación para eliminar experiencias
    function setupExperienceDeleteButtons() {
        document.querySelectorAll('.delete-experience').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('¿Estás seguro de eliminar esta experiencia laboral?')) {
                    e.preventDefault();
                }
            });
        });
    }

    setupExperienceDeleteButtons();

    // 🛑 Advertencias si faltan elementos
    if (!showLoginFormBtn || !welcomeSection || !loginFormSection || !registerFormSection || !showRegisterLink || !showLoginLink || !messageContainer) {
        console.warn("Advertencia: Algunos elementos HTML necesarios no se encontraron. Las funcionalidades podrían no operar correctamente.");
    }
});