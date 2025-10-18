/*
 * WoodWise Layout Logic (Refactorizado)
 *
 * 1. Eliminada toda la manipulación de estilos inline (navbar.style.transform/boxShadow).
 * La lógica ahora solo alterna clases CSS (.is-at-top, .is-hidden) para un rendimiento declarativo.
 * 2. Eliminada la reimplementación manual del colapso de Bootstrap. El bundle de BS5 ya maneja esto.
 * 3. Mantenida la UX de "cerrar menú al hacer clic" pero refactorizada para disparar el toggler nativo de BS.
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const navbar = document.querySelector('.navbar');
    const toggler = document.querySelector('.navbar-toggler');
    if (!navbar) return; // Salir si el navbar no existe

    let lastScroll = 0;

    /**
     * Alterna clases en el Navbar basado en la posición y dirección del scroll.
     * El CSS maneja toda la animación y estilos.
     */
    function handleScrollClassToggle() {
        const currentScroll = window.scrollY;

        // 1. En la parte superior (is-at-top)
        // Añade/quita esta clase para que el CSS pueda eliminar el box-shadow.
        if (currentScroll <= 50) {
            navbar.classList.add('is-at-top');
        } else {
            navbar.classList.remove('is-at-top');
        }

        // 2. Ocultar al bajar (is-hidden)
        // Solo oculta si estamos lejos de la parte superior (más de 100px) y bajando.
        if (currentScroll > lastScroll && currentScroll > 100) {
            navbar.classList.add('is-hidden'); // CSS: transform: translateY(-100%)
        } else if (currentScroll < lastScroll) {
            navbar.classList.remove('is-hidden'); // CSS: transform: translateY(0)
        }

        lastScroll = currentScroll <= 0 ? 0 : currentScroll; // Maneja el rebote en iOS
    }

    /**
     * Mejora de UX Móvil: Cierra el menú de Bootstrap nativo al hacer clic en un enlace.
     */
    function setupMobileMenuCloseOnClick() {
        const collapseElement = document.querySelector('.navbar-collapse');
        if (!collapseElement || !toggler) return;

        const navLinks = collapseElement.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                // Solo cerramos si el menú está abierto (visible en modo móvil)
                // Usamos getComputedStyle porque el toggler puede estar d-none en desktop.
                if (window.getComputedStyle(toggler).display !== 'none') {
                    // Disparamos un clic en el toggler para cerrar el menú.
                    // Esto permite que el gestor de colapso nativo de Bootstrap maneje la animación y el estado ARIA.
                    toggler.click();
                }
            });
        });
    }


    /**
     * Lógica de inicialización y Resize.
     * Añade/quita el listener de scroll para optimizar el rendimiento.
     */
    let isMobileView = window.innerWidth < 992;

    if (isMobileView) {
        window.addEventListener('scroll', handleScrollClassToggle);
    }
    setupMobileMenuCloseOnClick();

    // Listener de Resize para gestionar el estado
    window.addEventListener('resize', () => {
        const isNowMobile = window.innerWidth < 992;
        
        if (!isNowMobile && isMobileView) {
            // Transición de Móvil a Desktop
            window.removeEventListener('scroll', handleScrollClassToggle);
            // Limpiamos las clases de estado móvil del navbar
            navbar.classList.remove('is-hidden', 'is-at-top');
        } else if (isNowMobile && !isMobileView) {
            // Transición de Desktop a Móvil
            window.addEventListener('scroll', handleScrollClassToggle);
        }
        
        isMobileView = isNowMobile;
    });
});