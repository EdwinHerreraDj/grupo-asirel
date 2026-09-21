import React, { useEffect, useRef, useState } from "react";
import api from "../../shared/api";
import { useNotification } from "../../shared/NotificationContext";
import { formatEuro } from "../../shared/formato";
import SelectorObras from "./SelectorObras";
import { Campo, Modal, SeccionFormulario, claseInput } from "./Comunes";
import {
    botonPrimario,
    botonSecundario,
    dniValido,
    erroresDeValidacion,
    hoyISO,
    ibanLegible,
    ibanValido,
    limpiarIban,
    mensajeDeError,
} from "../utils";

const CAMPOS = [
    "nombre", "apellidos", "dni", "nss", "fecha_nacimiento", "telefono", "email",
    "direccion", "codigo_postal", "poblacion", "provincia",
    "puesto", "categoria_convenio", "tipo_contrato", "jornada", "horas_semanales",
    "salario_bruto_anual", "iban",
    "contacto_emergencia_nombre", "contacto_emergencia_relacion", "contacto_emergencia_telefono",
    "observaciones",
];

/** Apartados del formulario (menú lateral y errores por apartado). */
const SECCIONES = [
    { id: "personales", titulo: "Datos personales", descripcion: "Identificación y contacto", icono: "mgc_user_3_line", color: "cyan",
        campos: ["nombre", "apellidos", "dni", "nss", "fecha_nacimiento", "telefono", "email"] },
    { id: "direccion", titulo: "Dirección", descripcion: "Domicilio del empleado", icono: "mgc_home_3_line", color: "violet",
        campos: ["direccion", "codigo_postal", "poblacion", "provincia"] },
    { id: "contrato", titulo: "Puesto y contrato", descripcion: "Condiciones laborales", icono: "mgc_briefcase_line", color: "amber",
        campos: ["fecha_alta", "puesto", "categoria_convenio", "tipo_contrato", "jornada", "horas_semanales"] },
    { id: "obras", titulo: "Obras", descripcion: "Puede trabajar en varias a la vez", icono: "mgc_building_2_line", color: "emerald",
        campos: ["obra_ids"] },
    { id: "salario", titulo: "Salario y banco", descripcion: "Retribución y cuenta de cobro", icono: "mgc_bank_card_line", color: "indigo",
        campos: ["salario_bruto_anual", "iban"] },
    { id: "emergencia", titulo: "Contacto de emergencia", descripcion: "A quién avisar si ocurre algo", icono: "mgc_phone_line", color: "rose",
        campos: ["contacto_emergencia_nombre", "contacto_emergencia_relacion", "contacto_emergencia_telefono"] },
    { id: "observaciones", titulo: "Observaciones", descripcion: "Notas internas", icono: "mgc_edit_line", color: "slate",
        campos: ["observaciones"] },
];

const seccionDeCampo = (campo) =>
    SECCIONES.find((s) => s.campos.some((c) => campo === c || campo.startsWith(`${c}.`)))?.id;

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

/** Indicador de validez instantánea (DNI, IBAN). */
function Comprobacion({ valido, ok, mal }) {
    if (valido === null) return null;
    return valido ? (
        <p className="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-600">
            <i className="mgc_check_circle_line"></i> {ok}
        </p>
    ) : (
        <p className="mt-1 flex items-center gap-1 text-xs font-medium text-amber-600">
            <i className="mgc_warning_line"></i> {mal}
        </p>
    );
}

