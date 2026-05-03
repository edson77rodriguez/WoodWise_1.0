@extends('layouts.app')

{{-- 1. CSS movido al stack de 'styles' en el <head> --}}
@push('styles')
    <link href="{{ asset('css/WW/contacto.css') }}" rel="stylesheet">
@endpush

@section('content')

<section class="contact-section py-4 py-md-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-4 mb-md-5">
                <span class="contact-kicker">Asesoría Inteligente SIGMAD</span>
                <h1 class="display-5 display-md-4 fw-bold mb-2 mb-md-3 wood-text-dark contact-title">
                    Diseñemos decisiones forestales con <span class="text-gradient">datos confiables</span>
                </h1>
                <div class="divider mx-auto"></div>
                <p class="lead mt-2 mt-md-3 wood-text-medium px-2 px-md-0 contact-lead">
                    Cuéntanos tu operación y te proponemos el flujo más eficiente para captura en campo, procesamiento y trazabilidad maderera. Respuesta técnica, clara y orientada a resultados.
                </p>
                <div class="contact-intel-tags mt-3" aria-label="Puntos clave de atención">
                    <span class="contact-intel-tag"><i class="bi bi-lightning-charge-fill"></i> Diagnóstico rápido</span>
                    <span class="contact-intel-tag"><i class="bi bi-cpu-fill"></i> Enfoque técnico</span>
                    <span class="contact-intel-tag"><i class="bi bi-clock-history"></i> Respuesta en menos de 24h</span>
                </div>
            </div>
        </div>

        <div class="row g-4 g-md-5">
            <div class="col-lg-5 order-2 order-lg-1">
                {{-- [REFACTORIZACIÓN] Padding ajustado a utilidades: p-3 (móvil), p-md-5 (desktop) --}}
                <div class="contact-info-card p-3 p-md-5 rounded-4 shadow h-100 contact-info-card--premium">
                    <span class="contact-panel-kicker mb-2 d-inline-flex">Canales activos</span>
                    <h3 class="fw-bold mb-3 mb-md-4 wood-text-dark wood-border-bottom">
                        <i class="bi bi-info-circle-fill me-2 wood-text-accent"></i> Información
                    </h3>
                    
                    <div class="contact-items">
                        <div class="contact-item contact-item-card mb-3 mb-md-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="bi bi-geo-alt-fill fs-4 wood-text-accent"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 wood-text-dark">Ubicación</h5>
                                    <p class="mb-0 wood-text-medium">
                                        Carretera Federal Valle de Bravo Km 30<br>
                                        Ejido San Antonio Laguna<br>
                                        51200 Valle de Bravo, Méx.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item contact-item-card mb-3 mb-md-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="bi bi-telephone-fill fs-4 wood-text-accent"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 wood-text-dark">Teléfono</h5>
                                    <p class="mb-0 wood-text-medium">
                                        <a href="tel:+527226367194" class="contact-link">+52 7226367194</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item contact-item-card mb-3 mb-md-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="bi bi-envelope-fill fs-4 wood-text-accent"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 wood-text-dark">Email</h5>
                                    <p class="mb-0 wood-text-medium">
                                        <a href="mailto:woodwise.sigmad@gmail.com" class="contact-link">woodwise.sigmad@gmail.com</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item contact-item-card mb-3 mb-md-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="bi bi-whatsapp fs-4 wood-text-accent"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 wood-text-dark">WhatsApp</h5>
                                    <p class="mb-0 wood-text-medium">
                                        <a href="https://wa.me/527226367194" class="contact-link" target="_blank" rel="noopener">+52 7226367194</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links mt-4"> {{-- Reducido margen responsivo (mt-md-5 era mucho) --}}
                        <h5 class="fw-bold mb-2 mb-md-3 wood-text-dark">Síguenos</h5>
                        <div class="d-flex justify-content-start gap-2 gap-md-3 social-links-modern"> {{-- Eliminado justify-content-center en móvil --}}
                            <a href="#" class="social-icon facebook" aria-label="Facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://www.instagram.com/x_edsonj?igsh=Ynh0cXBsZXFpZDR6" class="social-icon instagram" aria-label="Instagram" target="_blank" rel="noopener">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="https://wa.me/527226367194" class="social-icon whatsapp" aria-label="WhatsApp" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="#" class="social-icon linkedin" aria-label="LinkedIn">
                                <i class="bi bi-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7 order-1 order-lg-2">
                 {{-- [REFACTORIZACIÓN] Padding ajustado a utilidades: p-3 (móvil), p-md-5 (desktop) --}}
                <div class="contact-form-card p-3 p-md-5 rounded-4 shadow contact-form-card--premium">
                    <span class="contact-panel-kicker mb-2 d-inline-flex">Canal directo</span>
                    <h3 class="fw-bold mb-3 mb-md-4 wood-text-dark wood-border-bottom">
                        <i class="bi bi-send-fill me-2 wood-text-accent"></i> Escríbenos
                    </h3>
                    
                    <p class="form-intro mb-3">
                        Completa tus datos y prepararemos un mensaje listo para enviar por WhatsApp.
                    </p>

                    <form action="#" method="POST" class="needs-validation contact-whatsapp-form" id="contactWhatsappForm" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control wood-input" id="name" placeholder="Nombre" required>
                                    <label for="name" class="wood-text-medium">Nombre completo</label>
                                    <div class="invalid-feedback">
                                        Por favor ingresa tu nombre
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control wood-input" id="email" placeholder="Email" required>
                                    <label for="email" class="wood-text-medium">Correo electrónico</label>
                                    <div class="invalid-feedback">
                                        Por favor ingresa un email válido
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control wood-input" id="subject" placeholder="Asunto" required>
                                    <label for="subject" class="wood-text-medium">Asunto</label>
                                    <div class="invalid-feedback">
                                        Por favor ingresa un asunto
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-floating">
                                    {{-- [REFACTORIZACIÓN] Eliminado style="height: 150px;". Esto se mueve al CSS. --}}
                                    <textarea class="form-control wood-input" id="message" placeholder="Mensaje" required></textarea>
                                    <label for="message" class="wood-text-medium">Tu mensaje</label>
                                    <div class="invalid-feedback">
                                        Por favor escribe tu mensaje
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                {{-- [REFACTORIZACIÓN] Padding ajustado: py-2 (móvil), py-lg-3 (desktop) --}}
                                <button class="btn btn-wood w-100 fw-bold py-2 py-lg-3" type="submit">
                                    <i class="bi bi-whatsapp me-2"></i> Enviar por WhatsApp
                                </button>
                                <small class="form-hint d-block mt-2">Se abrirá WhatsApp con tu mensaje ya estructurado, listo para enviar.</small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="row mt-4 mt-md-5 order-3">
            <div class="col-12">
                {{-- [REFACTORIZACIÓN] Padding ajustado: p-3 (móvil), p-md-4 (desktop) --}}
                <div class="map-card p-3 p-md-4 rounded-4 shadow map-card--premium">
                    <div class="map-card__head d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 mb-3 mb-md-4">
                        <div>
                            <span class="contact-panel-kicker mb-2 d-inline-flex">Presencia física</span>
                            <h3 class="fw-bold mb-2 wood-text-dark wood-border-bottom map-title">
                                <i class="bi bi-map-fill me-2 wood-text-accent"></i> Nuestra Ubicación
                            </h3>
                            <p class="map-subtitle mb-0">Visítanos en campus y conoce de cerca el ecosistema SIGMAD.</p>
                        </div>
                        <a href="https://maps.app.goo.gl/XunGChfPb5QY9hUj6" class="btn map-cta" target="_blank" rel="noopener">
                            <i class="bi bi-geo-alt-fill me-2"></i> Abrir en Google Maps
                        </a>
                    </div>

                    <div class="map-location-chip mb-3" aria-label="Dirección de referencia">
                        <i class="bi bi-signpost-2-fill"></i>
                        <span>Carretera Federal Valle de Bravo Km 30, Ejido San Antonio Laguna, 51200 Valle de Bravo, Méx.</span>
                    </div>

                    <div class="ratio ratio-16x9 map-frame-wrap">
                        {{-- [REFACTORIZACIÓN] Eliminados estilos inline. Movidos al CSS. --}}
                        <iframe src="https://www.google.com/maps?q=Carretera+Federal+Valle+de+Bravo+Km+30+Ejido+San+Antonio+Laguna+51200+Valle+de+Bravo+Mex&output=embed"
                                allowfullscreen="" loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 2. Script movido al stack de 'scripts' al final del body --}}
@push('scripts')
<script>
    (function() {
        'use strict';

        function buildWhatsappMessage(form) {
            var name = form.querySelector('#name').value.trim();
            var email = form.querySelector('#email').value.trim();
            var subject = form.querySelector('#subject').value.trim();
            var message = form.querySelector('#message').value.trim();

            return [
                'Hola equipo SIGMAD, me gustaria solicitar informacion.',
                '',
                'Nombre: ' + name,
                'Correo: ' + email,
                'Asunto: ' + subject,
                'Mensaje: ' + message,
                '',
                'Quedo atento(a) a su respuesta. Muchas gracias.'
            ].join('\n');
        }

        window.addEventListener('DOMContentLoaded', function() {
            var forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    if (form.checkValidity() === false) {
                        event.stopPropagation();
                        form.classList.add('was-validated');
                        return;
                    }

                    form.classList.add('was-validated');

                    if (form.classList.contains('contact-whatsapp-form')) {
                        var whatsappNumber = '527226367194';
                        var composedMessage = buildWhatsappMessage(form);
                        var whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(composedMessage);
                        window.open(whatsappUrl, '_blank', 'noopener');
                    }
                }, false);
            });
        }, false);
    })();
</script>
@endpush

@endsection