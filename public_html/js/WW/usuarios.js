document.addEventListener('alpine:init', () => {
    Alpine.data('userManagement', (initialData) => ({
        personas: initialData.personas,
        roles: initialData.roles,
        searchQuery: '',
        selectedPersona: null,
        isEditing: false,
        isModalOpen: false,
        formData: {},

        // Getter para filtrar personas en tiempo real
        get filteredPersonas() {
            if (this.searchQuery === '') {
                return this.personas;
            }
            return this.personas.filter(p => {
                const search = this.searchQuery.toLowerCase();
                return p.nom.toLowerCase().includes(search) ||
                       p.ap.toLowerCase().includes(search) ||
                       p.correo.toLowerCase().includes(search) ||
                       p.rol.nom_rol.toLowerCase().includes(search);
            });
        },

        // Lógica para los badges de rol
        getRoleBadge(roleName) {
            const role = roleName.toLowerCase();
            if (role.includes('administrador')) return 'bg-role-admin';
            if (role.includes('tecnico')) return 'bg-role-tecnico';
            if (role.includes('productor')) return 'bg-role-productor';
            return 'bg-role-default';
        },

        getRoleIcon(roleName) {
            const role = roleName.toLowerCase();
            if (role.includes('administrador')) return 'fas fa-user-shield';
            if (role.includes('tecnico')) return 'fas fa-user-cog';
            if (role.includes('productor')) return 'fas fa-user';
            return 'fas fa-user-tag';
        },

        // Abrir modales
        openViewModal(persona) {
            this.selectedPersona = persona;
            new bootstrap.Modal(document.getElementById('viewUserModal')).show();
        },

        openCreateModal() {
            this.isEditing = false;
            this.isModalOpen = true;
            this.formData = { nom: '', ap: '', am: '', correo: '', telefono: '', id_rol: this.roles[0]?.id_rol };
            // Pequeño delay para que el DOM se actualice antes de mostrar el modal
            setTimeout(() => new bootstrap.Modal(document.getElementById('formUserModal')).show(), 50);
        },

        openEditModal(persona) {
            this.isEditing = true;
            this.isModalOpen = true;
            this.selectedPersona = persona;
            this.formData = { ...persona }; // Clonar el objeto
             setTimeout(() => new bootstrap.Modal(document.getElementById('formUserModal')).show(), 50);
        },

        // Lógica de Formularios (a implementar con tu backend)
        storeUser() {
            alert('Lógica para CREAR usuario con datos:\n' + JSON.stringify(this.formData));
            // Aquí iría tu fetch/axios para enviar los datos a la ruta 'usuarios.store'
            // form.submit();
        },
        updateUser() {
            alert('Lógica para ACTUALIZAR usuario con datos:\n' + JSON.stringify(this.formData));
             // Aquí iría tu fetch/axios para enviar los datos a la ruta 'usuarios.update'
        },

        // Lógica para Eliminar
        confirmDelete(persona) {
            Swal.fire({
                title: '¿Confirmar eliminación?',
                html: `El usuario <strong>${persona.nom} ${persona.ap}</strong> será eliminado.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crear un formulario y enviarlo para seguir el patrón de Laravel
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/usuarios/${persona.id_persona}`; // Ajusta la ruta si es necesario
                    form.innerHTML = `<input type="hidden" name="_method" value="DELETE">` +
                                     `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        },
    }));
});

// Inicializar tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});