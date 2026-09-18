// ================================================================
// SIGMAD — acerca-animaciones.js
// Animaciones suaves, micro-interacciones y efectos visuales
// ================================================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ─────────────────────────────────────────────────────────────
    // 1. CONTADOR DE NÚMEROS EN TARJETAS DE IMPACTO
    // ─────────────────────────────────────────────────────────────
    function animarContadores() {
        const contadores = document.querySelectorAll('.card-counter');
        
        const observador = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.dataset.animado) {
                    const target = entry.target;
                    const valor = parseInt(target.dataset.target);
                    const duracion = 2000; // 2 segundos
                    const incremento = valor / (duracion / 16); // 60fps
                    let actual = 0;
                    
                    const animar = () => {
                        actual += incremento;
                        if (actual >= valor) {
                            target.textContent = valor.toLocaleString('es-ES');
                            target.dataset.animado = true;
                        } else {
                            target.textContent = Math.floor(actual).toLocaleString('es-ES');
                            requestAnimationFrame(animar);
                        }
                    };
                    
                    animar();
                }
            });
        }, { threshold: 0.5 });
        
        contadores.forEach(contador => observador.observe(contador));
    }
    
    // ─────────────────────────────────────────────────────────────
    // 2. EFECTO DE ESCRITURA SUAVE EN TÍTULOS
    // ─────────────────────────────────────────────────────────────
    function efecto3DscrollParallax() {
        const elementos3D = document.querySelectorAll('[data-parallax]');
        
        window.addEventListener('scroll', () => {
            elementos3D.forEach(elemento => {
                const velocidad = parseFloat(elemento.dataset.parallax);
                const posicion = window.scrollY;
                elemento.style.transform = `translateY(${posicion * velocidad}px)`;
            });
        });
    }
    
    // ─────────────────────────────────────────────────────────────
    // 3. ANIMACIÓN DE ENTRADA PARA ELEMENTOS CON SCROLL
    // ─────────────────────────────────────────────────────────────
    function observarElementosEntrada() {
        const elementos = document.querySelectorAll(
            '.impact-card-premium, .value-card, .timeline-item'
        );
        
        const observador = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
                    observador.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });
        
        elementos.forEach(elemento => observador.observe(elemento));
    }
    
    // ─────────────────────────────────────────────────────────────
    // 4. INTERACTIVIDAD DEL CARRUSEL
    // ─────────────────────────────────────────────────────────────
    function mejorarCarrusel() {
        const carousel = document.querySelector('#aboutCarousel');
        
        if (carousel) {
            const slides = carousel.querySelectorAll('.carousel-item');
            
            carousel.addEventListener('slide.bs.carousel', (e) => {
                slides.forEach(slide => {
                    slide.style.transition = 'opacity 0.8s ease-in-out';
                });
            });
        }
    }
    
    // ─────────────────────────────────────────────────────────────
    // 5. EFECTO HOVER SUAVE EN TARJETAS
    // ─────────────────────────────────────────────────────────────
    function efectoHoverTarjetas() {
        const tarjetas = document.querySelectorAll('.value-card-inner, .impact-card-premium');
        
        tarjetas.forEach(tarjeta => {
            tarjeta.addEventListener('mousemove', (e) => {
                const rect = tarjeta.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const rotX = (y - rect.height / 2) / rect.height * 5;
                const rotY = (x - rect.width / 2) / rect.width * 5;
                
                tarjeta.style.transform = `perspective(1000px) rotateX(${rotX}deg) rotateY(${rotY}deg)`;
            });
            
            tarjeta.addEventListener('mouseleave', () => {
                tarjeta.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
            });
        });
    }
    
    // ─────────────────────────────────────────────────────────────
    // 6. SUAVIDAD EN NAVEGACIÓN CON SMOOTH SCROLL
    // ─────────────────────────────────────────────────────────────
    function configurarSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
    
    // ─────────────────────────────────────────────────────────────
    // 7. EFECTO DE APARICIÓN AL CARGAR
    // ─────────────────────────────────────────────────────────────
    function efectoCargaInicial() {
        const body = document.body;
        body.style.animation = 'fadeIn 0.6s ease-out';
    }
    
    // ─────────────────────────────────────────────────────────────
    // 8. INTERACCIÓN CON BOTONES CTA
    // ─────────────────────────────────────────────────────────────
    function mejorarBotones() {
        const botones = document.querySelectorAll('.btn');
        
        botones.forEach(boton => {
            boton.addEventListener('click', function(e) {
                // Crear efecto ripple
                const ripple = document.createElement('span');
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.6)';
                ripple.style.width = ripple.style.height = '20px';
                ripple.style.pointerEvents = 'none';
                
                const rect = this.getBoundingClientRect();
                ripple.style.left = (e.clientX - rect.left - 10) + 'px';
                ripple.style.top = (e.clientY - rect.top - 10) + 'px';
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
    }
    
    // ─────────────────────────────────────────────────────────────
    // 9. DETECTAR PREFERENCIAS DE MOVIMIENTO REDUCIDO
    // ─────────────────────────────────────────────────────────────
    function respectarPreferenciaMovimiento() {
        const prefierenMovimientoReducido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        
        if (prefierenMovimientoReducido) {
            document.documentElement.style.scrollBehavior = 'auto';
        }
    }
    
    // ─────────────────────────────────────────────────────────────
    // 10. OPTIMIZACIÓN PARA DISPOSITIVOS MÓVILES
    // ─────────────────────────────────────────────────────────────
    function optimizarParaMovil() {
        const esMovil = window.innerWidth <= 768;
        
        if (esMovil) {
            // Desactivar algunos efectos en móvil para mejor rendimiento
            const hover3D = document.querySelectorAll('.value-card-inner');
            hover3D.forEach(elemento => {
                elemento.addEventListener('touchstart', function() {
                    this.style.transition = 'transform 0.3s ease';
                });
            });
        }
    }
    
    // ─────────────────────────────────────────────────────────────
    // INICIALIZACIÓN GENERAL
    // ─────────────────────────────────────────────────────────────
    
    // Ejecutar todas las funciones
    animarContadores();
    efecto3DscrollParallax();
    observarElementosEntrada();
    mejorarCarrusel();
    efectoHoverTarjetas();
    configurarSmoothScroll();
    efectoCargaInicial();
    mejorarBotones();
    respectarPreferenciaMovimiento();
    optimizarParaMovil();
    
    console.log('✨ Vista "Acerca de Nosotros" - Animaciones cargadas correctamente');
});

// ─────────────────────────────────────────────────────────────
// ESCUCHAR CAMBIOS DE TAMAÑO DE PANTALLA
// ─────────────────────────────────────────────────────────────
window.addEventListener('resize', () => {
    optimizarParaMovil();
});
