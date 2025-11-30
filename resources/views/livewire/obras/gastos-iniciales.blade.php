<div class="mt-10">

    <!-- TÍTULO -->
    <h2 class="text-lg font-semibold text-primary mb-2 flex items-center gap-2">
        <span class="w-1.5 h-5 bg-primary rounded"></span> Gastos iniciales de la obra
    </h2>
    <p class="text-sm text-gray-600 mb-4">Configura las categorías y sus importes iniciales.</p>


    <!-- SI NO EXISTE OBRA (CREATE) -->
    @if (!$obra)
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded text-yellow-700 text-sm mb-4">
            Para gestionar las categorías de gasto inicial debes <strong>crear primero la obra</strong>.
        </div>
    @endif


    <!-- BOTÓN NUEVA CATEGORÍA -->
    <div class="flex justify-end mb-5">
        <button type="button" @if (!$obra) disabled @endif wire:click="abrirModalCrear"
            class="bg-primary text-white px-4 py-2 rounded-md text-sm shadow transition 
                   hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500">
            + Nueva categoría
        </button>
    </div>


    <!-- LISTADO DE CATEGORÍAS -->
    @if ($categorias->isEmpty())
        <p class="text-gray-500 text-sm italic text-center py-4">
            No hay categorías creadas para esta obra.
        </p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            @foreach ($categorias as $cat)
                <div class="border border-gray-200 bg-white rounded-lg p-4 shadow-sm">

                    <!-- Encabezado -->
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $cat->nombre }}</p>
                            <p class="text-xs text-gray-500">{{ $cat->descripcion }}</p>
                        </div>

                        <div class="flex gap-3">
                            <button type="button" wire:click="abrirModalEditar({{ $cat->id }})"
                                class="text-primary text-xs hover:underline">
                                Editar
                            </button>

                            <button type="button" wire:click="eliminarCategoria({{ $cat->id }})"
                                class="text-red-600 text-xs hover:underline">
                                Eliminar
                            </button>
                        </div>
                    </div>

                    <!-- INPUT IMPORTE -->
                    <input type="number" min="0" step="0.01" name="gastos_iniciales[{{ $cat->id }}]"
                        class="form-input w-full text-sm sum-input" placeholder="0.00"
                        value="{{ old('gastos_iniciales.' . $cat->id, $gastosObra[$cat->id] ?? '') }}">
                </div>
            @endforeach

        </div>
    @endif

    <!-- RESUMEN DE SUMATORIAS -->
    @if ($obra)
        <div class="mt-8 bg-gray-50 border border-gray-200 p-4 rounded-lg">

            <h3 class="text-md font-semibold text-gray-700 mb-3">Resumen de gastos iniciales</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- Importe presupuestado -->
                <div class="p-3 bg-white rounded shadow-sm border text-center">
                    <p class="text-xs text-gray-500 uppercase">Importe presupuestado</p>
                    <p id="importe_presu" class="text-lg font-bold text-primary">
                        {{ number_format($obra->importe_presupuestado ?? old('importe_presupuestado', 0), 2, ',', '.') }}
                        €
                    </p>
                </div>

                <!-- Total gastos iniciales -->
                <div class="p-3 bg-white rounded shadow-sm border text-center">
                    <p class="text-xs text-gray-500 uppercase">Total gastos iniciales</p>
                    <p id="total_gastos" class="text-lg font-bold text-red-600">0.00 €</p>
                </div>

                <!-- Ganancia / Diferencia -->
                <div class="p-3 bg-white rounded shadow-sm border text-center">
                    <p class="text-xs text-gray-500 uppercase">Diferencia</p>
                    <p id="ganancia" class="text-lg font-bold text-emerald-600">0.00 €</p>
                </div>

            </div>
        </div>
    @endif



    <!-- MODAL CREAR / EDITAR -->
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">

            <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md border border-gray-200">

                <!-- Título -->
                <h3 class="text-lg font-semibold text-primary mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-primary rounded"></span>
                    {{ $modo === 'crear' ? 'Nueva categoría' : 'Editar categoría' }}
                </h3>

                <!-- Nombre -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" wire:model="nombre_categoria" class="form-input w-full">
                    @error('nombre_categoria')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea wire:model="descripcion_categoria" rows="3" class="form-input w-full"></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition text-sm">
                        Cancelar
                    </button>

                    <button type="button"
                        wire:click="{{ $modo === 'crear' ? 'crearCategoria' : 'actualizarCategoria' }}"
                        class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 shadow text-sm">
                        Guardar
                    </button>
                </div>

            </div>

        </div>
    @endif


    <!-- MODAL ELIMINAR -->
    @if ($showModalEliminar)
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">

            <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md border border-gray-200">

                <h3 class="text-lg font-semibold text-red-600 mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-red-600 rounded"></span>
                    Confirmar eliminación
                </h3>

                @if ($mensajeErrorEliminar)
                    <p class="text-sm text-red-600 font-medium mb-4">
                        {{ $mensajeErrorEliminar }}
                    </p>
                @else
                    <p class="text-sm text-gray-700 mb-4">
                        ¿Estás seguro de que deseas eliminar esta categoría?
                    </p>
                @endif

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" wire:click="$set('showModalEliminar', false)"
                        class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition text-sm">
                        Cancelar
                    </button>

                    @if (!$mensajeErrorEliminar)
                        <button type="button" wire:click="eliminarCategoria({{ $categoria_id }}, true)"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 shadow text-sm">
                            Eliminar
                        </button>
                    @endif
                </div>

            </div>

        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function recalcular() {

                const totalEl = document.getElementById('total_gastos');
                const presuEl = document.getElementById('importe_presu');
                const gananciaEl = document.getElementById('ganancia');

                // Si no existen (vista CREATE), evitar errores
                if (!totalEl || !presuEl || !gananciaEl) return;

                let total = 0;

                // Sumar todos los inputs con class sum-input
                document.querySelectorAll('.sum-input').forEach(el => {
                    let v = parseFloat(el.value);
                    if (!isNaN(v)) total += v;
                });

                totalEl.innerText = total.toFixed(2) + " €";

                // Obtener importe presupuestado desde su input (fuera de livewire)
                let inputPresu = document.querySelector('input[name="importe_presupuestado"]');
                let importePresu = parseFloat(inputPresu?.value || 0);

                // Mostrarlo en el bloque resumen
                presuEl.innerText = importePresu.toFixed(2) + " €";

                // Diferencia
                let diferencia = importePresu - total;
                gananciaEl.innerText = diferencia.toFixed(2) + " €";

                // Colores
                if (diferencia < 0) {
                    gananciaEl.classList.remove('text-emerald-600');
                    gananciaEl.classList.add('text-red-600');
                } else {
                    gananciaEl.classList.remove('text-red-600');
                    gananciaEl.classList.add('text-emerald-600');
                }
            }

            // Recalcular al escribir
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('sum-input')) {
                    recalcular();
                }
            });

            setTimeout(recalcular, 200);
        });
    </script>




</div>
