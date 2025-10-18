@extends('tecnicos.dashboard')

@section('content')
    <div class="container-fluid py-4">
        <!-- Encabezado -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-brown shadow-brown border-radius-xl">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="text-white mb-1">Panel del Técnico Forestal</h4>
                                <p class="text-white opacity-8 mb-0">Gestión especializada de recursos maderables</p>
                            </div>
                            <div class="bg-white shadow rounded-circle p-2">
                                <i class="fas fa-tree text-brown"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de bienvenida -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-radius-lg" style="background-color: #f5e9dc;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape icon-lg bg-brown shadow-brown text-center rounded-circle">
                                <i class="fas fa-user-tie text-white"></i>
                            </div>
                            <div class="ms-3">
                                @auth
                                    <h3 class="text-brown-dark mb-0">¡Bienvenido Técnico {{ Auth::user()->persona->nombre ?? 'Usuario' }}!</h3>
                                    <p class="text-sm text-brown mb-0">
                                        <i class="fas fa-id-card me-1"></i> Cédula: {{ $tecnico->cedula_p ?? 'No disponible' }}
                                    </p>
                                @else
                                    <h3 class="text-brown-dark mb-0">¡Bienvenido!</h3>
                                    <p class="text-sm text-brown mb-0">
                                        <i class="fas fa-id-card me-1"></i> Por favor inicie sesión
                                    </p>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resto del código de la vista... -->
        @isset($parcelas)
            <!-- Botón principal para ver parcelas -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm border-radius-lg" style="background-color: #f5e9dc;">
                        <div class="card-body p-3 text-center">
                            <button id="toggleParcelasBtn" class="btn bg-gradient-brown text-white btn-lg btn-action">
                                <i class="fas fa-map-marked-alt me-2"></i> Ver Parcelas
                            </button>
                            <p class="text-brown mt-2 mb-0" id="parcelasStatus">
                                @if($parcelas->count() > 0)
                                    Tienes {{ $parcelas->count() }} parcelas asignadas
                                @else
                                    No tienes parcelas asignadas actualmente
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de CREAR PARCELA (oculta inicialmente) -->
            <div id="crearParcelaSection" style="display: none;">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-radius-lg" style="background-color: #f5e9dc;">
                            <div class="card-body p-3 text-center">
                                <a href="{{ route('parcelas.create') }}" class="btn bg-gradient-brown text-white btn-lg btn-action">
                                    <i class="fas fa-plus-circle me-2"></i> CREAR PARCELA
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenedor de parcelas (inicialmente oculto) -->
            <div id="parcelasContainer" style="display: none;">
                <!-- Listado de parcelas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-radius-lg" style="background-color: #f5e9dc;">
                            <div class="card-header border-0 bg-transparent pb-0 p-3">
                                <h5 class="text-brown-dark mb-0">
                                    <i class="fas fa-map-marked-alt me-2"></i>
                                    Mis Parcelas Registradas
                                </h5>
                            </div>

                            <div class="card-body p-3">
                                @if($parcelas->isEmpty())
                                    <div class="empty-state text-center py-5" style="background-color: #f0e0d0;">
                                        <div class="icon icon-lg bg-brown text-white shadow-brown rounded-circle mb-3 mx-auto">
                                            <i class="fas fa-map opacity-10"></i>
                                        </div>
                                        <h5 class="text-brown-dark mb-1">No tienes parcelas registradas</h5>
                                        <p class="text-sm text-brown mb-3">Puedes crear una nueva parcela usando el botón superior</p>
                                    </div>
                                @else
                                    @foreach($parcelas as $parcela)
                                        <div class="parcela-item mb-4 p-3 border-radius-lg" style="background-color: #f0e0d0;">
                                            <h6 class="text-brown-dark mb-2"><strong>{{ $parcela->nom_parcela }}</strong></h6>
                                            <p class="text-sm text-brown mb-1">{{ $parcela->ubicacion }}</p>
                                            <p class="text-sm text-brown mb-1"><strong>Extensión:</strong> {{ $parcela->extension }} ha</p>
                                            <p class="text-sm text-brown mb-1"><strong>CP:</strong> {{ $parcela->CP }}</p>
                                            <p class="text-sm text-brown mb-3"><strong>Trozas:</strong> {{ $parcela->trozas_count }}</p>

                                            <!-- Botones de acción para esta parcela -->
                                            <div class="row g-2">
                                                <div class="col-6 col-md-3">
                                                    <a href="#" class="btn btn-block bg-brown text-white btn-parcela-action">
                                                        TROZAS
                                                    </a>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <a href="#" class="btn btn-block bg-brown text-white btn-parcela-action">
                                                        ESTIMACIONES
                                                    </a>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <a href="#" class="btn btn-block bg-brown text-white btn-parcela-action">
                                                        TURNO CORTA
                                                    </a>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <a href="{{ route('trozas.index', ['parcela' => $parcela->id_parcela]) }}"
                                                       class="btn btn-block bg-brown text-white btn-parcela-action">
                                                        DETALLES
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endisset
    </div>

    <!-- JavaScript para mostrar/ocultar secciones -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggleParcelasBtn');
            const parcelasContainer = document.getElementById('parcelasContainer');
            const crearParcelaSection = document.getElementById('crearParcelaSection');

            if (toggleBtn && parcelasContainer && crearParcelaSection) {
                toggleBtn.addEventListener('click', function() {
                    if (parcelasContainer.style.display === 'none') {
                        parcelasContainer.style.display = 'block';
                        crearParcelaSection.style.display = 'block';
                        toggleBtn.innerHTML = '<i class="fas fa-eye-slash me-2"></i> Ocultar Parcelas';
                    } else {
                        parcelasContainer.style.display = 'none';
                        crearParcelaSection.style.display = 'none';
                        toggleBtn.innerHTML = '<i class="fas fa-map-marked-alt me-2"></i> Ver Parcelas';
                    }
                });
            }
        });
    </script>

    <!-- Estilos (se mantienen igual) -->
    <style>
        /* Colores café personalizados */
        .bg-brown { background-color: #8B5A2B; }
        .bg-gradient-brown { background: linear-gradient(135deg, #8B5A2B 0%, #A67C52 100%); }
        .text-brown { color: #8B5A2B; }
        .text-brown-dark { color: #5D4037; }
        .bg-brown-light { background-color: #f5e9dc; }
        .shadow-brown { box-shadow: 0 4px 20px 0 rgba(139, 90, 43, 0.14), 0 7px 10px -5px rgba(139, 90, 43, 0.4); }

        /* Botones de acción */
        .btn-action {
            border-radius: 10px;
            padding: 12px 15px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(139, 90, 43, 0.2);
        }

        /* Botones por parcela */
        .btn-parcela-action {
            border-radius: 8px;
            padding: 8px 5px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.2s ease;
        }

        /* Items de parcela */
        .parcela-item {
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .parcela-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(139, 90, 43, 0.1);
        }

        /* Bordes redondeados */
        .border-radius-lg { border-radius: 16px; }

        /* Espaciado entre botones */
        .g-2 {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }

        /* Estado vacío */
        .empty-state {
            border-radius: 12px;
            padding: 2rem;
        }
    </style>
@endsection
