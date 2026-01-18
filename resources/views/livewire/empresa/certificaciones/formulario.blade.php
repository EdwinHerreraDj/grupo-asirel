<div class="overflow-y-auto">

    <form wire:submit.prevent="guardar" enctype="multipart/form-data" class="space-y-5">

        <!-- FECHA CERTIFICACIÓN -->
        <div>
            <label class="block font-medium mb-1">Fecha certificación *</label>
            <input type="date" wire:model.defer="fecha_ingreso"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            @error('fecha_ingreso')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- FECHA CONTABLE -->
        <div>
            <label class="block font-medium mb-1">Fecha contable</label>
            <input type="date" wire:model.defer="fecha_contable"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            @error('fecha_contable')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- CLIENTE -->
        <div>
            <label class="block font-medium mb-1">Cliente *</label>
            <select wire:model.defer="cliente_id"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                <option value="">-- Seleccionar cliente --</option>
                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                @endforeach
            </select>
            @error('cliente_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- NÚMERO CERTIFICACIÓN -->
        <div>
            <label class="block font-medium mb-1">Número certificación</label>
            <input type="text" wire:model.defer="numero_certificacion"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                placeholder="Ej: 001/2024">
            @error('numero_certificacion')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- OFICIO -->
        <div>
            <label class="block font-medium mb-1">Oficio *</label>
            <select wire:model.defer="obra_gasto_categoria_id"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                <option value="">-- Seleccionar oficio --</option>
                @foreach ($oficios as $oficio)
                    <option value="{{ $oficio->id }}">{{ $oficio->nombre }}</option>
                @endforeach
            </select>
            @error('obra_gasto_categoria_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- ESTADOS (SOLO VISUAL) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium mb-1">Estado certificación</label>
                <input type="text" value="Enviada" disabled
                    class="w-full rounded-xl border-gray-200 bg-gray-100 text-gray-700">
            </div>

            <div>
                <label class="block font-medium mb-1">Estado factura</label>
                <input type="text" value="—" disabled
                    class="w-full rounded-xl border-gray-200 bg-gray-100 text-gray-700">
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4">

            <!-- IVA -->
            <div>
                <label class="block font-medium mb-1">IVA (%)</label>
                <input type="number" step="0.01" wire:model.defer="iva_porcentaje"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                    placeholder="Iva"
                    >
            </div>

            <!-- IRPF -->
            <div>
                <label class="block font-medium mb-1">IRPF / Retención (%)</label>
                <input type="number" step="0.01" wire:model.defer="retencion_porcentaje"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                    placeholder="IRPF / Retención">
            </div>

        </div>

        <!-- BOTONES -->
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" wire:click="$dispatch('cerrarModal')"
                class="px-4 py-2 bg-gray-200 rounded-xl hover:bg-gray-300 transition">
                Cancelar
            </button>

            <button type="submit"
                class="px-5 py-2.5 rounded-xl bg-primary text-white font-semibold shadow-md hover:bg-primary/90 transition">
                Crear certificación
            </button>
        </div>

    </form>

</div>
