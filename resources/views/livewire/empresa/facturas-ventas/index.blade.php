<div>
    {{-- ===========================
         CABECERA
         =========================== --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)] mb-4">
        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Facturación
                    </div>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                        Facturas de venta
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Gestión global de facturas emitidas a clientes.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('unidad') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                        <i class="mgc_arrow_left_line"></i> Regresar
                    </a>

                    <a href="{{ route('empresa.facturas-series') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                        <i class="mgc_hashtag_line"></i> Series
                    </a>

                    <button wire:click="nuevaFactura"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(37,99,235,0.22)] transition hover:from-cyan-500 hover:to-blue-500">
                        <i class="mgc_add_line"></i> Nueva factura
                    </button>
                </div>
            </div>
        </div>

        {{-- FILTROS --}}
        <div class="border-b border-slate-200 bg-slate-50/50 px-5 py-4 sm:px-6">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <input type="text" wire:model.live.debounce.400ms="tmpSearch" placeholder="Buscar nº / serie…"
                    class="form-input rounded-xl border-slate-300 text-sm">

                <input type="text" wire:model.live.debounce.400ms="tmpCodigo"
                    placeholder="Código certificación…" class="form-input rounded-xl border-slate-300 text-sm">

                <select wire:model.live="tmpEstado" class="form-select rounded-xl border-slate-300 text-sm">
                    <option value="">Todos los estados</option>
                    @foreach (\App\Models\FacturaVenta::ESTADOS_META as $k => $m)
                        <option value="{{ $k }}">{{ $m['label'] }}</option>
                    @endforeach
                </select>

                <input type="date" wire:model.live="tmpFechaDesde" class="form-input rounded-xl border-slate-300 text-sm">
                <input type="date" wire:model.live="tmpFechaHasta" class="form-input rounded-xl border-slate-300 text-sm">
            </div>

            <div class="mt-3 flex items-center gap-2">
                <button wire:click="aplicarFiltros"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-900">
                    <i class="mgc_filter_line"></i> Aplicar
                </button>
                <button wire:click="limpiarFiltros"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                    <i class="mgc_close_line"></i> Limpiar
                </button>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50/80 text-slate-600">
                    <tr class="border-b border-slate-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Factura</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Obra</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Fecha</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">Total</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">Pagado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Estado</th>
                        <th class="w-24 px-4 py-3 text-right sm:px-5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($facturas as $factura)
                        @php
                            $meta = $estadosMeta[$factura->estado] ?? ['label' => $factura->estado, 'color' => 'bg-slate-100 text-slate-700 border-slate-200'];
                            $totalPagado = (float) ($factura->total_pagado ?? 0);
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 sm:px-5">
                                <div class="font-semibold text-slate-800 font-mono">
                                    {{ $factura->serie }}-{{ $factura->numero_factura ?? 'BORRADOR' }}
                                </div>
                                <div class="mt-0.5 flex items-center gap-2 text-[11px] text-slate-400">
                                    @if ($factura->origen === 'certificacion')
                                        <span class="inline-flex items-center gap-1 text-emerald-600">
                                            <i class="mgc_link_2_line"></i> Cert.
                                        </span>
                                    @else
                                        <span>Manual</span>
                                    @endif
                                    @if ($factura->adjunto)
                                        <a href="{{ asset('storage/' . $factura->adjunto) }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-red-600 hover:underline" title="Ver proforma">
                                            <i class="mgc_attachment_2_line"></i> Proforma
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 sm:px-5 text-slate-700">{{ $factura->cliente->nombre ?? '—' }}</td>
                            <td class="px-4 py-3 sm:px-5">
                                @if ($factura->obra)
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        {{ $factura->obra->nombre }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-5 text-slate-600">
                                {{ $factura->fecha_emision?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 sm:px-5 text-right font-semibold text-slate-900">
                                {{ number_format($factura->total, 2, ',', '.') }} €
                            </td>
                            <td class="px-4 py-3 sm:px-5 text-right text-slate-700">
                                {{ number_format($totalPagado, 2, ',', '.') }} €
                                @if ($totalPagado > 0 && $totalPagado < $factura->total)
                                    <div class="text-[10px] text-amber-600">Parcial</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-5">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $meta['color'] }}">
                                    {{ $meta['label'] }}
                                </span>
                                @if ($factura->estado !== 'borrador')
                                    @php $cobroMetaFila = \App\Support\EstadoCobro::meta($factura->estado_cobro); @endphp
                                    <div class="mt-1">
                                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-medium {{ $cobroMetaFila['color'] }}"
                                            title="Seguimiento de cobro (clasificación interna)">
                                            {{ $cobroMetaFila['label'] }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-5 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('empresa.facturas-ventas.detalle', $factura->id) }}"
                                        title="Ver detalle"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700">
                                        <i class="mgc_eye_2_line"></i>
                                    </a>
                                    @if ($factura->tienePdfOriginal())
                                        <a href="{{ route('empresa.facturas-ventas.pdf', $factura->id) }}" target="_blank"
                                            title="Descargar PDF original"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/><path d="M12 11v6"/><path d="m9.5 14.5 2.5 2.5 2.5-2.5"/></svg>
                                        </a>
                                    @endif
                                    <button wire:click="abrirAcciones({{ $factura->id }})" title="Más acciones"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-slate-400 hover:bg-slate-50">
                                        <i class="mgc_more_2_line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center text-sm text-slate-500 sm:px-5">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <i class="mgc_inbox_line text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-700">Sin facturas</p>
                                        <p class="mt-1 text-sm text-slate-500">Crea una nueva factura para empezar.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-4 py-3 sm:px-5">
            {{ $facturas->links() }}
        </div>
    </div>

    {{-- ===========================
         MODAL FORMULARIO NUEVA / EDITAR
         =========================== --}}
    @if ($showFormulario)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cerrarModalForm()">
            <div class="w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[90vh] flex flex-col">

                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-6 py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                {{ $facturaId ? 'Editar borrador' : 'Nueva factura' }}
                            </div>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900">
                                {{ $facturaId ? 'Modifica los datos del borrador' : 'Datos de la nueva factura' }}
                            </h3>
                            <p class="text-sm text-slate-500 mt-0.5">
                                Los importes se calcularán desde las líneas al guardar.
                            </p>
                        </div>
                        <button wire:click="cerrarModalForm"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                            <i class="mgc_close_line text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-5">
                    @if ($showFormulario)
                        <livewire:empresa.facturas-ventas.formulario
                            :factura-id="$facturaId"
                            :key="'form-' . ($facturaId ?? 'nuevo')" />
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ===========================
         MODAL ACCIONES
         =========================== --}}
    @if ($showAccionesModal && $facturaAcciones)
        @php
            $metaAcc = $estadosMeta[$facturaAcciones->estado] ?? ['label' => $facturaAcciones->estado, 'color' => 'bg-slate-100 text-slate-700 border-slate-200'];
        @endphp
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4"
            x-data x-on:keydown.escape.window="$wire.cerrarAcciones()">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="flex items-start justify-between px-6 py-5 border-b border-slate-200">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Acciones de factura</h3>
                        <p class="text-sm text-slate-500 mt-0.5 font-mono">
                            {{ $facturaAcciones->serie }}-{{ $facturaAcciones->numero_factura ?? 'BORRADOR' }}
                        </p>
                        <span class="mt-2 inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $metaAcc['color'] }}">
                            {{ $metaAcc['label'] }}
                        </span>
                    </div>
                    <button wire:click="cerrarAcciones"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                        <i class="mgc_close_line text-lg"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-2 p-5">
                    <a href="{{ route('empresa.facturas-ventas.detalle', $facturaAcciones->id) }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 hover:border-cyan-300 hover:bg-cyan-50">
                        <i class="mgc_eye_2_line text-cyan-600"></i>
                        Ver detalle
                    </a>

                    @if ($facturaAcciones->tienePdfOriginal())
                        <a href="{{ route('empresa.facturas-ventas.pdf', $facturaAcciones->id) }}" target="_blank"
                            class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 hover:border-red-300 hover:bg-red-50">
                            <svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/><path d="M12 11v6"/><path d="m9.5 14.5 2.5 2.5 2.5-2.5"/></svg>
                            Descargar original
                        </a>
                    @endif

                    @if ($facturaAcciones->puedeGenerarCopia())
                        <a href="{{ route('empresa.facturas-ventas.pdf.copia', $facturaAcciones->id) }}"
                            class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 hover:border-cyan-300 hover:bg-cyan-50">
                            <i class="mgc_print_line text-cyan-600"></i>
                            Generar copia PDF
                        </a>
                    @endif

                    @if ($facturaAcciones->adjunto)
                        <a href="{{ asset('storage/' . $facturaAcciones->adjunto) }}" target="_blank"
                            class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 hover:border-amber-300 hover:bg-amber-50">
                            <i class="mgc_attachment_2_line text-amber-600"></i>
                            Ver proforma adjunta
                        </a>
                    @endif

                    @if ($facturaAcciones->estado === 'borrador')
                        <button wire:click="editarFactura({{ $facturaAcciones->id }})"
                            class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 hover:border-indigo-300 hover:bg-indigo-50 text-left">
                            <i class="mgc_edit_2_line text-indigo-600"></i>
                            Editar borrador
                        </button>
                    @endif
                </div>

                <div class="bg-slate-50 px-5 py-3 flex justify-end border-t border-slate-200">
                    <button wire:click="cerrarAcciones"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
