import React from "react";  
import { createRoot } from "react-dom/client";
import DriveApp from "./Drive/DriveApp";
import ClienteApp from "./Clientes/ClienteApp";

const mounts = {
    "react-drive": DriveApp,
    "react-clientes": ClienteApp,
};

Object.entries(mounts).forEach(([id, Component]) => {
    const el = document.getElementById(id);
    if (el) {
        createRoot(el).render(<Component />);
    }
});
