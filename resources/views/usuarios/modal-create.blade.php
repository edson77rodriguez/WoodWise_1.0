<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Crear Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('usuarios.store') }}" id="userCreateForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" name="nom" class="form-control" required placeholder="Nombre"><label>Nombre*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="text" name="ap" class="form-control" required placeholder="Apellido Paterno"><label>Apellido Paterno*</label></div></div>
                    </div>
                    <div class="form-floating mb-3"><input type="text" name="am" class="form-control" placeholder="Apellido Materno"><label>Apellido Materno</label></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="email" name="email" class="form-control" required placeholder="Correo"><label>Correo*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="tel" name="telefono" class="form-control" required placeholder="Teléfono"><label>Teléfono*</label></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="password" name="password" class="form-control" required placeholder="Contraseña"><label>Contraseña*</label></div></div>
                        <div class="col-md-6 mb-3"><div class="form-floating"><input type="password" name="password_confirmation" class="form-control" required placeholder="Confirmar"><label>Confirmar Contraseña*</label></div></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="form-floating">
                            <select name="id_rol" id="rolSelect" class="form-select" required><option value="" disabled selected>Selecciona...</option>@foreach ($roles as $rol)<option value="{{ $rol->id_rol }}">{{ $rol->nom_rol }}</option>@endforeach</select>
                            <label>Rol*</label>
                        </div></div>
                        <div class="col-md-6 mb-3" id="cedulaField" style="display: none;"><div class="form-floating"><input type="text" name="cedula" class="form-control" placeholder="Cédula"><label>Cédula Profesional</label></div></div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-medium);"><i class="fas fa-check-circle me-2"></i>Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>