/** Alta (empleado = null) o edición de la ficha. */
export default function FormularioEmpleado({ empleado = null, opciones, onCerrar, onGuardado }) {
    const { showSuccess, showError } = useNotification();
    const [datos, setDatos] = useState(() => inicial(empleado));
    const [obras, setObras] = useState(() => empleado?.obras ?? []);
    const [errores, setErrores] = useState({});
    const [guardando, setGuardando] = useState(false);
    const [activa, setActiva] = useState(SECCIONES[0].id);

    const cuerpo = useRef(null);
    const refs = useRef({});

    // Sección visible → resaltada en el menú.
    useEffect(() => {
        const raiz = cuerpo.current;
        if (!raiz || !("IntersectionObserver" in window)) return;
        const obs = new IntersectionObserver(
            (entradas) => {
                const visible = entradas.filter((e) => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
                if (visible) setActiva(visible.target.id.replace("sec-", ""));
            },
            { root: raiz, rootMargin: "0px 0px -65% 0px" },
        );
        Object.values(refs.current).forEach((el) => el && obs.observe(el));
        return () => obs.disconnect();
    }, []);

    const irA = (id) => {
        setActiva(id);
        refs.current[id]?.scrollIntoView({ behavior: "smooth", block: "start" });
    };

    const cambiar = (e) => {
        const { name, value } = e.target;
        setDatos((prev) => ({ ...prev, [name]: value }));
        if (errores[name]) setErrores((prev) => ({ ...prev, [name]: null }));
    };

    const poner = (name, value) => {
        setDatos((prev) => ({ ...prev, [name]: value }));
        if (errores[name]) setErrores((prev) => ({ ...prev, [name]: null }));
    };

    const input = (name, props = {}) => {
        const { mono, ...resto } = props;
        return (
            <input
                name={name}
                id={`campo-${name}`}
                value={datos[name] ?? ""}
                onChange={cambiar}
                autoComplete="off"
                className={claseInput(errores[name], mono ? "font-mono" : "")}
                {...resto}
            />
        );
    };

    const select = (name, lista, vacio = "—") => (
        <select name={name} id={`campo-${name}`} value={datos[name] ?? ""} onChange={cambiar} className={claseInput(errores[name])}>
            <option value="">{vacio}</option>
            {Object.entries(lista || {}).map(([valor, texto]) => (
                <option key={valor} value={valor}>
                    {texto}
                </option>
            ))}
        </select>
    );

    const erroresPorSeccion = Object.entries(errores)
        .filter(([, v]) => v)
        .reduce((acc, [campo]) => {
            const s = seccionDeCampo(campo);
            if (s) acc[s] = (acc[s] || 0) + 1;
            return acc;
        }, {});

    const guardar = async (e) => {
        e.preventDefault();
        setGuardando(true);
        setErrores({});
        try {
            const payload = { ...datos, obra_ids: obras.map((o) => o.id) };
            const { data } = empleado
                ? await api.put(`/rrhh/empleados/${empleado.id}`, payload)
                : await api.post("/rrhh/empleados", payload);
            showSuccess(data.message || "Guardado");
            onGuardado(data);
        } catch (error) {
            const campos = erroresDeValidacion(error);
            setErrores(campos);
            const primero = Object.keys(campos)[0];
            if (primero) {
                showError("Revisa los apartados marcados en rojo.");
                irA(seccionDeCampo(primero) ?? SECCIONES[0].id);
            } else {
                showError(mensajeDeError(error, "No se pudo guardar la ficha."));
            }
        } finally {
            setGuardando(false);
        }
    };

    const seccion = (id) => {
        const s = SECCIONES.find((x) => x.id === id);
        return {
            id: `sec-${id}`,
            ref: (el) => (refs.current[id] = el),
            titulo: s.titulo,
            descripcion: s.descripcion,
            icono: s.icono,
            color: s.color,
            conError: !!erroresPorSeccion[id],
        };
    };

    const salario = parseFloat(datos.salario_bruto_anual);
    const valDni = dniValido(datos.dni);
    const valIban = ibanValido(datos.iban);

    return (
        <Modal
            etiqueta={empleado ? "Editar ficha" : "Alta de empleado"}
            titulo={empleado ? empleado.nombre_completo : "Nuevo empleado"}
            subtitulo={
                empleado
                    ? "Los campos con * son obligatorios."
                    : "Se creará su carpeta en el Drive (Trabajadores) con un apartado por tipo de documento."
            }
            onCerrar={onCerrar}
            ancho="max-w-5xl"
            refCuerpo={cuerpo}
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
            <form
                id="form-empleado"
                onSubmit={guardar}
                autoComplete="off"
                noValidate
                className="grid grid-cols-1 gap-5 md:grid-cols-[12.5rem_minmax(0,1fr)] md:gap-6"
            >
                {/* Menú de apartados: fila deslizable arriba en móvil, columna fija en escritorio */}
                <aside className="sticky -top-5 z-10 -mx-5 -mt-5 border-b border-slate-200 bg-white/95 px-5 py-2 backdrop-blur sm:-mx-6 sm:px-6 md:top-0 md:mx-0 md:mt-0 md:self-start md:border-0 md:bg-transparent md:p-0 md:backdrop-blur-none">
                    <nav className="flex gap-1 overflow-x-auto md:flex-col md:overflow-visible" aria-label="Apartados">
                        {SECCIONES.map((s) => {
                            const esActiva = activa === s.id;
                            const nErr = erroresPorSeccion[s.id];
                            return (
                                <button
                                    key={s.id}
                                    type="button"
                                    onClick={() => irA(s.id)}
                                    className={`inline-flex shrink-0 items-center gap-2 rounded-xl px-3 py-2 text-left text-sm font-medium transition md:w-full ${
                                        esActiva ? "bg-cyan-50 text-cyan-800" : "text-slate-500 hover:bg-slate-50 hover:text-slate-800"
                                    }`}
                                >
                                    <i className={`${s.icono} text-base`}></i>
                                    <span className="whitespace-nowrap md:flex-1 md:whitespace-normal">{s.titulo}</span>
                                    {nErr > 0 && (
                                        <span className="flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-[11px] font-bold text-white">
                                            {nErr}
                                        </span>
                                    )}
                                </button>
                            );
                        })}
                    </nav>
                </aside>

                <div className="min-w-0 space-y-5">
                    <SeccionFormulario {...seccion("personales")}>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Campo etiqueta="Nombre" obligatorio error={errores.nombre}>
                                {input("nombre", { placeholder: "Juan" })}
                            </Campo>
                            <Campo etiqueta="Apellidos" obligatorio error={errores.apellidos}>
                                {input("apellidos", { placeholder: "García López" })}
                            </Campo>
                            <Campo etiqueta="DNI / NIE" obligatorio error={errores.dni}>
                                {input("dni", { placeholder: "12345678Z", mono: true })}
                                {!errores.dni && <Comprobacion valido={valDni} ok="Letra correcta" mal="La letra no coincide o el formato no es válido" />}
                            </Campo>
                            <Campo etiqueta="Nº Seguridad Social" error={errores.nss} ayuda="12 dígitos">
                                {input("nss", { placeholder: "28 12345678 40", mono: true, inputMode: "numeric" })}
                            </Campo>
                            <Campo etiqueta="Fecha de nacimiento" error={errores.fecha_nacimiento}>
                                {input("fecha_nacimiento", { type: "date", max: hoyISO() })}
                            </Campo>
                            <Campo etiqueta="Teléfono" error={errores.telefono}>
                                {input("telefono", { type: "tel", placeholder: "600 000 000" })}
                            </Campo>
                            <Campo etiqueta="Email" error={errores.email} className="sm:col-span-2">
                                {input("email", { type: "email", placeholder: "nombre@ejemplo.com" })}
                            </Campo>
                        </div>
                    </SeccionFormulario>

                    <SeccionFormulario {...seccion("direccion")}>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-6">
                            <Campo etiqueta="Dirección" error={errores.direccion} className="sm:col-span-6">
                                {input("direccion", { placeholder: "Calle, número, piso…" })}
                            </Campo>
                            <Campo etiqueta="Código postal" error={errores.codigo_postal} className="sm:col-span-2">
                                {input("codigo_postal", { inputMode: "numeric", placeholder: "28001" })}
                            </Campo>
                            <Campo etiqueta="Población" error={errores.poblacion} className="sm:col-span-2">
                                {input("poblacion")}
                            </Campo>
                            <Campo etiqueta="Provincia" error={errores.provincia} className="sm:col-span-2">
                                {input("provincia")}
                            </Campo>
                        </div>
                    </SeccionFormulario>

                    <SeccionFormulario {...seccion("contrato")}>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {!empleado && (
                                <Campo etiqueta="Fecha de alta" obligatorio error={errores.fecha_alta} className="sm:col-span-2">
                                    {input("fecha_alta", { type: "date" })}
                                </Campo>
                            )}
                            <Campo etiqueta="Puesto" error={errores.puesto}>
                                {input("puesto", { placeholder: "Oficial de primera" })}
                            </Campo>
                            <Campo etiqueta="Categoría (convenio)" error={errores.categoria_convenio}>
                                {input("categoria_convenio")}
                            </Campo>
                            <Campo etiqueta="Tipo de contrato" error={errores.tipo_contrato} className="sm:col-span-2">
                                {select("tipo_contrato", opciones?.tipos_contrato, "Sin indicar")}
                            </Campo>
                            <Campo etiqueta="Jornada" error={errores.jornada}>
                                <div className="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1">
                                    {Object.entries(opciones?.jornadas || {}).map(([valor, texto]) => (
                                        <button
                                            key={valor}
                                            type="button"
                                            onClick={() => {
                                                poner("jornada", datos.jornada === valor ? "" : valor);
                                                if (valor === "completa" && !datos.horas_semanales) poner("horas_semanales", "40");
                                            }}
                                            className={`rounded-lg px-3 py-1.5 text-sm font-semibold transition ${
                                                datos.jornada === valor ? "bg-white text-slate-900 shadow-sm" : "text-slate-500 hover:text-slate-800"
                                            }`}
                                        >
                                            {texto}
                                        </button>
                                    ))}
                                </div>
                            </Campo>
                            <Campo etiqueta="Horas semanales" error={errores.horas_semanales}>
                                <div className="relative">
                                    {input("horas_semanales", { type: "number", min: 0, max: 60, step: "0.5" })}
                                    <span className="pointer-events-none absolute right-8 top-1/2 -translate-y-1/2 text-xs text-slate-400">h</span>
                                </div>
                            </Campo>
                        </div>
                    </SeccionFormulario>

                    <SeccionFormulario {...seccion("obras")}>
                        <Campo
                            etiqueta="Obras asignadas"
                            error={errores.obra_ids || Object.entries(errores).find(([k, v]) => k.startsWith("obra_ids.") && v)?.[1]}
                            ayuda="Escribe para buscar; puedes añadir varias. Déjalo vacío si no está asignado a ninguna."
                        >
                            <SelectorObras
                                multiple
                                enLinea
                                value={obras}
                                onChange={(lista) => {
                                    setObras(lista);
                                    setErrores((p) => Object.fromEntries(Object.entries(p).filter(([k]) => !k.startsWith("obra_ids"))));
                                }}
                                error={!!erroresPorSeccion.obras}
                            />
                        </Campo>
                    </SeccionFormulario>

                    <SeccionFormulario {...seccion("salario")}>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Campo etiqueta="Salario bruto anual" error={errores.salario_bruto_anual}>
                                <div className="relative">
                                    {input("salario_bruto_anual", { type: "number", min: 0, step: "0.01", placeholder: "0,00", className: claseInput(errores.salario_bruto_anual, "pr-8") })}
                                    <span className="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">€</span>
                                </div>
                                {salario > 0 && (
                                    <p className="mt-1 text-xs text-slate-500">
                                        ≈ {formatEuro(salario / 14)} en 14 pagas · {formatEuro(salario / 12)} en 12
                                    </p>
                                )}
                            </Campo>
                            <Campo etiqueta="IBAN" error={errores.iban}>
                                {input("iban", {
                                    placeholder: "ES00 0000 0000 0000 0000 0000",
                                    mono: true,
                                    onBlur: () => poner("iban", ibanLegible(limpiarIban(datos.iban))),
                                })}
                                {!errores.iban && (
                                    <Comprobacion valido={valIban} ok="IBAN correcto · se guarda cifrado" mal="Revisa los dígitos del IBAN" />
                                )}
                            </Campo>
                        </div>
                    </SeccionFormulario>

                    <SeccionFormulario {...seccion("emergencia")}>
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
                    </SeccionFormulario>

                    <SeccionFormulario {...seccion("observaciones")}>
                        <textarea
                            name="observaciones"
                            rows={3}
                            value={datos.observaciones ?? ""}
                            onChange={cambiar}
                            placeholder="Cualquier nota útil sobre el empleado…"
                            className={claseInput(errores.observaciones)}
                        />
                        {errores.observaciones && <p className="mt-1 text-xs text-red-600">{errores.observaciones}</p>}
                    </SeccionFormulario>
                </div>
            </form>
        </Modal>
    );
}
