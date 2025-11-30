<div class="p-1">

    <!-- Título -->
    <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
        <i class="mgc_edit_2_line text-primary text-2xl"></i>
        {{ $modo === 'editar' ? 'Editar categoría' : 'Nueva categoría' }}
    </h2>

    <form wire:submit.prevent="guardar" class="space-y-5">

        <!-- Categoría Padre -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Categoría padre
            </label>

            <select wire:model="parent_id"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary"
                @if ($tieneHijos) disabled @endif>
                <option value="">-- Sin padre (categoría principal) --</option>

                @foreach ($categoriasPadre as $p)
                    <option value="{{ $p->id }}">{{ $p->codigo }} · {{ $p->nombre }}</option>
                @endforeach
            </select>

            @if ($tieneHijos)
                <p class="text-sm text-red-600 mt-1">
                    No puedes cambiar el padre porque esta categoría tiene subcategorías.
                </p>
            @endif
        </div>

        <!-- Código (oculto pero consistente) -->
        <input type="hidden" wire:model.live="codigo">

        <!-- Nombre -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nombre
            </label>

            <input type="text" wire:model.defer="nombre" id="inputNombre"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary">

            @error('nombre')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Descripción -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Descripción
            </label>

            <textarea wire:model.defer="descripcion" rows="3"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-primary focus:border-primary"></textarea>
        </div>

        <!-- Nivel -->
        <div class="text-sm text-gray-600">
            Nivel:
            <strong class="text-gray-800">
                {{ $nivel }} ({{ $nivel === 1 ? 'Categoría padre' : 'Subcategoría' }})
            </strong>
        </div>

        <!-- Botón Guardar -->
        <div class="flex justify-end">
            <button
                class="px-5 py-2 rounded-full bg-primary text-white font-medium shadow hover:bg-primary/90 transition">
                {{ $modo === 'editar' ? 'Actualizar' : 'Crear' }}
            </button>
        </div>

    </form>


</div>
