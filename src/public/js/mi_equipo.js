document.addEventListener('DOMContentLoaded', function () {});

function seleccionarMiEquipo(empresaId) {
    if (!empresaId) return;

    const form = document.getElementById('formSeleccionMiEquipo');
    const inputId = document.getElementById('empresa_id_input');

    if (!form || !inputId) return;

    inputId.value = empresaId;
    form.submit();
}
