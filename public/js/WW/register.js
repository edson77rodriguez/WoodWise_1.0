/*
 * Lógica de Registro de WoodWise (Refactorizado)
 * - Consolidado en un único DOMContentLoaded.
 * - Eliminada la función global togglePassword() y reemplazada por un listener no intrusivo (como en login.js).
 * - Lógica de campo condicional (Cédula) refactorizada para usar clases (d-none) en lugar de estilos inline.
 * - [BUG CORREGIDO] La lógica de Cédula ahora se ejecuta al cargar la página para manejar los valores 'old()'.
 * - [REDUNDANCIA ELIMINADA] Se quitó el listener de focus del select (manejado por CSS) y el hack de zoom de iOS (manejado por CSS).
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    /**
     * 1. Lógica de campo condicional (Cédula)
     */
    const roleSelect = document.getElementById('id_rol');
    const cedulaContainer = document.getElementById('cedula-container');
    const cedulaInput = document.getElementById('cedula');

    function handleCedulaVisibility() {
        if (!roleSelect || !cedulaContainer || !cedulaInput) return;

        try {
            const selectedRoleText = roleSelect.options[roleSelect.selectedIndex].text.trim().toLowerCase();
            
            if (selectedRoleText === 'tecnico') {
                cedulaContainer.classList.remove('d-none');
                cedulaInput.setAttribute('required', 'required'); // Validación HTML5 dinámica
            } else {
                cedulaContainer.classList.add('d-none');
                cedulaInput.removeAttribute('required');
                cedulaInput.value = ''; // Limpiar el valor si el rol cambia
            }
        } catch (e) {
            // Manejar error si el select no tiene opciones (raro)
            cedulaContainer.classList.add('d-none');
            cedulaInput.removeAttribute('required');
        }
    }

    if (roleSelect) {
        // Ejecutar al cambiar la selección
        roleSelect.addEventListener('change', handleCedulaVisibility);
        // [CORRECCIÓN DE BUG] Ejecutar una vez al cargar. Esto arregla la visibilidad
        // si la página recarga con un error de validación y "Tecnico" ya está seleccionado.
        handleCedulaVisibility();
    }


    /**
     * 2. Toggle de Visibilidad de Contraseña (No intrusivo)
     */
    const passwordToggles = document.querySelectorAll('.toggle-password');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetSelector = this.dataset.bsTarget;
            if (!targetSelector) return;

            const field = document.querySelector(targetSelector);
            if (!field) return;
            
            const icon = this.querySelector('i');
            
            if (field.type === "password") {
                field.type = "text";
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = "password";
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });


    /**
     * 3. Validación de Formularios Bootstrap
     * (Incluyendo scroll-a-inválido en móvil)
     */
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // UX Móvil: Desplazar al primer campo inválido
                if (window.innerWidth <= 768) {
                    const invalidField = form.querySelector(':invalid');
                    if (invalidField) {
                        invalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }
            form.classList.add('was-validated');
        }, false);
    });

    /* * [REDUNDANCIA ELIMINADA] El bloque 'Prevenir zoom en iOS' se eliminó. 
     * El archivo CSS ya soluciona esto correctamente con font-size: 16px.
     * [REDUNDANCIA ELIMINADA] El bloque 'Mejorar interacción del select' (listeners de focus/blur) se eliminó.
     * El archivo CSS ya define los estilos :focus para .form-select. El JS era innecesario.
     */
});