<div>
    {{-- CABECERA --}}
    <div class="flex flex-wrap items-center gap-3 mb-7">

        <x-btns.regresar href="{{ route('empresa.facturas-ventas') }}">
            Regresar
        </x-btns.regresar>

        @if ($editable)
            <x-btns.agregar wire:click="abrirModalCrear">
                Agregar línea
            </x-btns.agregar>
        @endif

        {{-- Acciones a la derecha --}}
        <div class="ml-auto flex items-center gap-3">

            @if ($factura->puedeAnular())
                <button wire:click="confirmarAnular"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                       bg-red-600 text-white text-sm font-semibold
                       hover:bg-red-700 transition shadow-sm">
                    <i class="mgc_close_circle_line text-lg"></i>
                    Anular factura
                </button>
            @endif

            <span
                class="px-3 py-1 rounded-xl text-xs font-semibold
            {{ $factura->estado === 'borrador'
                ? 'bg-yellow-100 text-yellow-700 border border-yellow-200'
                : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                {{ strtoupper($factura->estado) }}
            </span>

        </div>

    </div>


    @if ($factura->estado === 'anulada')
        <div class="mb-6 rounded-xl border border-red-300 bg-red-50 p-4">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600 text-xl">
                    <i class="mgc_close_circle_line"></i>
                </div>

                <div>
                    <p class="font-semibold text-red-800">
                        Factura anulada
                    </p>

                    <p class="text-sm text-red-700 mt-2">
                        Esta factura está anulada y no admite ninguna acción.
                    </p>


                    <p class="text-sm text-red-700 mt-1">
                        Motivo: {{ $factura->motivo_anulacion }}
                    </p>
                </div>
            </div>
        </div>
    @endif


    @if (!$editable)
        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 text-xl">
                    <i class="mgc_lock_line"></i>
                </div>

                <div>
                    <p class="font-semibold text-blue-800">
                        Factura emitida
                    </p>
                    <p class="text-sm text-blue-700">
                        Esta factura ya ha sido emitida y no se puede modificar.
                        Las líneas, importes y datos fiscales están bloqueados.
                    </p>
                </div>
            </div>
        </div>
    @endif


    @if ($factura->origen === 'certificacion')
        <div class="mt-6 mb-6 rounded-2xl border border-gray-200 bg-gray-50 p-5">

            {{-- Header --}}
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <i class="mgc_link_2_line text-xl"></i>
                </div>

                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-semibold text-gray-800">
                        Origen de la factura
                    </h4>
                    <p class="text-xs text-gray-500">
                        Esta factura fue generada desde certificaciones.
                    </p>
                </div>

                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                    Certificación
                </span>
            </div>

            {{-- Info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">

                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs text-gray-500">Certificación</p>
                    <p class="font-semibold text-gray-900">
                        {{ $factura->codigo_certificacion ?? '—' }}
                    </p>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <p class="text-xs text-gray-500">Obra</p>
                    <p class="font-semibold text-gray-900 truncate">
                        {{ $factura->obra->nombre ?? '—' }}
                    </p>
                </div>

            </div>

            {{-- Acción --}}
            <div class="mt-4">
                <a href="{{ route('obras.certificaciones', $factura->obra_id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                       bg-white border border-gray-200 text-gray-700
                       hover:bg-gray-100 hover:border-gray-300 transition
                       text-sm font-semibold shadow-sm">
                    <i class="mgc_arrow_left_line text-lg"></i>
                    Ver certificaciones de la obra
                </a>
            </div>

        </div>
    @endif



    {{-- BLOQUE FACTURA --}}
    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">

            <div>
                <p class="text-gray-500">Factura</p>
                <p class="font-semibold">
                    {{ $factura->serie }}-{{ $factura->numero_factura ?? 'BORRADOR' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">Cliente</p>
                <p class="font-semibold">
                    {{ $factura->cliente->nombre ?? '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">Fecha emisión</p>
                <p class="font-semibold">
                    {{ $factura->fecha_emision ? \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') : '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">Vencimiento</p>
                <p class="font-semibold">
                    {{ $factura->vencimiento ? \Carbon\Carbon::parse($factura->vencimiento)->format('d/m/Y') : '—' }}
                </p>
            </div>

        </div>
    </div>

    @if ($editable && $factura->detalles->count() > 0)
        <button wire:click="confirmarEmitir"
            class="inline-flex items-center gap-2 px-4 py-2.5
               rounded-xl text-xs font-semibold
               bg-primary text-white
               hover:bg-primary/90
               focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1
               transition shadow-sm">

            <i class="mgc_file_check_line text-base"></i>
            Emitir factura
        </button>
    @endif


    {{-- TABLA DE LÍNEAS --}}
    <x-tablas.table>

        <x-slot name="columns">
            <th class="px-4 py-2 text-left">Concepto</th>
            <th class="px-4 py-2 w-24 text-left">Unidad</th>
            <th class="px-4 py-2 w-28 text-right">Cantidad</th>
            <th class="px-4 py-2 w-32 text-right">Precio</th>
            <th class="px-4 py-2 w-32 text-right">Importe</th>

            @if ($editable)
                <th class="px-4 py-2 w-24 text-center">Acciones</th>
            @endif
        </x-slot>

        <x-slot name="rows">
            @forelse ($factura->detalles as $detalle)
                <tr class="border-b hover:bg-gray-50 transition">

                    <td class="px-4 py-2">
                        {{ $detalle->concepto }}
                    </td>

                    <td class="px-4 py-2">
                        {{ $detalle->unidad ?? '—' }}
                    </td>

                    <td class="px-4 py-2 text-right">
                        {{ number_format($detalle->cantidad, 2, ',', '.') }}
                    </td>

                    <td class="px-4 py-2 text-right">
                        {{ number_format($detalle->precio_unitario, 2, ',', '.') }} €
                    </td>

                    <td class="px-4 py-2 text-right font-semibold">
                        {{ number_format($detalle->importe_linea, 2, ',', '.') }} €
                    </td>

                    @if ($editable)
                        <td class="px-4 py-2">
                            <div class="flex justify-center gap-2">
                                <button wire:click="abrirModalEditar({{ $detalle->id }})"
                                    class="text-yellow-600 hover:text-yellow-800">
                                    <i class="mgc_edit_2_line text-lg"></i>
                                </button>

                                <button wire:click="confirmarEliminar({{ $detalle->id }})"
                                    class="text-red-600 hover:text-red-800">
                                    <i class="mgc_delete_line text-lg"></i>
                                </button>
                            </div>
                        </td>
                    @endif

                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">
                        No hay líneas en esta factura.
                    </td>
                </tr>
            @endforelse
        </x-slot>

    </x-tablas.table>

    {{-- TOTALES --}}
    <div class="bg-gray-50 border rounded-xl p-5 grid grid-cols-1 md:grid-cols-4 gap-4 mt-6 shadow">

        <div>
            <p class="text-xs text-gray-500">Base imponible</p>
            <p class="text-lg font-semibold">
                {{ number_format($factura->base_imponible, 2, ',', '.') }} €
            </p>
        </div>

        <div>
            <p class="text-xs text-gray-500">IVA ({{ $factura->iva_porcentaje }}%)</p>
            <p class="text-lg font-semibold text-blue-700">
                {{ number_format($factura->iva_importe, 2, ',', '.') }} €
            </p>
        </div>

        @if ($factura->retencion_porcentaje > 0)
            <div>
                <p class="text-xs text-gray-500">Retención ({{ $factura->retencion_porcentaje }}%)</p>
                <p class="text-lg font-semibold text-red-600">
                    -{{ number_format($factura->retencion_importe, 2, ',', '.') }} €
                </p>
            </div>
        @endif

        <div class="border-l pl-4">
            <p class="text-xs text-gray-500">TOTAL</p>
            <p class="text-2xl font-bold text-primary">
                {{ number_format($factura->total, 2, ',', '.') }} €
            </p>
        </div>

    </div>

    @if ($showModal)
        <div wire:ignore.self
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

            <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl border overflow-hidden">

                {{-- CABECERA --}}
                <div class="flex items-center gap-3 p-5 border-b">
                    <div class="bg-primary/10 text-primary w-10 h-10 flex items-center justify-center rounded-xl">
                        <i class="mgc_calendar_month_line text-xl"></i>
                    </div>

                    <h3 class="text-lg font-semibold">
                        {{ $modoEdicion ? 'Editar línea de factura' : 'Añadir línea a factura' }}
                    </h3>

                    <button wire:click="cerrarModal"
                        class="ml-auto text-gray-500 hover:text-red-600 text-2xl leading-none">
                        &times;
                    </button>
                </div>

                {{-- CONTENIDO --}}
                <div class="p-6 space-y-5">

                    {{-- CONCEPTO --}}
                    <div>
                        <label class="block font-medium mb-1">Concepto *</label>
                        <input type="text" wire:model.defer="concepto"
                            class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                            placeholder="Descripción del producto o servicio">
                        @error('concepto')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- UNIDAD --}}
                    <div>
                        <label class="block font-medium mb-1">Unidad de medida</label>
                        <input type="text" wire:model.defer="unidad"
                            class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                            placeholder="Ejemplo: unidad, hora, metro, kg...">
                    </div>

                    {{-- CANTIDAD / PRECIO --}}
                    <div class="grid grid-cols-2 gap-4">

                        {{-- CANTIDAD --}}
                        <div>
                            <label class="block font-medium mb-1">Cantidad *</label>
                            <input type="text" inputmode="decimal" wire:model.lazy="cantidad"
                                class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary">
                            @error('cantidad')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PRECIO --}}
                        <div>
                            <label class="block font-medium mb-1">Precio unitario (€) *</label>
                            <input type="text" inputmode="decimal" wire:model.lazy="precio_unitario"
                                class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary">
                            @error('precio_unitario')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- IMPORTE --}}
                    <div class="text-right text-lg font-semibold border-t pt-3">
                        Importe:
                        <span class="text-primary">
                            {{ number_format(
                                ((float) str_replace(',', '.', $cantidad ?? 0)) * ((float) str_replace(',', '.', $precio_unitario ?? 0)),
                                2,
                                ',',
                                '.',
                            ) }}
                            €
                        </span>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="flex justify-end gap-3 p-5 border-t bg-gray-50">
                    <button wire:click="cerrarModal" class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300">
                        Cancelar
                    </button>

                    <button wire:click="guardarDetalle"
                        class="px-5 py-2 rounded-xl bg-primary text-white font-semibold hover:bg-primary/90">
                        {{ $modoEdicion ? 'Actualizar línea' : 'Guardar línea' }}
                    </button>
                </div>

            </div>
        </div>
    @endif




    @if ($showDeleteModal)
        <x-modals.confirmar titulo="Eliminar línea"
            mensaje="¿Seguro que deseas eliminar esta línea de la factura?<br>Esta acción no se puede deshacer."
            wire-close="wire:click=&quot;$set('showDeleteModal', false)&quot;">
            <x-btns.cancelar wire:click="$set('showDeleteModal', false)">
                Cancelar
            </x-btns.cancelar>

            <x-btns.danger wire:click="eliminarDetalle">
                Eliminar
            </x-btns.danger>
        </x-modals.confirmar>
    @endif

    @if ($showEmitirModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

            <div
                class="bg-white w-full max-w-lg rounded-2xl shadow-xl
                   border border-gray-200 overflow-hidden">

                {{-- CABECERA --}}
                <div class="flex items-center gap-3 p-5 border-b">
                    <div
                        class="bg-primary/10 text-primary w-10 h-10 flex items-center justify-center rounded-xl text-xl">
                        <i class="mgc_file_check_line"></i>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800">
                        Emitir factura
                    </h3>

                    <button wire:click="$set('showEmitirModal', false)"
                        class="ml-auto text-gray-500 hover:text-red-600 text-2xl leading-none">
                        &times;
                    </button>
                </div>

                {{-- CONTENIDO --}}
                <div class="p-6 text-gray-700 space-y-4">
                    <p>Al emitir la factura:</p>

                    <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                        <li>Se asignará numeración fiscal</li>
                        <li>No podrás modificar las líneas</li>
                        <li>La factura quedará cerrada</li>
                    </ul>

                    <p class="font-medium pt-2">
                        ¿Deseas continuar?
                    </p>
                </div>

                {{-- FOOTER --}}
                <div class="flex justify-end gap-3 p-5 border-t bg-gray-50">

                    <button wire:click="$set('showEmitirModal', false)"
                        class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition">
                        Cancelar
                    </button>

                    <button wire:click="emitirFactura"
                        class="px-5 py-2 rounded-xl bg-primary text-white font-semibold hover:bg-primary/90 transition">
                        Emitir factura
                    </button>

                </div>

            </div>
        </div>
    @endif

    @if ($showPagoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border overflow-hidden">

                {{-- CABECERA --}}
                <div class="flex items-center gap-3 p-5 border-b">
                    <div class="bg-emerald-100 text-emerald-600 w-10 h-10 flex items-center justify-center rounded-xl">
                        <i class="mgc_currency_euro_2_line text-xl"></i>
                    </div>

                    <h3 class="text-lg font-semibold">Registrar pago</h3>

                    <button wire:click="cerrarModalPago"
                        class="ml-auto text-gray-500 hover:text-red-600 text-2xl">&times;</button>
                </div>

                {{-- CONTENIDO --}}
                <div class="p-6 space-y-4">

                    <div>
                        <label class="block text-sm font-medium mb-1">Fecha de pago *</label>
                        <input type="date" wire:model.defer="pago_fecha"
                            class="w-full rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('pago_fecha')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Importe * (máx {{ number_format($factura->pendientePago(), 2, ',', '.') }} €)
                        </label>
                        <input type="number" step="0.01" wire:model.defer="pago_importe"
                            class="w-full rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500">
                        @error('pago_importe')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Método *</label>
                        <input type="text" wire:model.defer="pago_metodo"
                            class="w-full rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Transferencia, efectivo, tarjeta…">
                        @error('pago_metodo')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipo de pago</label>
                        <select wire:model.defer="pago_tipo" class="w-full rounded-xl border-gray-300">
                            <option value="normal">Pago normal</option>
                            <option value="correccion">Corrección</option>
                        </select>
                    </div>



                    <div>
                        <label class="block text-sm font-medium mb-1">Observaciones</label>
                        <textarea wire:model.defer="pago_observaciones"
                            class="w-full rounded-xl border-gray-300 focus:ring-emerald-500 focus:border-emerald-500" rows="3"></textarea>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="flex justify-end gap-3 p-5 border-t bg-gray-50">
                    <button wire:click="cerrarModalPago" class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300">
                        Cancelar
                    </button>

                    <button wire:click="guardarPago"
                        class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700">
                        Guardar pago
                    </button>
                </div>

            </div>
        </div>
    @endif

    @if ($showAnularModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border overflow-hidden">

                {{-- CABECERA --}}
                <div class="flex items-center gap-3 p-5 border-b">
                    <div class="bg-red-100 text-red-600 w-10 h-10 flex items-center justify-center rounded-xl">
                        <i class="mgc_close_circle_line text-xl"></i>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800">
                        Anular factura
                    </h3>

                    <button wire:click="cerrarAnularModal"
                        class="ml-auto text-gray-500 hover:text-red-600 text-2xl leading-none">
                        &times;
                    </button>
                </div>

                {{-- CONTENIDO --}}
                <div class="p-6 space-y-4 text-gray-700">
                    <p class="text-sm">
                        Esta acción marcará la factura como <strong>anulada</strong>.
                        No se eliminará ni se podrá modificar posteriormente.
                    </p>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Motivo de la anulación <span class="text-red-600">*</span>
                        </label>

                        <textarea wire:model.defer="motivoAnulacion" rows="4"
                            class="w-full rounded-xl border-gray-300 focus:ring-red-500 focus:border-red-500"
                            placeholder="Explica brevemente el motivo de la anulación"></textarea>

                        @error('motivoAnulacion')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex justify-end gap-3 p-5 border-t bg-gray-50">
                    <button wire:click="cerrarAnularModal"
                        class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition">
                        Cancelar
                    </button>

                    <button wire:click="anularFactura"
                        class="px-5 py-2 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition">
                        Anular factura
                    </button>
                </div>

            </div>
        </div>
    @endif



    {{-- ======================
     COBROS / PAGOS
====================== --}}
    @if ($factura->estado !== 'borrador')
        <div class="mt-10 border-t pt-6">

            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                    Cobros
                </h3>

                {{-- Botón registrar pago --}}
                @if (in_array($factura->estado, ['emitida', 'enviada']) && $factura->pendientePago() > 0)
                    <button wire:click="abrirModalPago"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                           bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition">
                        <i class="mgc_currency_euro_2_line"></i>
                        Registrar pago
                    </button>
                @endif
            </div>

            {{-- Resumen estilo TOTALES --}}
            <div class="bg-gray-50 border rounded-xl p-5 grid grid-cols-1 md:grid-cols-4 gap-4 shadow">

                <div>
                    <p class="text-xs text-gray-500">Total factura</p>
                    <p class="text-lg font-semibold text-gray-800">
                        {{ number_format($factura->total, 2, ',', '.') }} €
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Total pagado</p>
                    <p class="text-lg font-semibold text-emerald-700">
                        {{ number_format($factura->totalPagado(), 2, ',', '.') }} €
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Pendiente</p>
                    <p class="text-lg font-semibold text-red-600">
                        {{ number_format($factura->pendientePago(), 2, ',', '.') }} €
                    </p>
                </div>

                <div class="border-l pl-4">
                    <p class="text-xs text-gray-500">ESTADO</p>

                    @php
                        $pendiente = $factura->pendientePago();
                    @endphp

                    <p class="text-2xl font-bold {{ $pendiente <= 0 ? 'text-emerald-600' : 'text-primary' }}">
                        {{ $pendiente <= 0 ? 'PAGADA' : 'PENDIENTE' }}
                    </p>
                </div>

            </div>

            {{-- Tabla de pagos (más integrada) --}}
            <div class="mt-6 bg-white border rounded-xl shadow overflow-hidden">
                <div class="px-5 py-3 border-b bg-gray-50">
                    <p class="text-sm font-semibold text-gray-700">
                        Pagos registrados
                    </p>
                </div>

                @if ($factura->pagos->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Fecha</th>
                                    <th class="px-4 py-3 text-left font-semibold">Método</th>

                                    <th class="px-4 py-3 text-left font-semibold">Tipo</th>
                                    <th class="px-4 py-3 text-left font-semibold">Observaciones</th>
                                    <th class="px-4 py-3 text-right font-semibold">Importe</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y">
                                @foreach ($factura->pagos as $pago)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ optional($pago->fecha_pago)->format('d/m/Y') ?? '—' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-2">
                                                <i class="mgc_wallet_3_line text-gray-400"></i>
                                                {{ $pago->metodo }}
                                            </span>
                                        </td>


                                        <td class="px-4 py-2">
                                            @if ($pago->tipo === 'normal')
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                                    Normal
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                                    Corrección
                                                </span>
                                            @endif
                                        </td>


                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $pago->observaciones ?? '—' }}
                                        </td>
                                        <td
                                            class="px-4 py-2 text-right font-semibold
                                            {{ $pago->importe < 0 ? 'text-red-600' : 'text-green-700' }}">
                                            {{ number_format($pago->importe, 2, ',', '.') }} €
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-5 py-4 text-sm text-gray-500">
                        No hay pagos registrados.
                    </div>
                @endif
            </div>

        </div>
    @endif







</div>
