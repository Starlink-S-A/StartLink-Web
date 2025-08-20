// form-transitions.js
document.addEventListener('DOMContentLoaded', function() {
    // Configuración inicial de las pestañas con transiciones
    setupTabTransitions();
    
    // Configuración de los controles de fecha
    setupDateControls();
    
    // Manejo de mensajes de alerta
    handleAlertMessages();

    setupAutoDismissAlerts();

});

function setupTabTransitions() {
    const tabLinks = document.querySelectorAll('#profileTabs a.nav-link');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href').substring(1);
            const targetPane = document.getElementById(targetId);
            const currentActivePane = document.querySelector('.tab-pane.show');
            
            // Solo animar si no es la pestaña actual
            if (!this.classList.contains('active')) {
                // Desactivar todas las pestañas
                tabLinks.forEach(tab => tab.classList.remove('active'));
                
                // Activar la pestaña clickeada
                this.classList.add('active');
                
                // Animación de transición
                if (currentActivePane) {
                    currentActivePane.classList.remove('show');
                    currentActivePane.classList.add('fade-out');
                    
                    setTimeout(() => {
                        currentActivePane.classList.remove('fade-out');
                        showNewTab(targetPane);
                    }, 500);
                } else {
                    showNewTab(targetPane);
                }
                
                // Actualizar URL
                updateUrlParam('step', targetId);
            }
        });
    });
    
    // Activar la pestaña inicial basada en el parámetro 'step'
    const urlParams = new URLSearchParams(window.location.search);
    const initialStep = urlParams.get('step') || 'personal';
    const initialTab = document.querySelector(`#profileTabs a[href="#${initialStep}"]`);
    
    if (initialTab && !initialTab.classList.contains('active')) {
        initialTab.classList.add('active');
        const initialPane = document.getElementById(initialStep);
        if (initialPane) {
            initialPane.classList.add('show');
        }
    }
}

function showNewTab(tabPane) {
    tabPane.classList.add('fade-in');
    
    setTimeout(() => {
        tabPane.classList.add('show');
        tabPane.classList.remove('fade-in');
    }, 50);
}

function setupDateControls() {
    document.querySelectorAll('#current_job, #current_study').forEach(checkbox => {
        const dateInput = checkbox.closest('.row').querySelector('input[type="date"][name*="fecha_fin"]');
        
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                dateInput.value = '';
                dateInput.setAttribute('disabled', 'disabled');
            } else {
                dateInput.removeAttribute('disabled');
            }
        });
        
        // Estado inicial
        if (checkbox.checked) {
            dateInput.value = '';
            dateInput.setAttribute('disabled', 'disabled');
        }
    });
}

// Elimina completamente la función handleAlertMessages() o reemplázala por:

function handleAlertMessages() {
    // Solo maneja el cierre de alertas
    document.querySelectorAll('.alert .btn-close').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.alert').style.transition = 'opacity 0.15s linear';
            this.closest('.alert').style.opacity = '0';
            setTimeout(() => {
                this.closest('.alert').remove();
            }, 150);
        });
    });
}
function updateUrlParam(key, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(key, value);
    window.history.pushState({ path: url.href }, '', url.href);
}

function setupAutoDismissAlerts() {
    // Seleccionar todas las alertas dismissible
    const alerts = document.querySelectorAll('.alert.alert-dismissible');
    
    alerts.forEach(alert => {
        // Configurar el temporizador para auto-cierre después de 3 segundos (3000ms)
        const autoDismissTimer = setTimeout(() => {
            dismissAlert(alert);
        }, 3000);
        
        // Configurar el botón de cierre manual
        const closeButton = alert.querySelector('.btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                clearTimeout(autoDismissTimer); // Cancelar el auto-cierre si se cierra manualmente
                dismissAlert(alert);
            });
        }
        
        // Pausar el auto-cierre cuando el mouse está sobre la alerta
        alert.addEventListener('mouseenter', () => {
            clearTimeout(autoDismissTimer);
        });
        
        // Reanudar el auto-cierre cuando el mouse sale de la alerta
        alert.addEventListener('mouseleave', () => {
            // Establecer un nuevo temporizador con el tiempo restante proporcional
            setTimeout(() => {
                dismissAlert(alert);
            }, 1000); // Dar 1 segundo adicional después de que el mouse salga
        });
    });
}

function dismissAlert(alert) {
    if (!alert) return;
    
    // Aplicar animación de desvanecimiento
    alert.style.transition = 'opacity 0.5s ease';
    alert.style.opacity = '0';
    
    // Eliminar el elemento después de la animación
    setTimeout(() => {
        if (alert && document.body.contains(alert)) {
            alert.remove();
        }
    }, 500);
}