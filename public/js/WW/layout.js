/*
 * SIGMAD — Layout Logic
 * - Navbar scroll hide/show con rAF
 * - Menú móvil: toggler.click() corregido
 * - User dropdown: modal overlay profesional, sin bugs de posición
 * - Fade-in con IntersectionObserver
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const navbar   = document.querySelector('.navbar');
    const toggler  = document.querySelector('.navbar-toggler');
    const collapse = document.querySelector('.navbar-collapse');

    if (!navbar) return;

    let lastScroll = 0;
    let ticking    = false;

    /* ════════════════════════════════════════════════════════════
       1. SCROLL — Navbar hide/show
    ════════════════════════════════════════════════════════════ */
    function handleScroll() {
        const cur = window.scrollY;

        if (cur <= 50) {
            navbar.classList.add('is-at-top');
            navbar.classList.remove('is-hidden');
        } else {
            navbar.classList.remove('is-at-top');
            const menuOpen = collapse && collapse.classList.contains('show');
            if (cur > lastScroll && cur > 120 && !menuOpen) {
                navbar.classList.add('is-hidden');
            } else if (cur < lastScroll) {
                navbar.classList.remove('is-hidden');
            }
        }

        lastScroll = Math.max(cur, 0);
        ticking    = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) { requestAnimationFrame(handleScroll); ticking = true; }
    }, { passive: true });

    handleScroll(); // estado inicial

    /* ════════════════════════════════════════════════════════════
       2. MENÚ MÓVIL — cerrar al tocar un enlace
    ════════════════════════════════════════════════════════════ */
    if (collapse && toggler) {
        collapse.querySelectorAll('.nav-link').forEach(function (link) {
            if (link.closest('.user-menu-wrapper')) return; // no cerrar al abrir dropdown
            link.addEventListener('click', function () {
                if (window.getComputedStyle(toggler).display !== 'none') {
                    toggler.click(); // ✅ JS correcto
                }
            });
        });
    }

    /* ════════════════════════════════════════════════════════════
       3. FADE-IN — IntersectionObserver
    ════════════════════════════════════════════════════════════ */
    const fadeEls = document.querySelectorAll('.fade-in');
    if (fadeEls.length) {
        const io = new IntersectionObserver(function (entries, obs) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                e.target.classList.add('visible');
                obs.unobserve(e.target);
            });
        }, { threshold: 0.1 });
        fadeEls.forEach(function (el) { io.observe(el); });
    }

    /* ════════════════════════════════════════════════════════════
       4. USER DROPDOWN — Modal overlay profesional
       - Se abre como modal centrado sobre un backdrop
       - NO usa position:fixed con top calculado (eso era el bug)
       - NO modifica el layout del navbar en ningún momento
       - Cierra con Escape, click en backdrop, botón X
    ════════════════════════════════════════════════════════════ */
    (function setupUserDropdown() {
        const trigger  = document.getElementById('userMenuBtn');
        const dropdown = document.getElementById('userDropdown');
        if (!trigger || !dropdown) return;

        /* ── Crear backdrop ──────────────────────────────────── */
        const backdrop = document.createElement('div');
        backdrop.id = 'userMenuBackdrop';
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.appendChild(backdrop);

        /* ── Mover el dropdown al body para evitar clipping ─── */
        document.body.appendChild(dropdown);

        /* ── Estado ─────────────────────────────────────────── */
        let isOpen = false;

        function openMenu() {
            if (isOpen) return;
            isOpen = true;

            // Prevenir scroll del body sin quitar el espacio (evita layout shift)
            const scrollW = window.innerWidth - document.documentElement.clientWidth;
            document.body.style.overflow   = 'hidden';
            document.body.style.paddingRight = scrollW + 'px';

            backdrop.classList.add('show');
            dropdown.classList.add('show');
            trigger.setAttribute('aria-expanded', 'true');
            trigger.classList.add('active');

            // Foco al primer elemento interactivo
            setTimeout(function () {
                const first = dropdown.querySelector('a, button:not(.user-dropdown-close)');
                if (first) first.focus();
            }, 180);
        }

        function closeMenu(returnFocus) {
            if (!isOpen) return;
            isOpen = false;

            backdrop.classList.remove('show');
            dropdown.classList.remove('show');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.classList.remove('active');

            document.body.style.overflow    = '';
            document.body.style.paddingRight = '';

            if (returnFocus !== false) trigger.focus();
        }

        /* ── Trigger click ───────────────────────────────────── */
        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            isOpen ? closeMenu() : openMenu();
        });
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.setAttribute('aria-expanded', 'false');

        /* ── Backdrop click → cerrar ─────────────────────────── */
        backdrop.addEventListener('click', function () { closeMenu(); });

        /* ── Botón X dentro del modal ────────────────────────── */
        const closeBtn = dropdown.querySelector('.user-dropdown-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                closeMenu();
            });
        }

        /* ── Escape ──────────────────────────────────────────── */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) closeMenu();
        });

        /* ── Click en items → cerrar ─────────────────────────── */
        dropdown.querySelectorAll('a.dropdown-item, button.dropdown-item').forEach(function (item) {
            // No cerrar en items "próximamente" (no tienen acción real)
            if (item.hasAttribute('data-bs-toggle')) return;
            item.addEventListener('click', function () {
                // Para el logout dejamos que el form se envíe naturalmente
                if (!item.closest('.dropdown-form')) closeMenu(false);
            });
        });

        /* ── Trap focus dentro del modal (accesibilidad) ─────── */
        dropdown.addEventListener('keydown', function (e) {
            if (e.key !== 'Tab') return;
            const focusable = Array.from(
                dropdown.querySelectorAll('a, button:not([disabled]), [tabindex]:not([tabindex="-1"])')
            ).filter(function (el) { return !el.closest('[hidden]'); });
            if (!focusable.length) { e.preventDefault(); return; }

            const first = focusable[0];
            const last  = focusable[focusable.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault(); last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault(); first.focus();
            }
        });

        /* ── Si el menú móvil se cierra, también cerrar dropdown */
        if (collapse) {
            collapse.addEventListener('hidden.bs.collapse', function () {
                closeMenu(false);
            });
        }
    })();

});