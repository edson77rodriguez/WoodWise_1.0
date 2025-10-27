<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="WoodWise - Solución tecnológica para gestión forestal sostenible y cálculo preciso de volúmenes maderables">
    <title>@yield('title', 'WoodWise - Gestión Forestal Inteligente')</title>
    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" 
        crossorigin="anonymous" 
        referrerpolicy="no-referrer" 
    />
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" as="style">
    <link rel="preload" href="{{ asset('css/bootstrap.min.css') }}" as="style">

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
     <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/WW/layout.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    <header class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('welcome') }}">
                <img src="{{ asset('img/woodwise.png') }}" 
                     alt="WoodWise Logo"
                     width="40"
                     height="40"
                     class="me-2 rounded-circle floating"
                     loading="eager">
                <span class="text-gradient">WoodWise</span>
            </a>

            <button class="navbar-toggler border-0" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav"
                    aria-controls="navbarNav"
                    aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav gap-1">

                    @if (!Auth::check() || (Auth::check() && Auth::user()->persona->rol->nom_rol != 'Tecnico'))
                        <li class="nav-item">
                            <a href="{{ route('welcome') }}"
                               class="nav-link {{ request()->routeIs('welcome') ? 'active' : '' }}">
                                <i class="bi bi-house-door-fill me-1"></i> Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('acerca') }}"
                               class="nav-link {{ request()->routeIs('acerca') ? 'active' : '' }}">
                                <i class="bi bi-info-circle-fill me-1"></i> Acerca
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('contacto') }}"
                               class="nav-link {{ request()->routeIs('contacto') ? 'active' : '' }}">
                                <i class="bi bi-envelope-fill me-1"></i> Contacto
                            </a>
                        </li>
                    @endif

                    @guest
                        <li class="nav-item ms-lg-3">
                            <a href="{{ route('login') }}"
                               class="nav-link bg-success bg-opacity-90">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Ingresar
                            </a>
                        </li>
                    @else
                        <li class="nav-item ms-lg-3">
                            <form action="{{ route('logout') }}" method="POST" class="d-flex">
                                @csrf
                                <button type="submit" class="nav-link bg-danger bg-opacity-90 border-0">
                                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                                </button>
                            </form>
                        </li>
                    @endguest
                    
                </ul>
            </div>
        </div>
    </header>

    <main class="flex-grow-1 pt-3 pt-md-4">
        @yield('content')
    </main>

    <footer class="footer py-4">
    <div class="container">
        <div class="row g-4">
            
            <div class="col-lg-4 fade-in">
                <div class="footer-brand-container">
                    <h3 class="footer-logo">Wood<span>Wise</span></h3>
                    <p class="footer-description">Tecnología avanzada para la gestión forestal sostenible. Optimización de recursos con precisión científica.</p>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                        <a href="https://www.instagram.com/x_edsonj?igsh=Ynh0cXBsZXFpZDR6" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 fade-in">
                <div class="footer-column">
                    <h5 class="footer-title">Enlaces</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('welcome') }}" class="footer-link"><i class="bi bi-chevron-right me-2"></i>Inicio</a></li>
                        <li><a href="{{ route('acerca') }}" class="footer-link"><i class="bi bi-chevron-right me-2"></i>Acerca de</a></li>
                        <li><a href="{{ route('contacto') }}" class="footer-link"><i class="bi bi-chevron-right me-2"></i>Contacto</a></li>
                        <li><a href="#" class="footer-link"><i class="bi bi-chevron-right me-2"></i>Blog</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 fade-in">
                <div class="footer-column">
                    <h5 class="footer-title">Contacto</h5>
                    <ul class="footer-contact">
                        <li class="contact-item">
                            <i class="bi bi-geo-alt-fill contact-icon"></i>
                            <span>Av. Forestal 123, Santiago</span>
                        </li>
                        <li class="contact-item">
                            <i class="bi bi-telephone-fill contact-icon"></i>
                            <span>+56 9 1234 5678</span>
                        </li>
                        <li class="contact-item">
                            <i class="bi bi-envelope-fill contact-icon"></i>
                            <span>contacto@woodwise.cl</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-4 fade-in">
                <div class="footer-column">
                    <h5 class="footer-title">Legal</h5>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link"><i class="bi bi-gavel me-2"></i>Términos y condiciones</a></li>
                        <li><a href="#" class="footer-link"><i class="bi bi-shield-check me-2"></i>Política de privacidad</a></li>
                        <li><a href="#" class="footer-link"><i class="bi bi-tree-fill me-2"></i>Sustentabilidad</a></li>
                        <li><a href="#" class="footer-link"><i class="bi bi-patch-check-fill me-2"></i>Certificaciones</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="copyright text-center fade-in">
            <p class="copyright-text">&copy; 2025 WoodWise - Todos los derechos reservados</p>
            <small class="developer-credit">Desarrollado con <i class="bi bi-heart-fill heart-icon"></i> por TESVB COMPANY</small>
        </div>
    </div>
</footer>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/WW/layout.js') }}"></script>

    @stack('scripts')
</body>
</html>