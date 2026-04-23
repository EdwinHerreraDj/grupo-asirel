// Re-export de formateadores compartidos (fuente \u00fanica)
export {
    formatEuro,
    formatNumero,
    formatPorcentaje,
    redondear,
} from "../../shared/formato";

export const formatFecha = (fecha) => {
    if (!fecha) return "\u2014";
    return new Date(fecha).toLocaleDateString("es-ES");
};

export const estadoCertificacionLabel = (estado) =>
    ({
        pendiente: {
            label: "Pendiente",
            color: "bg-yellow-100 text-yellow-800 border-yellow-200",
        },
        aceptada: {
            label: "Aceptada",
            color: "bg-green-100 text-green-800 border-green-200",
        },
    })[estado] ?? { label: estado, color: "bg-gray-100 text-gray-700" };

export const estadoFacturaLabel = (estado) =>
    ({
        pendiente: {
            label: "Sin facturar",
            color: "bg-gray-100 text-gray-600 border-gray-200",
        },
        facturada: {
            label: "Facturada",
            color: "bg-blue-100 text-blue-800 border-blue-200",
        },
    })[estado] ?? { label: estado, color: "bg-gray-100 text-gray-700" };
