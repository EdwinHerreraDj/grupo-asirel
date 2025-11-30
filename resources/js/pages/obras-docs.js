import { Notyf } from 'notyf';
import Swal from 'sweetalert2';

// ✅ Esperar a que Livewire esté inicializado
document.addEventListener('livewire:init', () => {
    // Notificaciones Livewire -> Notyf
    Livewire.on('notificar', (data) => {
        const notyf = new Notyf({
            dismissible: true,
            duration: 4000,
            position: { x: 'right', y: 'top' }
        });

        const payload = data[0]; // Livewire v3 envía el evento como array
        if (payload.type === 'success') {
            notyf.success(payload.message);
        } else {
            notyf.error(payload.message || 'Ocurrió un error.');
        }
    });

    // Confirmación de eliminación
    window.confirmarEliminacion = function (id) {
        Swal.fire({
            title: '¿Eliminar documento?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                Livewire.dispatch('eliminarDocumento', { id });
            }
        });
    };
});


