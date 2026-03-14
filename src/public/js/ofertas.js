/**
 * ofertas.js
 * Lógica JavaScript para la vista de Ofertas de Empleo.
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─── Validación del formulario Crear Oferta ───────────────────────────────
    const formCrearOferta = document.getElementById('formCrearOferta');
    if (formCrearOferta) {
        formCrearOferta.addEventListener('submit', function (e) {
            const presupuestoMin = parseFloat(this.elements['presupuesto_min'].value);
            const presupuestoMax = parseFloat(this.elements['presupuesto_max'].value);

            if (presupuestoMin > presupuestoMax) {
                alert('El presupuesto mínimo no puede ser mayor que el máximo');
                e.preventDefault();
                return;
            }

            const fechaCierre = new Date(this.elements['fecha_cierre'].value);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (fechaCierre < hoy) {
                alert('La fecha de cierre no puede ser anterior a hoy');
                e.preventDefault();
            }
        });
    }

    // ─── Scroll en el modal: vuelve al inicio al abrir ───────────────────────
    const modalCrearOferta = document.getElementById('modalCrearOferta');
    if (modalCrearOferta) {
        modalCrearOferta.addEventListener('show.bs.modal', function () {
            // Reinicia scroll del body del modal al abrirlo
            const modalBody = this.querySelector('.modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
        });
    }

});
