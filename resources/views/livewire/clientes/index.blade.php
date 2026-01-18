<div class="p-6 bg-white rounded-xl shadow relative">

    {{-- BOTONES SUPERIORES --}}
    <div class="flex items-center mb-6">

        {{-- Botón regresar --}}
        <x-btns.regresar href="{{ route('unidad') }}">
            Regresar
        </x-btns.regresar>


        <x-btns.agregar wire:click="abrirModalCrear" class="ml-3">
            Nuevo cliente
        </x-btns.agregar>


    </div>

    <!-- TÍTULO -->
    <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4">
        Clientes
    </h2>

    <x-tablas.table>
        <x-slot name="filters">
            {{-- FILTROS --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <!-- Izquierda: Grupo de filtros -->
                <div class="flex flex-col md:flex-row gap-4 flex-1">

                    <!-- Buscar -->
                    <div class="flex-1">
                        <div class="relative">
                            <i class="mgc_search_3_line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" wire:model.defer="search"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-300 focus:ring-primary focus:border-primary shadow-sm"
                                placeholder="Buscar cliente...">
                        </div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <select wire:model.defer="filtroActivo"
                            class="w-full md:w-48 py-2.5 rounded-xl border-gray-300 focus:ring-primary focus:border-primary shadow-sm">
                            <option value="">Estado</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                    </div>

                </div>

                <!-- Derecha: Botones -->
                <div class="flex gap-2 md:gap-3">

                    <button wire:click="limpiarFiltros"
                        class="px-4 py-2.5 rounded-xl bg-white text-gray-700 font-medium border border-gray-300
                       hover:bg-gray-100 transition shadow-sm">
                        Limpiar
                    </button>

                    <button wire:click="aplicarFiltros"
                        class="px-5 py-2.5 rounded-xl bg-primary text-white font-semibold shadow-md 
                       hover:bg-primary/90 transition">
                        Filtrar
                    </button>

                </div>

            </div>
        </x-slot>
        <x-slot name="columns">
            <th class="px-4 py-3 text-left font-semibold">Nombre</th>
            <th class="px-4 py-3 text-left font-semibold">CIF</th>
            <th class="px-4 py-3 text-left font-semibold">Teléfono</th>
            <th class="px-4 py-3 text-left font-semibold">Email</th>
            <th class="px-4 py-3 text-left font-semibold">Dirección</th>
            <th class="px-4 py-3 text-left font-semibold">Descripcion</th>
            <th class="px-4 py-3 text-center font-semibold">Activo</th>
            <th class="px-4 py-3 text-right font-semibold">Acción</th>
        </x-slot>
        <x-slot name="rows">
            @forelse ($clientes as $c)
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-4 py-3 font-medium text-gray-900">{{ $c->nombre }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $c->cif ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $c->telefono ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $c->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $c->direccion ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $c->descripcion ?? '—' }}</td>
                    <!-- Activo -->
                    <td class="px-4 py-3 text-center">
                        <span
                            class="px-3 py-1 text-xs font-semibold rounded-full
                                {{ $c->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $c->activo ? 'Sí' : 'No' }}
                        </span>
                    </td>

                    <!-- Acciones -->
                    <td class="px-4 py-3 text-right flex justify-end gap-3">

                        <button wire:click="abrirModalEditar({{ $c->id }})"
                            class="text-yellow-600 hover:text-yellow-800 transition">
                            <i class="mgc_edit_2_line text-lg"></i>
                        </button>

                        <button wire:click="abrirModalEliminar({{ $c->id }})"
                            class="text-red-600 hover:text-red-800 transition">
                            <i class="mgc_delete_line text-lg"></i>
                        </button>

                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                        No se encontraron clientes.
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="pagination">
            {{ $clientes->links() }}
        </x-slot>

    </x-tablas.table>

    {{-- MODAL FORMULARIO CLIENTE (CREAR / EDITAR) --}}
    <div @class([
        'fixed inset-0 bg-black/50 backdrop-blur-sm z-[999] flex items-center justify-center p-4',
        'hidden' => !$modalFormulario,
    ])>
        <div class="bg-white w-full max-w-xl rounded-xl shadow-2xl p-6 border border-gray-200 relative">

            {{-- BOTÓN CERRAR --}}
            <button wire:click="cerrarModales" class="absolute top-3 right-4 text-gray-600 hover:text-red-600 text-2xl">
                &times;
            </button>

            {{-- UNA ÚNICA INSTANCIA --}}
            <livewire:clientes.formulario />

        </div>
    </div>

    
    {{-- MODAL ELIMINACIÓN --}}
    @if ($confirmarEliminacion)
        <x-modals.confirmar titulo="Eliminar cliente"
            mensaje="¿Estás seguro de que deseas eliminar este cliente? <strong class='text-red-600'>Esta acción no se puede deshacer.</strong>"
            wire-close="$set('confirmarEliminacion', false)">

            <x-btns.cancelar wire:click="$set('confirmarEliminacion', false)">Cancelar</x-btns.cancelar>
            <x-btns.danger wire:click="eliminar">Eliminar</x-btns.danger>
        </x-modals.confirmar>
    @endif


</div>
