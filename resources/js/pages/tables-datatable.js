document.addEventListener("DOMContentLoaded", () => {
    initializeDataTable("table-docs");
});


function initializeDataTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;

    // Inicializar DataTable (requiere DataTables versión con soporte vanilla o mediante API moderna)
    new DataTable(table, {
        paging: true,
        searching: true,
        info: true,
        language: {
            lengthMenu: createTailwindLengthMenu(),
            zeroRecords: "No se encontraron resultados",
            emptyTable: "No hay datos disponibles en la tabla",
            info: "Mostrando página _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search: "",
            searchPlaceholder: "Buscar...",
            paginate: {
                previous: "← Anterior",
                next: "Siguiente →"
            }
        },
        layout: {
            topStart: 'pageLength',
            topEnd: 'search',
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
    });

    // 💅 Mejorar diseño del buscador y selector dinámicamente
    styleDataTableElements(table);
}

function createTailwindLengthMenu() {
    return `
        <label class="flex items-center space-x-2 text-sm text-gray-700">
            <span>Mostrar</span>
            <select class="px-2 py-1 bg-white border border-gray-300 rounded-md shadow-sm text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="-1">Todos</option>
            </select>
            <span>registros</span>
        </label>
    `;
}

function styleDataTableElements(table) {
    const wrapper = table.closest(".dt-container");

    // 🔍 Buscador mejorado
    // 🔍 Buscador mejorado y centrado visualmente
    const searchInput = wrapper.querySelector("input[type='search']");
    if (searchInput) {
        const searchWrapper = searchInput.parentElement;
        if (searchWrapper) {
            searchWrapper.classList.add("relative", "flex", "items-center", "justify-end", "mb-3");
        }

        // Ajuste del input
        searchInput.className = `
        w-64 pl-10 pr-3 py-2 text-sm text-gray-700 bg-white border border-gray-300
        rounded-lg shadow-sm placeholder-gray-400
        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
        transition-all duration-200
    `.replace(/\s+/g, " ");

        searchInput.placeholder = "Buscar...";

        // Crear icono si no existe aún
        if (!searchWrapper.querySelector(".search-icon")) {
            const icon = document.createElement("span");
            icon.className = `
            search-icon absolute left-3 flex items-center justify-center text-gray-400 
            transition-colors duration-200
        `;
            searchWrapper.appendChild(icon);

            // Efecto dinámico de color en foco
            searchInput.addEventListener("focus", () => {
                icon.classList.replace("text-gray-400", "text-blue-500");
            });
            searchInput.addEventListener("blur", () => {
                icon.classList.replace("text-blue-500", "text-gray-400");
            });
        }
    }



    // 🔽 Selector de cantidad
    const select = wrapper.querySelector("select");
    if (select) {
        select.className =
            "px-2 py-1 bg-white border border-gray-300 rounded-md shadow-sm text-sm " +
            "focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all";
    }

    // 📄 Info
    const info = wrapper.querySelector(".dt-info");
    if (info) info.classList.add("text-sm", "text-gray-600");

    // 📑 Paginación
    const paginate = wrapper.querySelector(".dt-paging");
    if (paginate) {
        // Contenedor horizontal, centrado
        paginate.classList.add(
            "flex",
            "items-center",
            "justify-center",
            "gap-1.5",
            "mt-4"
        );

        paginate.querySelectorAll("li").forEach(li => {
            li.classList.add("list-none");

            const btn = li.querySelector("button");
            if (!btn) return;

            const isActive =
                btn.getAttribute("aria-current") === "page" ||
                btn.classList.contains("current");

            btn.className = `
            min-w-[34px] h-8 px-3 flex items-center justify-center rounded-md text-sm font-medium
            border transition-all duration-200
            ${isActive
                    ? "bg-blue-600 text-white border-blue-600 shadow-sm"
                    : "bg-gray-50 text-gray-700 border-gray-200 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700"
                }
        `.replace(/\s+/g, " ");

            // Efecto de presión visual sutil
            btn.addEventListener("mousedown", () => btn.classList.add("scale-95"));
            btn.addEventListener("mouseup", () => btn.classList.remove("scale-95"));
            btn.addEventListener("mouseleave", () => btn.classList.remove("scale-95"));
        });

        // Ajustar botones “Anterior” y “Siguiente” si existen
        const prev = paginate.querySelector("button[aria-label='Previous']") || paginate.querySelector(".previous button");
        const next = paginate.querySelector("button[aria-label='Next']") || paginate.querySelector(".next button");

        if (prev) prev.textContent = "‹";
        if (next) next.textContent = "›";
    }


}
