import React from "react";  
import { createRoot } from "react-dom/client";
import DriveApp from "./Drive/DriveApp";
import ClienteApp from "./Clientes/ClienteApp";
import ProveedoresApp from "./Proveedores/ProveedoresApp";

const mounts = {
    "react-drive": DriveApp,
    "react-clientes": ClienteApp,
    "react-proveedores": ProveedoresApp,
};

Object.entries(mounts).forEach(([id, Component]) => {
    const el = document.getElementById(id);
    if (el) {
        createRoot(el).render(<Component />);
    }
});
