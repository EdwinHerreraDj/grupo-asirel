<div>

    {{-- Botón regresar --}}

    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">

        {{-- Izquierda: navegación --}}
        <div class="flex items-center gap-2">
            <x-btns.regresar href="{{ route('obras.gastos', $obra->id) }}">
                Regresar
            </x-btns.regresar>
        </div>

        {{-- Derecha: acciones --}}
        <div class="flex items-center gap-2">

            <x-btns.agregar wire:click="abrirFormulario">
                Agregar factura
            </x-btns.agregar>

            <button wire:click="abrirModalInforme"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl
                   bg-primary text-white font-semibold
                   hover:bg-primary/90 shadow-sm transition">
                <i class="mgc_file_download_line text-lg"></i>
                Generar informe
            </button>


        </div>
    </div>

    {{-- ================= FILTROS ================= --}}
    <x-tablas.filters>
        <x-slot name="title">Filtros de búsqueda</x-slot>
        <div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                <input type="text" wire:model.defer="search" placeholder="Buscar..."
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">

                <select wire:model.defer="filtroProveedor"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    <option value="">Proveedor</option>
                    @foreach ($proveedores as $prov)
                        <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                    @endforeach
                </select>

                <select wire:model.defer="filtroOficio"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    <option value="">Oficio</option>
                    @foreach ($oficios as $oficio)
                        <option value="{{ $oficio->id }}">{{ $oficio->nombre }}</option>
                    @endforeach
                </select>

                <select wire:model.defer="filtroEstado"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    <option value="">Estado</option>
                    <option value="pendiente_emision_doc_pago">Pend. emisión</option>
                    <option value="pendiente_vencimiento">Pend. venc.</option>
                    <option value="pagada">Pagada</option>
                    <option value="devuelta">Devuelta</option>
                    <option value="impagada">Impagada</option>
                </select>

                <select wire:model.defer="filtroTipoCoste"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    <option value="">Tipo coste</option>
                    <option value="material">Material</option>
                    <option value="mano_obra">Mano de obra</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <button wire:click="limpiarFiltros" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">
                    Limpiar
                </button>

                <button wire:click="aplicarFiltros"
                    class="px-4 py-2 rounded-lg bg-primary text-white hover:bg-primary/90">
                    Filtrar
                </button>
            </div>
        </div>
    </x-tablas.filters>

    {{-- ================= TABLA ================= --}}



    <x-tablas.table class="min-w-[1600px]">
        <x-slot name="columns">
            <th class="w-[40px] px-2 py-2 text-center">#</th>
            <th class="w-[160px] px-2 py-2">Proveedor</th>
            <th class="w-[140px] px-2 py-2">Oficio</th>
            <th class="w-[90px] px-2 py-2">Tipo</th>
            <th class="w-[260px] px-2 py-2">Concepto</th>
            <th class="w-[110px] px-2 py-2 text-right">Base</th>
            <th class="w-[90px] px-2 py-2 text-right">IVA</th>
            <th class="w-[90px] px-2 py-2 text-right">IRPF</th>
            <th class="w-[120px] px-2 py-2 text-right">Total</th>
            <th class="w-[120px] px-2 py-2">Factura</th>
            <th class="w-[110px] px-2 py-2">Fecha</th>
            <th class="w-[160px] px-2 py-2">Estado</th>
            <th class="w-[90px] px-2 py-2 text-center">Acción</th>
        </x-slot>

        <x-slot name="rows">
            @forelse ($facturas as $factura)
                <tr class="hover:bg-gray-50 align-top">

                    <td class="px-2 py-2 text-center whitespace-nowrap">
                        {{ $loop->iteration }}
                    </td>

                    <td class="px-2 py-2 break-words whitespace-normal">
                        {{ $factura->proveedor->nombre ?? '—' }}
                    </td>

                    <td class="px-2 py-2 break-words whitespace-normal">
                        {{ $factura->oficio->nombre ?? '—' }}
                    </td>

                    <td class="px-2 py-2 whitespace-nowrap">
                        <span
                            class="px-2 py-1 rounded-full text-xs font-semibold
                        {{ $factura->tipo_coste === 'material' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ ucfirst($factura->tipo_coste) }}
                        </span>
                    </td>

                    <td class="px-2 py-2 break-words whitespace-normal">
                        {{ $factura->concepto ?: '—' }}
                    </td>

                    <td class="px-2 py-2 text-right whitespace-nowrap">
                        {{ number_format($factura->base_imponible, 2, ',', '.') }} €
                    </td>

                    <td class="px-2 py-2 text-right whitespace-nowrap">
                        {{ number_format($factura->iva_importe, 2, ',', '.') }} €
                    </td>

                    <td class="px-2 py-2 text-right whitespace-nowrap">
                        {{ number_format($factura->retencion_importe, 2, ',', '.') }} €
                    </td>

                    <td class="px-2 py-2 text-right font-semibold whitespace-nowrap">
                        {{ number_format($factura->total, 2, ',', '.') }} €
                    </td>

                    <td class="px-2 py-2 whitespace-nowrap">
                        @if ($factura->adjunto)
                            <div class="flex gap-2">
                                <x-btns.descargar href="{{ asset('storage/' . $factura->adjunto) }}" />
                                <x-btns.ver pdf="{{ asset('storage/' . $factura->adjunto) }}" />
                            </div>
                        @else
                            —
                        @endif
                    </td>

                    <td class="px-2 py-2 whitespace-nowrap">
                        {{ $factura->fecha_factura?->format('d/m/Y') ?? '—' }}
                    </td>

                    <td class="px-2 py-2">
                        <select wire:change="actualizarEstado({{ $factura->id }}, $event.target.value)"
                            class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="pendiente_emision_doc_pago" @selected($factura->estado === 'pendiente_emision_doc_pago')>🟡 Pend. emisión
                            </option>
                            <option value="pendiente_vencimiento" @selected($factura->estado === 'pendiente_vencimiento')>🔵 Pend. venc.</option>
                            <option value="pagada" @selected($factura->estado === 'pagada')>🟢 Pagada</option>
                            <option value="devuelta" @selected($factura->estado === 'devuelta')>🟣 Devuelta</option>
                            <option value="impagada" @selected($factura->estado === 'impagada')>🔴 Impagada</option>
                        </select>
                    </td>

                    <td class="px-2 py-2 text-center whitespace-nowrap">
                        <x-btns.editar wire:click="editarFactura({{ $factura->id }})" />
                        <x-btns.eliminar wire:click="confirmarEliminar({{ $factura->id }})" />
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="13" class="px-6 py-8 text-center text-gray-500">
                        No se encontraron facturas recibidas.
                    </td>
                </tr>
            @endforelse
        </x-slot>

        <x-slot name="pagination">
            <div class="p-4 border-t border-gray-200">
                {{ $facturas->links() }}
            </div>
        </x-slot>
    </x-tablas.table>



    {{-- MODAL CREAR FACTURA --}}
    <div x-data="{ open: @entangle('showForm') }" x-show="open" x-cloak
        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">

        <div x-transition
            class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl border border-gray-200 
               max-h-[95vh] flex flex-col overflow-hidden">

            <!-- HEADER (fijo) -->
            <div class="flex items-center gap-3 p-5 border-b border-gray-200 bg-white sticky top-0 z-10">
                <div class="bg-primary/10 text-primary w-10 h-10 flex items-center justify-center rounded-xl text-2xl">
                    <i class="mgc_file_check_line"></i>
                </div>

                <h3 class="text-xl font-semibold text-gray-800">
                    {{ $modoEdicion ? 'Editar factura recibida' : 'Registrar factura recibida' }}
                </h3>


                <!-- Botón cerrar -->
                <button @click="open = false" class="ml-auto text-gray-500 hover:text-red-600 transition">
                    <i class="mgc_close_line text-2xl"></i>
                </button>
            </div>

            <!-- CONTENIDO SCROLLEABLE -->
            <div class="p-5 space-y-5 overflow-y-auto">
                <form wire:submit.prevent="guardar" enctype="multipart/form-data" class="space-y-5">

                    <!-- Proveedor -->
                    <div>
                        <label class="block font-medium mb-1">Proveedor</label>
                        <select wire:model="proveedor_id"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">-- Seleccionar proveedor --</option>
                            @foreach ($proveedores as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                            @endforeach
                        </select>
                        @error('proveedor_id')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Oficio -->
                    <div>
                        <label class="block font-medium mb-1">Oficio</label>
                        <select wire:model="oficio_id"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">-- Seleccionar oficio --</option>
                            @foreach ($oficios as $oficio)
                                <option value="{{ $oficio->id }}">{{ $oficio->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tipo coste -->
                    <div>
                        <label class="block font-medium mb-1">Tipo de coste</label>
                        <select wire:model="tipo_coste"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="material">Material</option>
                            <option value="mano_obra">Mano de obra</option>
                        </select>
                    </div>

                    <!-- Concepto -->
                    <div>
                        <label class="block font-medium mb-1">Concepto</label>
                        <input type="text" wire:model="concepto"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            placeholder="Pladur, reparación muro...">
                    </div>

                    <!-- Número factura -->
                    <div>
                        <label class="block font-medium mb-1">Número factura</label>
                        <input type="text" wire:model="numero_factura"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            placeholder="0001/2024">
                    </div>

                    <!-- Importe -->
                    <!-- BASE IMPONIBLE -->
                    <div>
                        <label class="block font-medium mb-1">Base imponible (€)</label>
                        <input type="number" step="0.01" wire:model.live="base_imponible"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            placeholder="0.00">
                    </div>

                    <!-- IMPUESTOS -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- IVA -->
                        <div>
                            <label class="block font-medium mb-1">IVA (%)</label>
                            <input type="number" step="0.01" wire:model.live="iva_porcentaje"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        </div>

                        <!-- IRPF -->
                        <div>
                            <label class="block font-medium mb-1">IRPF / Retención (%)</label>
                            <input type="number" step="0.01" wire:model.live="retencion_porcentaje"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        </div>

                    </div>

                    <!-- RESUMEN CALCULADO -->
                    @php
                        $base = (float) ($base_imponible ?? 0);
                        $iva = (float) ($iva_porcentaje ?? 0);
                        $ret = (float) ($retencion_porcentaje ?? 0);

                        $ivaImporte = ($base * $iva) / 100;
                        $retImporte = ($base * $ret) / 100;
                        $total = $base + $ivaImporte - $retImporte;
                    @endphp

                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2 text-sm">

                        <div class="flex justify-between">
                            <span class="text-gray-600">Base imponible</span>
                            <span class="font-medium">
                                {{ number_format($base, 2, ',', '.') }} €
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">IVA</span>
                            <span class="font-medium text-green-700">
                                + {{ number_format($ivaImporte, 2, ',', '.') }} €
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-gray-600">IRPF / Retención</span>
                            <span class="font-medium text-red-700">
                                - {{ number_format($retImporte, 2, ',', '.') }} €
                            </span>
                        </div>

                        <div class="flex justify-between border-t pt-2 text-base font-semibold">
                            <span>Total</span>
                            <span class="text-primary">
                                {{ number_format($total, 2, ',', '.') }} €
                            </span>
                        </div>

                    </div>



                    <!-- Fechas -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-medium mb-1">Fecha factura</label>
                            <input type="date" wire:model="fecha_factura"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div>
                            <label class="block font-medium mb-1">Fecha contable</label>
                            <input type="date" wire:model="fecha_contable"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        </div>

                        <div>
                            <label class="block font-medium mb-1">Vencimiento</label>
                            <input type="date" wire:model="vencimiento"
                                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        </div>
                    </div>

                    <!-- Tipo pago -->
                    <div>
                        <label class="block font-medium mb-1">Tipo de pago</label>
                        <select wire:model="tipo_pago"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="">-- Seleccionar --</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="pronto_pago">Pronto pago</option>
                            <option value="confirming">Confirming</option>
                            <option value="pagare">Pagaré</option>
                            <option value="contado">Contado</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block font-medium mb-1">Estado</label>
                        <select wire:model="estado"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                            <option value="pendiente_emision_doc_pago">Pendiente emisión doc. pago</option>
                            <option value="pendiente_vencimiento">Pendiente vencimiento</option>
                            <option value="pagada">Pagada</option>
                            <option value="devuelta">Devuelta</option>
                            <option value="impagada">Impagada</option>
                        </select>
                    </div>

                    <!-- Adjunto -->
                    <div>
                        <label class="block font-medium mb-2">Adjuntar factura</label>

                        <div class="flex items-center gap-3">

                            <!-- Botón -->
                            <label
                                class="px-4 py-2 rounded-xl bg-primary text-white font-medium cursor-pointer
                   hover:bg-primary/90 transition
                   disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="adjunto">
                                Seleccionar archivo
                                <input type="file" wire:model="adjunto" class="hidden" />
                            </label>

                            <!-- Estado -->
                            <div class="text-sm flex items-center gap-2">

                                {{-- CARGANDO --}}
                                <div wire:loading wire:target="adjunto" class="flex items-center gap-2 text-blue-600">
                                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" fill="none" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                    </svg>
                                    <span>Subiendo archivo…</span>
                                </div>

                                {{-- ARCHIVO CARGADO --}}
                                <div wire:loading.remove wire:target="adjunto">
                                    @if ($adjunto)
                                        <span class="inline-flex items-center gap-2 text-green-600 font-medium">
                                            <i class="mgc_check_line text-lg"></i>
                                            <span class="truncate max-w-[200px]">
                                                {{ $adjunto->getClientOriginalName() }}
                                            </span>
                                        </span>
                                    @else
                                        <span class="text-gray-500 italic">
                                            Ningún archivo seleccionado
                                        </span>
                                    @endif
                                </div>

                            </div>

                        </div>

                        @error('adjunto')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>




                </form>
            </div>

            <!-- FOOTER (fijo) -->
            <div class="p-5 border-t border-gray-200 bg-white sticky bottom-0 z-10 flex justify-end gap-3">
                <button @click="open = false"
                    class="px-4 py-2.5 rounded-xl bg-gray-100 text-gray-700 border border-gray-300 hover:bg-gray-200 transition">
                    Cancelar
                </button>

                <button wire:click="guardar"
                    class="px-5 py-2.5 rounded-xl bg-primary text-white font-semibold shadow-md hover:bg-primary/90 transition">
                    {{ $modoEdicion ? 'Actualizar factura' : 'Guardar factura' }}
                </button>

            </div>

        </div>

    </div>


    {{-- MODAL CONFIRMACIÓN ELIMINAR --}}
    @if ($confirmarEliminacion)
        <x-modals.confirmar titulo="Confirmar eliminación"
            mensaje="¿Seguro que deseas eliminar esta factura?<br>Esta acción no se puede deshacer."
            wire-close="wire:click=&quot;$set('showDeleteModal', false)&quot;">

            {{-- Botón CANCELAR --}}
            <x-btns.cancelar wire:click="$set('confirmarEliminacion', false)">
                Cancelar
            </x-btns.cancelar>


            {{-- Botón ELIMINAR --}}
            <x-btns.danger wire:click="eliminarFactura({{ $facturaAEliminar }})">
                Eliminar
            </x-btns.danger>

        </x-modals.confirmar>
    @endif


    {{-- Modal Informe --}}
    @include('obras.facturas.recibidas.modal.informe')

    {{-- Visor PDF --}}
    @include('components-vs.modals.visor-pdf')

    {{-- Modal Generar Informe PDF y Excel --}}
    @if ($showInformeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border overflow-hidden">

                {{-- HEADER --}}
                <div class="p-5 border-b flex items-center gap-3">
                    <div
                        class="bg-primary/10 text-primary w-10 h-10 flex items-center justify-center rounded-xl text-xl">
                        <i class="mgc_clipboard_line"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Generar informe de facturas</h3>
                    <button wire:click="cerrarModalInforme" class="ml-auto text-gray-400 hover:text-red-600">
                        <i class="mgc_close_line text-2xl"></i>
                    </button>
                </div>

                {{-- BODY --}}
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">

                    <select wire:model.defer="informeProveedor" class="rounded-xl border-gray-300">
                        <option value="">Proveedor</option>
                        @foreach ($proveedores as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>

                    <select wire:model.defer="informeOficio" class="rounded-xl border-gray-300">
                        <option value="">Oficio</option>
                        @foreach ($oficios as $of)
                            <option value="{{ $of->id }}">{{ $of->nombre }}</option>
                        @endforeach
                    </select>

                    <select wire:model.defer="informeEstado" class="rounded-xl border-gray-300">
                        <option value="">Estado</option>
                        <option value="pendiente_emision_doc_pago">Pend. emisión</option>
                        <option value="pendiente_vencimiento">Pend. vencimiento</option>
                        <option value="pagada">Pagada</option>
                        <option value="devuelta">Devuelta</option>
                        <option value="impagada">Impagada</option>
                    </select>

                    <select wire:model.defer="informeTipoCoste" class="rounded-xl border-gray-300">
                        <option value="">Tipo coste</option>
                        <option value="material">Material</option>
                        <option value="mano_obra">Mano de obra</option>
                    </select>

                    <input type="date" wire:model.defer="informeFechaDesde" class="rounded-xl border-gray-300">
                    <input type="date" wire:model.defer="informeFechaHasta" class="rounded-xl border-gray-300">
                </div>

                {{-- FOOTER --}}
                <div class="p-5 border-t flex justify-end gap-3">
                    <button wire:click="cerrarModalInforme"
                        class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button wire:click="exportarExcel"
                        class="px-5 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700">
                        Generar Excel
                    </button>


                    <button wire:click="generarInformePDF"
                        class="px-5 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700">
                        Generar PDF
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
