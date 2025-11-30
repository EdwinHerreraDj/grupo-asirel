<div>

    {{-- ========================= --}}
    {{--      BOTONES ACCIONES     --}}
    {{-- ========================= --}}

    <div class="flex flex-wrap items-center gap-3 mb-7">

        {{-- REGRESAR --}}
        <a href="{{ route('empresa.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium
               border border-gray-300 shadow-sm
               hover:bg-gray-200 hover:text-gray-900 transition-all duration-300 active:scale-[0.98]
               focus:outline-none focus:ring-2 focus:ring-primary/30">
            <i class="mgc_arrow_left_line text-lg"></i>
            Regresar
        </a>

        {{-- NUEVA CATEGORÍA --}}
        <a href="{{ route('categorias.empresa.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full
               bg-cyan-500/15 text-cyan-700 font-medium border border-cyan-500/30 shadow-sm
               hover:bg-cyan-600 hover:text-white hover:border-cyan-600
               active:scale-[0.98] transition-all duration-300
               focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
            <i class="mgc_black_board_2_line text-lg"></i>
            Nueva categoría
        </a>

        {{-- AGREGAR GASTO --}}
        <button wire:click="abrirModal"
            class="inline-flex items-center gap-2  px-4 py-2 rounded-full bg-green-500/20 text-green-700 font-medium border border-green-500/30
           shadow-sm hover:bg-green-600 hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-green-400/40">
            <i class="mgc_add_line text-lg"></i>
            Agregar gasto
        </button>

    </div>

    {{-- ========== MODAL DEL FORMULARIO CREACION ========== --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[999] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">

            <div
                class="bg-white w-full max-w-2xl rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.15)] border border-gray-200 
                   p-6 relative animate-[fadeIn_0.25s_ease-out,slideUp_0.3s_ease-out]">

                {{-- Botón cerrar --}}
                <button wire:click="cerrarModal"
                    class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl font-light
                       transition-all duration-200">
                    &times;
                </button>

                {{-- CABECERA OPCIONAL --}}

                <div class="mb-4 pb-3 border-b border-gray-200 flex items-center gap-3">
                    <div
                        class="bg-primary/10 text-primary w-10 h-10 flex items-center justify-center rounded-xl text-xl">
                        <i class="mgc_add_line"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Nuevo gasto</h3>
                </div>


                {{-- Contenido del formulario --}}
                <div class="mt-2">
                    @livewire('empresa.gastos.formulario', [], key('form-gasto'))
                </div>

            </div>
        </div>
    @endif

    <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow ">
        Gastos de la empresa
    </h2>

    {{-- ========================= --}}
    {{--       TABLA GASTOS        --}}
    {{-- ========================= --}}
    <x-tablas.table>

        {{-- FILTROS --}}
        <x-slot name="filters">
            {{-- FILTROS --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                {{-- BUSCADOR --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" wire:model.defer="pendingSearch"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 shadow-sm 
                       focus:ring-primary focus:border-primary transition"
                        placeholder="Concepto, descripción...">
                </div>

                {{-- CATEGORÍA --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select wire:model.defer="pendingCategoria"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 shadow-sm 
                       focus:ring-primary focus:border-primary transition">
                        <option value="">Todas</option>

                        @foreach ($categoriasPadre as $padre)
                            <optgroup label="{{ $padre->codigo }} - {{ $padre->nombre }}">
                                @foreach ($padre->children as $hijo)
                                    <option value="{{ $hijo->id }}">— {{ $hijo->codigo }} - {{ $hijo->nombre }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                {{-- FECHA DESDE --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha desde</label>
                    <input type="date" wire:model.defer="pendingFechaDesde"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 shadow-sm 
                       focus:ring-primary focus:border-primary transition">
                </div>

                {{-- FECHA HASTA --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha hasta</label>
                    <input type="date" wire:model.defer="pendingFechaHasta"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3 py-2 shadow-sm 
                       focus:ring-primary focus:border-primary transition">
                </div>

                {{-- BOTONES --}}
                <div class="md:col-span-4 flex justify-end gap-3 mt-3">
                    <button wire:click="limpiarFiltros"
                        class="px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium 
                       border border-gray-300 hover:bg-gray-200 transition">
                        Limpiar
                    </button>

                    <button wire:click="aplicarFiltros"
                        class="px-4 py-2 rounded-full bg-primary text-white font-semibold
                       shadow hover:bg-primary/90 transition">
                        Aplicar filtros
                    </button>
                </div>
            </div>
        </x-slot>

        {{-- COLUMNAS --}}
        <x-slot name="columns">
            <th class="px-4 py-3 font-semibold w-32">Nº Factura</th>
            <th class="px-4 py-3 font-semibold w-40">Concepto</th>
            <th class="px-4 py-3 font-semibold">Especificación</th>
            <th class="px-4 py-3 font-semibold w-28">Importe</th>
            <th class="px-4 py-3 font-semibold w-32">Categoría</th>
            <th class="px-4 py-3 font-semibold w-32">F. factura</th>
            <th class="px-4 py-3 font-semibold w-32">F. contable</th>
            <th class="px-4 py-3 font-semibold w-32">Vencimiento</th>
            <th class="px-4 py-3 font-semibold w-24">Factura</th>
            <th class="px-4 py-3 font-semibold text-center w-28">Acción</th>
        </x-slot>

        {{-- FILAS --}}
        <x-slot name="rows">
            @forelse ($gastos as $gasto)
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-4 py-3">{{ $gasto->numero_factura ?? '—' }}</td>

                    <td class="px-4 py-3">{{ $gasto->concepto }}</td>

                    <td class="px-4 py-3">{{ $gasto->especificacion ?? '—' }}</td>

                    <td class="px-4 py-3 font-semibold">
                        {{ number_format($gasto->importe, 2, ',', '.') }} €
                    </td>

                    <td class="px-4 py-3">{{ $gasto->categoria->nombre ?? '—' }}</td>

                    <td class="px-4 py-3">
                        {{ $gasto->fecha_factura ? \Carbon\Carbon::parse($gasto->fecha_factura)->format('d/m/Y') : '—' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $gasto->fecha_contable ? \Carbon\Carbon::parse($gasto->fecha_contable)->format('d/m/Y') : '—' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $gasto->fecha_vencimiento ? \Carbon\Carbon::parse($gasto->fecha_vencimiento)->format('d/m/Y') : '—' }}
                    </td>

                    <td class="px-4 py-3">
                        @if ($gasto->factura_url)
                            <div class="flex gap-2">
                                <x-btns.descargar href="{{ asset('storage/' . $gasto->factura_url) }}" />
                                <x-btns.ver pdf="{{ asset('storage/' . $gasto->factura_url) }}" />
                            </div>
                        @else
                            —
                        @endif
                    </td>

                    <td class="px-4 py-3 text-center">
                        <x-btns.eliminar wire:click="confirmarEliminar({{ $gasto->id }})" />
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                        No se encontraron gastos que coincidan con los filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </x-slot>

        {{-- PAGINACIÓN --}}
        <x-slot name="pagination">
            {{ $gastos->links() }}
        </x-slot>
    </x-tablas.table>



    {{-- ========================== --}}
    {{-- MODAL ELIMINAR GASTO      --}}
    {{-- ========================== --}}
    @if ($showDeleteModal)

        <x-modals.confirmar titulo="Eliminar gasto"
            mensaje="¿Estás seguro de que deseas eliminar este gasto? <strong class='text-red-600'>Esta acción no se puede deshacer.</strong>">
          
            <x-btns.cancelar wire:click="cancelarEliminar" >Cancelar</x-btns.cancelar>
            <x-btns.danger wire:click="eliminar">Eliminar</x-btns.danger>
        </x-modals.confirmar>   
    @endif



    @include('components-vs.modals.visor-pdf')

</div>
