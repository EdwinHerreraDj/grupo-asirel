import React, { useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { Campo, Modal, claseInput } from "./Comunes";
import { botonPrimario, botonSecundario, erroresDeValidacion, hoyISO, ibanLegible, mensajeDeError } from "../utils";

const CAMPOS = [
    "nombre", "apellidos", "dni", "nss", "fecha_nacimiento", "telefono", "email",
    "direccion", "codigo_postal", "poblacion", "provincia",
    "puesto", "categoria_convenio", "tipo_contrato", "jornada", "horas_semanales", "obra_id",
    "salario_bruto_anual", "iban",
    "contacto_emergencia_nombre", "contacto_emergencia_relacion", "contacto_emergencia_telefono",
    "observaciones",
];

const inicial = (empleado) => {
    const datos = Object.fromEntries(CAMPOS.map((c) => [c, empleado?.[c] ?? ""]));
    datos.iban = ibanLegible(datos.iban);
    if (!empleado) {
        datos.fecha_alta = hoyISO();
        datos.jornada = "completa";
        datos.horas_semanales = "40";
    }
    return datos;
};

function Seccion({ titulo, icono, children }) {
    return (
        <section className="space-y-4">
            <h4 className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <i className={`${icono} text-sm`}></i>
                {titulo}
            </h4>
            {children}
        </section>
    );
}

/** Alta (empleado = null) o edición de la ficha. */
export default function FormularioEmpleado({ empleado = null, obras, opciones, onCerrar, onGuardado }) {
    const { showSuccess, showError } = useNotification();
    const [datos, setDatos] = useState(() => inicial(empleado));
    const [errores, setErrores] = useState({});
    const [guardando, setGuardando] = useState(false);

    const cambiar = (e) => {
        const { name, value } = e.target;
        setDatos((prev) => ({ ...prev, [name]: value }));
        if (errores[name]) setErrores((prev) => ({ ...prev, [name]: null }));
    };

    const input = (name, props = {}) => (
        <input
            name={name}
            value={datos[name] ?? ""}
            onChange={cambiar}
            autoComplete="off"
            className={claseInput(errores[name], props.mono ? "font-mono" : "")}
            {...Object.fromEntries(Object.entries(props).filter(([k]) => k !== "mono"))}
        />
    );

    const select = (name, lista, vacio = "—") => (
        <select name={name} value={datos[name] ?? ""} onChange={cambiar} className={claseInput(errores[name])}>
            <option value="">{vacio}</option>
            {Object.entries(lista || {}).map(([valor, texto]) => (
                <option key={valor} value={valor}>
                    {texto}
                </option>
            ))}
        </select>
    );

    const guardar = async (e) => {
        e.preventDefault();
        setGuardando(true);
        setErrores({});
        try {
            const { data } = empleado
                ? await api.put(`/rrhh/empleados/${empleado.id}`, datos)
                : await api.post("/rrhh/empleados", datos);
            showSuccess(data.message || "Guardado");
            onGuardado(data);
        } catch (error) {
            const campos = erroresDeValidacion(error);
            setErrores(campos);
            showError(
                Object.keys(campos).length
                    ? "Revisa los campos marcados en rojo."
                    : mensajeDeError(error, "No se pudo guardar la ficha."),
            );
        } finally {
            setGuardando(false);
        }
    };

    const obrasPorId = Object.fromEntries(obras.map((o) => [o.id, o.nombre]));

    return (
        <Modal
            etiqueta={empleado ? "Editar ficha" : "Alta"}
            titulo={empleado ? empleado.nombre_completo : "Nuevo empleado"}
            subtitulo={
                empleado
                    ? "Los campos con * son obligatorios."
                    : "Se creará su carpeta en el Drive (Trabajadores) con un apartado por tipo de documento."
            }
            onCerrar={onCerrar}
            ancho="max-w-3xl"
            pie={
                <>
                    <button type="button" onClick={onCerrar} className={botonSecundario} disabled={guardando}>
                        Cancelar
                    </button>
                    <button type="submit" form="form-empleado" className={botonPrimario} disabled={guardando}>
                        {guardando ? (
                            <>
                                <i className="mgc_loading_line animate-spin"></i> Guardando…
                            </>
                        ) : (
                            <>
                                <i className="mgc_check_line"></i> {empleado ? "Guardar cambios" : "Dar de alta"}
                            </>
                        )}
                    </button>
                </>
            }
        >
            <form id="form-empleado" onSubmit={guardar} className="space-y-7" autoComplete="off" noValidate>
                <Seccion titulo="Datos personales" icono="mgc_user_3_line">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Nombre" obligatorio error={errores.nombre}>
                            {input("nombre", { placeholder: "Juan" })}
                        </Campo>
                        <Campo etiqueta="Apellidos" obligatorio error={errores.apellidos}>
                            {input("apellidos", { placeholder: "García López" })}
                        </Campo>
                        <Campo etiqueta="DNI / NIE" obligatorio error={errores.dni}>
                            {input("dni", { placeholder: "12345678Z", mono: true })}
                        </Campo>
                        <Campo etiqueta="Nº Seguridad Social" error={errores.nss} ayuda="12 dígitos">
                            {input("nss", { placeholder: "28 12345678 40", mono: true, inputMode: "numeric" })}
                        </Campo>
                        <Campo etiqueta="Fecha de nacimiento" error={errores.fecha_nacimiento}>
                            {input("fecha_nacimiento", { type: "date" })}
                        </Campo>
                        <Campo etiqueta="Teléfono" error={errores.telefono}>
                            {input("telefono", { type: "tel", placeholder: "600 000 000" })}
                        </Campo>
                        <Campo etiqueta="Email" error={errores.email} className="sm:col-span-2">
                            {input("email", { type: "email", placeholder: "nombre@ejemplo.com" })}
                        </Campo>
                    </div>
                </Seccion>

                <Seccion titulo="Dirección" icono="mgc_home_3_line">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-6">
                        <Campo etiqueta="Dirección" error={errores.direccion} className="sm:col-span-6">
                            {input("direccion", { placeholder: "Calle, número, piso…" })}
                        </Campo>
                        <Campo etiqueta="Código postal" error={errores.codigo_postal} className="sm:col-span-2">
                            {input("codigo_postal", { inputMode: "numeric" })}
                        </Campo>
                        <Campo etiqueta="Población" error={errores.poblacion} className="sm:col-span-2">
                            {input("poblacion")}
                        </Campo>
                        <Campo etiqueta="Provincia" error={errores.provincia} className="sm:col-span-2">
                            {input("provincia")}
                        </Campo>
                    </div>
                </Seccion>

                <Seccion titulo="Puesto y contrato" icono="mgc_briefcase_line">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {!empleado && (
                            <Campo etiqueta="Fecha de alta" obligatorio error={errores.fecha_alta}>
                                {input("fecha_alta", { type: "date" })}
                            </Campo>
                        )}
                        <Campo etiqueta="Puesto" error={errores.puesto}>
                            {input("puesto", { placeholder: "Oficial de primera" })}
                        </Campo>
                        <Campo etiqueta="Categoría (convenio)" error={errores.categoria_convenio}>
                            {input("categoria_convenio")}
                        </Campo>
                        <Campo etiqueta="Tipo de contrato" error={errores.tipo_contrato}>
                            {select("tipo_contrato", opciones?.tipos_contrato, "Sin indicar")}
                        </Campo>
                        <Campo etiqueta="Jornada" error={errores.jornada}>
                            {select("jornada", opciones?.jornadas, "Sin indicar")}
                        </Campo>
                        <Campo etiqueta="Horas semanales" error={errores.horas_semanales}>
                            {input("horas_semanales", { type: "number", min: 0, max: 60, step: "0.5" })}
                        </Campo>
                        <Campo etiqueta="Obra asignada" error={errores.obra_id} className={empleado ? "sm:col-span-2" : ""}>
                            <select name="obra_id" value={datos.obra_id ?? ""} onChange={cambiar} className={claseInput(errores.obra_id)}>
                                <option value="">Sin obra asignada</option>
                                {datos.obra_id && !obrasPorId[datos.obra_id] && (
                                    <option value={datos.obra_id}>Obra #{datos.obra_id}</option>
                                )}
                                {obras.map((o) => (
                                    <option key={o.id} value={o.id}>
                                        {o.nombre}
                                    </option>
                                ))}
                            </select>
                        </Campo>
                    </div>
                </Seccion>

                <Seccion titulo="Salario y datos bancarios" icono="mgc_bank_card_line">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Campo etiqueta="Salario bruto anual (€)" error={errores.salario_bruto_anual}>
                            {input("salario_bruto_anual", { type: "number", min: 0, step: "0.01", placeholder: "0,00" })}
                        </Campo>
                        <Campo etiqueta="IBAN" error={errores.iban} ayuda="Se guarda cifrado.">
                            {input("iban", { placeholder: "ES00 0000 0000 0000 0000 0000", mono: true })}
                        </Campo>
                    </div>
                </Seccion>

                <Seccion titulo="Contacto de emergencia" icono="mgc_phone_line">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <Campo etiqueta="Nombre" error={errores.contacto_emergencia_nombre}>
                            {input("contacto_emergencia_nombre")}
                        </Campo>
                        <Campo etiqueta="Relación" error={errores.contacto_emergencia_relacion}>
                            {input("contacto_emergencia_relacion", { placeholder: "Pareja, madre…" })}
                        </Campo>
                        <Campo etiqueta="Teléfono" error={errores.contacto_emergencia_telefono}>
                            {input("contacto_emergencia_telefono", { type: "tel" })}
                        </Campo>
                    </div>
                </Seccion>

                <Seccion titulo="Observaciones" icono="mgc_edit_line">
                    <textarea
                        name="observaciones"
                        rows={3}
                        value={datos.observaciones ?? ""}
                        onChange={cambiar}
                        className={claseInput(errores.observaciones)}
                    />
                    {errores.observaciones && <p className="text-xs text-red-600">{errores.observaciones}</p>}
                </Seccion>
            </form>
        </Modal>
    );
}
