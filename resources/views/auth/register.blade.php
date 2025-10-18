@extends('layouts.app')

@section('title', 'Registro - WoodWise')

{{-- 1. CSS movido al stack 'styles' (carga en <head>) --}}
@push('styles')
    <link href="{{ asset('css/WW/register.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container py-3 py-md-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-gradient-primary py-3">
                    <div class="text-center">
                        <h2 class="text-white mb-0 h4">{{ __('Crear nueva cuenta') }}</h2>
                        <p class="text-white-50 mb-0 small">Únete a nuestra plataforma</p>
                    </div>
                </div>

                <div class="card-body p-4"> {{-- Padding base unificado (CSS lo maneja en móvil) --}}
                    
                    {{-- Alertas de Sesión (Estructura correcta) --}}
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3 rounded-2" role="alert">
                         <div class="d-flex align-items-center"><i class="fas fa-exclamation-circle me-2"></i><div class="small">{{ session('error') }}</div><button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button></div>
                    </div>
                    @endif
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3 rounded-2" role="alert">
                         <div class="d-flex align-items-center"><i class="fas fa-check-circle me-2"></i><div class="small">{{ session('success') }}</div><button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button></div>
                    </div>
                    @endif

                    <form id="register-form" method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
                        @csrf

                        <div class="mb-3">
                            <h5 class="form-section-title"><i class="fas fa-user-circle me-1"></i> Información Personal</h5>
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" placeholder="Nombre" value="{{ old('nom') }}" required autofocus>
                                        <label for="nom">{{ __('Nombre') }}</label>
                                        @error('nom')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('ap') is-invalid @enderror" id="ap" name="ap" placeholder="Apellido Paterno" value="{{ old('ap') }}" required>
                                        <label for="ap">{{ __('Apellido Paterno') }}</label>
                                        @error('ap')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('am') is-invalid @enderror" id="am" name="am" placeholder="Apellido Materno" value="{{ old('am') }}" required>
                                        <label for="am">{{ __('Apellido Materno') }}</label>
                                        @error('am')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" placeholder="Teléfono" value="{{ old('telefono') }}" required inputmode="tel">
                                        <label for="telefono">{{ __('Teléfono') }}</label>
                                        @error('telefono')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h5 class="form-section-title"><i class="fas fa-key me-1"></i> Credenciales de Acceso</h5>
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required inputmode="email">
                                        <label for="email">{{ __('Correo Electrónico') }}</label>
                                        @error('email')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Contraseña" required minlength="8">
                                        <label for="password">{{ __('Contraseña') }}</label>
                                        {{-- [REFACTORIZACIÓN] Eliminado onclick/style. Añadida clase y data-bs-target --}}
                                        <span class="toggle-password" data-bs-target="#password" title="Mostrar/Ocultar contraseña">
                                            <i class="fas fa-eye small"></i>
                                        </span>
                                        @error('password')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                                    </div>
                                    <small class="form-text text-muted">Mínimo 8 caracteres</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="password" class="form-control" id="password-confirm" name="password_confirmation" placeholder="Confirmar Contraseña" required minlength="8">
                                        <label for="password-confirm">{{ __('Confirmar Contraseña') }}</label>
                                        {{-- [REFACTORIZACIÓN] Eliminado onclick/style. Añadida clase y data-bs-target --}}
                                        <span class="toggle-password" data-bs-target="#password-confirm" title="Mostrar/Ocultar contraseña">
                                            <i class="fas fa-eye small"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="card border-light shadow-sm">
                                <div class="card-header bg-light py-2">
                                    <h5 class="form-section-title mb-0"><i class="fas fa-user-tag me-1"></i> Tipo de Usuario</h5>
                                </div>
                                <div class="card-body p-2 p-md-3">
                                    <label for="id_rol" class="form-label fw-bold text-muted mb-2 small">Selecciona tu tipo de usuario</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user-shield text-primary small"></i></span>
                                        {{-- [REFACTORIZACIÓN] Eliminado style="cursor: pointer;" (redundante) --}}
                                        <select class="form-select @error('id_rol') is-invalid @enderror" name="id_rol" id="id_rol" required>
                                            <option value="" disabled {{ old('id_rol') ? '' : 'selected' }}>-- Selecciona un rol --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id_rol }}" {{ old('id_rol') == $role->id_rol ? 'selected' : '' }}>
                                                    {{ ucfirst($role->nom_rol) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('id_rol')<div class="invalid-feedback d-block mt-1 small"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</div>@enderror
                                    <small class="form-text text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Este campo determina tus permisos.</small>
                                </div>
                            </div>
                        </div>

                        {{-- [REFACTORIZACIÓN] Usando 'd-none' y lógica de 'old()' para visibilidad inicial correcta --}}
                        <div id="cedula-container" class="mb-3 @if(old('id_rol') != 2) d-none @endif"> {{-- ASUMIENDO ID 2 = TECNICO. Ajustar si es necesario. --}}
                            <div class="form-floating">
                                <input type="text" class="form-control @error('cedula') is-invalid @enderror" id="cedula" name="cedula" placeholder="Cédula" value="{{ old('cedula', '') }}" inputmode="numeric">
                                <label for="cedula">{{ __('Cédula Profesional') }}</label>
                                @error('cedula')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold">
                                <i class="fas fa-user-plus me-1"></i> {{ __('Crear cuenta') }}
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <p class="text-muted small">¿Ya tienes una cuenta? 
                                <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">
                                    {{ __('Inicia sesión aquí') }}
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- 2. JS movido al stack 'scripts' (carga al final del <body>) --}}
@push('scripts')
    <script src="{{ asset('js/WW/register.js') }}"></script>
@endpush