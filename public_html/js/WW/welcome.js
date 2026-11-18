/*
 * WoodWise Welcome Page Logic
 * Refactorizado a un módulo cohesivo.
 * Se eliminaron todos los listeners duplicados, declaraciones de variables globales,
 * y el sistema manual de partículas DOM que entraba en conflicto con la librería Particles.js (Canvas).
 */
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const navbar = document.getElementById('mainNavbar');
    const video = document.getElementById('bg-video'); // Usado solo para la comprobación 'if'. La lógica se mueve a CSS.
    const particleContainerID = 'particles-js';
    const carousel = document.getElementById('featuresCarousel');

    /**
     * 1. Lógica del Navbar Scroll
     * Añade/quita clases al navbar basado en la posición de scroll.
     * La manipulación de la opacidad del video se ha movido enteramente a CSS para mejor rendimiento.
     */
    function handleNavbarScroll() {
        if (!navbar) return;
        
        if (window.scrollY > 100) {
            navbar.classList.remove('navbar-initial');
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.add('navbar-initial');
            navbar.classList.remove('navbar-scrolled');
        }
    }

    /**
     * 2. Inicialización de Particles.js
     * Se mantiene una única configuración de particles.js (la primera configuración)
     * y se elimina la segunda configuración conflictiva.
     */
    function initParticles() {
        if (document.getElementById(particleContainerID)) {
            particlesJS(particleContainerID, {
                particles: {
                    number: { value: 80, density: { enable: true, value_area: 800 } },
                    color: { value: "#C8B560" }, // Color de acento correcto
                    shape: { type: "circle" },
                    opacity: { value: 0.5, random: true },
                    size: { value: 3, random: true },
                    line_linked: { enable: false },
                    move: {
                        enable: true,
                        speed: 1,
                        direction: "none",
                        random: true,
                        straight: false,
                        out_mode: "out",
                        bounce: false
                    }
                },
                interactivity: {
                    detect_on: "canvas",
                    events: {
                        onhover: { enable: true, mode: "repulse" },
                        onclick: { enable: true, mode: "push" }
                    },
                    modes: {
                        repulse: { distance: 100, duration: 0.4 },
                        push: { particles_nb: 4 }
                    }
                },
                retina_detect: true
            });
        }
    }

    /**
     * 3. Animación de aparición (Fade-in)
     * Se unifica el IntersectionObserver. Solo se necesita una instancia para observar todos los elementos.
     */
    function initIntersectionObserver() {
        const fadeElements = document.querySelectorAll('.fade-in');
        if (fadeElements.length === 0) return;

        const appearOnScroll = new IntersectionObserver(function(entries, observer) {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.1 });

        fadeElements.forEach(element => {
            appearOnScroll.observe(element);
        });
    }

    /**
     * 4. Scroll Suave para Anclas (Anchor Links)
     */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                // Prevenir si el href es solo '#' (un ancla vacía)
                const hrefTarget = this.getAttribute('href');
                if (hrefTarget === '#') {
                    e.preventDefault();
                    return;
                }
                
                const targetElement = document.querySelector(hrefTarget);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    /**
     * 5. Lógica del Carrusel Marquee (Pausar en hover)
     */
    function initCarouselLogic() {
        if (!carousel) return;

        function pauseCarousel() {
            carousel.classList.add('paused');
        }

        function resumeCarousel() {
            carousel.classList.remove('paused');
        }

        // Pausar al interactuar
        carousel.addEventListener('mouseenter', pauseCarousel);
        carousel.addEventListener('touchstart', pauseCarousel, { passive: true }); // Mejora de rendimiento pasivo

        // Reanudar al dejar de interactuar
        carousel.addEventListener('mouseleave', resumeCarousel);
        carousel.addEventListener('touchend', resumeCarousel, { passive: true });
        
        // (Nota: La lógica de duplicado de HTML ya está manejada en el Blade)
    }


    // --- Ejecución de todos los módulos ---
    handleNavbarScroll(); // Ejecutar una vez al cargar por si la página recarga a mitad
    window.addEventListener('scroll', handleNavbarScroll);
    
    initParticles();
    initIntersectionObserver();
    initSmoothScroll();
    initCarouselLogic();

});