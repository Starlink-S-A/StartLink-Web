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

    // ─── Lógica del modal Editar Oferta ─────────────────────────────────────
    const modalEditarOferta = document.getElementById('modalEditarOferta');
    const formEditarOferta = document.getElementById('formEditarOferta');

    if (modalEditarOferta) {
        modalEditarOferta.addEventListener('show.bs.modal', function (event) {
            // Botón que activó el modal
            const button = event.relatedTarget;

            // Extraer información de los atributos data-*
            const id = button.getAttribute('data-id');
            const titulo = button.getAttribute('data-titulo');
            const descripcion = button.getAttribute('data-descripcion');
            const pMin = button.getAttribute('data-presupuesto-min');
            const pMax = button.getAttribute('data-presupuesto-max');
            const ubicacion = button.getAttribute('data-ubicacion');
            const modalidad = button.getAttribute('data-modalidad');
            const fechaCierre = button.getAttribute('data-fecha-cierre');
            const requisitos = button.getAttribute('data-requisitos');
            const limite = button.getAttribute('data-limite');

            // Rellenar el formulario
            document.getElementById('edit_id_oferta').value = id;
            document.getElementById('edit_titulo').value = titulo;
            document.getElementById('edit_descripcion').value = descripcion;
            document.getElementById('edit_presupuesto_min').value = pMin;
            document.getElementById('edit_presupuesto_max').value = pMax;
            document.getElementById('edit_ubicacion').value = ubicacion;
            document.getElementById('edit_modalidad').value = modalidad;
            document.getElementById('edit_fecha_cierre').value = fechaCierre;
            document.getElementById('edit_requisitos').value = requisitos;
            document.getElementById('edit_limite_postulantes').value = limite;

            // Reinicia scroll
            const modalBody = this.querySelector('.modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
        });
    }

    if (formEditarOferta) {
        formEditarOferta.addEventListener('submit', function (e) {
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

});
