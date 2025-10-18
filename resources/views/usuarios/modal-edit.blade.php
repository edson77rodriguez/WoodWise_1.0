<div class="modal fade" id="editUserModal{{ $persona->id_persona }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-dark);">
                <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Editar Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route('usuarios.update', $persona->id_persona) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-floating mb-3"><input type="text" name="nom" class="form-control" value="{{ $persona->nom }}" required placeholder="Nombre"><label>Nombre</label></div>
                    <div class="form-floating mb-3"><input type="text" name="ap" class="form-control" value="{{ $persona->ap }}" required placeholder="Apellido"><label>Apellido</label></div>
                    <div class="form-floating mb-3"><input type="email" name="correo" class="form-control" value="{{ $persona->correo }}" required placeholder="Correo"><label>Correo</label></div>
                    <div class="form-floating mb-3"><input type="tel" name="telefono" class="form-control" value="{{ $persona->telefono }}" required placeholder="Teléfono"><label>Teléfono</label></div>
                    <div class="form-floating mb-3">
                        <select name="id_rol" class="form-select">
                            @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" {{ $persona->id_rol == $rol->id_rol ? 'selected' : '' }}>{{ $rol->nom_rol }}</option>
                            @endforeach
                        </select>
                        <label>Rol</label>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white" style="background-color: var(--succulent-dark);"><i class="fas fa-save me-2"></i>Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>