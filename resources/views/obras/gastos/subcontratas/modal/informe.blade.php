<div id="informe"
    class="fixed top-0 left-0 z-50 transition-all duration-500 fc-modal hidden w-full h-full min-h-full items-center fc-modal-open:flex">
    <div
        class="fc-modal-open:opacity-100 duration-500 opacity-0 ease-out transition-[opacity] sm:max-w-lg sm:w-full sm:mx-auto  flex-col bg-white border shadow-sm rounded-md dark:bg-slate-800 dark:border-gray-700">
        <div class="flex justify-between items-center py-2.5 px-4 border-b dark:border-gray-700">
            <h3 class="font-medium text-gray-800 dark:text-white text-lg">
                Informe de Materiales obra {{ $obra->nombre }}
            </h3>
            <button class="inline-flex flex-shrink-0 justify-center items-center h-8 w-8 dark:text-gray-200"
                data-fc-dismiss type="button">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>




        <div class="px-4 py-8 overflow-y-auto">
            <div>

                <form method="GET" id="informes-form" class="p-2">

                    <!-- Filtro por rango de fechas -->
                    <label for="fecha_inicio" class="font-medium text-gray-700">Fecha inicio:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-5 focus:ring focus:ring-blue-200 focus:border-blue-400">

                    <label for="fecha_fin" class="font-medium text-gray-700">Fecha fin:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-8 focus:ring focus:ring-blue-200 focus:border-blue-400">

                    <!-- Botones -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="button" onclick="exportPDF({{ $obra->id }})"
                            class="w-full sm:w-1/2 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white font-medium shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400/40 transition-all">
                            <i class="mgc_file_pdf_line text-lg"></i> Descargar PDF
                        </button>

                        <button type="button" onclick="exportExcel({{ $obra->id }})"
                            class="w-full sm:w-1/2 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white font-medium shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400/40 transition-all">
                            <i class="mgc_file_excel_line text-lg"></i> Descargar Excel
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
