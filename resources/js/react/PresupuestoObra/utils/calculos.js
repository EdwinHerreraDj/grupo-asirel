// Re-export de formateadores compartidos (fuente \u00fanica)
export {
    formatEuro,
    formatNumero,
    formatPorcentaje,
    redondear,
} from "../../shared/formato";

/**
 * Calcula el importe de una partida.
 * Igual que el booted() del modelo Laravel.
 */
export const calcularImporte = (medicion, precioUnitario) => {
    const m = parseFloat(medicion) || 0;
    const p = parseFloat(precioUnitario) || 0;
    return Math.round(m * p * 100) / 100;
};

/**
 * Calcula el margen absoluto entre venta y coste.
 */
export const calcularMargen = (venta, coste) => {
    return Math.round((venta - coste) * 100) / 100;
};

/**
 * Calcula el margen en porcentaje sobre venta.
 * Devuelve null si venta es 0.
 */
export const calcularMargenPct = (venta, coste) => {
    if (!venta || venta === 0) return null;
    return Math.round(((venta - coste) / venta) * 10000) / 100;
};

/**
 * Suma el importe total de un array de partidas.
 */
export const totalPartidas = (partidas = []) => {
    return (
        Math.round(
            partidas.reduce((acc, p) => acc + (parseFloat(p.importe) || 0), 0) *
                100,
        ) / 100
    );
};
