document.addEventListener('alpine:init', () => {
    Alpine.data('speciesManagement', (config) => ({
        especies: config.especies,
        searchQuery: '',
        selectedEspecie: null,
        isEditing: false,
        formData: {},

        get filteredEspecies() {
            if (this.searchQuery === '') return this.especies;
            return this.especies.filter(s => {
                const search = this.searchQuery.toLowerCase();
                return s.nom_comun.toLowerCase().includes(search) ||
                       s.nom_cientifico.toLowerCase().includes(search);
            });
        },

        openViewModal(especie) {
            this.selectedEspecie = especie;
            new bootstrap.Modal(document.getElementById('viewEspecieModal')).show();
        },

        openCreateModal() {
            this.isEditing = false;
            this.formData = { nom_cientifico: '', nom_comun: '', imagen: null };
            new bootstrap.Modal(document.getElementById('formEspecieModal')).show();
        },

        openEditModal(especie) {
            this.isEditing = true;
            this.selectedEspecie = especie;
            this.formData = { 
                ...especie,
                imagen: null, // Reset file input
                imagen_url: especie.imagen ? `/storage/${especie.imagen}` : null
            };
            new bootstrap.Modal(document.getElementById('formEspecieModal')).show();
        },

        async submitForm(url, method, successMessage) {
            const data = new FormData();
            for (const key in this.formData) {
                if (key !== 'imagen_url') { // Don't send the display URL
                    data.append(key, this.formData[key]);
                }
            }
            if (method.toUpperCase() === 'PUT') {
                data.append('_method', 'PUT');
            }
            
            try {
                const response = await fetch(url, {
                    method: 'POST', // Always POST for forms with files
                    body: data,
                    headers: { 'X-CSRF-TOKEN': config.csrfToken, 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Error en la petición');
                
                // Cierra el modal y recarga la página para ver los cambios
                bootstrap.Modal.getInstance(document.getElementById('formEspecieModal')).hide();
                window.location.reload();

            } catch (error) {
                console.error('Error:', error);
                alert('Ocurrió un error al guardar los datos.');
            }
        },

        storeEspecie() {
            this.submitForm(config.storeUrl, 'POST', 'Especie creada con éxito');
        },
        updateEspecie() {
            const url = `/especies/${this.selectedEspecie.id_especie}`;
            this.submitForm(url, 'PUT', 'Especie actualizada con éxito');
        },

        confirmDelete(especie) {
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Se eliminará la especie <strong>${especie.nom_comun}</strong>.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/especies/${especie.id_especie}`;
                    form.innerHTML = `<input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="${config.csrfToken}">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    }));
});