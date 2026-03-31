/**
 * StartLink Global JS & Form Validation System
 * -------------------------------------------
 * Este archivo centraliza las utilidades comunes y el sistema de validación
 * de formularios para todo el proyecto.
 */

const StartLink = (function() {
    'use strict';

    // Configuración base
    const config = {
        baseUrl: typeof BASE_URL !== 'undefined' ? BASE_URL : '/',
        animationDuration: 300,
        validationClasses: {
            error: 'is-invalid',
            success: 'is-valid',
            feedback: 'invalid-feedback'
        }
    };

    /**
     * Sistema de Notificaciones (Alertas)
     */
    const notify = (message, type = 'info', containerId = 'alertMessageContainer') => {
        const container = document.getElementById(containerId);
        if (!container) return;

        const icon = {
            success: 'fa-check-circle',
            danger: 'fa-exclamation-triangle',
            warning: 'fa-exclamation-circle',
            info: 'fa-info-circle'
        }[type] || 'fa-info-circle';

        const html = `
            <div class="alert alert-${type} alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        container.innerHTML = html;
    };

    /**
     * Validaciones de Formularios
     */
    const validators = {
        required: (value) => value.trim().length > 0 || 'Este campo es obligatorio.',
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || 'Ingresa un correo electrónico válido.',
        minLength: (min) => (value) => value.length >= min || `Mínimo ${min} caracteres.`,
        match: (otherId, label) => (value) => {
            const other = document.getElementById(otherId);
            return (other && value === other.value) || `Las contraseñas no coinciden.`;
        },
        password: (value) => {
            const hasUpper = /[A-Z]/.test(value);
            const hasLower = /[a-z]/.test(value);
            const hasNumber = /[0-9]/.test(value);
            const hasSpecial = /[^A-Za-z0-9]/.test(value);
            const hasLength = value.length >= 8;
            
            if (!hasLength) return 'Mínimo 8 caracteres.';
            if (!hasUpper || !hasLower || !hasNumber || !hasSpecial) return 'Debe incluir mayúsculas, minúsculas, números y símbolos.';
            return true;
        }
    };

    /**
     * Inicializar validación automática en un formulario
     * @param {string} formId ID del formulario
     * @param {Object} rules Reglas de validación { fieldId: ['required', 'email'] }
     */
    const setupValidation = (formId, rules) => {
        const form = document.getElementById(formId);
        if (!form) return;

        // Desactivar validación nativa del navegador
        form.setAttribute('novalidate', '');

        const validateField = (fieldId, fieldRules) => {
            const field = document.getElementById(fieldId);
            if (!field) return true;

            let isValid = true;
            let errorMessage = '';

            for (const rule of fieldRules) {
                let ruleFn, ruleParams = [];
                
                if (typeof rule === 'string') {
                    ruleFn = validators[rule];
                } else if (typeof rule === 'object' && rule.name) {
                    ruleFn = validators[rule.name](...rule.params);
                }

                if (ruleFn) {
                    const result = ruleFn(field.value);
                    if (result !== true) {
                        isValid = false;
                        errorMessage = result;
                        break;
                    }
                }
            }

            // Aplicar clases visuales
            field.classList.toggle(config.validationClasses.error, !isValid);
            field.classList.toggle(config.validationClasses.success, isValid);

            // Mostrar/Ocultar mensaje de error
            let feedback = field.parentNode.querySelector(`.${config.validationClasses.feedback}`);
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = config.validationClasses.feedback;
                field.parentNode.appendChild(feedback);
            }
            feedback.textContent = errorMessage;

            return isValid;
        };

        // Validar en tiempo real (blur)
        Object.keys(rules).forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('blur', () => validateField(fieldId, rules[fieldId]));
                field.addEventListener('input', () => {
                    if (field.classList.contains(config.validationClasses.error)) {
                        validateField(fieldId, rules[fieldId]);
                    }
                });
            }
        });

        // Validar al enviar
        form.addEventListener('submit', (e) => {
            let formIsValid = true;
            Object.keys(rules).forEach(fieldId => {
                if (!validateField(fieldId, rules[fieldId])) {
                    formIsValid = false;
                }
            });

            if (!formIsValid) {
                e.preventDefault();
                e.stopPropagation();
                notify('Por favor, corrige los errores en el formulario.', 'danger');
            }
        });
    };

    /**
     * Utilidades de UI
     */
    const ui = {
        showLoader: (btnId) => {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando...';
                btn.disabled = true;
            }
        },
        hideLoader: (btnId) => {
            const btn = document.getElementById(btnId);
            if (btn && btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
                btn.disabled = false;
            }
        },
        togglePassword: (btnId, inputId) => {
            const btn = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            if (btn && input) {
                btn.addEventListener('click', () => {
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    btn.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
                });
            }
        }
    };

    // API Pública
    return {
        config,
        notify,
        validators,
        setupValidation,
        ui
    };
})();
