<div class="card p-10">

    {{-- Botón regresar --}}

    <x-btns.regresar href="{{ route('obras.gastos', $obra->id) }}">
        Regresar
    </x-btns.regresar>

    <x-btns.agregar wire:click="abrirFormulario">
        Agregar factura
    </x-btns.agregar>


    {{-- Botón Generar Informe --}}
    {{-- <button type="button" data-fc-type="modal" data-fc-target="modalInformeFacturas"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/20 text-cyan-700 font-medium border border-cyan-500/30 
               shadow-sm hover:bg-cyan-600 hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
        <i class="mgc_document_line text-lg"></i>
        Generar informe
    </button> --}}


    {{-- FILTROS y TABLA --}}
    <div class="mt-6">

        <!-- TÍTULO -->
        <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4">
            Facturas Recibidas
        </h2>

        <div class="overflow-x-auto">

            {{-- FILTROS --}}
            <div class="bg-gray-50 p-5 rounded-xl shadow border border-gray-200 mb-6">

                <h3 class="text-gray-700 font-semibold mb-3 flex items-center gap-2">
                    <i class="mgc_filter_3_line text-lg text-primary"></i>
                    Filtros
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                    <input type="text" wire:model.defer="search" placeholder="Buscar..."
                        class="form-input w-full rounded-lg border-gray-300">

                    <select wire:model.defer="filtroProveedor" class="form-select w-full rounded-lg border-gray-300">
                        <option value="">Proveedor</option>
                        @foreach ($proveedores as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>

                    <select wire:model.defer="filtroOficio" class="form-select w-full rounded-lg border-gray-300">
                        <option value="">Oficio</option>
                        @foreach ($oficios as $oficio)
                            <option value="{{ $oficio->id }}">{{ $oficio->nombre }}</option>
                        @endforeach
                    </select>

                    <select wire:model.defer="filtroEstado" class="form-select w-full rounded-lg border-gray-300">
                        <option value="">Estado</option>
                        <option value="pendiente_de_vencimiento">Pendiente venc.</option>
                        <option value="pagada">Pagada</option>
                        <option value="pendiente_de_emision">Pend. emisión</option>
                        <option value="aplazada">Aplazada</option>
                        <option value="impagada">Impagada</option>
                    </select>

                    <select wire:model.defer="filtroTipoCoste" class="form-select w-full rounded-lg border-gray-300">
                        <option value="">Tipo coste</option>
                        <option value="material">Material</option>
                        <option value="mano_obra">Mano de obra</option>
                    </select>

                </div>

                <!-- BOTONES FILTROS -->
                <div class="flex justify-end mt-5 gap-3">

                    <button wire:click="limpiarFiltros"
                        class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition">
                        Limpiar
                    </button>

                    <button wire:click="aplicarFiltros"
                        class="px-4 py-2 rounded-full bg-primary text-white font-medium hover:bg-primary/80 transition">
                        Filtrar
                    </button>

                </div>

            </div>

            <!-- TABLA -->
            <table class="min-w-full divide-y divide-gray-200 shadow rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr class="text-left text-gray-700 text-sm">
                        <th class="px-4 py-3 font-semibold">ID</th>
                        <th class="px-4 py-3 font-semibold">Proveedor</th>
                        <th class="px-4 py-3 font-semibold">Oficio</th>
                        <th class="px-4 py-3 font-semibold">Tipo</th>
                        <th class="px-4 py-3 font-semibold">Concepto</th>
                        <th class="px-4 py-3 font-semibold">Importe</th>
                        <th class="px-4 py-3 font-semibold">Factura</th>
                        <th class="px-4 py-3 font-semibold">N° Factura</th>
                        <th class="px-4 py-3 font-semibold">Fecha</th>
                        <th class="px-4 py-3 font-semibold">Vencimiento</th>
                        <th class="px-4 py-3 font-semibold">Tipo Pago</th>
                        <th class="px-4 py-3 font-semibold">Estado</th>
                        <th class="px-4 py-3 font-semibold">Acción</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 text-sm">

                    @forelse ($facturas as $factura)
                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-4 py-3">{{ $loop->iteration }}</td>

                            <td class="px-4 py-3">{{ $factura->proveedor->nombre ?? '—' }}</td>

                            <td class="px-4 py-3">{{ $factura->oficio->nombre ?? '—' }}</td>

                            <td class="px-4 py-3">
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-medium
                                {{ $factura->tipo_coste === 'material' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ ucfirst($factura->tipo_coste) }}
                                </span>
                            </td>

                            <td class="px-4 py-3">{{ $factura->concepto }}</td>

                            <td class="px-4 py-3 font-semibold">
                                {{ number_format($factura->importe, 2, ',', '.') }} €
                            </td>

                            <td class="px-4 py-3">
                                @if ($factura->adjunto)
                                    <div class="flex gap-2">
                                        <x-btns.descargar href="{{ asset('storage/' . $factura->adjunto) }}" />
                                        <x-btns.ver pdf="{{ asset('storage/' . $factura->adjunto) }}" />
                                    </div>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-4 py-3">{{ $factura->numero_factura ?? '—' }}</td>

                            <td class="px-4 py-3">
                                {{ $factura->fecha_factura->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $factura->vencimiento ? $factura->vencimiento->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $factura->tipo_pago ? str_replace('_', ' ', ucfirst($factura->tipo_pago)) : '—' }}
                            </td>

                            <td>
                                <select
                                    class="form-select text-sm rounded-lg border-gray-300 focus:ring-primary focus:border-primary"
                                    wire:change="actualizarEstado({{ $factura->id }}, $event.target.value)">
                                    <option value="pendiente_de_vencimiento"
                                        {{ $factura->estado == 'pendiente_de_vencimiento' ? 'selected' : '' }}>
                                        🔵 Pendiente de vencimiento
                                    </option>

                                    <option value="pagada" {{ $factura->estado == 'pagada' ? 'selected' : '' }}>
                                        🟢 Pagada
                                    </option>

                                    <option value="pendiente_de_emision"
                                        {{ $factura->estado == 'pendiente_de_emision' ? 'selected' : '' }}>
                                        🟡 Pendiente de emisión
                                    </option>

                                    <option value="aplazada" {{ $factura->estado == 'aplazada' ? 'selected' : '' }}>
                                        🟣 Aplazada
                                    </option>

                                    <option value="impagada" {{ $factura->estado == 'impagada' ? 'selected' : '' }}>
                                        🔴 Impagada
                                    </option>
                                </select>
                            </td>

                            <td class="px-4 py-3">
                                <x-btns.eliminar wire:click="confirmarEliminar({{ $factura->id }})" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-4 py-6 text-center text-gray-500">
                                No se encontraron facturas recibidas.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>
    </div>
    {{-- PAGINACIÓN --}}
    <div class="mt-6">
        {{ $facturas->links() }}
    </div>




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

                <h3 class="text-xl font-semibold text-gray-800">Registrar factura recibida</h3>

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
                            placeholder="0001/2024"
                            >
                    </div>

                    <!-- Importe -->
                    <div>
                        <label class="block font-medium mb-1">Importe (€)</label>
                        <input type="number" step="0.01" wire:model="importe"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            placeholder="0.00">
                        @error('importe')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
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
                            <option value="pendiente_de_vencimiento">Pendiente de vencimiento</option>
                            <option value="pagada">Pagada</option>
                            <option value="pendiente_de_emision">Pendiente de emisión</option>
                            <option value="aplazada">Aplazada</option>
                            <option value="impagada">Impagada</option>
                        </select>
                    </div>

                    <!-- Adjunto -->
                    <div>
                        <label class="block font-medium mb-2">Adjuntar factura</label>

                        <div class="flex items-center gap-3">
                            <label
                                class="px-4 py-2 rounded-xl bg-primary text-white font-medium cursor-pointer hover:bg-primary/90 transition">
                                Seleccionar archivo
                                <input type="file" wire:model="adjunto" class="hidden" />
                            </label>

                            <span class="text-sm flex items-center gap-2">

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

                            </span>

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
                    Guardar factura
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




</div>
