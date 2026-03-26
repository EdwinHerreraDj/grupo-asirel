import React from "react";
import { createRoot } from "react-dom/client";
import DriveApp from "./Drive/DriveApp";
import ClienteApp from "./Clientes/ClienteApp";
import ProveedoresApp from "./Proveedores/ProveedoresApp";
import PresupuestoObraApp from "./PresupuestoObra/PresupuestoObraApp";
import CertificacionesApp from "./Certificaciones/CertificacionesApp";
import DetalleApp from "./Certificaciones/Detalle/DetalleApp";

const mounts = {
    "react-drive": DriveApp,
    "react-clientes": ClienteApp,
    "react-proveedores": ProveedoresApp,
    "react-presupuesto-obra": PresupuestoObraApp,
    "react-certificaciones-listado": CertificacionesApp,
    "react-certificaciones-detalle": DetalleApp
};

Object.entries(mounts).forEach(([id, Component]) => {
    const el = document.getElementById(id);
    if (el) {
        createRoot(el).render(<Component />);
    }
});
