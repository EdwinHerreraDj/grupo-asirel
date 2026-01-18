<div class="p-3 relative">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold tracking-tight text-gray-800 flex items-center gap-2">
            <i class="mgc_edit_2_line text-primary text-3xl"></i>
            {{ $cliente_id ? 'Editar cliente' : 'Nuevo cliente' }}
        </h2>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="guardar" class="space-y-5">

        <!-- Nombre -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Nombre *
            </label>
            <input type="text" wire:model.defer="nombre"
                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                placeholder="Nombre del cliente" />
            @error('nombre')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- CIF - Teléfono -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    CIF
                </label>
                <input type="text" wire:model.defer="cif"
                    class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                    placeholder="CIF del cliente" />
                @error('cif')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Teléfono
                </label>
                <input type="text" wire:model.defer="telefono"
                    class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                    placeholder="Teléfono del cliente" />
            </div>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Email
            </label>
            <input type="email" wire:model.defer="email"
                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                placeholder="Email del cliente" />
        </div>

        <!-- Dirección -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Dirección
            </label>
            <input type="text" wire:model.defer="direccion"
                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                placeholder="Calle, número, piso, puerta..." />
        </div>

        <!-- Notas -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Descripción
            </label>
            <textarea wire:model.defer="descripcion" rows="3"
                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"></textarea>
        </div>

        <!-- Checkbox -->
        <div class="flex items-center gap-3 mt-2">
            <input type="checkbox" wire:model="activo"
                class="h-5 w-5 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer" />
            <span class="text-gray-700 font-medium">
                Cliente activo
            </span>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
            <x-btns.cancel wire:click="$dispatch('cerrarModalCliente')" type="button">
                Cancelar
            </x-btns.cancel>

            <x-btns.save type="submit">
                Guardar
            </x-btns.save>
        </div>

    </form>

    
</div>
