@php
    $meta = $factura->estadoMeta();
@endphp

<div>
    {{-- PRELOADER EMITIR --}}
    <div wire:loading.flex wire:target="emitirFactura"
        class="fixed inset-0 z-[10000] items-center justify-center bg-slate-900/60 backdrop-blur-sm"
        style="display: none;">
        <div class="flex flex-col items-center gap-4 rounded-2xl bg-white px-8 py-6 shadow-2xl">
            <div class="relative h-12 w-12">
                <div class="absolute inset-0 rounded-full border-4 border-slate-200"></div>
                <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-cyan-600"></div>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-slate-800">Emitiendo factura…</p>
                <p class="text-xs text-slate-500 mt-0.5">Consumiendo numeración y generando PDF.</p>
            </div>
        </div>
    </div>

    {{-- CABECERA --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)] mb-4">
        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('empresa.facturas-ventas') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">
                    <i class="mgc_arrow_left_line text-lg"></i> Facturas
                </a>
            </div>

            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Factura
                    </div>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 font-mono">
                        {{ $factura->serie }}-{{ $factura->numero_factura ?? 'BORRADOR' }}
                    </h2>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $meta['color'] }}">
                            {{ $meta['label'] }}
                        </span>
                        @if ($factura->origen === 'certificacion')
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                <i class="mgc_link_2_line"></i> Desde cert. {{ $factura->codigo_certificacion }}
                            </span>
                        @endif
                        @if ($factura->adjunto)
                            <a href="{{ asset('storage/' . $factura->adjunto) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700 hover:bg-amber-100">
                                <i class="mgc_attachment_2_line"></i> Ver proforma adjunta
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    @if ($factura->tienePdfOriginal())
                        <a href="{{ route('empresa.facturas-ventas.pdf', $factura->id) }}" target="_blank"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            <svg class="w-4 h-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/><path d="M12 11v6"/><path d="m9.5 14.5 2.5 2.5 2.5-2.5"/></svg> Descargar original
                        </a>
                    @endif

                    @if ($factura->puedeGenerarCopia())
                        <a href="{{ route('empresa.facturas-ventas.pdf.copia', $factura->id) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-cyan-300 bg-cyan-50 px-4 py-2.5 text-sm font-medium text-cyan-700 transition hover:bg-cyan-100"
                            title="Genera una copia visual con el logo/plantilla actuales. No modifica el original emitido.">
                            <i class="mgc_print_line"></i> Generar copia PDF
                        </a>
                    @endif

                    @if ($editable)
                        <button wire:click="abrirLineaNueva"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            <i class="mgc_add_line"></i> Añadir línea
                        </button>
                    @endif

                    @if ($factura->puedeEmitirse())
                        <button wire:click="confirmarEmitir"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(5,150,105,0.22)] transition hover:from-emerald-500 hover:to-green-500">
                            <i class="mgc_check_line"></i> Emitir factura
                        </button>
                    @endif

                    @if ($factura->puedeAnular())
                        <button wire:click="confirmarAnular"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-300 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700 transition hover:bg-red-100">
                            <i class="mgc_close_circle_line"></i> Anular
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- INFO GRID --}}
        <div class="grid grid-cols-1 gap-3 px-5 py-5 sm:grid-cols-2 lg:grid-cols-4 sm:px-6">
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Cliente</p>
                <p class="mt-1 text-sm font-semibold text-slate-900 truncate">{{ $factura->cliente->nombre ?? '—' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Obra</p>
                <p class="mt-1 text-sm font-semibold text-slate-900 truncate">{{ $factura->obra->nombre ?? '—' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Fecha emisión</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $factura->fecha_emision?->format('d/m/Y') ?? '—' }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Vencimiento</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">
                    {{ $factura->vencimiento?->format('d/m/Y') ?? '—' }}
                </p>
            </div>
        </div>

        {{-- SEGUIMIENTO DE COBRO (informativo, no fiscal) --}}
        @php $cobroMeta = $factura->estadoCobroMeta(); @endphp
        <div class="border-t border-slate-200 px-5 py-3 sm:px-6 flex flex-wrap items-center gap-2">
            <span class="text-xs font-medium text-slate-500">Seguimiento de cobro</span>
            <span class="text-[11px] text-slate-400">(clasificación interna, no afecta al estado fiscal)</span>
            <div class="flex-1"></div>

            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $cobroMeta['color'] }}">
                {{ $cobroMeta['label'] }}
            </span>

            @if ($factura->puedeGestionarEstadoCobro())
                <select x-data x-on:change="$wire.cambiarEstadoCobro($event.target.value)"
                    class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/30">
                    @foreach (\App\Support\EstadoCobro::opciones() as $op)
                        <option value="{{ $op['value'] }}" @selected($factura->estado_cobro === $op['value'])>{{ $op['label'] }}</option>
                    @endforeach
                </select>
            @else
                <span class="text-[11px] text-slate-400">no editable en «{{ $meta['label'] }}»</span>
            @endif

            @if ($factura->estado_cobro_actualizado_at)
                <span class="w-full text-[11px] text-slate-400 sm:w-auto">
                    · actualizado {{ $factura->estado_cobro_actualizado_at->format('d/m/Y H:i') }}
                    @if ($factura->estadoCobroActualizadoPor) por {{ $factura->estadoCobroActualizadoPor->name }} @endif
                </span>
            @endif
        </div>

        {{-- TRAZABILIDAD DOCUMENTAL (VeriFactu) --}}
        @if ($factura->estado !== 'borrador' && ($factura->pdf_original_generado_at || $factura->reimpresiones->isNotEmpty()))
            <div class="border-t border-slate-200 px-5 py-3 sm:px-6 text-xs text-slate-500 flex flex-wrap items-center gap-x-4 gap-y-1">
                @if ($factura->pdf_original_generado_at)
                    <span class="inline-flex items-center gap-1.5">
                        <i class="mgc_certificate_line text-slate-400"></i>
                        Original emitido el {{ $factura->pdf_original_generado_at->format('d/m/Y H:i') }}
                    </span>
                @endif
                @if ($factura->reimpresiones->isNotEmpty())
                    @php $ultima = $factura->reimpresiones->first(); @endphp
                    <span class="inline-flex items-center gap-1.5">
                        <i class="mgc_print_line text-slate-400"></i>
                        {{ $factura->reimpresiones->count() }}
                        {{ $factura->reimpresiones->count() === 1 ? 'copia generada' : 'copias generadas' }}
                        · última {{ $ultima->created_at->format('d/m/Y H:i') }}
                        @if ($ultima->user) por {{ $ultima->user->name }} @endif
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- ANULADA ALERT --}}
    @if ($factura->estado === 'anulada')
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 mb-4">
            <div class="flex items-start gap-3">
                <i class="mgc_warning_line text-red-600 text-xl"></i>
                <div>
                    <p class="font-semibold text-red-800">Factura anulada</p>
                    @if ($factura->motivo_anulacion)
                        <p class="text-sm text-red-700 mt-1">Motivo: {{ $factura->motivo_anulacion }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- TABLA LÍNEAS --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h3 class="text-sm font-semibold text-slate-800">Líneas de factura</h3>
            <p class="text-xs text-slate-500">Detalle de conceptos facturados.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50/80 text-slate-600">
                    <tr class="border-b border-slate-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Concepto</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide sm:px-5">Ud</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">Cantidad</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">Precio</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">Importe</th>
                        @if ($editable)
                            <th class="w-24 px-4 py-3 sm:px-5"></th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($factura->detalles as $linea)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 sm:px-5 text-slate-800">{{ $linea->concepto }}</td>
                            <td class="px-4 py-3 sm:px-5 text-center text-slate-600">{{ $linea->unidad ?: '—' }}</td>
                            <td class="px-4 py-3 sm:px-5 text-right text-slate-700">
                                {{ number_format($linea->cantidad, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 sm:px-5 text-right text-slate-700">
                                {{ number_format($linea->precio_unitario, 2, ',', '.') }} €
                            </td>
                            <td class="px-4 py-3 sm:px-5 text-right font-semibold text-slate-900">
                                {{ number_format($linea->importe_linea, 2, ',', '.') }} €
                            </td>
                            @if ($editable)
                                <td class="px-4 py-3 sm:px-5 text-right">
                                    <div class="flex justify-end gap-1">
                                        <button wire:click="abrirLineaEditar({{ $linea->id }})"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50">
                                            <i class="mgc_edit_2_line"></i>
                                        </button>
                                        <button wire:click="confirmarEliminarLinea({{ $linea->id }})"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 hover:border-red-300 hover:bg-red-50">
                                            <i class="mgc_delete_line"></i>
                                        </button>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $editable ? 6 : 5 }}" class="px-4 py-10 text-center text-sm text-slate-500 sm:px-5">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="mgc_inbox_line text-2xl text-slate-400"></i>
                                    <p>Sin líneas. Añade la primera para poder emitir la factura.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TOTALES --}}
        <div class="border-t border-slate-200 px-5 py-4 sm:px-6 bg-slate-50/50">
            <div class="ml-auto w-full sm:w-96 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-600">Base imponible</span>
                    <span class="font-semibold text-slate-900">{{ number_format($factura->base_imponible, 2, ',', '.') }} €</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">IVA ({{ number_format($factura->iva_porcentaje, 2, ',', '.') }}%)</span>
                    <span class="font-semibold text-slate-900">{{ number_format($factura->iva_importe, 2, ',', '.') }} €</span>
                </div>
                @if ($factura->retencion_porcentaje > 0)
                    <div class="flex justify-between">
                        <span class="text-slate-600">Retención ({{ number_format($factura->retencion_porcentaje, 2, ',', '.') }}%)</span>
                        <span class="font-semibold text-red-600">-{{ number_format($factura->retencion_importe, 2, ',', '.') }} €</span>
                    </div>
                @endif
                <div class="flex justify-between rounded-xl bg-gradient-to-r from-slate-900 to-slate-800 px-4 py-3 text-white">
                    <span class="font-semibold">TOTAL</span>
                    <span class="text-lg font-bold text-cyan-300">{{ number_format($factura->total, 2, ',', '.') }} €</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================
         DOCUMENTACIÓN ADJUNTA
         (soporte documental del expediente; no es el documento fiscal)
         ======================================== --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm mb-4">
        <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4 sm:px-6">
            <div>
                <h3 class="text-sm font-semibold text-slate-800">Documentación adjunta</h3>
                <p class="text-xs text-slate-500">
                    Albaranes, partes, justificantes, fotos… documentación del expediente.
                    No forma parte del documento fiscal ni del PDF original.
                </p>
            </div>
            <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                <i class="mgc_folder_open_line"></i> {{ $factura->documentos->count() }}
            </span>
        </div>

        {{-- Subida --}}
        <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
            <label class="block">
                <span class="text-xs font-medium text-slate-600">Añadir archivos</span>
                <input type="file" multiple wire:model="nuevosDocumentos"
                    class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-cyan-700 hover:file:bg-cyan-100">
            </label>
            <p class="mt-1 text-[11px] text-slate-400">PDF, imágenes, Office, TXT, CSV o ZIP. Máx. 20 MB por archivo.</p>

            @error('nuevosDocumentos.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            @error('nuevosDocumentos') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            <div wire:loading wire:target="nuevosDocumentos" class="mt-2 text-xs text-slate-500">Cargando archivos…</div>

            @if (!empty($nuevosDocumentos))
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @foreach ($nuevosDocumentos as $tmp)
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-600">
                            {{ \Illuminate\Support\Str::limit(method_exists($tmp, 'getClientOriginalName') ? $tmp->getClientOriginalName() : 'archivo', 32) }}
                        </span>
                    @endforeach
                </div>
                <div class="mt-3 flex gap-2">
                    <button type="button" wire:click="subirDocumentos"
                        wire:loading.attr="disabled" wire:target="subirDocumentos"
                        class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-60">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
                        <span wire:loading.remove wire:target="subirDocumentos">Subir</span>
                        <span wire:loading wire:target="subirDocumentos">Subiendo…</span>
                    </button>
                    <button type="button" wire:click="$set('nuevosDocumentos', [])"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-600 transition hover:bg-slate-50">
                        Cancelar
                    </button>
                </div>
            @endif
        </div>

        {{-- Lista --}}
        <ul class="divide-y divide-slate-100">
            @forelse ($factura->documentos as $doc)
                <li class="flex items-center gap-3 px-5 py-3 sm:px-6">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[10px] font-bold text-slate-500">
                        {{ \Illuminate\Support\Str::limit($doc->extension(), 4, '') }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-800">{{ $doc->nombre_original }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $doc->tamanoLegible() }} · {{ $doc->created_at->format('d/m/Y H:i') }}
                            @if ($doc->user) · {{ $doc->user->name }} @endif
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-1">
                        <a href="{{ route('empresa.facturas-ventas.documentos.descargar', [$factura->id, $doc->id]) }}"
                            title="Descargar"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                        </a>
                        @if ($puedeEliminarDocumentos)
                            <button wire:click="confirmarEliminarDocumento({{ $doc->id }})" title="Eliminar"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                <i class="mgc_delete_line"></i>
                            </button>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm text-slate-500 sm:px-6">
                    <div class="flex flex-col items-center gap-2">
                        <i class="mgc_folder_open_line text-2xl text-slate-400"></i>
                        <p>Sin documentación adjunta.</p>
                    </div>
                </li>
            @endforelse
        </ul>
    </div>

    {{-- MODAL ELIMINAR DOCUMENTO --}}
    @if ($documentoAEliminarId)
        <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <i class="mgc_delete_line text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Eliminar documento</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Se eliminará el archivo adjunto. La factura y su PDF original no se ven afectados.
                    </p>
                </div>
                <div class="mt-4 flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button wire:click="cancelarEliminarDocumento"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button wire:click="eliminarDocumento"
                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================
         MODAL LÍNEA (nueva / editar)
         ======================================== --}}
    @if ($showLineaModal)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cerrarLineaModal()">
            <div class="w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-6 py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                {{ $modoEdicionLinea ? 'Editar' : 'Nueva' }}
                            </div>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900">
                                {{ $modoEdicionLinea ? 'Editar línea' : 'Nueva línea de factura' }}
                            </h3>
                        </div>
                        <button wire:click="cerrarLineaModal"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                            <i class="mgc_close_line text-lg"></i>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="guardarLinea" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-700">Concepto *</label>
                        <input type="text" wire:model="concepto" class="mt-1 form-input w-full rounded-xl">
                        @error('concepto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Unidad</label>
                            <input type="text" wire:model="unidad" placeholder="Ej: ud, m²" class="mt-1 form-input w-full rounded-xl">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Cantidad *</label>
                            <input type="number" step="0.01" min="0.01" wire:model="cantidad" class="mt-1 form-input w-full rounded-xl">
                            @error('cantidad') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Precio unit. *</label>
                            <input type="number" step="0.01" min="0" wire:model="precio_unitario" class="mt-1 form-input w-full rounded-xl">
                            @error('precio_unitario') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 flex items-center justify-between">
                        <span class="text-xs text-slate-500 uppercase tracking-wide">Importe línea</span>
                        <span class="font-bold text-slate-900">{{ number_format(($cantidad ?? 0) * ($precio_unitario ?? 0), 2, ',', '.') }} €</span>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-200">
                        <button type="button" wire:click="cerrarLineaModal"
                            class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow hover:from-cyan-500 hover:to-blue-500">
                            {{ $modoEdicionLinea ? 'Guardar cambios' : 'Añadir línea' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL ELIMINAR LÍNEA --}}
    @if ($detalleAEliminarId)
        <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600">
                        <i class="mgc_warning_line text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Eliminar línea</h3>
                    <p class="mt-2 text-sm text-slate-600">Se recalcularán los totales de la factura.</p>
                </div>
                <div class="mt-6 px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button wire:click="cancelarEliminarLinea"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button wire:click="eliminarLinea"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700">
                        Eliminar línea
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EMITIR --}}
    @if ($showEmitirModal)
        <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 text-emerald-600">
                        <i class="mgc_check_line text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Emitir factura</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Se consumirá el siguiente número de la serie <strong class="font-mono">{{ $factura->serie }}</strong>,
                        se generará el PDF y la factura ya no será editable.
                    </p>
                </div>
                <div class="mt-6 px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button wire:click="cancelarEmitir"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button wire:click="emitirFactura" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-emerald-600 to-green-600 text-white shadow hover:from-emerald-500 hover:to-green-500 disabled:opacity-60">
                        <span wire:loading.remove wire:target="emitirFactura">Emitir ahora</span>
                        <span wire:loading wire:target="emitirFactura">Emitiendo…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL ANULAR --}}
    @if ($showAnularModal)
        <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600">
                        <i class="mgc_close_circle_line text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Anular factura</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Indica el motivo de anulación (mínimo 5 caracteres).
                    </p>
                    @if ($factura->origen === 'certificacion')
                        <div class="mt-3 flex items-start gap-2 text-left rounded-xl bg-amber-50 border border-amber-200 px-3 py-2">
                            <i class="mgc_information_line text-amber-600 mt-0.5"></i>
                            <p class="text-xs text-amber-800">
                                Al anular esta factura, sus certificaciones vinculadas
                                volverán a quedar <strong>disponibles</strong> (aceptadas y
                                pendientes de factura), listas para editar o volver a facturar.
                            </p>
                        </div>
                    @endif
                </div>
                <div class="px-6 pt-4 pb-2">
                    <textarea wire:model="motivoAnulacion" rows="3"
                        placeholder="Ej: error en el importe facturado"
                        class="w-full form-textarea rounded-xl border-slate-300"></textarea>
                    @error('motivoAnulacion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mt-4 px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button wire:click="cerrarAnularModal"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button wire:click="anularFactura"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700">
                        Anular factura
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
