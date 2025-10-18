@extends('layouts.app')

{{-- El CSS se carga correctamente en el stack de la cabecera --}}
@push('styles')
    <link href="{{ asset('css/WW/acerca.css') }}" rel="stylesheet">
@endpush

@section('content')

<section class="about-section"> {{-- El fondo se aplica vía CSS --}}
    <div class="container">
        <div class="text-center mb-4 mb-md-5 animate__animated animate__fadeInDown">
            {{-- Clases de utilidad de Bootstrap + color base --}}
            <h2 class="display-5 display-md-4 fw-bold text-uppercase mb-2 mb-md-3 text-dark about-title">
                <span class="text-gradient">Nuestra Historia</span>
            </h2>
            <div class="divider mx-auto"></div> {{-- Clase objetivo del CSS --}}
            <p class="lead mt-2 mt-md-3 px-2 px-md-0 text-medium about-subtitle mx-auto">
                Innovación y sostenibilidad en la gestión forestal desde 2023
            </p>
        </div>

        <div id="aboutCarousel" class="carousel slide carousel-dark" data-bs-ride="carousel" data-bs-interval="6000" data-bs-touch="true">
            <div class="carousel-inner rounded-3 rounded-md-4 shadow-lg overflow-hidden">
                
                <div class="carousel-item active">
                    <div class="carousel-content-wrapper">
                        <div class="row align-items-center h-100">
                            <div class="col-md-6 order-2 order-md-1 mt-3 mt-md-0">
                                <div class="icon-box"> {{-- Clase objetivo del CSS --}}
                                    <i class="bi bi-bullseye carousel-icon"></i> {{-- Clase para tamaño y color --}}
                                </div>
                                <h3 class="carousel-title fw-bold mb-2 mb-md-3 text-center text-md-start text-dark">Nuestra Misión</h3>
                                <p class="fs-6 fs-md-5 text-center text-md-start text-dark-emphasis">
                                    En <strong class="text-gradient">WoodWise</strong>, revolucionamos la gestión forestal mediante tecnología de punta que combina precisión científica con sostenibilidad ambiental.
                                </p>
                            </div>
                            <div class="col-md-6 order-1 order-md-2">
                                <img src="{{ asset('img/woodwise.png') }}" class="img-fluid rounded-3 shadow" alt="Misión WoodWise" loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="carousel-content-wrapper">
                        <div class="row align-items-center h-100">
                            <div class="col-md-6 order-2 order-md-2 mt-3 mt-md-0">
                                <div class="icon-box">
                                    <i class="bi bi-people-fill carousel-icon"></i>
                                </div>
                                <h3 class="carousel-title fw-bold mb-2 mb-md-3 text-center text-md-start text-dark">¿Quiénes Somos?</h3>
                                <p class="fs-6 fs-md-5 text-center text-md-start text-dark-emphasis">
                                    Somos un equipo multidisciplinario de <strong>ingenieros forestales, desarrolladores y expertos en sostenibilidad</strong>, unidos por la pasión por la conservación de los bosques.
                                </p>
                            </div>
                            <div class="col-md-6 order-1 order-md-1">
                                <img src="{{ asset('img/woodwise.png') }}" class="img-fluid rounded-3 shadow" alt="Equipo WoodWise" loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="carousel-content-wrapper">
                        <div class="row align-items-center h-100">
                            <div class="col-md-6 order-2 order-md-1 mt-3 mt-md-0">
                                <div class="icon-box">
                                    <i class="bi bi-cpu-fill carousel-icon"></i>
                                </div>
                                <h3 class="carousel-title fw-bold mb-2 mb-md-3 text-center text-md-start text-dark">Nuestra Tecnología</h3>
                                <p class="fs-6 fs-md-5 text-center text-md-start text-dark-emphasis">
                                    Desarrollamos <strong>herramientas inteligentes</strong> que integran:
                                </p>
                                <ul class="fs-6 fs-md-5 text-center text-md-start ps-0 carousel-list">
                                    <li>Análisis satelital de precisión</li>
                                    <li>Algoritmos de cálculo volumétrico</li>
                                    <li>Modelos predictivos de crecimiento</li>
                                </ul>
                            </div>
                            <div class="col-md-6 order-1 order-md-2">
                                <img src="{{ asset('img/woodwise.png') }}" class="img-fluid rounded-3 shadow" alt="Tecnología WoodWise" loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="carousel-content-wrapper">
                        <div class="row align-items-center h-100">
                            <div class="col-md-6 order-2 order-md-2 mt-3 mt-md-0">
                                <div class="icon-box">
                                    <i class="bi bi-stars carousel-icon"></i>
                                </div>
                                <h3 class="carousel-title fw-bold mb-2 mb-md-3 text-center text-md-start text-dark">Nuestros Valores</h3>
                                <div class="row g-2">
                                    {{-- Clases variantes para fondos --}}
                                    <div class="col-6">
                                        <div class="value-card value-card--sustainability rounded-3 h-100">
                                            <i class="bi bi-tree-fill d-block mb-1 mb-md-2 value-card__icon"></i>
                                            <h6 class="value-card__title fw-bold mb-1 text-dark fs-6">Sostenibilidad</h6>
                                            <small class="value-card__subtitle text-medium">Gestión responsable</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="value-card value-card--precision rounded-3 h-100">
                                            <i class="bi bi-pin-map-fill d-block mb-1 mb-md-2 value-card__icon text-dark"></i>
                                            <h6 class="value-card__title fw-bold mb-1 text-dark fs-6">Precisión</h6>
                                            <small class="value-card__subtitle text-medium">Datos exactos</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="value-card value-card--innovation rounded-3 h-100">
                                            <i class="bi bi-lightbulb-fill d-block mb-1 mb-md-2 value-card__icon text-accent-dark"></i>
                                            <h6 class="value-card__title fw-bold mb-1 text-dark fs-6">Innovación</h6>
                                            <small class="value-card__subtitle text-medium">Soluciones avanzadas</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="value-card value-card--commitment rounded-3 h-100">
                                            <i class="bi bi-hand-thumbs-up-fill d-block mb-1 mb-md-2 value-card__icon text-medium"></i>
                                            <h6 class="value-card__title fw-bold mb-1 text-dark fs-6">Compromiso</h6>
                                            <small class="value-card__subtitle text-medium">Apoyo continuo</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 order-1 order-md-1">
                                <img src="{{ asset('img/woodwise.png') }}" class="img-fluid rounded-3 shadow" alt="Valores WoodWise" loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="carousel-control-prev d-none d-md-flex" type="button" data-bs-target="#aboutCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next d-none d-md-flex" type="button" data-bs-target="#aboutCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
            
            <div class="carousel-indicators position-static mt-3 d-flex d-md-none justify-content-center">
                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
            </div>
        </div>

        <div class="row mt-4 mt-md-5 g-3">
            <div class="col-12 col-md-4">
                <div class="impact-card p-3 p-md-4 rounded-3 rounded-md-4 text-center h-100 shadow-sm">
                    <i class="bi bi-tree fs-3 fs-md-1 mb-2 mb-md-3 text-accent"></i>
                    <h3 class="fs-5 fs-md-4 fw-bold mb-2 mb-md-3 text-dark">5,000 Hectáreas</h3>
                    <p class="small text-medium">Gestionadas con nuestra tecnología</p>
                </div>
            </div>
            <div class="col-12 col-md-4 mt-3 mt-md-0">
                <div class="impact-card p-3 p-md-4 rounded-3 rounded-md-4 text-center h-100 shadow-sm">
                    <i class="bi bi-people fs-3 fs-md-1 mb-2 mb-md-3 text-accent"></i>
                    <h3 class="fs-5 fs-md-4 fw-bold mb-2 mb-md-3 text-dark">+120 Clientes</h3>
                    <p class="small text-medium">Confían en nuestras soluciones</p>
                </div>
            </div>
            <div class="col-12 col-md-4 mt-3 mt-md-0">
                <div class="impact-card p-3 p-md-4 rounded-3 rounded-md-4 text-center h-100 shadow-sm">
                    <i class="bi bi-graph-up-arrow fs-3 fs-md-1 mb-2 mb-md-3 text-accent"></i>
                    <h3 class="fs-5 fs-md-4 fw-bold mb-2 mb-md-3 text-dark">30% Más Eficiente</h3>
                    <p class="small text-medium">Que los métodos tradicionales</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection