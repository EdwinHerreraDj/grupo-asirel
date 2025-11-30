<div>
    {{-- Formulario de creacion y edicion --}}
    <form wire:submit.prevent="guardar" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Nombre *</label>
            <input type="text" wire:model="nombre" placeholder="Ej: Construcciones Alminares S.L."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400">
            @error('nombre')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">CIF</label>
                <input type="text" wire:model="cif" placeholder="Ej: B12345678"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Teléfono</label>
                <input type="text" wire:model="telefono" placeholder="Ej: +34 958 123 456"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" wire:model="email" placeholder="Ej: info@miempresa.com"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
            @error('email')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Dirección</label>
            <input type="text" wire:model="direccion" placeholder="Ej: Calle Real, 25"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Ciudad</label>
                <input type="text" wire:model="ciudad" placeholder="Ej: Granada"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Provincia</label>
                <input type="text" wire:model="provincia" placeholder="Ej: Andalucía"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200">
            </div>
        </div>

        <div class="mb-6">
            <label for="logo" class="block font-medium text-gray-700 mb-2">Logo de la empresa <span
                    class="text-slate-500">(Dimensiones recomendadas 500x500px)</span>:</label>

            <div class="relative">
                <input type="file" id="logo" wire:model="logo"
                    class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50
                   focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-400
                   file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                   file:text-sm file:font-medium file:bg-blue-600 file:text-white
                   hover:file:bg-blue-700 transition-all">
            </div>

            @error('logo')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror


            @if ($empresa && $empresa->logo)
                <div class="mt-4 flex items-center gap-3">
                    <div
                        class="w-24 h-24 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-white dark:bg-slate-800 shadow-sm">
                        <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo actual"
                            class="w-full h-full object-contain p-2">
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Logo actual</p>
                </div>
            @endif
        </div>



        <div>
            <label class="block text-sm font-medium mb-1">Descripción</label>
            <textarea wire:model="descripcion" placeholder="Breve descripción de la empresa..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-3">
            {{-- Botón cancelar: usa tu sistema para cerrar el modal --}}
            <button type="button" data-fc-dismiss
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2 rounded-lg font-semibold shadow-sm transition-all duration-200">
                Cancelar
            </button>

            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold shadow-sm transition-all duration-200">
                {{ $modoEdicion ? 'Guardar cambios' : 'Crear empresa' }}
            </button>
        </div>
    </form>
</div>
