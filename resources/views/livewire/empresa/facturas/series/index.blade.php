<div>

    {{-- CABECERA --}}
    <div class="flex flex-wrap items-center gap-3 mb-7">


        <x-btns.regresar href="{{ route('empresa.facturas-ventas') }}">
            Regresar
        </x-btns.regresar>


        <x-btns.agregar wire:click="nuevaSerie">
            Nueva serie
        </x-btns.agregar>

    </div>

    <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-6">
        Series de facturación
    </h2>

    <x-tablas.filters>
        {{-- ================= FILTROS ================= --}}
        <div class="bg-white">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

                {{-- FILTRO SERIE --}}
                <input type="text" wire:model.defer="tmpSerie" placeholder="Buscar serie..."
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">

                {{-- FILTRO ESTADO --}}
                <select wire:model.defer="tmpEstado"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    <option value="">Todas</option>
                    <option value="1">Activas</option>
                    <option value="0">Inactivas</option>
                </select>


                {{-- BOTONES --}}
                <div class="flex gap-2">
                    <button wire:click="aplicarFiltros"
                        class="px-4 py-2 rounded-xl bg-primary text-white font-semibold shadow hover:bg-primary/90 transition">
                        Filtrar
                    </button>

                    <button wire:click="limpiarFiltros"
                        class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition">
                        Limpiar
                    </button>
                </div>

            </div>

        </div>
    </x-tablas.filters>

    {{-- ================= MODAL ================= --}}
    @if ($mostrarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

            <div
                class="bg-white w-full max-w-3xl rounded-2xl shadow-[0_15px_40px_rgba(0,0,0,0.2)]
                   border border-gray-200 overflow-hidden animate-[fadeIn_0.25s_ease-out,slideUp_0.3s_ease-out]
                   max-h-[92vh] flex flex-col p-6">

                <h3 class="text-lg font-semibold mb-4 border-b pb-2">
                    {{ $editandoId ? 'Editar serie' : 'Nueva serie' }}
                </h3>

                <form wire:submit.prevent="guardar" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- SERIE --}}
                    <div>
                        <label class="block font-medium mb-1">Serie *</label>
                        <input type="text" wire:model.defer="serie"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            placeholder="Ej: FV">
                        @error('serie')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ÚLTIMO NÚMERO --}}
                    <div>
                        <label class="block font-medium mb-1">Último número *</label>
                        <input type="number" wire:model.defer="ultimo_numero"
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                            min="0">
                        @error('ultimo_numero')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- BOTONES --}}
                    <div class="col-span-2 flex justify-end gap-2 pt-4">

                        <button type="button" wire:click="cancelarEdicion"
                            class="px-4 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 transition">
                            Cancelar
                        </button>

                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-primary text-white font-semibold shadow hover:bg-primary/90 transition">
                            {{ $editandoId ? 'Actualizar' : 'Crear' }}
                        </button>

                    </div>

                </form>

            </div>
        </div>
    @endif
    {{-- ================= FIN MODAL ================= --}}

    {{-- ================= TABLA ================= --}}
    <div class="bg-white rounded-xl shadow overflow-hidden mt-6">

        <table class="w-full table-auto divide-y divide-gray-200">

            <thead class="bg-gray-100 text-left text-gray-700 text-sm">
                <tr>
                    <th class="px-4 py-3">Serie</th>
                    <th class="px-4 py-3 text-right">Último número</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 text-sm">

                @forelse ($series as $s)
                    <tr class="hover:bg-gray-50 transition">

                        <td class="px-4 py-3 font-medium">
                            {{ $s->serie }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            {{ $s->ultimo_numero }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $s->activa ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $s->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right space-x-2">

                            <button wire:click="editar({{ $s->id }})"
                                class="px-3 py-1.5 rounded-lg bg-blue-100 text-blue-700 text-xs font-semibold hover:bg-blue-200 transition">
                                Editar
                            </button>

                            <button wire:click="toggleActiva({{ $s->id }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition
                                {{ $s->activa ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                {{ $s->activa ? 'Desactivar' : 'Activar' }}
                            </button>

                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                            No hay series de facturación creadas.
                        </td>
                    </tr>
                @endforelse

            </tbody>



        </table>
    </div>

</div>
