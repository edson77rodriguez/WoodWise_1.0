@foreach($parcela->trozas as $troza)
<div class="modal fade" id="editTrozaModal{{ $troza->id_troza }}" tabindex="-1"
    aria-labelledby="editTrozaLabel{{ $troza->id_troza }}" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content border-0 shadow">
           <div class="modal-header bg-gradient-primary text-white">
               <h5 class="modal-title text-white">
                   <i class="fas fa-edit me-2"></i>Editar Troza #{{ $troza->id_troza }}
               </h5>
               <button type="button" class="btn-close btn-close-white"
                       data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body p-4">
               <form method="POST" action="{{ route('trozas.update', $troza->id_troza) }}">
                   @csrf
                   @method('PUT')
                   <div class="row">
                       <div class="col-md-6 mb-3">
                           <label class="form-label text-muted">Longitud (m)</label>
                           <input type="number" step="0.01" class="form-control border-2"
                                  name="longitud" value="{{ $troza->longitud }}" required>
                       </div>
                       <div class="col-md-6 mb-3">
                           <label class="form-label text-muted">Diámetro (cm)</label>
                           <input type="number" step="0.01" class="form-control border-2"
                                  name="diametro" value="{{ $troza->diametro }}" required>
                       </div>
                   </div>
                   <div class="mb-3">
                       <label class="form-label text-muted">Densidad</label>
                       <input type="number" step="0.01" class="form-control border-2"
                              name="densidad" value="{{ $troza->densidad }}" required>
                   </div>
                   <div class="row">
                       <div class="col-md-6 mb-3">
                           <label class="form-label text-muted">Especie</label>
                           <select class="form-select border-2" name="id_especie" required>
                               @foreach ($especies as $especie)
                               <option value="{{ $especie->id_especie }}"
                                       {{ $troza->id_especie == $especie->id_especie ? 'selected' : '' }}>
                                   {{ $especie->nom_cientifico }}
                               </option>
                               @endforeach
                           </select>
                       </div>
                       <div class="col-md-6 mb-3">
                           <label class="form-label text-muted">Parcela</label>
                           <select class="form-select border-2" name="id_parcela" required>
                               @foreach ($parcelas as $parcelaItem)
                               <option value="{{ $parcelaItem->id_parcela }}"
                                       {{ $troza->id_parcela == $parcelaItem->id_parcela ? 'selected' : '' }}>
                                   {{ $parcelaItem->nom_parcela }}
                               </option>
                               @endforeach
                           </select>
                       </div>
                   </div>
                   <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                       <button type="button" class="btn btn-outline-secondary me-md-2 rounded-pill"
                               data-bs-dismiss="modal">Cancelar</button>
                       <button type="submit" class="btn btn-primary rounded-pill">
                           <i class="fas fa-save me-1"></i>Guardar Cambios
                       </button>
                   </div>
               </form>
           </div>
       </div>
   </div>
</div>
@endforeach