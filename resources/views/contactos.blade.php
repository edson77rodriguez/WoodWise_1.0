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
                <h1 class="display-5 display-md-4 fw-bold mb-2 mb-md-3 wood-text-dark">
                    <span class="text-gradient">Contáctanos</span>
                </h1>
                <div class="divider mx-auto"></div>
                <p class="lead mt-2 mt-md-3 wood-text-medium px-2 px-md-0">
                    Estamos aquí para responder tus preguntas y ayudarte con tus necesidades forestales
                </p>
            </div>
        </div>

        <div class="row g-4 g-md-5">
            <div class="col-lg-5 order-2 order-lg-1">
                {{-- [REFACTORIZACIÓN] Padding ajustado a utilidades: p-3 (móvil), p-md-5 (desktop) --}}
                <div class="contact-info-card p-3 p-md-5 rounded-4 shadow h-100">
                    <h3 class="fw-bold mb-3 mb-md-4 wood-text-dark wood-border-bottom">
                        <i class="bi bi-info-circle-fill me-2 wood-text-accent"></i> Información
                    </h3>
                    
                    <div class="contact-items">
                        <div class="contact-item mb-3 mb-md-4">
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
                        
                        <div class="contact-item mb-3 mb-md-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="bi bi-telephone-fill fs-4 wood-text-accent"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 wood-text-dark">Teléfono</h5>
                                    <p class="mb-0 wood-text-medium">
                                        +52 5511329075
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3 mb-md-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="bi bi-envelope-fill fs-4 wood-text-accent"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 wood-text-dark">Email</h5>
                                    <p class="mb-0 wood-text-medium">
                                        woodwise@gmail.com
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3 mb-md-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="bi bi-whatsapp fs-4 wood-text-accent"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 wood-text-dark">WhatsApp</h5>
                                    <p class="mb-0 wood-text-medium">
                                        +52 5511329075
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links mt-4"> {{-- Reducido margen responsivo (mt-md-5 era mucho) --}}
                        <h5 class="fw-bold mb-2 mb-md-3 wood-text-dark">Síguenos</h5>
                        <div class="d-flex justify-content-start gap-2 gap-md-3"> {{-- Eliminado justify-content-center en móvil --}}
                            <a href="#" class="social-icon facebook">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://www.instagram.com/x_edsonj?igsh=Ynh0cXBsZXFpZDR6" class="social-icon instagram">
                                <i class="bi bi-instagram"></i>
                            </a>
                            <a href="#" class="social-icon whatsapp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="#" class="social-icon linkedin">
                                <i class="bi bi-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7 order-1 order-lg-2">
                 {{-- [REFACTORIZACIÓN] Padding ajustado a utilidades: p-3 (móvil), p-md-5 (desktop) --}}
                <div class="contact-form-card p-3 p-md-5 rounded-4 shadow">
                    <h3 class="fw-bold mb-3 mb-md-4 wood-text-dark wood-border-bottom">
                        <i class="bi bi-send-fill me-2 wood-text-accent"></i> Escríbenos
                    </h3>
                    
                    <form action="#" method="POST" class="needs-validation" novalidate>
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
                                    <i class="bi bi-send-fill me-2"></i> Enviar Mensaje
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="row mt-4 mt-md-5 order-3">
            <div class="col-12">
                {{-- [REFACTORIZACIÓN] Padding ajustado: p-3 (móvil), p-md-4 (desktop) --}}
                <div class="map-card p-3 p-md-4 rounded-4 shadow">
                    <h3 class="fw-bold mb-3 mb-md-4 wood-text-dark wood-border-bottom">
                        <i class="bi bi-map-fill me-2 wood-text-accent"></i> Nuestra Ubicación
                    </h3>
                    <div class="ratio ratio-16x9">
                        {{-- [REFACTORIZACIÓN] Eliminados estilos inline. Movidos al CSS. --}}
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3766.123456789012!2d-100.12345678901234!3d19.123456789012345!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTnCsDA3JzI0LjQiTiAxMDDCsDA3JzI0LjQiVw!5e0!3m2!1sen!2smx!4v1234567890123!5m2!1sen!2smx" 
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
    // Validación de formulario Bootstrap
    (function() {
        'use strict';
        // El DOMContentLoaded es bueno, asegura que todo esté listo aunque el script se cargue antes.
        window.addEventListener('DOMContentLoaded', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
@endpush

@endsection