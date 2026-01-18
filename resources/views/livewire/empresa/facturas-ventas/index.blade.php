<div>

    {{-- ======================
        ACCIONES SUPERIORES
    ======================= --}}

    <div class="flex flex-wrap items-center gap-3 mb-7">

        <x-btns.regresar href="{{ route('empresa.index') }}">
            Regresar
        </x-btns.regresar>

        <x-btns.agregar wire:click="nuevaFactura">
            Nueva factura
        </x-btns.agregar>

        <a href="{{ route('empresa.facturas-series') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-medium shadow-sm
                   bg-blue-500/20 text-blue-700 border border-blue-500/30
                   hover:bg-blue-600 hover:text-white transition-all duration-300">
            <i class="mgc_paper_line text-lg"></i>
            Series
        </a>



    </div>

    <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4">
        Facturas de ventas {{ $showFormulario }}
    </h2>

    {{-- ======================
        FILTROS
    ======================= --}}
    <x-tablas.filters>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">

            {{-- FECHA DESDE --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Fecha desde
                </label>
                <input type="date" wire:model.defer="tmpFechaDesde"
                    class="w-full rounded-xl border-gray-300 text-sm focus:ring-primary focus:border-primary">
            </div>

            {{-- FECHA HASTA --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Fecha hasta
                </label>
                <input type="date" wire:model.defer="tmpFechaHasta"
                    class="w-full rounded-xl border-gray-300 text-sm focus:ring-primary focus:border-primary">
            </div>

            {{-- Nº / SERIE --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Nº / Serie
                </label>
                <input type="text" wire:model.defer="tmpSearch" placeholder="Ej: FV-12"
                    class="w-full rounded-xl border-gray-300 text-sm focus:ring-primary focus:border-primary">
            </div>

            {{-- CÓDIGO FACTURA --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Código factura
                </label>
                <input type="text" wire:model.defer="tmpCodigo" placeholder="Ej: CERT-2026-01"
                    class="w-full rounded-xl border-gray-300 text-sm focus:ring-primary focus:border-primary">
            </div>

            {{-- ACCIONES --}}
            <div class="flex gap-2">
                <button wire:click="aplicarFiltros"
                    class="px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90">
                    Filtrar
                </button>

                <button wire:click="limpiarFiltros" class="px-4 py-2 rounded-xl bg-gray-100 text-sm hover:bg-gray-200">
                    Limpiar
                </button>
            </div>

        </div>
    </x-tablas.filters>





    {{-- ======================
        TABLA
    ======================= --}}


    <x-tablas.table>

        {{-- CABECERA --}}
        <x-slot name="columns">
            <th class="px-4 py-2 text-left">Factura</th>
            <th class="px-4 py-2 text-left">Origen</th>
            <th class="px-4 py-2 text-left">Cliente</th>
            <th class="px-4 py-2 text-left">Obra</th>
            <th class="px-4 py-2 text-left">Fecha emisión</th>
            <th class="px-4 py-2 text-right">Total</th>
            <th class="px-4 py-2 text-left">Estado</th>
            <th class="px-4 py-2 text-center">PDF</th>
            <th class="px-4 py-2 text-right">Acciones</th>
        </x-slot>

        {{-- FILAS --}}
        <x-slot name="rows">
            @forelse ($facturas as $factura)
                <tr class="hover:bg-gray-50 transition">

                    {{-- FACTURA --}}
                    <td class="px-6 py-4 font-medium">
                        @if ($factura->numero_factura)
                            {{ $factura->serie }}-{{ $factura->numero_factura }}
                        @else
                            <span class="italic text-gray-400">Borrador</span>
                        @endif
                    </td>

                    {{-- ORIGEN --}}
                    <td class="px-6 py-4">
                        <span
                            class="px-2 py-1 rounded-full text-xs font-semibold
                        {{ $factura->origen === 'manual' ? 'bg-indigo-100 text-indigo-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ ucfirst($factura->origen) }}
                        </span>
                    </td>

                    {{-- CLIENTE --}}
                    <td class="px-6 py-4 max-w-[220px]">
                        <span class="block truncate">
                            {{ $factura->cliente?->nombre ?? '—' }}
                        </span>
                    </td>

                    {{-- OBRA --}}
                    <td class="px-6 py-4 max-w-[220px]">
                        <span class="block truncate">
                            {{ $factura->obra?->nombre ?? '—' }}
                        </span>
                    </td>

                    {{-- FECHA --}}
                    <td class="px-6 py-4">
                        {{ $factura->fecha_emision ? date('d/m/Y', strtotime($factura->fecha_emision)) : '—' }}
                    </td>

                    {{-- TOTAL --}}
                    <td class="px-6 py-4 text-right font-semibold">
                        @if ($factura->estado === 'borrador')
                            <span class="italic text-gray-400">Pendiente</span>
                        @else
                            {{ number_format($factura->total, 2, ',', '.') }} €
                        @endif
                    </td>

                    {{-- ESTADO --}}
                    {{-- ESTADO --}}
                    <td class="px-6 py-4">
                        @if ($factura->estado === 'anulada')
                            <span title="{{ $factura->motivo_anulacion }}"
                                class="px-2 py-1 rounded-full text-xs font-semibold
                   bg-red-100 text-red-700 cursor-help">
                                Anulada
                            </span>
                        @else
                            <span
                                class="px-2 py-1 rounded-full text-xs font-semibold
            @class([
                'bg-gray-100 text-gray-700' => $factura->estado === 'borrador',
                'bg-blue-100 text-blue-700' => $factura->estado === 'emitida',
                'bg-yellow-100 text-yellow-700' => $factura->estado === 'enviada',
                'bg-green-100 text-green-700' => $factura->estado === 'pagada',
            ])">
                                {{ ucfirst($factura->estado) }}
                            </span>
                        @endif
                    </td>


                    {{-- PDF --}}
                    <td class="px-6 py-4 text-center">
                        @if (in_array($factura->estado, ['emitida', 'anulada']))
                            <a href="{{ route('empresa.facturas-ventas.pdf', $factura) }}" target="_blank"
                                class="inline-flex items-center justify-center gap-2
                  px-4 py-2 rounded-xl
                  bg-white text-gray-700
                  border border-gray-200
                  shadow-sm
                  hover:bg-gray-50 hover:border-gray-300 hover:shadow
                  focus:outline-none focus:ring-2 focus:ring-blue-500/30
                  transition-all duration-200">

                                <i class="mgc_pdf_line text-lg"></i>
                                <span class="text-sm font-medium">PDF</span>
                            </a>
                        @else
                            <span
                                class="inline-flex items-center justify-center gap-2
                     px-4 py-2 rounded-xl
                     bg-gray-50 text-gray-400
                     border border-gray-200
                     text-sm font-medium select-none">
                                <i class="mgc_pdf_line text-lg opacity-40"></i>
                                PDF
                            </span>
                        @endif
                    </td>


                    {{-- ACCIONES --}}
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div x-data="{ open: false }" class="relative inline-flex" @keydown.escape.window="open = false">

                            {{-- BOTÓN --}}
                            <button @click="open = !open" type="button"
                                class="inline-flex items-center justify-center w-9 h-9
                   rounded-lg text-gray-600
                   hover:bg-gray-100 hover:text-gray-900
                   focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                                <i class="mgc_more_2_line text-lg"></i>
                            </button>

                            {{-- MENÚ (abre hacia arriba) --}}
                            <div x-show="open" x-transition.origin.bottom.right @click.away="open = false"
                                class="absolute right-0 bottom-full mb-2 z-[9999]
                   w-40 rounded-xl bg-white shadow-xl border
                   overflow-hidden"
                                style="display: none">

                                {{-- DETALLE --}}
                                <a href="{{ route('empresa.facturas-ventas.detalle', $factura->id) }}"
                                    class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100">
                                    <i class="mgc_eye_line"></i>
                                    Detalle
                                </a>

                                {{-- EDITAR (solo borrador) --}}
                                @if ($factura->estado === 'borrador')
                                    <button wire:click="editarFactura({{ $factura->id }})"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-left hover:bg-gray-100">
                                        <i class="mgc_edit_line"></i>
                                        Editar
                                    </button>
                                @endif
                            </div>

                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-6 text-center text-gray-500">
                        No hay facturas de venta registradas.
                    </td>
                </tr>
            @endforelse
        </x-slot>

        <x-slot name="pagination">
            {{ $facturas->links() }}
        </x-slot>

    </x-tablas.table>


    {{-- ======================
        MODAL FORMULARIO
    ======================= --}}

    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4   {{ $showFormulario === true ? '' : 'hidden' }}">
        <div class="bg-white w-full max-w-3xl rounded-2xl shadow border max-h-[92vh] flex flex-col">

            <div class="flex items-center p-5 border-b">
                <h3 class="text-xl font-semibold">
                    {{ $facturaId ? 'Editar factura' : 'Nueva factura' }}
                </h3>
                <button wire:click="cerrarModalForm" class="ml-auto text-3xl">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto">
                <livewire:empresa.facturas-ventas.formulario :factura-id="$facturaId"
                    wire:key="formulario-factura-{{ $facturaId ?? 'new' }}" />
            </div>

        </div>
    </div>


</div>
