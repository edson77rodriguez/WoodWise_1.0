/*
 * SIGMAD — Layout Logic
 * FIXES:
 *  - toggler.click() corregido (era sintaxis Markdown inválida)
 *  - Scroll activo en TODOS los viewports, no solo móvil
 *  - Menú no se oculta si el collapse está abierto
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const navbar   = document.querySelector('.navbar');
    const toggler  = document.querySelector('.navbar-toggler');
    const collapse = document.querySelector('.navbar-collapse');

    if (!navbar) return;

    let lastScroll = 0;
    let ticking    = false;

    /* ── 1. Scroll: clases .is-at-top / .is-hidden ─────────────── */
    function handleScrollClassToggle() {
        const currentScroll = window.scrollY;

        // En la cima → sin sombra extra
        if (currentScroll <= 50) {
            navbar.classList.add('is-at-top');
            navbar.classList.remove('is-hidden');
        } else {
            navbar.classList.remove('is-at-top');

            // Bajando y lejos de la cima → ocultar (solo si el menú está cerrado)
            const menuOpen = collapse && collapse.classList.contains('show');
            if (currentScroll > lastScroll && currentScroll > 120 && !menuOpen) {
                navbar.classList.add('is-hidden');
            } else if (currentScroll < lastScroll) {
                navbar.classList.remove('is-hidden');
            }
        }

        lastScroll = Math.max(currentScroll, 0);
        ticking    = false;
    }

    // Usar rAF para no bloquear el hilo principal
    window.addEventListener('scroll', function () {
        if (!ticking) {
            requestAnimationFrame(handleScrollClassToggle);
            ticking = true;
        }
    }, { passive: true });

    // Estado inicial al cargar
    handleScrollClassToggle();

    /* ── 2. UX Móvil: cerrar menú al tocar un enlace ───────────── */
    function setupMobileMenuCloseOnClick() {
        if (!collapse || !toggler) return;

        collapse.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                // Solo actuar si el toggler es visible (vista móvil)
                if (window.getComputedStyle(toggler).display !== 'none') {
                    toggler.click(); // ✅ JS correcto — antes era [toggler.click](http://...) (Markdown)
                }
            });
        });
    }

    setupMobileMenuCloseOnClick();

    /* ── 3. Fade-in al scroll (IntersectionObserver) ───────────── */
    const fadeEls = document.querySelectorAll('.fade-in');
    if (fadeEls.length > 0) {
        const observer = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('visible');
                obs.unobserve(entry.target);
            });
        }, { threshold: 0.1 });

        fadeEls.forEach(function (el) { observer.observe(el); });
    }
});