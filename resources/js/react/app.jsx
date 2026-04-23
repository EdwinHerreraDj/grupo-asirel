import React from "react";
import { createRoot } from "react-dom/client";
import DriveApp from "./Drive/DriveApp";
import ClienteApp from "./Clientes/ClienteApp";
import ProveedoresApp from "./Proveedores/ProveedoresApp";
import CosteTeoricoApp from "./PresupuestoObra/CosteTeoricoApp";
import CosteTeoricoGlobalApp from "./PresupuestoObra/CosteTeoricoGlobalApp";
import PresupuestoVentaApp from "./PresupuestoObra/PresupuestoVentaApp";
import PresupuestoVentaGlobalApp from "./PresupuestoObra/PresupuestoVentaGlobalApp";
import CertificacionesApp from "./Certificaciones/CertificacionesApp";
import DetalleApp from "./Certificaciones/Detalle/DetalleApp";
import TareasApp from "./Tareas/TareasApp";

const mounts = {
    "react-drive": DriveApp,
    "react-clientes": ClienteApp,
    "react-proveedores": ProveedoresApp,
    "react-coste-teorico": CosteTeoricoApp,
    "react-coste-teorico-global": CosteTeoricoGlobalApp,
    "react-presupuesto-venta": PresupuestoVentaApp,
    "react-presupuesto-venta-global": PresupuestoVentaGlobalApp,
    "react-certificaciones-listado": CertificacionesApp,
    "react-certificaciones-detalle": DetalleApp,
    "react-tareas": TareasApp
};

Object.entries(mounts).forEach(([id, Component]) => {
    const el = document.getElementById(id);
    if (el) {
        createRoot(el).render(<Component />);
    }
});
