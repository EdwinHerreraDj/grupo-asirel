<div class="p-3">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold tracking-tight text-gray-800 flex items-center gap-2">
            <i class="mgc_edit_2_line text-primary text-3xl"></i>
            {{ $proveedor_id ? 'Editar proveedor' : 'Nuevo proveedor' }}
        </h2>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="guardar" class="space-y-5">

        <!-- Nombre -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre *</label>
            <input type="text" wire:model.defer="nombre"
                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                placeholder="Nombre del proveedor" />
            @error('nombre')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- CIF - Teléfono -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">CIF</label>
                <input type="text" wire:model.defer="cif"
                    class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                    placeholder="CIF del proveedor" />
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                <input type="text" wire:model.defer="telefono"
                    class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                    placeholder="Teléfono del proveedor" />
            </div>
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
            <input type="email" wire:model.defer="email"
                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                placeholder="Email del proveedor" />
        </div>

        <!-- Dirección -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Dirección</label>
            <input type="text" wire:model.defer="direccion"
                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm"
                placeholder="Calle, número, piso, puerta..." />
        </div>

        <!-- Tipo de proveedor -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de proveedor</label>
            <select wire:model.defer="tipo"
                class="w-full rounded-xl border-gray-300 focus:border-primary focus:ring-primary shadow-sm">
                <option value="">Seleccione tipo</option>
                <option value="material">Material</option>
                <option value="mano_obra">Mano de obra</option>
                <option value="servicio">Servicio</option>
                <option value="mixto">Mixto</option>
            </select>
        </div>

        <!-- Checkbox -->
        <div class="flex items-center gap-3 mt-2">
            <input type="checkbox" wire:model="activo"
                class="h-5 w-5 text-primary rounded border-gray-300 focus:ring-primary cursor-pointer" />
            <span class="text-gray-700 font-medium">Proveedor activo</span>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
            
            <x-btns.cancel wire:click="$dispatch('cerrarModalProveedor')">
                Cancelar
            </x-btns.cancel>

            <!-- Botón guardar -->
            <x-btns.save type="submit">
                Guardar
            </x-btns.save>

        </div>


    </form>

</div>
