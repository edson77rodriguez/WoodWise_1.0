<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="{{ asset('img/roda.jpg') }}">
    <title>WoodWise - Gestión Forestal Inteligente</title>

    <meta name="description" content="Sistema de gestión forestal WoodWise para cálculo de volúmenes maderables y administración de parcelas">

    {{-- Fuentes para el nuevo tema 'Glassmorphism' --}}
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    {{-- Archivo CSS único y consolidado. Los otros (soft-ui, db.css) deben ser eliminados. --}}
    <link href="{{ asset('css/WW/dashboard.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="bg-light sidenav-hover-enabled"> {{-- Clase 'g-sidenav-show' eliminada ya que no pertenece al nuevo CSS --}}
    
    <div class="sidebar-overlay"></div>
    
    <aside class="sidenav" id="sidenav-main">
        <div class="sidenav-header">
            <a class="navbar-brand" href="{{ route('dashboard1') }}">
                <img src="{{ asset('img/woodwise.png') }}" height="32" alt="WoodWise Logo" loading="eager">
                <span>WoodWise</span>
            </a>
            {{-- Botón de cierre para móvil, controlado por JS --}}
            <button class="btn-close-sidenav d-lg-none" id="sidenav-close" aria-label="Cerrar menú">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="sidenav-inner">
            <ul class="navbar-nav">
                @if(Auth::user()->persona->rol->nom_rol == 'Administrador')
                    
                    {{-- [REFACTORIZACIÓN] Estructura de enlace semántica (BEM) que coincide con el nuevo CSS --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard1') ? 'active' : '' }}" href="{{ route('dashboard1') }}">
                            <div class="nav-link__icon"><i class="fas fa-home text-primary"></i></div>
                            <span class="nav-link__text">Inicio</span>
                        </a>
                    </li>
                    
                    <li class="nav-item-header">Configuración</li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('usuarios*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-user-shield text-primary"></i></div>
                            <span class="nav-link__text">Usuarios</span>
                        </a>
                    </li> 
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('especies*') ? 'active' : '' }}" href="{{ route('especies.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-tree text-success"></i></div>
                            <span class="nav-link__text">Especies</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('formulas*') ? 'active' : '' }}" href="{{ route('formulas.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-square-root-alt text-secondary"></i></div>
                            <span class="nav-link__text">Fórmulas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('tipo_estimaciones*') ? 'active' : '' }}" href="{{ route('tipo_estimaciones.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-ruler-combined text-info"></i></div>
                            <span class="nav-link__text">Tipos Estimación</span>
                        </a>
                    </li>

                    <li class="nav-item-header">Gestión</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('tecnicos*') ? 'active' : '' }}" href="{{ route('tecnicos.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-user-tie text-secondary"></i></div>
                            <span class="nav-link__text">Técnicos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('productores*') ? 'active' : '' }}" href="{{ route('productores.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-tractor text-success"></i></div>
                            <span class="nav-link__text">Productores</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('parcelas*') ? 'active' : '' }}" href="{{ route('parcelas.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-map text-dark"></i></div>
                            <span class="nav-link__text">Parcelas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('turno_cortas*') ? 'active' : '' }}" href="{{ route('turno_cortas.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-calendar-alt text-primary"></i></div>
                            <span class="nav-link__text">Planificación Cortas</span>
                        </a>
                    </li>

                    <li class="nav-item-header">Operaciones</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('trozas*') ? 'active' : '' }}" href="{{ route('trozas.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-cut text-warning"></i></div>
                            <span class="nav-link__text">Trozas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('estimaciones*') ? 'active' : '' }}" href="{{ route('estimaciones.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-calculator text-danger"></i></div>
                            <span class="nav-link__text">Estimaciones</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('asigna_parcelas*') ? 'active' : '' }}" href="{{ route('asigna_parcelas.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-map-marked-alt text-info"></i></div>
                            <span class="nav-link__text">Asignación Parcelas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('arboles_completos*') ? 'active' : '' }}" href="{{ route('arboles.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-tree text-warning"></i></div>
                            <span class="nav-link__text">Árboles Completos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('estimaciones_arboles_completos*') ? 'active' : '' }}" href="{{ route('estimaciones1.index') }}">
                            <div class="nav-link__icon"><i class="fas fa-chart-bar text-success"></i></div>
                            <span class="nav-link__text">Estimaciones Árboles</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        
        <div class="sidenav-footer">
            <small>© {{ date('Y') }} WoodWise v1.0</small>
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
                            <li class="breadcrumb-item"><a href="{{ route('dashboard1') }}"><i class="fas fa-home"></i> Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('breadcrumb', 'Dashboard')</li>
                        </ol>
                        <h6 class="page-title">@yield('title', 'Panel de Control')</h6>
                    </div>
                </div>

                <div class="navbar-user-menu">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('especies.catalogo') }}"><i class="fas fa-seedling me-1"></i> Catálogo</a>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('perfil.index') }}"><i class="fas fa-user me-1"></i> Perfil</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-sign-out-alt me-1"></i> Salir</button>
                    </form>
                </div>

            </div>
        </nav>

        <div class="content-wrapper">
            @if(request()->is('dashboard1') && Auth::user()->persona->rol->nom_rol == 'Administrador')
                {{-- Los widgets del dashboard ahora viven en un parcial para mantener este layout limpio --}}
                {{-- Las variables de conteo ($userCount, etc.) deben ser pasadas desde tu DashboardController --}}
                @include('partials.dashboard-widgets', [
                    'userCount' => $userCount ?? 0,
                    'parcelaCount' => $parcelaCount ?? 0,
                    'especieCount' => $especieCount ?? 0,
                    'turnosActivosCount' => $turnosActivosCount ?? 0
                ])
            @else
                {{-- Aquí es donde se carga el contenido de las páginas CRUD (ej. usuarios.index, parcelas.create) --}}
                @yield('crud_content')
            @endif
        </div>
    </main>

    {{-- jQuery ya no es necesario para Bootstrap 5, pero lo mantenemos si tus otros plugins (como SweetAlert2) lo requieren --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    
    {{-- JS dedicado para la lógica del sidebar móvil --}}
    <script src="{{ asset('js/WW/dashboard.js') }}"></script> 

    @stack('scripts')
</body>
</html>