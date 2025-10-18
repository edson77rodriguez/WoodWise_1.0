<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="WoodWise - Solución tecnológica para cálculo preciso de volúmenes maderables y gestión forestal sostenible">
    <title>WoodWise - Gestión Inteligente de Bosques</title>

    <link rel="icon" href="{{ asset('img/woodwise.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('img/woodwise.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <link href="{{ asset('css/WW/welcome.css') }}" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

    <div class="video-background">
        <video autoplay muted loop playsinline id="bg-video" poster="{{ asset('img/forest-bg.jpg') }}">
            <source src="{{ asset('videos/tree.mp4') }}" type="video/mp4">
            <img src="{{ asset('img/forest-bg.jpg') }}" alt="Fondo de bosque (fallback)">
        </video>
    </div>
    
    <div class="particles" id="particles-js"></div>
    
    <header class="navbar navbar-expand-lg navbar-dark sticky-top navbar-initial" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('welcome') }}">
                <img src="{{ asset('img/woodwise.png') }}" 
                     alt="WoodWise Logo" 
                     width="50" 
                     height="50" 
                     class="me-2 rounded-circle floating">
                <span class="text-gradient">WoodWise</span>
            </a>

            <button class="navbar-toggler" type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav"
                    aria-controls="navbarNav"
                    aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav gap-1">
                    <li class="nav-item">
                        <a href="{{ route('welcome') }}" class="nav-link active">
                            <i class="fas fa-home me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('acerca') }}" class="nav-link">
                            <i class="fas fa-info-circle me-1"></i> Acerca
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('contacto') }}" class="nav-link">
                            <i class="fas fa-envelope me-1"></i> Contacto
                        </a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="{{ route('login') }}" class="nav-link btn-login">
                            <i class="fas fa-sign-in-alt me-1"></i> Ingresar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="flex-grow-1">
        @section('content')
        <section class="hero-section">
            <div class="hero-overlay"></div>
            <div class="container">
                <div class="row justify-content-center align-items-center">
                    <div class="col-lg-10 fade-in">
                        <div class="hero-card">
                            <div class="row g-0">
                                <div class="col-md-6 hero-img">
                                    <img src="{{ asset('img/portada.jpg') }}" 
                                         class="img-fluid" 
                                         alt="Sistema WoodWise en acción"
                                         loading="lazy">
                                </div>
                                <div class="col-md-6 hero-text-content">
                                    <h1 class="hero-title">Bienvenido a <span>WoodWise</span></h1>
                                    <p class="hero-text">
                                        La solución tecnológica más avanzada para el cálculo preciso de volúmenes maderables. 
                                        Nuestra plataforma combina algoritmos inteligentes con datos satelitales para ofrecer 
                                        la gestión forestal más eficiente del mercado, garantizando precisión y sostenibilidad 
                                        en cada análisis.
                                    </p>
                                    <div class="d-flex flex-wrap gap-3 btn-group">
                                        <a href="{{ route('login') }}" class="btn btn-wood">
                                            <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                                        </a>
                                        <a href="{{ route('register') }}" class="btn btn-outline-wood">
                                            <i class="fas fa-user-plus me-1"></i> Registrarse
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="features-section py-5">
            <div class="container">
                <div class="features-carousel-container">
                    <div class="features-carousel-track" id="featuresCarousel">
                        <div class="feature-card">
                            <i class="fas fa-tree"></i>
                            <h3>Precisión Forestal</h3>
                            <p>Cálculos volumétricos con margen de error menor al 1%</p>
                        </div>
                        <div class="feature-card">
                            <i class="fas fa-satellite-dish"></i>
                            <h3>Monitoreo con Dron</h3>
                            <p>Análisis multiespectral con drones de última generación</p>
                        </div>
                        <div class="feature-card">
                            <i class="fas fa-leaf"></i>
                            <h3>Gestión de Carbono</h3>
                            <p>Cuantificación de captura de CO2 con modelos científicos</p>
                        </div>
                        <div class="feature-card">
                            <i class="fas fa-tree"></i>
                            <h3>Precisión Forestal</h3>
                            <p>Cálculos volumétricos con margen de error menor al 1%</p>
                        </div>
                        <div class="feature-card">
                            <i class="fas fa-satellite-dish"></i>
                            <h3>Monitoreo con Dron</h3>
                            <p>Análisis multiespectral con drones de última generación</p>
                        </div>
                        <div class="feature-card">
                            <i class="fas fa-leaf"></i>
                            <h3>Gestión de Carbono</h3>
                            <p>Cuantificación de captura de CO2 con modelos científicos</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @show
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                
                <div class="col-lg-4 fade-in">
                    <div class="footer-brand-container">
                        <h3 class="footer-logo">Wood<span>Wise</span></h3>
                        <p class="footer-description">Tecnología avanzada para la gestión forestal sostenible. Optimización de recursos con precisión científica.</p>
                        <div class="social-icons">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <a href="https://www.instagram.com/x_edsonj?igsh=Ynh0cXBsZXFpZDR6" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4 fade-in">
                    <div class="footer-column">
                        <h5 class="footer-title">Enlaces</h5>
                        <ul class="footer-links">
                            <li><a href="{{ route('welcome') }}" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Inicio</a></li>
                            <li><a href="{{ route('acerca') }}" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Acerca de</a></li>
                            <li><a href="{{ route('contacto') }}" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Contacto</a></li>
                            <li><a href="#" class="footer-link"><i class="fas fa-chevron-right me-2"></i>Blog</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 fade-in">
                    <div class="footer-column">
                        <h5 class="footer-title">Contacto</h5>
                        <ul class="footer-contact">
                            <li class="contact-item">
                                <i class="fas fa-map-marker-alt contact-icon"></i>
                                <span>Av. Forestal 123, Santiago</span>
                            </li>
                            <li class="contact-item">
                                <i class="fas fa-phone-alt contact-icon"></i>
                                <span>+56 9 1234 5678</span>
                            </li>
                            <li class="contact-item">
                                <i class="fas fa-envelope contact-icon"></i>
                                <span>contacto@woodwise.cl</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-4 fade-in">
                    <div class="footer-column">
                        <h5 class="footer-title">Legal</h5>
                        <ul class="footer-links">
                            <li><a href="#" class="footer-link"><i class="fas fa-gavel me-2"></i>Términos y condiciones</a></li>
                            <li><a href="#" class="footer-link"><i class="fas fa-shield-alt me-2"></i>Política de privacidad</a></li>
                            <li><a href="#" class="footer-link"><i class="fas fa-leaf me-2"></i>Sustentabilidad</a></li>
                            <li><a href="#" class="footer-link"><i class="fas fa-certificate me-2"></i>Certificaciones</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="copyright text-center fade-in">
                <p class="copyright-text">&copy; 2025 WoodWise - Todos los derechos reservados</p>
                <small class="developer-credit">Desarrollado con <i class="fas fa-heart heart-icon"></i> por TESVB COMPANY</small>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    
    <script src="{{ asset('js/WW/welcome.js') }}"></script>
    
    </body>
</html>