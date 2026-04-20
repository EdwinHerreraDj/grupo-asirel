import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

const notyf = new Notyf({
    duration: 4000,
    dismissible: true,
    position: { x: 'right', y: 'top' },
});

document.addEventListener('livewire:init', () => {
    Livewire.on('notify', (data) => {
        const payload = Array.isArray(data) ? data[0] : data;
        if (!payload) return;

        const message = payload.message ?? 'Operación realizada.';
        if (payload.type === 'error') {
            notyf.error(message);
        } else {
            notyf.success(message);
        }
    });
});
