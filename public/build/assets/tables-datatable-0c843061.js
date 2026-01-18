document.addEventListener("DOMContentLoaded",()=>{d("table-docs")});function d(a){const t=document.getElementById(a);t&&(new DataTable(t,{paging:!0,searching:!0,info:!0,language:{lengthMenu:p(),zeroRecords:"No se encontraron resultados",emptyTable:"No hay datos disponibles en la tabla",info:"Mostrando página _PAGE_ de _PAGES_",infoEmpty:"No hay registros disponibles",infoFiltered:"(filtrado de _MAX_ registros totales)",search:"",searchPlaceholder:"Buscar...",paginate:{previous:"← Anterior",next:"Siguiente →"}},layout:{topStart:"pageLength",topEnd:"search",bottomStart:"info",bottomEnd:"paging"}}),b(t))}function p(){return`
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
    `}function b(a){const t=a.closest(".dt-container"),s=t.querySelector("input[type='search']");if(s){const r=s.parentElement;if(r&&r.classList.add("relative","flex","items-center","justify-end","mb-3"),s.className=`
        w-64 pl-10 pr-3 py-2 text-sm text-gray-700 bg-white border border-gray-300
        rounded-lg shadow-sm placeholder-gray-400
        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
        transition-all duration-200
    `.replace(/\s+/g," "),s.placeholder="Buscar...",!r.querySelector(".search-icon")){const n=document.createElement("span");n.className=`
            search-icon absolute left-3 flex items-center justify-center text-gray-400 
            transition-colors duration-200
        `,r.appendChild(n),s.addEventListener("focus",()=>{n.classList.replace("text-gray-400","text-blue-500")}),s.addEventListener("blur",()=>{n.classList.replace("text-blue-500","text-gray-400")})}}const i=t.querySelector("select");i&&(i.className="px-2 py-1 bg-white border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all");const l=t.querySelector(".dt-info");l&&l.classList.add("text-sm","text-gray-600");const o=t.querySelector(".dt-paging");if(o){o.classList.add("flex","items-center","justify-center","gap-1.5","mt-4"),o.querySelectorAll("li").forEach(c=>{c.classList.add("list-none");const e=c.querySelector("button");if(!e)return;const u=e.getAttribute("aria-current")==="page"||e.classList.contains("current");e.className=`
            min-w-[34px] h-8 px-3 flex items-center justify-center rounded-md text-sm font-medium
            border transition-all duration-200
            ${u?"bg-blue-600 text-white border-blue-600 shadow-sm":"bg-gray-50 text-gray-700 border-gray-200 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700"}
        `.replace(/\s+/g," "),e.addEventListener("mousedown",()=>e.classList.add("scale-95")),e.addEventListener("mouseup",()=>e.classList.remove("scale-95")),e.addEventListener("mouseleave",()=>e.classList.remove("scale-95"))});const r=o.querySelector("button[aria-label='Previous']")||o.querySelector(".previous button"),n=o.querySelector("button[aria-label='Next']")||o.querySelector(".next button");r&&(r.textContent="‹"),n&&(n.textContent="›")}}
