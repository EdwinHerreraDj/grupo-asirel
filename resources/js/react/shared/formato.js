/**
 * Formateadores num\u00e9ricos locale es-ES.
 *
 * Reglas generales del ERP:
 *  - Importes (\u20ac) siempre con 2 decimales fijos.
 *  - Cantidades / mediciones: hasta 2 decimales, sin relleno de ceros.
 *  - Porcentajes: hasta 2 decimales, sin relleno.
 */

const esNumeroValido = (valor) =>
    valor !== null && valor !== undefined && !Number.isNaN(parseFloat(valor));

/**
 * Moneda con 2 decimales fijos. "1234.5" -> "1.234,50 \u20ac"
 */
export const formatEuro = (valor) => {
    const n = esNumeroValido(valor) ? parseFloat(valor) : 0;
    return (
        new Intl.NumberFormat("es-ES", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(n) + " \u20ac"
    );
};

/**
 * N\u00famero gen\u00e9rico (cantidad, medici\u00f3n). Hasta `decimales` decimales, sin relleno.
 * "5" -> "5" | "3.14159" -> "3,14" | "12.5" -> "12,5"
 */
export const formatNumero = (valor, decimales = 2) => {
    if (!esNumeroValido(valor)) return "\u2014";
    return new Intl.NumberFormat("es-ES", {
        minimumFractionDigits: 0,
        maximumFractionDigits: decimales,
    }).format(parseFloat(valor));
};

/**
 * Porcentaje. "21" -> "21%" | "21.456" -> "21,46%"
 * Devuelve guion si el valor es null/undefined/NaN.
 */
export const formatPorcentaje = (valor, decimales = 2) => {
    if (!esNumeroValido(valor)) return "\u2014";
    return (
        new Intl.NumberFormat("es-ES", {
            minimumFractionDigits: 0,
            maximumFractionDigits: decimales,
        }).format(parseFloat(valor)) + "%"
    );
};

/**
 * Devuelve un n\u00famero redondeado para usar en inputs (evita floats largos
 * en defaultValue / value). No lo formatea como string con coma.
 */
export const redondear = (valor, decimales = 2) => {
    if (!esNumeroValido(valor)) return 0;
    const factor = 10 ** decimales;
    return Math.round(parseFloat(valor) * factor) / factor;
};
