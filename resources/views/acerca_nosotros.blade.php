@extends('layouts.app')

{{-- CSS Premium Ultra Responsivo --}}
@push('styles')
    <link href="{{ asset('css/WW/acerca.css') }}?v={{ time() }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endpush

@section('content')

{{-- HERO SECTION PROFESIONAL --}}
<section class="about-hero animate__animated animate__fadeIn">
    <div class="container">
        <div class="about-hero__header text-center animate__animated animate__fadeInDown">
            <h1 class="about-hero__title">
                Nuestra <span class="text-highlight">Esencia</span>
            </h1>
            <div class="wood-divider mx-auto gold-glow floating"></div> {{-- Usando la utilidad global --}}
            <p class="about-hero__subtitle mx-auto">
                Redefiniendo la gobernanza forestal. SIGMAD es la primera arquitectura de micro-orquestación y mensajería ubicua que erradica la brecha digital en la industria maderera. Transformamos el aislamiento del campo en trazabilidad absoluta y resiliencia de datos, sin importar la conectividad.
            </p>
        </div>
    </div>
</section>

{{-- CARRUSEL PREMIUM MEJORADO --}}
<section class="carousel-section pt-5">
    <div class="container py-5">
        {{-- Importante: data-bs-touch="true" permite el deslizamiento en móviles --}}
        <div id="aboutCarousel" class="carousel slide carousel-fade carousel-dark about-carousel" data-bs-ride="carousel" data-bs-interval="8000" data-bs-touch="true">
            <div class="carousel-inner about-carousel__inner shadow-lg">
                
                {{-- Diapositiva 1: Sistema de Gestión (Excel a SIGMAD) --}}
                <div class="carousel-item active slide-gestion">
                    <div class="about-carousel__content">
                        <div class="row align-items-center g-4 g-md-5">
                            <div class="col-md-7 col-lg-8 order-2 order-md-1">
                                <div class="about-carousel__text-block text-center text-md-start">
                                    <div class="about-carousel__icon-wrapper mb-3 mx-auto mx-md-0 gold-glow">
                                        <i class="fas fa-server"></i>
                                    </div>
                                    <h3 class="about-carousel__title">Núcleo de Gobernanza Centralizada</h3>
                                    <p class="about-carousel__description">
                                        Erradicamos la fragmentación de datos y el caos manual. Nuestro robusto backend estructurado transforma datos asíncronos en inventarios precisos mediante modelos matemáticos validados, centralizando la toma de decisiones.
                                    </p>
                                    <ul class="about-carousel__list d-inline-block text-start">
                                        <li><i class="fas fa-check text-accent-solid me-2"></i> Auditoría inmutable de extremo a extremo</li>
                                        <li><i class="fas fa-check text-accent-solid me-2"></i> Cálculos volumétricos y de biomasa automatizados</li>
                                    </ul>
                                </div>
                            </div>
                            {{-- ANIMACIÓN ÉPICA: EXCEL -> SIGMAD --}}
                            <div class="col-md-5 col-lg-4 order-1 order-md-2 text-center">
                                <div class="morph-showcase mx-auto floating-2">
                                    <div class="morph-card">
                                        <div class="morph-front">
                                            <i class="fas fa-file-excel excel-icon"></i>
                                            <span class="morph-text">Caos Manual</span>
                                        </div>
                                        <div class="morph-back gold-glow">
                                            <img src="{{ asset('assets/images/SIGMAD.svg') }}" alt="SIGMAD IA" class="morph-logo">
                                            <span class="morph-text text-accent-solid">Gestión Inteligente</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="producer-happiness mt-3 mx-auto" aria-hidden="true">
                                    <span class="producer-avatar"><i class="fas fa-user-tie"></i></span>
                                    <span class="producer-mood"><i class="fas fa-smile-beam"></i> Productor satisfecho</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Diapositiva 2: Chatbot y Campo --}}
                <div class="carousel-item">
                    <div class="about-carousel__content">
                        <div class="row align-items-center g-4 g-md-5">
                            <div class="col-md-7 col-lg-8 order-2 order-md-1 mt-3 mt-md-0">
                                <div class="about-carousel__text-block text-center text-md-start">
                                    <div class="about-carousel__icon-wrapper mb-3 mx-auto mx-md-0 gold-glow">
                                        <i class="fas fa-project-diagram"></i>
                                    </div>
                                    <h3 class="about-carousel__title">Micro-orquestación y Mensajería Ubicua</h3>
                                    <p class="about-carousel__description">
                                        Eliminamos la fricción tecnológica integrando <span class="text-accent-solid">WhatsApp Cloud API</span> orquestado por n8n. Un modelo basado en eventos que permite la captura offline y una ingesta de datos asíncrona altamente resiliente.
                                    </p>
                                    <ul class="about-carousel__list d-inline-block text-start mb-0">
                                        <li><i class="fas fa-check text-accent-solid me-2"></i> Ingestión idempotente mediante Stateless Batching</li>
                                        <li><i class="fas fa-check text-accent-solid me-2"></i> Cero barreras de adopción para el operador en campo</li>
                                    </ul>
                                </div>
                            </div>
                            {{-- ANIMACIÓN ÉPICA: CHATBOT WHATSAPP --}}
                            <div class="col-md-5 col-lg-4 order-1 order-md-2 text-center">
                                <div class="chatbot-showcase mx-auto">
                                    <div class="connectivity-status" aria-hidden="true">
                                        <span class="chatbot-badge badge-online"><i class="fab fa-whatsapp"></i> Integración n8n</span>
                                        <span class="chatbot-badge badge-offline"><i class="fas fa-wifi"></i> Encolado asíncrono</span>
                                    </div>
                                    <div class="mockup-phone shadow-lg mx-auto">
                                    <div class="chat-header">
                                        <i class="fab fa-whatsapp"></i> SIGMAD Bot
                                    </div>
                                    <div class="chat-body">
                                        <div class="chat-bubble bot delay-1">Operador de campo, ingrese el diámetro de la troza (cm):</div>
                                        <div class="chat-bubble user delay-2">45</div>
                                        <div class="chat-bubble bot delay-3">
                                            <i class="fas fa-check-double text-info"></i> Lote procesado. Volumen estimado: 1.2m³. Sincronizando con SIGMAD central...
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            {{-- Navegación Inteligente (Reemplaza a las flechas y puntitos) --}}
            <div class="carousel-indicators smart-nav">
                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="0" class="active smart-nav__btn" aria-current="true">
                    <i class="fas fa-database"></i>
                    <span>Gestión</span>
                </button>
                <button type="button" data-bs-target="#aboutCarousel" data-bs-slide-to="1" class="smart-nav__btn">
                    <i class="fab fa-whatsapp"></i>
                    <span>Chatbot</span>
                </button>
            </div>
            
        </div>
    </div>
