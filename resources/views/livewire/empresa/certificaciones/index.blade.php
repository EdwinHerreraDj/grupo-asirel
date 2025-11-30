<div>

    {{-- ======================
        CABECERA + BOTÓN NUEVO
    ======================= --}}
    <div class="flex flex-wrap items-center gap-3 mb-7">

        {{-- REGRESAR --}}
        <x-btns.regresar href="{{ route('obras.gastos', $obraId) }}">
            Regresar
        </x-btns.regresar>

        <x-btns.agregar wire:click="abrirModal">
            Nueva certificación
        </x-btns.agregar>

    </div>

    <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4">
        Certificaciones registradas
    </h2>

    {{-- ======================
        TABLA DE CERTIFICACIONES
    ======================= --}}

    {{-- Tabla --}}

    <x-tablas.table>

        <x-slot name="filters">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                <!-- Oficio -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Oficio</label>
                    <select wire:model.defer="pendingOficio"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Todos</option>
                        @foreach ($oficios as $oficio)
                            <option value="{{ $oficio->id }}">{{ $oficio->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Tipo</label>
                    <select wire:model.defer="pendingTipo"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Todos</option>
                        <option value="factura">Factura</option>
                        <option value="certificacion">Certificación</option>
                    </select>
                </div>

                <!-- Fecha desde -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Fecha desde</label>
                    <input type="date" wire:model.defer="pendingFechaDesde"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <!-- Fecha hasta -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Fecha hasta</label>
                    <input type="date" wire:model.defer="pendingFechaHasta"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                </div>

                <!-- Buscador -->
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Buscar</label>
                    <input type="text" wire:model.defer="pendingSearch"
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                        placeholder="Nº Certificación o texto...">
                </div>

                <!-- Botones -->
                <div class="md:col-span-5 flex justify-end gap-3 mt-2">
                    <button wire:click="limpiarFiltros"
                        class="px-4 py-2 rounded-xl bg-gray-100 border border-gray-300 text-gray-700 hover:bg-gray-200 transition">
                        Limpiar
                    </button>

                    <button wire:click="aplicarFiltros"
                        class="px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary/90 transition shadow-sm">
                        Aplicar filtros
                    </button>
                </div>

            </div>
        </x-slot>


        <x-slot name="columns">
            <th class="px-4 py-2 w-36 text-left">Nº Certificación</th>
            <th class="px-4 py-2 w-28 text-left">Fecha cert.</th>
            <th class="px-4 py-2 w-28 text-left">Fecha cont.</th>
            <th class="px-4 py-2 w-32 text-left">Oficio</th>
            <th class="px-4 py-2 w-48 text-left">Especificación</th>
            <th class="px-4 py-2 w-24 text-left">Tipo</th>
            <th class="px-4 py-2 w-24 text-left">Total</th>
            <th class="px-4 py-2 w-20 text-left">Adjunto</th>
            <th class="px-4 py-2 w-24 text-center">Acciones</th>
        </x-slot>


        <x-slot name="rows">
            @forelse ($certificaciones as $cert)
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-2">
                        {{ $cert->numero_certificacion ?? '-' }}
                    </td>

                    <td class="px-4 py-2">{{ $cert->fecha_ingreso }}</td>

                    <td class="px-4 py-2">
                        {{ $cert->fecha_contable ?? '-' }}
                    </td>


                    <td class="px-4 py-2">
                        {{ $cert->oficio->nombre ?? '-' }}
                    </td>

                    <td class="px-4 py-2">
                        {{ Str::limit($cert->especificacion, 40) }}
                    </td>

                    <td class="px-4 py-2 capitalize">
                        <span
                            class="px-2 py-1 rounded-md text-xs font-semibold
                                {{ $cert->tipo_documento === 'factura' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            {{ $cert->tipo_documento }}
                        </span>
                    </td>

                    <td class="px-4 py-2">
                        {{ number_format($cert->total, 2, ',', '.') }} €
                    </td>

                    <td class="px-4 py-2">
                        @if ($cert->adjunto_url)
                            <x-btns.descargar href="{{ asset('storage/' . $cert->adjunto_url) }}" />
                            <x-btns.ver pdf="{{ asset('storage/' . $cert->adjunto_url) }}" />
                        @else
                            -
                        @endif
                    </td>

                    <td class="px-4 py-2 text-center">
                        <x-btns.eliminar wire:click="confirmarEliminar({{ $cert->id }})" />
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-4 text-center text-gray-500">
                        No hay certificaciones registradas.
                    </td>
                </tr>
            @endforelse
        </x-slot>


        <x-slot name="pagination">
            {{ $certificaciones->links() }}
        </x-slot>
    </x-tablas.table>




    {{-- ======================
        MODAL FORMULARIO
    ======================= --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

            <div
                class="bg-white w-full max-w-3xl rounded-2xl shadow-[0_15px_40px_rgba(0,0,0,0.2)]
                   border border-gray-200 overflow-hidden animate-[fadeIn_0.25s_ease-out,slideUp_0.3s_ease-out]
                   max-h-[92vh] flex flex-col">

                <!-- CABECERA -->
                <div class="flex items-center gap-3 p-5 border-b bg-white sticky top-0 z-20 shadow-sm">

                    <div
                        class="bg-primary/10 text-primary w-12 h-12 flex items-center justify-center rounded-xl text-2xl">
                        <i class="mgc_file_check_line"></i>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-800">
                        Registrar factura recibida
                    </h3>

                    <!-- Botón cerrar -->
                    <button wire:click="cerrarModal"
                        class="ml-auto text-gray-500 hover:text-red-600 transition text-3xl leading-none">
                        &times;
                    </button>
                </div>

                <!-- CONTENIDO (scroll interno) -->
                <div class="p-6 overflow-y-auto flex-1">
                    @livewire('empresa.certificaciones.formulario', ['obraId' => $obraId], key('form-' . $obraId))
                </div>

            </div>
        </div>
    @endif




    {{-- ======================
        MODAL ELIMINAR
    ======================= --}}
    @if ($showDeleteModal)
        <x-modals.confirmar titulo="Confirmar eliminación"
            mensaje="¿Seguro que deseas eliminar esta certificación?<br>Esta acción no se puede deshacer."
            wire-close="wire:click=&quot;$set('showDeleteModal', false)&quot;">

            {{-- Botón CANCELAR --}}
            <x-btns.cancelar wire:click="$set('showDeleteModal', false)">
                Cancelar
            </x-btns.cancelar>


            {{-- Botón ELIMINAR --}}
            <x-btns.danger wire:click="eliminar">
                Eliminar
            </x-btns.danger>

        </x-modals.confirmar>
    @endif

    @include('components-vs.modals.visor-pdf')

</div>
