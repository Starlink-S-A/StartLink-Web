// js/form-logic.js

document.addEventListener('DOMContentLoaded', () => {
    const showLoginFormBtn = document.getElementById('showLoginFormBtn');
    const showRegisterLink = document.getElementById('showRegisterLink');
    const showLoginLink = document.getElementById('showLoginLink');

    const welcomeSection = document.getElementById('welcomeSection');
    const loginFormSection = document.getElementById('loginFormSection');
    const registerFormSection = document.getElementById('registerFormSection');

    const loginForm = document.getElementById('loginForm');
    const registrationForm = document.getElementById('registrationForm');

    const messageContainer = document.getElementById('alertMessageContainer');

    const transitionDuration = 600; // Duración de la transición en ms, debe coincidir con el CSS

    // Función para mostrar un mensaje (compatible con Bootstrap 5)
    const displayMessage = (message, type) => {
        if (!messageContainer) return;
        messageContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    };

    // Función principal para cambiar de sección con animación
    const changeSection = (sectionToShow) => {
        const sections = [welcomeSection, loginFormSection, registerFormSection];
        let currentActiveSection = null;

        // Encontrar la sección actualmente visible
        for (const section of sections) {
            if (section && section.style.display === 'block') {
                currentActiveSection = section;
                break;
            }
        }

        if (currentActiveSection && currentActiveSection !== sectionToShow) {
            // Animar la sección saliente
            currentActiveSection.style.opacity = '0';
            currentActiveSection.style.transform = 'translateY(20px)';

            setTimeout(() => {
                currentActiveSection.style.display = 'none'; // Ocultar después de la transición
                
                // Mostrar y animar la nueva sección
                sectionToShow.style.display = 'block';
                // Forzar reflow para que la transición ocurra
                void sectionToShow.offsetWidth; // eslint-disable-line no-unused-expressions
                sectionToShow.style.opacity = '1';
                sectionToShow.style.transform = 'translateY(0)';

            }, transitionDuration);
        } else if (!currentActiveSection) {
            // Si no hay ninguna sección visible (primera carga), simplemente mostrar la deseada
            sectionToShow.style.display = 'block';
            sectionToShow.style.opacity = '1';
            sectionToShow.style.transform = 'translateY(0)';
        }
    };

    // Lógica para mostrar la sección correcta al cargar la página
    const formToShowOnLoad = "<?php echo $form_to_show_on_load; ?>";
    let initialSection;
    if (formToShowOnLoad === 'login') {
        initialSection = loginFormSection;
    } else if (formToShowOnLoad === 'register') {
        initialSection = registerFormSection;
    } else {
        initialSection = welcomeSection;
    }

    // Ocultar todas las secciones al inicio para que JS las muestre limpiamente
    if (welcomeSection) welcomeSection.style.display = 'none';
    if (loginFormSection) loginFormSection.style.display = 'none';
    if (registerFormSection) registerFormSection.style.display = 'none';

    // Mostrar la sección inicial sin transición si es la primera carga
    if (initialSection) {
        initialSection.style.display = 'block';
        initialSection.style.opacity = '1';
        initialSection.style.transform = 'translateY(0)';
    }

    // Limpiar URL de parámetros si existen, para una experiencia más limpia
    if (window.location.search) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Manejador del formulario de Registro
    if (registrationForm) {
        registrationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registrationForm);

            if (formData.get('password') !== formData.get('confirm_password')) {
                displayMessage('Las contraseñas no coinciden.', 'danger');
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}src/index.php?action=login`,{
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    displayMessage(result.message, 'success');
                    registrationForm.reset();
                    changeSection(loginFormSection); // Redirigir al login
                } else {
                    displayMessage(result.message, 'danger');
                }
            } catch (error) {
                console.error('Error en el registro:', error);
                displayMessage('Hubo un problema al intentar registrarte. Inténtalo de nuevo.', 'danger');
            }
        });
    }

// Manejador del formulario de Login - Versión corregida
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(loginForm);
        try {
            const response = await fetch(`${BASE_URL}src/index.php?action=login`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            // Verificar si la respuesta es JSON válido
            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('La respuesta no es JSON válido:', text);
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
            console.error('Error en el inicio de sesión:', error);
            displayMessage('Hubo un problema al intentar iniciar sesión. Inténtalo de nuevo.', 'danger');
        }
    });
}

    // Manejadores de eventos para botones y enlaces
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

    // Advertencias si faltan elementos HTML importantes
    if (!showLoginFormBtn || !welcomeSection || !loginFormSection || !registerFormSection || !showRegisterLink || !showLoginLink || !messageContainer) {
        console.warn("Advertencia: Algunos elementos HTML necesarios no se encontraron en bienvenida.php. Las funcionalidades de cambio de formulario o mensajes podrían no operar correctamente.");
    }

    // Confirmación para eliminar experiencias
function setupExperienceDeleteButtons() {
    document.querySelectorAll('.delete-experience').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de eliminar esta experiencia laboral?')) {
                e.preventDefault();
            }
        });
    });
}

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    setupExperienceDeleteButtons();
    
    // Otro código JavaScript que ya tengas...
});
});