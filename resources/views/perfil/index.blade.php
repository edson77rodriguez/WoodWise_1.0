@extends('layouts.app')

@section('title', 'Mi Perfil - SIGMAD')

@push('styles')
<style>
    .profile-page {
        position: relative;
        padding: 2rem 0 3rem;
        overflow: hidden;
    }

    .profile-hero {
        position: relative;
        border-radius: 28px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, rgba(26, 58, 22, 0.96), rgba(45, 90, 39, 0.96));
        color: #fff;
        box-shadow: 0 24px 60px rgba(26, 58, 22, 0.22);
        overflow: hidden;
    }

    .profile-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.25), transparent 35%),
                    radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.16), transparent 34%);
        pointer-events: none;
    }

    .profile-hero-grid {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.9fr);
        gap: 1.5rem;
        align-items: center;
    }

    .profile-brand {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .profile-avatar {
        width: 78px;
        height: 78px;
        border-radius: 22px;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 16px 34px rgba(0, 0, 0, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.18);
        flex-shrink: 0;
    }

    .profile-avatar span {
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: -0.05em;
    }

    .profile-kicker {
        text-transform: uppercase;
        letter-spacing: 0.18em;
        font-size: 0.72rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.78);
        margin-bottom: 0.35rem;
    }

    .profile-hero h1 {
        margin: 0;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        line-height: 1.1;
    }

    .profile-hero .subtitle {
        margin-top: 0.55rem;
        color: rgba(255, 255, 255, 0.88);
        font-size: 1rem;
        line-height: 1.55;
        max-width: 60ch;
    }

    .profile-badges {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }

    .profile-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .profile-panel {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 24px;
        padding: 1.25rem;
        backdrop-filter: blur(18px);
    }

    .profile-panel-title {
        margin: 0 0 0.95rem;
        font-size: 0.82rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.7);
        font-weight: 700;
    }

    .profile-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .metric-card {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 18px;
        padding: 1rem;
        min-height: 96px;
    }

    .metric-card .metric-label {
        display: block;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 0.35rem;
        font-weight: 700;
    }

    .metric-card .metric-value {
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.35;
        word-break: break-word;
    }

    .profile-body {
        display: grid;
        grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr);
        gap: 1.5rem;
    }

    .profile-card {
        background: rgba(255, 255, 255, 0.96);
        border-radius: 24px;
        padding: 1.5rem;
        box-shadow: 0 20px 40px rgba(26, 58, 22, 0.12);
        border: 1px solid rgba(76, 175, 80, 0.14);
    }

    .profile-card h2 {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1a3a16;
        margin-bottom: 1rem;
    }

    .detail-list {
        display: grid;
        gap: 0.85rem;
    }

    .detail-item {
        padding: 0.9rem 1rem;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.07), rgba(59, 130, 246, 0.05));
        border: 1px solid rgba(76, 175, 80, 0.12);
    }

    .detail-item .label {
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-size: 0.72rem;
        color: #64748b;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }

    .detail-item .value {
        font-size: 0.98rem;
        font-weight: 700;
        color: #1f2937;
        word-break: break-word;
    }

    .form-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 24px;
        padding: 1.5rem;
        box-shadow: 0 20px 40px rgba(26, 58, 22, 0.1);
        border: 1px solid rgba(76, 175, 80, 0.14);
    }

    .form-card + .form-card {
        margin-top: 1.25rem;
    }

    .form-card h2 {
        font-size: 1.2rem;
        font-weight: 800;
        color: #1a3a16;
        margin-bottom: 1rem;
    }

    .form-card .section-text {
        color: #64748b;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }

    .wood-label {
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #355f30;
        margin-bottom: 0.45rem;
    }

    .wood-input {
        border-radius: 14px;
        border: 1px solid rgba(76, 175, 80, 0.18);
        padding: 0.82rem 0.95rem;
        background: #fff;
        box-shadow: none;
    }

    .wood-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.12);
    }

    .primary-btn,
    .secondary-btn,
    .danger-btn {
        width: 100%;
        border: none;
        border-radius: 14px;
        padding: 0.9rem 1rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        transition: transform 0.25s ease, box-shadow 0.25s ease, filter 0.25s ease;
    }

    .primary-btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        box-shadow: 0 14px 30px rgba(16, 185, 129, 0.22);
    }

    .danger-btn {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        box-shadow: 0 14px 30px rgba(239, 68, 68, 0.2);
    }

    .primary-btn:hover,
    .danger-btn:hover,
    .secondary-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.02);
    }

    .profile-alerts {
        margin-bottom: 1rem;
    }

    .profile-alerts .alert {
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
    }

    .helper-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.7rem;
        border-radius: 999px;
        background: rgba(16, 185, 129, 0.1);
        color: #1a3a16;
        font-size: 0.78rem;
        font-weight: 700;
    }

    @media (max-width: 992px) {
        .profile-hero-grid,
        .profile-body {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .profile-page {
            padding: 1rem 0 2rem;
        }

        .profile-hero,
        .profile-card,
        .form-card {
            border-radius: 20px;
            padding: 1.1rem;
        }

        .profile-brand {
            align-items: flex-start;
        }

        .profile-avatar {
            width: 64px;
            height: 64px;
            border-radius: 18px;
        }

        .profile-avatar span {
            font-size: 1.45rem;
        }

        .profile-hero .subtitle {
            font-size: 0.92rem;
        }

        .profile-metrics {
            grid-template-columns: 1fr;
        }

        .metric-card {
            min-height: auto;
            padding: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
@php
    $user = Auth::user();
    $persona = $user->persona;
    $nombreCompleto = trim(($persona->nom ?? 'Usuario') . ' ' . ($persona->ap ?? ''));
    $iniciales = strtoupper(substr($persona->nom ?? 'U', 0, 1) . substr($persona->ap ?? 'U', 0, 1));
@endphp

<div class="profile-page">
    <div class="container">
        <div class="profile-alerts">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-circle-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-triangle-exclamation me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-circle-exclamation me-2"></i>Revisa los campos marcados e intenta de nuevo.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <section class="profile-hero mb-4">
            <div class="profile-hero-grid">
                <div>
                    <div class="profile-brand">
                        <div class="profile-avatar">
                            <span>{{ $iniciales }}</span>
                        </div>
                        <div>
                            <div class="profile-kicker">Mi Perfil</div>
                            <h1>{{ $nombreCompleto }}</h1>
                            <div class="profile-badges">
                                <span class="profile-badge"><i class="fas fa-leaf"></i>Técnico Forestal</span>
                                <span class="profile-badge"><i class="fas fa-shield-halved"></i>{{ $persona->rol->nom_rol ?? 'Usuario' }}</span>
                            </div>
                        </div>
                    </div>
                    <p class="subtitle">
                        Administra tu información personal, mantén tus datos actualizados y protege tu acceso al sistema desde una vista moderna, clara y pensada para trabajo de campo.
                    </p>
                </div>

                <aside class="profile-panel">
                    <p class="profile-panel-title">Resumen rápido</p>
                    <div class="profile-metrics">
                        <div class="metric-card">
                            <span class="metric-label">Correo</span>
                            <div class="metric-value">{{ $persona->correo ?? 'Sin correo' }}</div>
                        </div>
                        <div class="metric-card">
                            <span class="metric-label">Teléfono</span>
                            <div class="metric-value">{{ $persona->telefono ?? 'No registrado' }}</div>
                        </div>
                        <div class="metric-card">
                            <span class="metric-label">Rol</span>
                            <div class="metric-value">{{ $persona->rol->nom_rol ?? 'Técnico' }}</div>
                        </div>
                        <div class="metric-card">
                            <span class="metric-label">Usuario</span>
                            <div class="metric-value">{{ $user->email }}</div>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <div class="profile-body">
            <section class="profile-card">
                <h2><i class="fas fa-id-card me-2"></i>Información del Perfil</h2>
                <div class="detail-list">
                    <div class="detail-item">
                        <span class="label">Nombre completo</span>
                        <span class="value">{{ $nombreCompleto }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Correo electrónico</span>
                        <span class="value">{{ $persona->correo ?? $user->email }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Teléfono</span>
                        <span class="value">{{ $persona->telefono ?? 'No registrado' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Perfil de acceso</span>
                        <span class="value">{{ $persona->rol->nom_rol ?? 'Técnico Forestal' }}</span>
                    </div>
                </div>
            </section>

            <div>
                <section class="form-card">
                    <h2><i class="fas fa-user-pen me-2"></i>Actualizar datos</h2>
                    <p class="section-text">Mantén tu información personal al día para que el sistema siempre te identifique correctamente.</p>

                    <form method="POST" action="{{ route('perfil.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="wood-label">Nombre</label>
                            <input type="text" name="nom" class="form-control wood-input" value="{{ old('nom', $persona->nom) }}" required>
                            @error('nom') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="wood-label">Apellido paterno</label>
                            <input type="text" name="ap" class="form-control wood-input" value="{{ old('ap', $persona->ap) }}" required>
                            @error('ap') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="wood-label">Correo electrónico</label>
                            <input type="email" name="correo" class="form-control wood-input" value="{{ old('correo', $persona->correo) }}" required>
                            @error('correo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="wood-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control wood-input" value="{{ old('telefono', $persona->telefono) }}">
                            @error('telefono') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="primary-btn">
                            <i class="fas fa-floppy-disk me-2"></i>Guardar cambios
                        </button>
                    </form>
                </section>

                <section class="form-card">
                    <h2><i class="fas fa-lock me-2"></i>Cambiar contraseña</h2>
                    <p class="section-text">Usa una contraseña segura para proteger tu acceso y tus registros.</p>
                    <span class="helper-pill mb-3"><i class="fas fa-shield-heart"></i>Mínimo 8 caracteres</span>

                    <form method="POST" action="{{ route('perfil.updatePassword') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="wood-label">Contraseña actual</label>
                            <input type="password" name="current_password" class="form-control wood-input" required>
                            @error('current_password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="wood-label">Nueva contraseña</label>
                            <input type="password" name="new_password" class="form-control wood-input" required>
                            @error('new_password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="wood-label">Confirmar nueva contraseña</label>
                            <input type="password" name="new_password_confirmation" class="form-control wood-input" required>
                        </div>

                        <button type="submit" class="danger-btn">
                            <i class="fas fa-key me-2"></i>Actualizar contraseña
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
