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
                    <td class="px-6 py-4">
                        @if ($factura->estado === 'anulada')
                            <span title="{{ $factura->motivo_anulacion }}"
                                class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 cursor-help">
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
                        @if ($factura->pdf_url)
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
                        <button wire:click="abrirAcciones({{ $factura->id }})" type="button"
                            class="inline-flex items-center justify-center w-9 h-9
                                        rounded-lg text-gray-600
                                        hover:bg-gray-100 hover:text-gray-900
                                        focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                            title="Acciones">
                            <i class="mgc_more_2_line text-lg"></i>
                        </button>
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

    {{-- MODAL ACCIONES --}}
    @if ($showAccionesModal && $facturaAcciones)
        <div class="fixed inset-0 z-[99999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-8"
            x-data x-on:keydown.escape.window="$wire.cerrarAcciones()">

            <div
                class="w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">

                {{-- HEADER --}}
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div
                                class="h-10 w-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center shrink-0">
                                <i class="mgc_bill_line text-xl text-gray-800 dark:text-gray-100"></i>
                            </div>

                            <div class="min-w-0">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    Acciones de factura
                                </h3>

                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 truncate">
                                    @if ($facturaAcciones->numero_factura)
                                        {{ $facturaAcciones->serie }}-{{ $facturaAcciones->numero_factura }}
                                    @else
                                        BORRADOR ({{ $facturaAcciones->serie ?? '—' }})
                                    @endif
                                </p>
                            </div>
                        </div>

                        <button wire:click="cerrarAcciones" type="button"
                            class="h-9 w-9 inline-flex items-center justify-center rounded-xl
                               border border-gray-200 dark:border-gray-700
                               text-gray-500 hover:text-gray-800 dark:text-gray-300 dark:hover:text-white
                               hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            <i class="mgc_close_line text-xl"></i>
                        </button>
                    </div>

                    {{-- Badge estado --}}
                    <div class="mt-4 flex items-center gap-2">
                        @php
                            $estado = $facturaAcciones->estado ?? '';
                            $estadoBadge = match ($estado) {
                                'borrador'
                                    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'emitida' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                'enviada' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
                                'pagada' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                'anulada' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
                            };
                        @endphp

                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $estadoBadge }}">
                            {{ strtoupper($facturaAcciones->estado) }}
                        </span>

                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                 bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            {{ strtoupper($facturaAcciones->origen) }}
                        </span>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="px-6 py-5 space-y-4">

                    {{-- INFO (tarjeta) --}}
                    <div
                        class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 p-4">
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-500 dark:text-gray-400">Estado</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ strtoupper($facturaAcciones->estado) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <span class="text-gray-500 dark:text-gray-400">Origen</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ strtoupper($facturaAcciones->origen) }}
                                </span>
                            </div>

                            <div class="flex items-start justify-between gap-3">
                                <span class="text-gray-500 dark:text-gray-400">Cliente</span>
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100 text-right max-w-[65%] truncate">
                                    {{ $facturaAcciones->cliente?->nombre ?? '—' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- ACCIONES --}}
                    <div class="grid gap-2.5">

                        {{-- DETALLE --}}
                        <a href="{{ route('empresa.facturas-ventas.detalle', $facturaAcciones->id) }}"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                               bg-gray-900 text-white hover:bg-gray-800 transition">
                            <i class="mgc_eye_2_line text-lg"></i>
                            Agregar detalle
                        </a>

                        {{-- PDF --}}
                        @if ($facturaAcciones->pdf_url)
                            <a href="{{ route('empresa.facturas-ventas.pdf', $facturaAcciones) }}" target="_blank"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                                   bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100
                                   border border-gray-200 dark:border-gray-700
                                   hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <i class="mgc_pdf_line text-lg"></i>
                                Ver PDF
                            </a>
                        @else
                            <button type="button" disabled
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                                   bg-gray-100 dark:bg-gray-800 text-gray-400
                                   border border-gray-200 dark:border-gray-700 cursor-not-allowed">
                                <i class="mgc_pdf_line text-lg opacity-40"></i>
                                PDF no disponible
                            </button>
                        @endif

                        {{-- EDITAR --}}
                        @if ($facturaAcciones->estado === 'borrador')
                            <button wire:click="editarFactura({{ $facturaAcciones->id }})" type="button"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                                   bg-blue-600 text-white hover:bg-blue-700 transition">
                                <i class="mgc_edit_line text-lg"></i>
                                Editar borrador
                            </button>
                        @endif

                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <button wire:click="cerrarAcciones" type="button"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl
                           bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200
                           border border-gray-200 dark:border-gray-700
                           hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    @endif




</div>
