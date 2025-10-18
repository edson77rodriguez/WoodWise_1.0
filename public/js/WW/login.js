/*
 * Lógica de Login de WoodWise (Refactorizado)
 * - Consolidado en un único DOMContentLoaded.
 * - Eliminada la función global togglePassword() y reemplazada por un listener de JS no intrusivo.
 * - Eliminado hack anti-zoom de iOS (solucionado en CSS).
 * - Eliminada la manipulación de estilo inline (min-height: 44px) (movido a CSS).
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    /**
     * 1. Lógica de Validación de Formularios (Incluyendo scroll-a-inválido en móvil)
     */
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // UX Móvil: Desplazar al primer campo inválido
                if (window.innerWidth <= 576) {
                    const invalidField = form.querySelector(':invalid');
                    if (invalidField) {
                        invalidField.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                }
            }
            form.classList.add('was-validated');
        }, false);
    });


    /**
     * 2. Toggle de Password (No intrusivo)
     * Adjunta listener a cualquier elemento con la clase .toggle-password
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
            
            // Opcional: Devolver el foco al campo
            field.focus();
        });
    });


    /**
     * 3. Efecto 3D del Logo (Parallax)
     */
    const logoContainer = document.querySelector('.logo-3d-container');
    if (logoContainer) {
        
        // Efecto Parallax con mouse
        document.body.addEventListener('mousemove', function(e) {
            if (window.innerWidth > 992) { // Solo en desktop (LG+)
                const xAxis = (window.innerWidth / 2 - e.pageX) / 25; // Reducida intensidad
                const yAxis = (window.innerHeight / 2 - e.pageY) / 25; // Reducida intensidad
                logoContainer.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
            }
        });
        
        // Resetear posición
        const cardHeader = document.querySelector('.card-header');
        if (cardHeader) {
             cardHeader.addEventListener('mouseleave', function() {
                 logoContainer.style.transform = 'rotateY(0) rotateX(0)';
             });
        }
        
        // Efecto de clic (Pulso)
        logoContainer.addEventListener('click', function() {
            this.classList.add('clicked');
            setTimeout(() => {
                this.classList.remove('clicked');
            }, 500); // Duración de la animación de pulso en CSS
        });
    }

});