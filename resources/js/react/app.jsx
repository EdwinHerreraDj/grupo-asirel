import React from "react";  
import { createRoot } from "react-dom/client";
import DriveApp from "./Drive/DriveApp";

const mounts = {
    "react-drive": DriveApp,
};

Object.entries(mounts).forEach(([id, Component]) => {
    const el = document.getElementById(id);
    if (el) {
        createRoot(el).render(<Component />);
    }
});
