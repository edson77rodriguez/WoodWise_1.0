<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/roda.jpg')); ?>">
    <title>SIGMAD - Sistema Inteligente de Gestión Maderable</title>

    <meta name="description" content="SIGMAD - Sistema Inteligente de Gestión Maderable para cálculo de volúmenes y administración de parcelas">

    
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    
    <link href="<?php echo e(asset('css/WW/dashboard.css')); ?>" rel="stylesheet">

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-light sidenav-hover-enabled"> 
    
    <div class="sidebar-overlay"></div>
    
    <aside class="sidenav" id="sidenav-main">
        <div class="sidenav-header">
            <a class="navbar-brand" href="<?php echo e(route('dashboard1')); ?>">
                <img src="<?php echo e(asset('assets/images/SIGMAD.svg')); ?>" height="32" alt="Logo SIGMAD" loading="eager">
                <span>SIGMAD</span>
            </a>
            
            <button class="btn-close-sidenav d-lg-none" id="sidenav-close" aria-label="Cerrar menú">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="sidenav-inner">
            <ul class="navbar-nav">
                <?php if(Auth::user()->persona->rol->nom_rol == 'Administrador'): ?>
                    
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('dashboard1') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard1')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-home text-primary"></i></div>
                            <span class="nav-link__text">Inicio</span>
                        </a>
                    </li>
                    
                    <li class="nav-item-header">Configuración</li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('usuarios*') ? 'active' : ''); ?>" href="<?php echo e(route('usuarios.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-user-shield text-primary"></i></div>
                            <span class="nav-link__text">Usuarios</span>
                        </a>
                    </li> 
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('especies*') ? 'active' : ''); ?>" href="<?php echo e(route('especies.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-tree text-success"></i></div>
                            <span class="nav-link__text">Especies</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('formulas*') ? 'active' : ''); ?>" href="<?php echo e(route('formulas.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-square-root-alt text-secondary"></i></div>
                            <span class="nav-link__text">Fórmulas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('tipo_estimaciones*') ? 'active' : ''); ?>" href="<?php echo e(route('tipo_estimaciones.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-ruler-combined text-info"></i></div>
                            <span class="nav-link__text">Tipos Estimación</span>
                        </a>
                    </li>

                    <li class="nav-item-header">Gestión</li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('tecnicos*') ? 'active' : ''); ?>" href="<?php echo e(route('tecnicos.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-user-tie text-secondary"></i></div>
                            <span class="nav-link__text">Técnicos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('productores*') ? 'active' : ''); ?>" href="<?php echo e(route('productores.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-tractor text-success"></i></div>
                            <span class="nav-link__text">Productores</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('parcelas*') ? 'active' : ''); ?>" href="<?php echo e(route('parcelas.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-map text-dark"></i></div>
                            <span class="nav-link__text">Parcelas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('turno_cortas*') ? 'active' : ''); ?>" href="<?php echo e(route('turno_cortas.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-calendar-alt text-primary"></i></div>
                            <span class="nav-link__text">Planificación Cortas</span>
                        </a>
                    </li>

                    <li class="nav-item-header">Operaciones</li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('trozas*') ? 'active' : ''); ?>" href="<?php echo e(route('trozas.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-cut text-warning"></i></div>
                            <span class="nav-link__text">Trozas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('estimaciones*') ? 'active' : ''); ?>" href="<?php echo e(route('estimaciones.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-calculator text-danger"></i></div>
                            <span class="nav-link__text">Estimaciones</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('asigna_parcelas*') ? 'active' : ''); ?>" href="<?php echo e(route('asigna_parcelas.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-map-marked-alt text-info"></i></div>
                            <span class="nav-link__text">Asignación Parcelas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('arboles_completos*') ? 'active' : ''); ?>" href="<?php echo e(route('arboles.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-tree text-warning"></i></div>
                            <span class="nav-link__text">Árboles Completos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('estimaciones_arboles_completos*') ? 'active' : ''); ?>" href="<?php echo e(route('estimaciones1.index')); ?>">
                            <div class="nav-link__icon"><i class="fas fa-chart-bar text-success"></i></div>
                            <span class="nav-link__text">Estimaciones Árboles</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="sidenav-footer">
            <small>© <?php echo e(date('Y')); ?> SIGMAD v1.0</small>
        </div>
    </aside>

    <main class="main-content">
        <nav class="navbar-main" id="navbarBlur">
            <div class="d-flex align-items-center justify-content-between w-100">
                
                <div class="d-flex align-items-center">
                    <button class="navbar-toggler-sidebar d-lg-none" id="sidenav-toggler" aria-label="Abrir menú">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="breadcrumb-container">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard1')); ?>"><i class="fas fa-home"></i> Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo $__env->yieldContent('breadcrumb', 'Dashboard'); ?></li>
                        </ol>
                        <h6 class="page-title"><?php echo $__env->yieldContent('title', 'Panel de Control'); ?></h6>
                    </div>
                </div>

                <div class="navbar-user-menu">
                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(route('especies.catalogo')); ?>"><i class="fas fa-seedling me-1"></i> Catálogo</a>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('perfil.index')); ?>"><i class="fas fa-user me-1"></i> Perfil</a>
                    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt me-1"></i> Salir</button>
                    </form>
                </div>

            </div>
        </nav>

        <div class="content-wrapper">
            <?php if(request()->is('dashboard1') && Auth::user()->persona->rol->nom_rol == 'Administrador'): ?>
                
                
                <?php echo $__env->make('partials.dashboard-widgets', [
                    'userCount' => $userCount ?? 0,
                    'parcelaCount' => $parcelaCount ?? 0,
                    'especieCount' => $especieCount ?? 0,
                    'turnosActivosCount' => $turnosActivosCount ?? 0
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
                
                <?php echo $__env->yieldContent('crud_content'); ?>
            <?php endif; ?>
        </div>
    </main>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    
    
    <script src="<?php echo e(asset('js/WW/dashboard.js')); ?>"></script> 

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH /home/u535412430/domains/woodwise.me/woodwise_src/resources/views/dashboard.blade.php ENDPATH**/ ?>