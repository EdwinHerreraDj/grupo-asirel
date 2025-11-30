<div class="p-8 space-y-10">

    {{-- Encabezado --}}
    <div class="space-y-2">
        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-100 flex items-center gap-3">
            <i class="mgc_chart_2_line text-blue-600 text-3xl"></i>
            Informes Generales
        </h2>
        <p class="text-gray-500 dark:text-gray-400 text-base">
            Genera informes globales o filtrados por obra, y descárgalos fácilmente en formato Excel o PDF.
        </p>
    </div>

    {{-- Informes de costes de obras --}}
    <div
        class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 shadow-sm">

        {{-- Cabecera descriptiva --}}
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="mgc_filter_2_line text-blue-600"></i>
                    Informe de Costes de Obras
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Consulta los gastos totales por categoría (materiales, subcontratas, alquileres, etc.) y
                    filtra según tus necesidades.
                </p>
            </div>
        </div>

        {{-- Contenedor de filtros --}}
        <div class="p-6 grid grid-cols-1 md:grid-cols-6 gap-6">

            {{-- Obra --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Obra
                </label>
                <div class="relative">
                    <i
                        class="mgc_building_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select wire:model="obraSeleccionada"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todas">Todas las obras</option>
                        @foreach ($obras as $obra)
                            <option value="{{ $obra->id }}">{{ $obra->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Estado --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Estado
                </label>
                <div class="relative">
                    <i
                        class="mgc_flag_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select wire:model="estadoSeleccionado"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="todas">Todas</option>
                        <option value="planificacion">Planificación</option>
                        <option value="ejecucion">Ejecución</option>
                        <option value="finalizada">Finalizada</option>
                    </select>
                </div>
            </div>

            {{-- Fechas --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Desde</label>
                <input type="date" wire:model="fechaInicio"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hasta</label>
                <input type="date" wire:model="fechaFin"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Formato --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Formato</label>
                <div class="relative">
                    <i
                        class="mgc_file_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select wire:model="formato"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="pdf">PDF (.pdf)</option>
                    </select>
                </div>
            </div>

            {{-- Botón Exportar --}}
            <div class="flex items-end">
                <button wire:click="exportarCosteTotal"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white font-medium shadow-md hover:shadow-lg transition-all">
                    <i class="mgc_download_2_line text-lg"></i>
                    Generar informe
                </button>
            </div>



        </div>
    </div>

    {{-- Informe de Facturación Total --}}
    <div
        class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 shadow-sm">

        {{-- Cabecera descriptiva --}}
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="mgc_document_3_line text-green-600"></i>
                    Informe de Facturación Total
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Muestra el total facturado por obra o de forma global, con opción de filtrar por fechas y estado.
                </p>
            </div>
        </div>

        {{-- Contenedor de filtros --}}
        <div class="p-6 grid grid-cols-1 md:grid-cols-6 gap-6">

            {{-- Obra --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Obra
                </label>
                <div class="relative">
                    <i
                        class="mgc_building_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select wire:model="obraSeleccionada"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="todas">Todas las obras</option>
                        @foreach ($obras as $obra)
                            <option value="{{ $obra->id }}">{{ $obra->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Estado --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Estado
                </label>
                <div class="relative">
                    <i
                        class="mgc_flag_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select wire:model="estadoSeleccionado"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="todas">Todas</option>
                        <option value="planificacion">Planificación</option>
                        <option value="ejecucion">Ejecución</option>
                        <option value="finalizada">Finalizada</option>
                    </select>
                </div>
            </div>

            {{-- Fechas --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Desde</label>
                <input type="date" wire:model="fechaInicio"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hasta</label>
                <input type="date" wire:model="fechaFin"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- Formato --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Formato</label>
                <div class="relative">
                    <i
                        class="mgc_file_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select wire:model="formato"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="pdf">PDF (.pdf)</option>
                    </select>
                </div>
            </div>

            {{-- Botón Exportar --}}
            <div class="flex items-end">
                <button wire:click="exportarFacturacionTotal"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white font-medium shadow-md hover:shadow-lg transition-all">
                    <i class="mgc_download_2_line text-lg"></i>
                    Generar informe
                </button>
            </div>
        </div>
    </div>


    {{-- Informe de Rentabilidad --}}
    <div
        class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 shadow-sm">

        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="mgc_document_2_line text-yellow-500"></i>
                    Informe de Rentabilidad (Coste - Venta)
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Calcula el beneficio y margen de rentabilidad comparando los costes totales con la facturación por
                    obra.
                </p>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-6 gap-6">
            {{-- Obra --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Obra
                </label>
                <div class="relative">
                    <i
                        class="mgc_building_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select wire:model="obraSeleccionada"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="todas">Todas las obras</option>
                        @foreach ($obras as $obra)
                            <option value="{{ $obra->id }}">{{ $obra->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Estado --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Estado
                </label>
                <div class="relative">
                    <i
                        class="mgc_flag_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select wire:model="estadoSeleccionado"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="todas">Todas</option>
                        <option value="planificacion">Planificación</option>
                        <option value="ejecucion">Ejecución</option>
                        <option value="finalizada">Finalizada</option>
                    </select>
                </div>
            </div>

            {{-- Fechas --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Desde</label>
                <input type="date" wire:model="fechaInicio"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hasta</label>
                <input type="date" wire:model="fechaFin"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>

            {{-- Formato --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Formato</label>
                <div class="relative">
                    <i
                        class="mgc_file_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                    <select wire:model="formato"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="pdf">PDF (.pdf)</option>
                    </select>
                </div>
            </div>
            <div class="flex items-end">
                <button wire:click="exportarRentabilidad"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-600 hover:to-yellow-500 text-white font-medium shadow-md hover:shadow-lg transition-all">
                    <i class="mgc_download_2_line text-lg"></i>
                    Generar informe
                </button>
            </div>
        </div>
    </div>


    {{-- Informe Coste-Venta Mensual --}}
    <div
        class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 shadow-sm">

        {{-- Cabecera descriptiva --}}
        <div class="p-6 border-b border-emerald-100 dark:border-emerald-800 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="mgc_calendar_line text-emerald-500 text-xl"></i>
                    Informe Coste-Venta Mensual
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Genera un resumen mensual con costes, ventas y beneficio aplicando un porcentaje adicional de
                    <strong>gastos internos</strong>.
                </p>
            </div>
        </div>

        {{-- Contenedor de filtros --}}
        <div class="p-6 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                {{-- Obra --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Obra
                    </label>
                    <div class="relative">
                        <i
                            class="mgc_building_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                        <select wire:model="obraSeleccionada"
                            class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            <option value="todas">Todas las obras</option>
                            @foreach ($obras as $obra)
                                <option value="{{ $obra->id }}">{{ $obra->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Estado --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Estado
                    </label>
                    <div class="relative">
                        <i
                            class="mgc_flag_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                        <select wire:model="estadoSeleccionado"
                            class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            <option value="todas">Todas</option>
                            <option value="planificacion">Planificación</option>
                            <option value="ejecucion">Ejecución</option>
                            <option value="finalizada">Finalizada</option>
                        </select>
                    </div>
                </div>

                {{-- Fechas --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Desde</label>
                    <input type="date" wire:model="fechaInicio"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hasta</label>
                    <input type="date" wire:model="fechaFin"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                </div>

                {{-- Formato --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Formato</label>
                    <div class="relative">
                        <i
                            class="mgc_file_2_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                        <select wire:model="formato"
                            class="w-full pl-10 pr-4 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF (.pdf)</option>
                        </select>
                    </div>
                </div>

                {{-- % Gastos Internos --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">% Gastos
                        Internos</label>
                    <input type="number" wire:model="porcentajeAdicional" min="0" max="50"
                        step="0.1"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                        placeholder="Ej. 10">
                </div>
            </div>

            {{-- Botón fuera del grid --}}
            <div class="flex pt-4 border-t border-gray-100 dark:border-gray-700">
                <button wire:click="exportarCosteVentaMensual"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-medium shadow-md hover:shadow-lg transition-all duration-200">
                    <i class="mgc_download_2_line text-lg"></i>
                    Generar informe mensual
                </button>
            </div>
        </div>
    </div>


</div>
