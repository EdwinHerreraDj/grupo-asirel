// resources/js/react/Drive/utils/archivos.js
/**
 * Utilidades de archivos del Drive, compartidas por la vista de cuadrícula
 * (FileItem) y la de lista (DriveListView): icono, tipo, tamaño, fechas y
 * estado de caducidad.
 */

const PREVISUALIZABLES = ["pdf", "jpg", "jpeg", "png", "gif", "webp"];

/** Días de antelación con los que una caducidad se marca como próxima. */
export const DIAS_AVISO_CADUCIDAD = 15;

export const extensionDe = (nombre = "") => {
    const partes = String(nombre).split(".");
    return partes.length > 1 ? partes.pop().toLowerCase() : "";
};

export const esPrevisualizable = (nombre) =>
    PREVISUALIZABLES.includes(extensionDe(nombre));

export const esZip = (nombre) => extensionDe(nombre) === "zip";

const ROJO = "text-red-600 bg-red-100 dark:bg-red-900/40 dark:text-red-300";
const AZUL = "text-blue-600 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300";
const VERDE = "text-green-600 bg-green-100 dark:bg-green-900/40 dark:text-green-300";
const NARANJA = "text-orange-600 bg-orange-100 dark:bg-orange-900/40 dark:text-orange-300";
const ROSA = "text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300";
const MORADO = "text-purple-600 bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300";
const INDIGO = "text-indigo-600 bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300";
const AMARILLO = "text-yellow-600 bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-300";
const GRIS = "text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300";
const TEAL = "text-teal-600 bg-teal-100 dark:bg-teal-900/40 dark:text-teal-300";

/** Solo iconos que existen en la fuente MingCute del proyecto. */
const ICONOS = {
    pdf: { icon: "mgc_pdf_fill", color: ROJO },
    doc: { icon: "mgc_doc_fill", color: AZUL },
    docx: { icon: "mgc_doc_fill", color: AZUL },
    xls: { icon: "mgc_table_fill", color: VERDE },
    xlsx: { icon: "mgc_table_fill", color: VERDE },
    csv: { icon: "mgc_table_fill", color: TEAL },
    ppt: { icon: "mgc_presentation_1_fill", color: NARANJA },
    pptx: { icon: "mgc_presentation_1_fill", color: NARANJA },
    jpg: { icon: "mgc_pic_2_fill", color: ROSA },
    jpeg: { icon: "mgc_pic_2_fill", color: ROSA },
    png: { icon: "mgc_pic_fill", color: ROSA },
    gif: { icon: "mgc_photo_album_fill", color: ROSA },
    webp: { icon: "mgc_pic_fill", color: ROSA },
    svg: { icon: "mgc_pic_fill", color: ROSA },
    mp4: { icon: "mgc_video_fill", color: MORADO },
    mov: { icon: "mgc_video_fill", color: MORADO },
    avi: { icon: "mgc_video_fill", color: MORADO },
    mp3: { icon: "mgc_music_fill", color: INDIGO },
    wav: { icon: "mgc_music_fill", color: INDIGO },
    zip: { icon: "mgc_file_zip_fill", color: AMARILLO },
    rar: { icon: "mgc_file_zip_fill", color: AMARILLO },
    "7z": { icon: "mgc_file_zip_fill", color: AMARILLO },
    txt: { icon: "mgc_document_fill", color: GRIS },
    default: { icon: "mgc_file_line", color: GRIS },
};

export const iconoArchivo = (nombre) =>
    ICONOS[extensionDe(nombre)] || ICONOS.default;

const TIPOS = {
    pdf: "PDF",
    doc: "Word",
    docx: "Word",
    xls: "Excel",
    xlsx: "Excel",
    csv: "CSV",
    ppt: "PowerPoint",
    pptx: "PowerPoint",
    jpg: "Imagen",
    jpeg: "Imagen",
    png: "Imagen",
    gif: "Imagen",
    webp: "Imagen",
    svg: "Imagen",
    mp4: "Vídeo",
    mov: "Vídeo",
    avi: "Vídeo",
    mp3: "Audio",
    wav: "Audio",
    zip: "ZIP",
    rar: "RAR",
    "7z": "7-Zip",
    txt: "Texto",
};

export const tipoArchivo = (nombre) => {
    const extension = extensionDe(nombre);
    return TIPOS[extension] || (extension ? extension.toUpperCase() : "Archivo");
};

export const formatoTamano = (bytes) => {
    const n = Number(bytes);
    if (!n || n < 0) return "0 B";

    const unidades = ["B", "KB", "MB", "GB"];
    const i = Math.min(Math.floor(Math.log(n) / Math.log(1024)), unidades.length - 1);

    return `${new Intl.NumberFormat("es-ES", { maximumFractionDigits: 2 }).format(n / 1024 ** i)} ${unidades[i]}`;
};

/**
 * Convierte a Date. Las fechas "YYYY-MM-DD" (sin hora) se leen como fecha
 * local para que no cambien de día por la zona horaria.
 */
const aFecha = (valor) => {
    if (!valor) return null;

    if (valor instanceof Date) return valor;

    if (/^\d{4}-\d{2}-\d{2}$/.test(String(valor))) {
        const [a, m, d] = String(valor).split("-").map(Number);
        return new Date(a, m - 1, d);
    }

    const fecha = new Date(valor);
    return Number.isNaN(fecha.getTime()) ? null : fecha;
};

export const formatoFecha = (valor) => {
    const fecha = aFecha(valor);
    return fecha
        ? fecha.toLocaleDateString("es-ES", { day: "2-digit", month: "2-digit", year: "numeric" })
        : "—";
};

export const formatoFechaHora = (valor) => {
    const fecha = aFecha(valor);
    return fecha
        ? fecha.toLocaleString("es-ES", {
              day: "2-digit",
              month: "2-digit",
              year: "numeric",
              hour: "2-digit",
              minute: "2-digit",
          })
        : "—";
};

/**
 * Estado de caducidad de un archivo o null si no tiene fecha.
 *  - vencido: la fecha ya pasó
 *  - proximo: caduca hoy o en los próximos DIAS_AVISO_CADUCIDAD días
 *  - vigente: caduca más adelante
 */
export const estadoCaducidad = (file) => {
    if (!file?.fecha_caducidad) return null;

    const fecha = aFecha(String(file.fecha_caducidad).slice(0, 10));
    if (!fecha) return null;

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const dias = Math.round((fecha - hoy) / 86400000);

    if (dias < 0) {
        return {
            estado: "vencido",
            dias,
            texto: "Vencido",
            clases: "bg-rose-100 text-rose-700 border-rose-200",
        };
    }

    if (dias <= DIAS_AVISO_CADUCIDAD) {
        return {
            estado: "proximo",
            dias,
            texto: dias === 0 ? "Caduca hoy" : `Caduca en ${dias}d`,
            clases: "bg-amber-100 text-amber-700 border-amber-200",
        };
    }

    return {
        estado: "vigente",
        dias,
        texto: formatoFecha(fecha),
        clases: "bg-slate-100 text-slate-600 border-slate-200",
    };
};