</section>

{{-- SECCIÓN DE IMPACTO REAL (Tecnología dura) --}}
<section class="impact-section py-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="impact-card p-3 p-md-4 rounded-3 rounded-md-4 text-center h-100 wood-shadow-lg floating-3">
                    <div class="impact-visual impact-visual--management" aria-hidden="true">
                        <div class="flow-icon flow-icon--photo"><i class="fas fa-tree"></i></div>
                        <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
                        <div class="flow-stack">
                            <span class="flow-icon flow-icon--money"><i class="fas fa-database"></i></span>
                            <span class="flow-icon flow-icon--decision"><i class="fas fa-chart-line"></i></span>
                        </div>
                    </div>
                    <h4 class="impact-title fw-bold fs-5 text-dark">Gestión del Sistema</h4>
                    <p class="impact-desc small text-medium">Transformamos capturas de campo en <span class="text-accent-solid">valor económico y decisiones operativas</span>, conectando registro, cálculo volumétrico y trazabilidad en un solo flujo.</p>
                </div>
            </div>
            <div class="col-md-4 mt-3 mt-md-0">
                <div class="impact-card p-3 p-md-4 rounded-3 rounded-md-4 text-center h-100 wood-shadow-lg floating-4">
                    <div class="impact-visual impact-visual--architecture" aria-hidden="true">
                        <div class="cloud-core"><i class="fas fa-network-wired"></i></div>
                        <span class="node node-1"></span>
                        <span class="node node-2"></span>
                        <span class="node node-3"></span>
                        <span class="node node-4"></span>
                        <span class="link link-1"></span>
                        <span class="link link-2"></span>
                        <span class="link link-3"></span>
                        <span class="link link-4"></span>
                    </div>
                    <h4 class="impact-title fw-bold fs-5 text-dark">Arquitectura del Sistema</h4>
                    <p class="impact-desc small text-medium">Diseño robusto sobre Laravel, eventos y servicios desacoplados para asegurar <span class="text-accent-solid">escalabilidad, trazabilidad y consistencia de datos</span> en todo el ecosistema.</p>
                </div>
            </div>
            <div class="col-md-4 mt-3 mt-md-0">
                <div class="impact-card p-3 p-md-4 rounded-3 rounded-md-4 text-center h-100 wood-shadow-lg floating">
                    <div class="impact-visual impact-visual--automation" aria-hidden="true">
                        <div class="robot-head"><i class="fas fa-robot"></i></div>
                        <div class="gear gear-a"><i class="fas fa-cog"></i></div>
                        <div class="gear gear-b"><i class="fas fa-cog"></i></div>
                        <div class="pulse-ring"></div>
                    </div>
                    <h4 class="impact-title fw-bold fs-5 text-dark">Automatizaciones Inteligentes</h4>
                    <p class="impact-desc small text-medium">Orquestamos procesos automáticos para validación, sincronización y cálculo, reduciendo carga manual y acelerando el ciclo <span class="text-accent-solid">del dato al reporte</span>.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- SECCIÓN DE VALORES PREMIUM --}}
