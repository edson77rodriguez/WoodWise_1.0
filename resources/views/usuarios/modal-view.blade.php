<div class="modal fade" id="viewUserModal{{ $persona->id_persona }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--succulent-medium);">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalles de Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-2"><div class="col-5 text-muted">ID:</div><div class="col-7 fw-bold">{{ $persona->id_persona }}</div></div>
                <div class="row mb-2"><div class="col-5 text-muted">Nombre:</div><div class="col-7 fw-bold">{{ $persona->nom }} {{ $persona->ap }}</div></div>
                <div class="row mb-2"><div class="col-5 text-muted">Correo:</div><div class="col-7 fw-bold">{{ $persona->correo }}</div></div>
                <div class="row mb-2"><div class="col-5 text-muted">Teléfono:</div><div class="col-7 fw-bold">{{ $persona->telefono }}</div></div>
                <div class="row mb-2"><div class="col-5 text-muted">Rol:</div><div class="col-7 fw-bold">{{ $persona->rol->nom_rol }}</div></div>
                <div class="row"><div class="col-5 text-muted">Registro:</div><div class="col-7 fw-bold">{{ $persona->created_at?->format('d/m/Y') }}</div></div>
            </div>
        </div>
    </div>
</div>