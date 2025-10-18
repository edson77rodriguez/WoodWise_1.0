/*
 * Lógica del Dashboard de WoodWise (Refactorizado)
 * - Eliminada toda la lógica de 'collapsed desktop' (perteneciente a un tema CSS conflictivo).
 * - Eliminada la función 'setupHoverEffects()' (redundante, el CSS maneja :hover).
 * - Eliminada la función 'checkFontAwesome()' (redundante, el layout ya carga FA).
 * - Eliminada toda la manipulación de estilos inline.
 * - El script ahora se enfoca únicamente en su trabajo: manejar el sidebar móvil.
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // --- Elementos del DOM para el Sidebar Móvil ---
    const sidebar = document.getElementById('sidenav-main');
    const toggler = document.getElementById('sidenav-toggler');
    const closeBtn = document.getElementById('sidenav-close');
    const overlay = document.querySelector('.sidebar-overlay');
    const navLinks = document.querySelectorAll('.sidenav-inner .nav-link');

    // Salir si los elementos esenciales no existen
    if (!sidebar || !toggler || !closeBtn || !overlay) {
        console.error('Elementos del sidebar no encontrados. La funcionalidad móvil puede estar rota.');
        return;
    }

    /**
     * Abre el sidebar móvil y el overlay, y bloquea el scroll del body.
     */
    const openSidenav = () => {
        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden'; // Previene el scroll del fondo
    };

    /**
     * Cierra el sidebar móvil y el overlay, y restaura el scroll del body.
     */
    const closeSidenav = () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = ''; // Restaura el scroll
    };

    // --- Asignación de Listeners de Eventos ---

    // 1. Abrir con el botón "Hamburguesa"
    toggler.addEventListener('click', openSidenav);

    // 2. Cerrar con el botón "X" interno
    closeBtn.addEventListener('click', closeSidenav);

    // 3. Cerrar haciendo clic en el overlay oscuro
    overlay.addEventListener('click', closeSidenav);

    // 4. [UX] Cerrar el menú móvil al hacer clic en un enlace
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1199.98) { // Asegurarse de que solo ocurra en vista móvil
                closeSidenav();
            }
        });
    });

    // 5. Limpiar estado al redimensionar a desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1199.98) { // Breakpoint 'lg' (o el que use tu CSS)
            // Si el usuario redimensiona a desktop, forzar el cierre del modo móvil.
            if (sidebar.classList.contains('show')) {
                closeSidenav();
            }
        }
    });

});