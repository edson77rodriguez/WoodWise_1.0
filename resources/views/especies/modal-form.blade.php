<div class="modal fade" id="formEspecieModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form @submit.prevent="isEditing ? updateEspecie() : storeEspecie()" enctype="multipart/form-data">
                <div class="modal-header text-white" :class="isEditing ? 'bg-gradient-succulent' : 'bg-gradient-success'">
                    <h5 class="modal-title"><i class="me-2" :class="isEditing ? 'fas fa-edit' : 'fas fa-plus-circle'"></i><span x-text="isEditing ? 'Editar Especie' : 'Nueva Especie'"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-floating mb-3">
                        <input type="text" x-model="formData.nom_cientifico" class="form-control" required placeholder="Nombre Científico">
                        <label>Nombre Científico</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" x-model="formData.nom_comun" class="form-control" required placeholder="Nombre Común">
                        <label>Nombre Común</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagen</label>
                        <input type="file" name="imagen" @change="formData.imagen = $event.target.files[0]" class="form-control">
                        <template x-if="isEditing && formData.imagen_url">
                            <div class="mt-2"><small class="text-muted">Imagen actual:</small><img :src="formData.imagen_url" class="img-thumbnail mt-1" style="width: 80px;"></div>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" :style="{ backgroundColor: isEditing ? 'var(--succulent-dark)' : 'var(--succulent-medium)' }">
                        <i class="fas fa-save me-2"></i><span x-text="isEditing ? 'Guardar Cambios' : 'Crear Especie'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>