import React, { useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { Aviso, Campo, Modal, SeccionFormulario, claseInput } from "./Comunes";
import { botonPeligro, botonPrimario, botonSecundario, erroresDeValidacion, fechaCorta, hoyISO, mensajeDeError } from "../utils";

/** Envía el formulario y reparte errores de validación por campo. */
function useEnvio(url, onHecho) {
    const { showSuccess, showError } = useNotification();
    const [errores, setErrores] = useState({});
    const [enviando, setEnviando] = useState(false);

    const enviar = async (datos) => {
        setEnviando(true);
        setErrores({});
        try {
            const { data } = await api.post(url, datos);
            showSuccess(data.message || "Hecho");
            onHecho(data);
        } catch (error) {
            const campos = erroresDeValidacion(error);
            setErrores(campos);
            if (!Object.keys(campos).length) showError(mensajeDeError(error, "No se pudo completar la operación."));
        } finally {
            setEnviando(false);
        }
    };

    return { errores, enviando, enviar };
}

/** Días naturales entre dos fechas ISO (ambas incluidas). */
const diasEntre = (desde, hasta) => {
    if (!desde || !hasta) return null;
    const d = (Date.parse(hasta) - Date.parse(desde)) / 86400000;
    return Number.isFinite(d) && d >= 0 ? Math.round(d) + 1 : null;
};

export function ModalBaja({ empleado, opciones, onCerrar, onHecho }) {
    const [datos, setDatos] = useState({ fecha_baja: hoyISO(), motivo_baja: "fin_contrato", observaciones_baja: "" });
    const { errores, enviando, enviar } = useEnvio(`/rrhh/empleados/${empleado.id}/baja`, onHecho);
    const cambiar = (e) => setDatos((p) => ({ ...p, [e.target.name]: e.target.value }));

    const periodo = empleado.periodos?.find((p) => !p.fecha_baja);
    const dias = diasEntre(periodo?.fecha_alta, datos.fecha_baja);

    return (
        <Modal
            etiqueta="Baja"
            titulo={`Dar de baja a ${empleado.nombre_completo}`}
            onCerrar={onCerrar}
            ancho="max-w-xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-baja" className={botonPeligro} disabled={enviando}>
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_user_remove_line"></i>}
                        Confirmar baja
                    </button>
                </>
            }
        >
            <form
                id="form-baja"
                className="space-y-4"
                onSubmit={(e) => {
                    e.preventDefault();
                    enviar(datos);
                }}
            >
                <Aviso>
                    Su carpeta del Drive pasará a <strong>«Trabajadores de baja»</strong>. La ficha, el historial y los
                    documentos se conservan y podrás reincorporarlo cuando vuelva.
                </Aviso>

                <SeccionFormulario
                    titulo="Fin del periodo"
                    descripcion={periodo ? `De alta desde el ${fechaCorta(periodo.fecha_alta)}` : "Fecha y motivo de la baja"}
                    icono="mgc_calendar_line"
                    color="rose"
                    conError={!!(errores.fecha_baja || errores.motivo_baja)}
                >
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo
                            etiqueta="Fecha de baja"
                            obligatorio
                            error={errores.fecha_baja}
                            ayuda={dias ? `${dias} ${dias === 1 ? "día" : "días"} en este periodo` : null}
                        >
                            <input
                                type="date"
                                name="fecha_baja"
                                min={periodo?.fecha_alta?.slice(0, 10)}
                                value={datos.fecha_baja}
                                onChange={cambiar}
                                className={claseInput(errores.fecha_baja)}
                            />
                        </Campo>
                        <Campo etiqueta="Motivo" obligatorio error={errores.motivo_baja}>
                            <select name="motivo_baja" value={datos.motivo_baja} onChange={cambiar} className={claseInput(errores.motivo_baja)}>
                                {Object.entries(opciones?.motivos_baja || {}).map(([valor, texto]) => (
                                    <option key={valor} value={valor}>
                                        {texto}
                                    </option>
                                ))}
                            </select>
                        </Campo>
                    </div>
                </SeccionFormulario>

                <SeccionFormulario titulo="Observaciones" descripcion="Opcional" icono="mgc_edit_line" color="slate" conError={!!errores.observaciones_baja}>
                    <textarea
                        name="observaciones_baja"
                        rows={3}
                        value={datos.observaciones_baja}
                        onChange={cambiar}
                        placeholder="Ej.: fin de la obra, finiquito entregado…"
                        className={claseInput(errores.observaciones_baja)}
                    />
                    {errores.observaciones_baja && <p className="mt-1 text-xs text-red-600">{errores.observaciones_baja}</p>}
                </SeccionFormulario>
            </form>
        </Modal>
    );
}

export function ModalReingreso({ empleado, opciones, onCerrar, onHecho }) {
    const [datos, setDatos] = useState({ fecha_alta: hoyISO(), tipo_contrato: empleado.tipo_contrato || "" });
    const { errores, enviando, enviar } = useEnvio(`/rrhh/empleados/${empleado.id}/reingreso`, onHecho);
    const cambiar = (e) => setDatos((p) => ({ ...p, [e.target.name]: e.target.value }));

    const ultimo = empleado.periodos?.[0];

    return (
        <Modal
            etiqueta="Reincorporación"
            titulo={`Reincorporar a ${empleado.nombre_completo}`}
            onCerrar={onCerrar}
            ancho="max-w-xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={enviando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-reingreso" className={botonPrimario} disabled={enviando}>
                        {enviando ? <i className="mgc_loading_line animate-spin"></i> : <i className="mgc_user_follow_line"></i>}
                        Confirmar alta
                    </button>
                </>
            }
        >
            <form
                id="form-reingreso"
                className="space-y-4"
                onSubmit={(e) => {
                    e.preventDefault();
                    enviar(datos);
                }}
            >
                <Aviso>
                    Se abre un <strong>nuevo periodo</strong> en su historial y su carpeta vuelve a «Trabajadores».
                    {ultimo?.fecha_baja && <> Última baja: {fechaCorta(ultimo.fecha_baja)}.</>}
                </Aviso>

                <SeccionFormulario
                    titulo="Nuevo periodo"
                    descripcion="Fecha de alta y contrato"
                    icono="mgc_user_follow_line"
                    color="emerald"
                    conError={!!(errores.fecha_alta || errores.tipo_contrato)}
                >
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Nueva fecha de alta" obligatorio error={errores.fecha_alta}>
                            <input type="date" name="fecha_alta" value={datos.fecha_alta} onChange={cambiar} className={claseInput(errores.fecha_alta)} />
                        </Campo>
                        <Campo etiqueta="Tipo de contrato" error={errores.tipo_contrato}>
                            <select name="tipo_contrato" value={datos.tipo_contrato} onChange={cambiar} className={claseInput(errores.tipo_contrato)}>
                                <option value="">Sin cambios</option>
                                {Object.entries(opciones?.tipos_contrato || {}).map(([valor, texto]) => (
                                    <option key={valor} value={valor}>
                                        {texto}
                                    </option>
                                ))}
                            </select>
                        </Campo>
                    </div>
                </SeccionFormulario>
            </form>
        </Modal>
    );
}