<section class="values-premium py-6">
    <div class="container">
        <div class="section-header-premium text-center mb-6">
            <span class="text-gold-gradient fw-bold text-uppercase tracking-widest small d-block mb-2">ADN Tecnológico</span>
            <h2 class="display-5 fw-bold text-dark mb-3" style="font-family: 'Playfair Display', serif;">Filosofía de Impacto</h2>
            <div class="premium-divider mx-auto mb-4"></div>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">Construimos el futuro de la informática ecológica basándonos en cuatro pilares inquebrantables que garantizan la integridad sistémica y operativa.</p>
        </div>

        <div class="row g-4 justify-content-center">
            {{-- Valor 1: Innovación --}}
            <div class="col-lg-3 col-md-6">
                <div class="value-card-glass h-100">
                    <div class="value-card-glass__content p-4">
                        <div class="value-card-glass__icon-wrapper mb-4">
                            <div class="icon-circle gold-glow"><i class="fas fa-code-branch"></i></div>
                            <span class="value-number">01</span>
                        </div>
                        <h4 class="h5 fw-bold mb-3">Diseño Resiliente</h4>
                        <p class="text-secondary small mb-0">Reemplazamos aplicaciones frágiles con arquitecturas de mensajería asíncrona, tolerantes a las desconexiones más extremas.</p>
                    </div>
                    <div class="value-card-glass__footer-line bg-gold"></div>
                </div>
            </div>

            {{-- Valor 2: Sostenibilidad --}}
            <div class="col-lg-3 col-md-6">
                <div class="value-card-glass h-100">
                    <div class="value-card-glass__content p-4">
                        <div class="value-card-glass__icon-wrapper mb-4">
                            <div class="icon-circle green-glow"><i class="fas fa-leaf"></i></div>
                            <span class="value-number">02</span>
                        </div>
                        <h4 class="h5 fw-bold mb-3">Empoderamiento</h4>
                        <p class="text-secondary small mb-0">Democratizamos el acceso a los mercados de carbono para pequeños productores a través de datos confiables de biomasa.</p>
                    </div>
                    <div class="value-card-glass__footer-line bg-green"></div>
                </div>
            </div>

            {{-- Valor 3: Excelencia --}}
            <div class="col-lg-3 col-md-6">
                <div class="value-card-glass h-100">
                    <div class="value-card-glass__content p-4">
                        <div class="value-card-glass__icon-wrapper mb-4">
                            <div class="icon-circle blue-glow"><i class="fas fa-calculator"></i></div>
                            <span class="value-number">03</span>
                        </div>
                        <h4 class="h5 fw-bold mb-3">Rigor Científico</h4>
                        <p class="text-secondary small mb-0">Aplicamos modelos matemáticos precisos en nuestra capa lógica para una trazabilidad maderera impecable.</p>
                    </div>
                    <div class="value-card-glass__footer-line bg-blue"></div>
                </div>
            </div>

            {{-- Valor 4: Transparencia --}}
            <div class="col-lg-3 col-md-6">
                <div class="value-card-glass h-100">
                    <div class="value-card-glass__content p-4">
                        <div class="value-card-glass__icon-wrapper mb-4">
                            <div class="icon-circle dark-glow"><i class="fas fa-fingerprint"></i></div>
                            <span class="value-number">04</span>
                        </div>
                        <h4 class="h5 fw-bold mb-3">Trazabilidad Total</h4>
                        <p class="text-secondary small mb-0">Eventos auditables de extremo a extremo que construyen confianza y garantizan la legalidad absoluta de los recursos.</p>
                    </div>
                    <div class="value-card-glass__footer-line bg-dark"></div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
    <script src="{{ asset('js/acerca-animaciones.js') }}"></script>
@endpush