// src/public/js/configurar_perfil.js

// Hacer que el clic en la imagen de perfil active el input de archivo
document.querySelector('.profile-picture-container').addEventListener('click', function() {
    document.getElementById('foto_perfil').click();
});

// Actualizar la imagen de vista previa cuando se selecciona un nuevo archivo
document.getElementById('foto_perfil').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('.profile-picture').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Evitar clics múltiples en botones de eliminar habilidad
document.querySelectorAll('.skill-delete-btn').forEach(button => {
    button.addEventListener('click', function(e) {
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

// Validaciones client-side para el formulario de cambio de contraseña
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('confirm_password').value;

    if (newPass !== confirmPass) {
        alert('Las contraseñas no coinciden.');
        e.preventDefault();
        return;
    }

    if (newPass.length < 8) {
        alert('La contraseña debe tener al menos 8 caracteres.');
        e.preventDefault();
        return;
    }
});

// Aquí puedes agregar más funcionalidades futuras para el frontend del formulario