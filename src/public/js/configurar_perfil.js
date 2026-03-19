// src/public/js/configurar_perfil.js

// Hacer que el clic en la imagen de perfil active el input de archivo
const profilePicContainer = document.querySelector('.profile-picture-container');
if (profilePicContainer) {
    profilePicContainer.addEventListener('click', function () {
        document.getElementById('foto_perfil').click();
    });
}

// Actualizar la imagen de vista previa cuando se selecciona un nuevo archivo
const fotoPerfilInput = document.getElementById('foto_perfil');
if (fotoPerfilInput) {
    fotoPerfilInput.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.querySelector('.profile-picture').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

// Evitar clics múltiples en botones de eliminar habilidad
document.querySelectorAll('.skill-delete-btn').forEach(button => {
    button.addEventListener('click', function (e) {
        if (this.classList.contains('disabled')) {
            e.preventDefault();
            return false;
        }
        this.classList.add('disabled');
        this.style.cursor = 'not-allowed';
        this.style.opacity = '0.6';
        return true;
    });
});

// ========================================================
// Validación completa del modal "Cambiar Contraseña"
// ========================================================
(function () {
    const newPwd     = document.getElementById('new_password');
    const confirmPwd = document.getElementById('confirm_password');
    const bar        = document.getElementById('pwdStrengthBar');
    const barLabel   = document.getElementById('pwdStrengthLabel');
    const matchMsg   = document.getElementById('matchMsg');
    const btnSubmit  = document.getElementById('btnCambiarContrasena');

    if (!newPwd || !confirmPwd) return; // solo aplica en la pestaña personal

    const rules = {
        length:  { el: document.getElementById('req-length'),  test: v => v.length >= 8 },
        upper:   { el: document.getElementById('req-upper'),   test: v => /[A-Z]/.test(v) },
        lower:   { el: document.getElementById('req-lower'),   test: v => /[a-z]/.test(v) },
        number:  { el: document.getElementById('req-number'),  test: v => /[0-9]/.test(v) },
        special: { el: document.getElementById('req-special'), test: v => /[^A-Za-z0-9]/.test(v) },
    };

    function updateRule(el, pass) {
        el.className = pass ? 'text-success' : 'text-danger';
        el.querySelector('i').className = pass
            ? 'fas fa-check-circle me-1'
            : 'fas fa-times-circle me-1';
    }

    function evalStrength(v) {
        const passed = Object.values(rules).filter(r => r.test(v)).length;
        bar.style.width = (passed / 5 * 100) + '%';
        bar.className = 'progress-bar ' + (
            passed <= 2 ? 'bg-danger' : passed <= 3 ? 'bg-warning' : passed <= 4 ? 'bg-info' : 'bg-success'
        );
        barLabel.textContent = passed <= 2 ? 'Débil' : passed <= 3 ? 'Regular' : passed <= 4 ? 'Buena' : 'Fuerte';
        return passed === 5;
    }

    function canSubmit() {
        const strong = evalStrength(newPwd.value);
        const match  = newPwd.value && newPwd.value === confirmPwd.value;
        if (btnSubmit) btnSubmit.disabled = !(strong && match);
    }

    newPwd.addEventListener('input', function () {
        Object.values(rules).forEach(r => updateRule(r.el, r.test(newPwd.value)));
        evalStrength(newPwd.value);
        if (confirmPwd.value) checkMatch();
        canSubmit();
    });

    function checkMatch() {
        const ok = newPwd.value === confirmPwd.value;
        matchMsg.textContent = ok ? '✔ Las contraseñas coinciden' : '✖ Las contraseñas no coinciden';
        matchMsg.className   = ok ? 'text-success small' : 'text-danger small';
        canSubmit();
    }

    confirmPwd.addEventListener('input', checkMatch);

    // Botones mostrar / ocultar contraseña
    [['toggleNewPwd', 'new_password'], ['toggleConfirmPwd', 'confirm_password']].forEach(function ([btnId, inputId]) {
        const btn = document.getElementById(btnId);
        const inp = document.getElementById(inputId);
        if (btn && inp) {
            btn.addEventListener('click', function () {
                const show = inp.type === 'password';
                inp.type = show ? 'text' : 'password';
                btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        }
    });

    // Guardia final al enviar el formulario
    const form = document.getElementById('changePasswordForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const allPass  = Object.values(rules).every(r => r.test(newPwd.value));
            const matching = newPwd.value === confirmPwd.value;
            if (!allPass || !matching) {
                e.preventDefault();
                alert('Por favor corrige los errores antes de continuar.');
            }
        });
    }
})();