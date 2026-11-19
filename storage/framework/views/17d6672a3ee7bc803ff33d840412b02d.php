<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIGMAD - Sistema Inteligente de Gestión Maderable para administración sostenible y cálculo preciso de volúmenes">
    <title><?php echo $__env->yieldContent('title', 'SIGMAD - Sistema Inteligente de Gestión Maderable'); ?></title>

    <link rel="icon" href="<?php echo e(asset('assets/images/SIGMAD.svg')); ?>" type="image/svg+xml">

    
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>

    
    <link href="<?php echo e(asset('css/bootstrap.min.css')); ?>" rel="stylesheet">

    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">

    
    <link href="<?php echo e(asset('css/WW/layout.css')); ?>?v=<?php echo e(filemtime(public_path('css/WW/layout.css'))); ?>" rel="stylesheet">

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="d-flex flex-column min-vh-100">


<header class="navbar navbar-expand-lg navbar-dark" id="mainNavbar">
    <div class="container">

        
        <a class="navbar-brand" href="<?php echo e(route('welcome')); ?>" aria-label="SIGMAD inicio">
            <img src="<?php echo e(asset('assets/images/SIGMAD.svg')); ?>"
                 alt="Logo SIGMAD" width="50" height="50">
            <span class="text-gradient">SIGMAD</span>
        </a>

        
        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav gap-lg-1 align-items-lg-center">

                <li class="nav-item">
                    <a href="<?php echo e(route('welcome')); ?>"
                       class="nav-link <?php echo e(request()->routeIs('welcome') ? 'active' : ''); ?>">
                        <i class="fas fa-home" aria-hidden="true"></i> Inicio
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo e(route('acerca')); ?>"
                       class="nav-link <?php echo e(request()->routeIs('acerca') ? 'active' : ''); ?>">
                        <i class="fas fa-info-circle" aria-hidden="true"></i> Acerca
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?php echo e(route('contacto')); ?>"
                       class="nav-link <?php echo e(request()->routeIs('contacto') ? 'active' : ''); ?>">
                        <i class="fas fa-envelope" aria-hidden="true"></i> Contacto
                    </a>
                </li>

                <?php if(auth()->guard()->guest()): ?>
                    <li class="nav-item ms-lg-2">
                        <a href="<?php echo e(route('login')); ?>" class="nav-link btn-login">
                            <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Ingresar
                        </a>
                    </li>
                <?php else: ?>
                    
                    <?php if(request()->routeIs('tecnico.*')): ?>
                        
                        <li class="nav-item ms-lg-2 user-menu-wrapper">
                            <button class="user-menu-trigger"
                                    id="userMenuBtn"
                                    type="button"
                                    aria-haspopup="dialog"
                                    aria-expanded="false"
                                    aria-controls="userDropdown">
                                <div class="user-avatar" aria-hidden="true">
                                    <span><?php echo e(strtoupper(substr(auth()->user()->persona->nom ?? 'U', 0, 1))); ?><?php echo e(strtoupper(substr(auth()->user()->persona->ap ?? '', 0, 1))); ?></span>
                                </div>
                                <div class="user-info-mini">
                                    <span class="user-name"><?php echo e(auth()->user()->persona->nom ?? 'Usuario'); ?></span>
                                    <span class="user-role">Técnico Forestal</span>
                                </div>
                                <i class="fas fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
                            </button>
                        </li>

                        
                        <div id="userDropdown"
                             role="dialog"
                             aria-modal="true"
                             aria-label="Menú de usuario">

                            
                            <button class="user-dropdown-close" type="button" aria-label="Cerrar menú">
                                <i class="fas fa-times" aria-hidden="true"></i>
                            </button>

                            
                            <div class="dropdown-header">
                                <div class="user-avatar-large" aria-hidden="true">
                                    <span><?php echo e(strtoupper(substr(auth()->user()->persona->nom ?? 'U', 0, 1))); ?><?php echo e(strtoupper(substr(auth()->user()->persona->ap ?? '', 0, 1))); ?></span>
                                </div>
                                <div class="user-details">
                                    <span class="name"><?php echo e(auth()->user()->persona->nom ?? ''); ?> <?php echo e(auth()->user()->persona->ap ?? ''); ?></span>
                                    <span class="email"><?php echo e(auth()->user()->email); ?></span>
                                    <span class="badge-role">
                                        <i class="fas fa-leaf" aria-hidden="true"></i>
                                        Técnico Forestal
                                    </span>
                                </div>
                            </div>

                            <div class="dropdown-divider" role="separator"></div>

                            
                            <div class="dropdown-body">
                                <a href="<?php echo e(route('perfil.index')); ?>" class="dropdown-item">
                                    <i class="fas fa-user" aria-hidden="true"></i>
                                    <span>Mi Perfil</span>
                                </a>

                                <a href="<?php echo e(route('especies.catalogo')); ?>" class="dropdown-item">
                                    <i class="fas fa-seedling" aria-hidden="true"></i>
                                    <span>Catálogo de Especies</span>
                                </a>

                                <button class="dropdown-item"
                                        type="button"
                                        disabled
                                        aria-disabled="true"
                                        title="Próximamente">
                                    <i class="fas fa-cog" aria-hidden="true"></i>
                                    <span>Configuración <small style="opacity:.55">(próximamente)</small></span>
                                </button>

                                <button class="dropdown-item"
                                        type="button"
                                        disabled
                                        aria-disabled="true"
                                        title="Próximamente">
                                    <i class="fas fa-chart-bar" aria-hidden="true"></i>
                                    <span>Estadísticas <small style="opacity:.55">(próximamente)</small></span>
                                </button>

                                <div class="dropdown-divider" role="separator"></div>

                                <form action="<?php echo e(route('logout')); ?>" method="POST" class="dropdown-form">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="dropdown-item logout-item">
                                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                                        <span>Cerrar Sesión</span>
                                    </button>
                                </form>
                            </div>

                        </div>

                    <?php else: ?>
                        
                        <li class="nav-item ms-lg-2">
                            <form action="<?php echo e(route('logout')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="nav-link btn-login">
                                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

            </ul>
        </div>

    </div>
