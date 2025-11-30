import { Notyf } from 'notyf'; // Importa Notyf
import 'notyf/notyf.min.css'; // Importa los estilos de Notyf

// Configuración global de Notyf
const notyf = new Notyf({
    duration: 3000, // Duración en milisegundos
    position: { x: 'right', y: 'top' }, // Posición del mensaje
});

// Función para mostrar mensajes de éxito
export function showSuccessMessage(message) {
    notyf.success(message);
}

// Función para mostrar mensajes de error
export function showErrorMessage(message) {
    notyf.error(message);
}
