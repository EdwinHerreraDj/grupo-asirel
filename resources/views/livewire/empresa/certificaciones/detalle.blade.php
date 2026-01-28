<div>
    <div class="flex flex-wrap items-center gap-3 mb-7">
        {{-- REGRESAR --}}
        @if ($this->certificacion)
            <x-btns.regresar href="{{ route('obras.certificaciones', $this->certificacion->obra_id) }}">
                Regresar
            </x-btns.regresar>
        @endif

        @if ($this->certificacion && $this->certificacion->estado_certificacion === 'pendiente')
            <x-btns.agregar wire:click="abrirModalCrear">
                Agregar detalle
            </x-btns.agregar>
        @endif


    </div>

    {{-- Notificacion de si esta a aceptada --}}
    @if ($certificacion->estado_certificacion === 'aceptada')
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="bg-green-100 text-green-600 w-10 h-10 flex items-center justify-center rounded-xl text-xl">
                    <i class="mgc_check_line"></i>
                </div>
                <p class="font-medium">
                    Esta certificación ha sido aceptada y ya no se pueden realizar modificaciones.
                </p>
            </div>
        </div>
    @endif

    <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4">
        Certificación
        <span class="ml-2 text-sm font-normal">
            #{{ $this->certificacion->numero_certificacion ?? $this->certificacion->id }}
        </span>
    </h2>

    {{-- BLOQUE INFORMACIÓN CERTIFICACIÓN --}}
    @if ($this->certificacion)
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">

                <div>
                    <p class="text-gray-500">Cliente</p>
                    <p class="font-semibold">
                        {{ $this->certificacion->cliente->nombre ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Oficio</p>
                    <p class="font-semibold">
                        {{ $this->certificacion->oficio->nombre ?? '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-gray-500">Fecha certificación</p>
                    <p class="font-semibold">
                        {{ $this->certificacion->fecha_ingreso
                            ? \Carbon\Carbon::parse($this->certificacion->fecha_ingreso)->format('d/m/Y')
                            : '—' }}
                    </p>
                </div>


                <div>
                    <p class="text-gray-500">Fecha contable</p>
                    <p class="font-semibold">
                        {{ $this->certificacion->fecha_contable
                            ? \Carbon\Carbon::parse($this->certificacion->fecha_contable)->format('d/m/Y')
                            : '—' }}
                    </p>
                </div>


                <div>
                    <p class="text-gray-500">Estado certificación</p>
                    <span
                        class="inline-block px-2 py-1 rounded text-xs font-semibold
                        {{ $this->certificacion->estado_certificacion === 'aceptada'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($this->certificacion->estado_certificacion) }}
                    </span>
                </div>

                <div>
                    <p class="text-gray-500">Estado factura</p>
                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-700">
                        {{ $this->certificacion->estado_factura ?? '—' }}
                    </span>
                </div>

            </div>
        </div>
    @endif


    @if ($certificacion->estado_certificacion === 'pendiente')
        <div class="flex items-center gap-3">

            {{-- Aceptar --}}
            <button wire:click="confirmarAceptar"
                class="inline-flex items-center gap-2 px-4 py-2.5
               rounded-xl text-xs font-semibold
               bg-green-600 text-white
               hover:bg-green-700
               focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1
               transition shadow-sm">
                <i class="mgc_check_line text-base"></i>
                Aceptar certificación
            </button>

            {{-- Ajustar impuestos --}}
            <button wire:click="abrirModalImpuestos"
                class="inline-flex items-center gap-2 px-4 py-2.5
           rounded-xl text-xs font-semibold
           bg-primary text-white
           hover:bg-primary/90
           focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1
           transition shadow-sm">
                <i class="mgc_balance_line text-base"></i>
                Ajustar IVA y Retención
            </button>


        </div>
    @endif

    @if ($alertaPresupuestoActiva)
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-300 text-red-800 mt-6">
            <div class="flex items-start gap-3">
                <div class="text-red-600 text-2xl">
                    <i class="mgc_warning_line"></i>
                </div>

                <div class="text-sm">
                    <p class="font-semibold mb-1">
                        Atención: esta certificación supera el presupuesto de venta contratado.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                        <div>
                            <p class="font-semibold">Importe contratado</p>
                            <p>{{ number_format($alertaImporteContratado, 2, ',', '.') }} €</p>
                        </div>

                        <div>
                            <p class="font-semibold">Importe certificado actual</p>
                            <p>{{ number_format($alertaImporteCertificado, 2, ',', '.') }} €</p>
                        </div>

                        <div>
                            <p class="font-semibold">Cantidad contratada</p>
                            <p>{{ number_format($alertaCantidadContratada, 2, ',', '.') }}</p>
                        </div>

                        <div>
                            <p class="font-semibold">Cantidad certificada</p>
                            <p>{{ number_format($alertaCantidadCertificada, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif





    <x-tablas.table>

        <x-slot name="filters">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 px-4 py-3 bg-gray-50 border-b">

                <!-- Buscar concepto -->
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Buscar concepto</label>
                    <input type="text" wire:model.defer="pendingSearch" placeholder="Ej: demolición"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <!-- Unidad -->
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Unidad</label>
                    <select wire:model.defer="pendingUnidad"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Todas</option>
                        @foreach ($certificacion->detalles->pluck('unidad')->unique()->filter() as $unidad)
                            <option value="{{ $unidad }}">{{ $unidad }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Importe mínimo -->
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Importe mín.</label>
                    <input type="number" step="0.01" wire:model.defer="pendingImporteMin"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <!-- Importe máximo -->
                <div>
                    <label class="text-xs font-medium text-gray-600 mb-1 block">Importe máx.</label>
                    <input type="number" step="0.01" wire:model.defer="pendingImporteMax"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                </div>

            </div>

            <!-- BOTONES -->
            <div class="flex justify-end gap-2 px-4 py-3 bg-gray-50 border-b">
                <button wire:click="limpiarFiltros"
                    class="px-4 py-2 rounded-xl bg-white text-gray-700 border border-gray-300 hover:bg-gray-100 transition">
                    Limpiar
                </button>

                <button wire:click="aplicarFiltros"
                    class="px-5 py-2 rounded-xl bg-primary text-white font-semibold hover:bg-primary/90 transition">
                    Filtrar
                </button>
            </div>
        </x-slot>



        <x-slot name="columns">
            <th class="px-4 py-2 text-left">Concepto</th>
            <th class="px-4 py-2 w-24 text-left">Unidad</th>
            <th class="px-4 py-2 w-28 text-right">Cantidad</th>
            <th class="px-4 py-2 w-32 text-right">Precio unit.</th>
            <th class="px-4 py-2 w-32 text-right">Importe</th>

            @if ($certificacion->estado_certificacion === 'pendiente')
                <th class="px-4 py-2 w-24 text-center">Acciones</th>
            @endif

        </x-slot>

        <x-slot name="rows">
            @forelse ($this->detallesFiltrados as $detalle)
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

                    @if ($certificacion->estado_certificacion === 'pendiente')
                        <td class="px-4 py-2">
                            <div class="flex justify-center gap-2">

                                <button wire:click="abrirModalEditar({{ $detalle->id }})"
                                    class="text-yellow-600 hover:text-yellow-800 transition">
                                    <i class="mgc_edit_2_line text-lg"></i>
                                </button>

                                <button class="text-red-600 hover:text-red-800 transition"
                                    wire:click="confirmarEliminarDetalle({{ $detalle->id }})">
                                    <i class="mgc_delete_line text-lg"></i>
                                </button>

                            </div>
                        </td>
                    @endif


                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">
                        No hay detalles registrados en esta certificación.
                    </td>
                </tr>
            @endforelse
        </x-slot>

    </x-tablas.table>

    <div class="bg-gray-50 border rounded-xl p-5 grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 shadow-lg mt-6">

        <div>
            <p class="text-xs text-gray-500">Base imponible</p>
            <p class="text-lg font-semibold text-gray-800">
                {{ number_format($certificacion->base_imponible, 2, ',', '.') }} €
            </p>
        </div>

        <div>
            <p class="text-xs text-gray-500">
                IVA ({{ $certificacion->iva_porcentaje }}%)
            </p>
            <p class="text-lg font-semibold text-blue-700">
                {{ number_format($certificacion->iva_importe, 2, ',', '.') }} €
            </p>
        </div>

        <div>
            <p class="text-xs text-gray-500">
                IRPF ({{ $certificacion->retencion_porcentaje }}%)
            </p>
            <p class="text-lg font-semibold text-red-600">
                -{{ number_format($certificacion->retencion_importe, 2, ',', '.') }} €
            </p>
        </div>

        <div class="border-l pl-4">
            <p class="text-xs text-gray-500">TOTAL</p>
            <p class="text-2xl font-bold text-primary">
                {{ number_format($certificacion->total, 2, ',', '.') }} €
            </p>
        </div>

    </div>



    @if ($showModal)
        <div wire:ignore.self
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

            <div
                class="bg-white w-full max-w-3xl rounded-2xl shadow-[0_15px_40px_rgba(0,0,0,0.2)]
                   border border-gray-200 overflow-hidden
                   max-h-[92vh] flex flex-col">

                <!-- CABECERA -->
                <div class="flex items-center gap-3 p-5 border-b bg-white sticky top-0 z-20 shadow-sm">

                    <div
                        class="bg-primary/10 text-primary w-12 h-12 flex items-center justify-center rounded-xl text-2xl">
                        <i class="mgc_file_check_line"></i>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-800">
                        {{ $modoEdicion ? 'Editar detalle de certificación' : 'Añadir detalle de certificación' }}
                    </h3>

                    <button wire:click="cerrarModal"
                        class="ml-auto text-gray-500 hover:text-red-600 transition text-3xl leading-none">
                        &times;
                    </button>
                </div>

                <!-- CONTENIDO -->
                <div class="p-6 overflow-y-auto flex-1 space-y-5">

                    <!-- CONCEPTO -->
                    <div>
                        <label class="block font-medium mb-1">
                            Concepto <span class="text-red-600">*</span>
                        </label>
                        <input type="text" wire:model.defer="concepto"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            placeholder="Ej: Demolición tabiques interiores">
                        @error('concepto')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- UNIDAD -->
                    <div>
                        <label class="block font-medium mb-1">Unidad</label>
                        <input type="text" wire:model.defer="unidad"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            placeholder="m², ml, ud, h, etc.">
                    </div>

                    <!-- CANTIDAD -->
                    <div>
                        <label class="block font-medium mb-1">
                            Cantidad <span class="text-red-600">*</span>
                        </label>
                        <input type="number" step="0.01" wire:model.lazy="cantidad"
                            class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary">
                        @error('cantidad')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PRECIO UNITARIO -->
                    <div>
                        <label class="block font-medium mb-1">
                            Precio unitario (€) <span class="text-red-600">*</span>
                        </label>
                        <input type="number" step="0.01" wire:model.lazy="precio_unitario"
                            class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary">
                        @error('precio_unitario')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- IMPORTE -->
                    <div class="text-right text-lg font-semibold text-gray-700 pt-2 border-t">
                        Importe:
                        <span class="text-primary">
                            {{ number_format(((float) ($cantidad ?? 0)) * ((float) ($precio_unitario ?? 0)), 2, ',', '.') }}
                            €
                        </span>
                    </div>

                    @if ($excedePresupuesto)
                        <div
                            class="mt-3 p-4 rounded-xl bg-red-50 border border-red-300 text-red-800 text-sm space-y-3">
                            <div>
                                <strong>Atención:</strong><br>
                                El importe certificado supera el valor contratado del presupuesto de venta.
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-xs">
                                <div>
                                    <p class="font-semibold">Importe contratado</p>
                                    <p>{{ number_format($importeContratado, 2, ',', '.') }} €</p>
                                </div>

                                <div>
                                    <p class="font-semibold">Importe tras guardar</p>
                                    <p>{{ number_format($importeCertificadoTrasGuardar, 2, ',', '.') }} €</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-xs pt-2 border-t border-red-200">
                                <div>
                                    <p class="font-semibold">Cantidad contratada</p>
                                    <p>{{ number_format($cantidadContratada, 2, ',', '.') }}</p>
                                </div>

                                <div>
                                    <p class="font-semibold">Cantidad tras guardar</p>
                                    <p>{{ number_format($cantidadCertificadaTrasGuardar, 2, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif



                </div>

                <!-- FOOTER -->
                <div class="flex justify-end gap-3 p-5 border-t bg-gray-50">

                    <button wire:click="cerrarModal"
                        class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition">
                        Cancelar
                    </button>

                    <button wire:click="guardarDetalle"
                        class="px-5 py-2 rounded-xl bg-primary text-white font-semibold hover:bg-primary/90 transition shadow">
                        {{ $modoEdicion ? 'Actualizar detalle' : 'Guardar detalle' }}
                    </button>

                </div>

            </div>
        </div>
    @endif



    @if ($showDeleteModal)
        <x-modals.confirmar titulo="Confirmar eliminación"
            mensaje="¿Seguro que deseas eliminar este detalle?<br>Esta acción no se puede deshacer."
            wire-close="wire:click=&quot;$set('showDeleteModal', false)&quot;">

            {{-- CANCELAR --}}
            <x-btns.cancelar wire:click="$set('showDeleteModal', false)">
                Cancelar
            </x-btns.cancelar>

            {{-- ELIMINAR --}}
            <x-btns.danger wire:click="eliminarDetalle">
                Eliminar
            </x-btns.danger>

        </x-modals.confirmar>
    @endif

    @if ($showAceptarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

            <div
                class="bg-white w-full max-w-lg rounded-2xl shadow-xl
                   border border-gray-200 overflow-hidden">

                <!-- CABECERA -->
                <div class="flex items-center gap-3 p-5 border-b">
                    <div
                        class="bg-green-100 text-green-600 w-10 h-10 flex items-center justify-center rounded-xl text-xl">
                        <i class="mgc_check_line"></i>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800">
                        Aceptar certificación
                    </h3>

                    <button wire:click="$set('showAceptarModal', false)"
                        class="ml-auto text-gray-500 hover:text-red-600 text-2xl">
                        &times;
                    </button>
                </div>

                <!-- CONTENIDO -->
                <div class="p-6 text-gray-700">
                    <p>
                        Al aceptar la certificación:
                    </p>
                    <ul class="list-disc pl-5 mt-2 text-sm text-gray-600 space-y-1">
                        <li>No podrás modificar los detalles</li>
                        <li>Los importes quedarán bloqueados</li>
                        <li>Solo podrás generar la factura</li>
                    </ul>

                    <p class="mt-4 font-medium">
                        ¿Deseas continuar?
                    </p>
                </div>

                <!-- FOOTER -->
                <div class="flex justify-end gap-3 p-5 border-t bg-gray-50">
                    <button wire:click="$set('showAceptarModal', false)"
                        class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition">
                        Cancelar
                    </button>

                    <button wire:click="aceptarCertificacion"
                        class="px-4 py-2 rounded-xl bg-green-600 text-white
                           hover:bg-green-700 transition font-semibold">
                        Aceptar
                    </button>
                </div>

            </div>
        </div>
    @endif

    @if ($showImpuestosModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

            <div
                class="bg-white w-full max-w-lg rounded-2xl shadow-lg
                border border-gray-200 overflow-hidden">

                <!-- Header -->
                <div class="flex items-center gap-3 p-5 border-b">
                    <div class="bg-primary/10 text-primary w-10 h-10 flex items-center justify-center rounded-xl">
                        <i class="mgc_balance_line text-xl"></i>
                    </div>

                    <h3 class="text-lg font-semibold">
                        Ajustar IVA y Retención
                    </h3>

                    <button wire:click="$set('showImpuestosModal', false)"
                        class="ml-auto text-gray-500 hover:text-red-600 text-2xl">
                        &times;
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-4">

                    <div>
                        <label class="block font-medium mb-1">IVA (%)</label>
                        <input type="number" step="0.01" wire:model.defer="iva_porcentaje"
                            class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label class="block font-medium mb-1">IRPF / Retención (%)</label>
                        <input type="number" step="0.01" wire:model.defer="retencion_porcentaje"
                            class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary">
                    </div>

                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 p-5 border-t bg-gray-50">
                    <button wire:click="$set('showImpuestosModal', false)"
                        class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300">
                        Cancelar
                    </button>

                    <button wire:click="guardarImpuestos"
                        class="px-5 py-2 rounded-xl bg-primary text-white font-semibold hover:bg-primary/90">
                        Guardar
                    </button>
                </div>

            </div>
        </div>
    @endif






</div>
