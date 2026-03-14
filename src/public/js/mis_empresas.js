/**
 * mis_empresas.js
 * Lógica para la vista de selección de Mis Empresas
 */

document.addEventListener('DOMContentLoaded', function() {
    // Si se necesita inicializar algo al cargar la página
});

/**
 * Función llamada al hacer clic en una tarjeta de empresa.
 * Asigna el ID de la empresa al formulario oculto y lo envía.
 * 
 * @param {number} empresaId ID de la empresa seleccionada
 */
function seleccionarEmpresa(empresaId) {
    if (!empresaId) return;
    
    // Obtener los elementos ocultos
    const form = document.getElementById('formSeleccionEmpresa');
    const inputId = document.getElementById('empresa_id_input');
    
    if (form && inputId) {
        // Asignar el valor
        inputId.value = empresaId;
        
        // Enviar el formulario
        form.submit();
    } else {
        console.error('No se encontró el formulario de selección de empresa.');
    }
}
