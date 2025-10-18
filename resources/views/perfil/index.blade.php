@extends('dashboard')

@section('template_title')
    Mi Perfil
@endsection

@section('crud_content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h5><i class="fas fa-user-circle"></i> Mi Perfil</h5>
                </div>
                <div class="card-body">
                    <!-- Información del usuario -->
                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle fa-5x text-primary"></i>
                        <h4 class="mt-2">{{ Auth::user()->persona->nom }} {{ Auth::user()->persona->ap }}</h4>
                        <span class="badge bg-info">{{ Auth::user()->persona->rol->nom_rol }}</span>
                    </div>

                    <!-- Formulario para actualizar información -->
                    <form method="POST" action="{{ route('perfil.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nom" class="form-control" value="{{ Auth::user()->persona->nom }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" name="ap" class="form-control" value="{{ Auth::user()->persona->ap }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="correo" class="form-control" value="{{ Auth::user()->persona->correo }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="{{ Auth::user()->persona->telefono }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </form>

                    <hr class="my-4">

                    <!-- Formulario para cambiar contraseña -->
                    <h5 class="text-center">Cambiar Contraseña</h5>
                    <form method="POST" action="{{ route('perfil.updatePassword') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Contraseña Actual</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-lock"></i> Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('success'))
        Swal.fire('¡Éxito!', '{{ session('success') }}', 'success');
    @endif

    @if(session('error'))
        Swal.fire('Error', '{{ session('error') }}', 'error');
    @endif
</script>
@endsection