</header>


<main class="flex-grow-1">
    <?php echo $__env->yieldContent('content'); ?>
</main>


<footer class="footer">
    <div class="container">
        <div class="row g-4">

            
            <div class="col-lg-4 fade-in">
                <div class="footer-brand-container">
                    <h3 class="footer-logo">SIGMAD</h3>
                    <p class="footer-description">
                        Sistema Inteligente de Gestión Maderable.
                        Optimización de recursos con precisión científica.
                    </p>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook">
                            <i class="fab fa-facebook-f" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="Twitter">
                            <i class="fab fa-twitter" aria-hidden="true"></i>
                        </a>
                        <a href="https://wa.me/527226367194" aria-label="WhatsApp" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                        </a>
                        <a href="https://www.instagram.com/x_edsonj?igsh=Ynh0cXBsZXFpZDR6"
                           aria-label="Instagram" target="_blank" rel="noopener">
                            <i class="fab fa-instagram" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>

            
            <div class="col-lg-2 col-md-4 fade-in">
                <div class="footer-column">
                    <h5 class="footer-title">Enlaces</h5>
                    <ul class="footer-links">
                        <li><a href="<?php echo e(route('welcome')); ?>" class="footer-link"><i class="fas fa-chevron-right"></i>Inicio</a></li>
                        <li><a href="<?php echo e(route('acerca')); ?>" class="footer-link"><i class="fas fa-chevron-right"></i>Acerca de</a></li>
                        <li><a href="<?php echo e(route('contacto')); ?>" class="footer-link"><i class="fas fa-chevron-right"></i>Contacto</a></li>
                        <li><a href="#" class="footer-link"><i class="fas fa-chevron-right"></i>Blog</a></li>
                    </ul>
                </div>
            </div>

            
            <div class="col-lg-3 col-md-4 fade-in">
                <div class="footer-column">
                    <h5 class="footer-title">Contacto</h5>
                    <ul class="footer-contact">
                        <li class="contact-item">
                            <i class="fas fa-map-marker-alt contact-icon" aria-hidden="true"></i>
                            <span>Carretera Federal Valle de Bravo Km 30, Ejido San Antonio Laguna, 51200 Valle de Bravo, Mex.</span>
                        </li>
                        <li class="contact-item">
                            <i class="fas fa-phone-alt contact-icon" aria-hidden="true"></i>
                            <span>+52 7226367194</span>
                        </li>
                        <li class="contact-item">
                            <i class="fab fa-whatsapp contact-icon" aria-hidden="true"></i>
                            <span>+52 7226367194</span>
                        </li>
                        <li class="contact-item">
                            <i class="fas fa-envelope contact-icon" aria-hidden="true"></i>
                            <span>woodwise.sigmad@gmail.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            
            <div class="col-lg-3 col-md-4 fade-in">
                <div class="footer-column">
                    <h5 class="footer-title">Legal</h5>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link"><i class="fas fa-gavel"></i>Términos y condiciones</a></li>
                        <li><a href="#" class="footer-link"><i class="fas fa-shield-alt"></i>Política de privacidad</a></li>
                        <li><a href="#" class="footer-link"><i class="fas fa-leaf"></i>Sustentabilidad</a></li>
                        <li><a href="#" class="footer-link"><i class="fas fa-certificate"></i>Certificaciones</a></li>
                    </ul>
                </div>
            </div>

        </div>

        
        <div class="copyright fade-in">
            <p class="copyright-text">&copy; 2025 SIGMAD — Todos los derechos reservados</p>
            <small class="developer-credit">
                Desarrollado con <i class="fas fa-heart heart-icon" aria-hidden="true"></i> por TESVB COMPANY
            </small>
        </div>
    </div>
</footer>


<script src="<?php echo e(asset('js/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(asset('js/WW/layout.js')); ?>"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>

</body>
</html><?php /**PATH C:\Users\rodri\Downloads\WW_V1\WoodWise_1.0\resources\views/layouts/app.blade.php ENDPATH**/ ?>