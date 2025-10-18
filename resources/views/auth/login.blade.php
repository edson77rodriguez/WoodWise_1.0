@extends('layouts.app')

@section('title', 'Iniciar Sesión - WoodWise')

{{-- 1. CSS movido al stack de 'styles' en el <head> --}}
@push('styles')
    <link href="{{ asset('css/WW/login.css') }}" rel="stylesheet">
@endpush


@section('content')
<div class="container-fluid bg-light min-vh-100 d-flex align-items-center">
    <div class="container py-4 py-md-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    
                    <div class="card-header bg-gradient-primary py-3 py-md-4">
                        <div class="text-center position-relative">
                            <div class="logo-3d-container mb-3">
                                <img src="{{ asset('img/woodwise.png') }}" alt="WoodWise Logo" 
                                     class="logo-3d img-fluid">
                            </div>
                            <h2 class="text-white mb-1 h4 animate__animated animate__fadeIn">Bienvenido a WoodWise</h2>
                            <p class="text-white-50 mb-0 small animate__animated animate__fadeIn animate__delay-1s">Gestión Forestal Inteligente</p>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i> {{-- FA Icon: Asegúrate que FA esté cargado en layout si usas esto --}}
                                <div>Credenciales incorrectas. Por favor intente nuevamente.</div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                            @csrf
                            
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           placeholder="Correo electrónico"
                                           value="{{ old('email') }}" 
                                           required 
                                           autofocus>
                                    <label for="email">
                                        <i class="fas fa-envelope me-2 text-muted"></i>Correo electrónico
                                    </label>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-floating position-relative">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Contraseña"
                                           required>
                                    <label for="password">
                                        <i class="fas fa-lock me-2 text-muted"></i>Contraseña
                                    </label>
                                    
                                    {{-- [REFACTORIZACIÓN] Eliminados 'onclick' y 'style'. 
                                         Añadido 'data-bs-target' para que el JS lo encuentre. --}}
                                    <span class="position-absolute end-0 top-50 translate-middle-y me-3 toggle-password" 
                                          data-bs-target="#password"
                                          title="Mostrar/Ocultar contraseña">
                                        <i class="fas fa-eye text-primary"></i>
                                    </span>
                                    
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remember" 
                                           id="remember">
                                    <label class="form-check-label text-muted" for="remember">
                                        <i class="fas fa-check-circle me-1"></i>Recordar sesión
                                    </label>
                                </div>
                                <a href="{{ route('password.request') }}" 
                                   class="text-decoration-none text-primary fw-bold">
                                    <i class="fas fa-key me-1"></i>¿Olvidó su contraseña?
                                </a>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </button>
                            </div>

                            <div class="text-center mt-4">
                                <p class="text-muted">¿No tiene una cuenta? 
                                    <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">
                                        <i class="fas fa-user-plus me-1"></i>Regístrese aquí
                                    </a>
                                </p>
                            </div>

                            <div class="position-relative my-4">
                                <hr>
                                <div class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted">
                                    O continuar con
                                </div>
                            </div>

                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="btn btn-outline-primary rounded-circle p-3 social-btn">
                                    <i class="fab fa-google"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle p-3 social-btn">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle p-3 social-btn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- 2. JS movido al stack de 'scripts' --}}
@push('scripts')
    <script src="{{ asset('js/WW/login.js') }}"></script>
@endpush