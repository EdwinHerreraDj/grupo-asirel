<form wire:submit.prevent="guardar" class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Concepto --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Concepto <span class="text-red-600">*</span></label>
        <input type="text" wire:model.defer="concepto"
            class="w-full rounded-xl border border-gray-300 px-3 py-2 shadow-sm 
                   focus:ring-primary focus:border-primary transition"
            placeholder="Describa el concepto del gasto" 
                   >
        @error('concepto')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Número factura --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Número factura</label>
        <input type="text" wire:model.defer="numero_factura"
            class="w-full rounded-xl border border-gray-300 px-3 py-2 shadow-sm
                   focus:ring-primary focus:border-primary transition"
                   placeholder="Número de la factura">
        @error('numero_factura')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Importe --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Importe (€) <span
                class="text-red-600">*</span></label>
        <input type="number" step="0.01" wire:model.defer="importe"
            class="w-full rounded-xl border border-gray-300 px-3 py-2 shadow-sm
                   focus:ring-primary focus:border-primary transition"
                   placeholder="0.00">
        @error('importe')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Fecha factura --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha factura <span
                class="text-red-600">*</span></label>
        <input type="date" wire:model.defer="fecha_factura"
            class="w-full rounded-xl border border-gray-300 px-3 py-2 shadow-sm
                   focus:ring-primary focus:border-primary transition">
        @error('fecha_factura')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Fecha contable --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha contable</label>
        <input type="date" wire:model.defer="fecha_contable"
            class="w-full rounded-xl border border-gray-300 px-3 py-2 shadow-sm
                   focus:ring-primary focus:border-primary transition">
        @error('fecha_contable')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Fecha vencimiento --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha vencimiento</label>
        <input type="date" wire:model.defer="fecha_vencimiento"
            class="w-full rounded-xl border border-gray-300 px-3 py-2 shadow-sm
                   focus:ring-primary focus:border-primary transition">
        @error('fecha_vencimiento')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Categoría --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría <span
                class="text-red-600">*</span></label>
        <select wire:model.defer="categoria_id"
            class="w-full rounded-xl border border-gray-300 px-3 py-2 shadow-sm
                   bg-white focus:ring-primary focus:border-primary transition">
            <option value="">-- Seleccionar --</option>

            @foreach ($categoriasPadre as $padre)
                <optgroup label="{{ $padre->codigo }} - {{ $padre->nombre }}">
                    @foreach ($padre->children as $hijo)
                        <option value="{{ $hijo->id }}">
                            — {{ $hijo->codigo }} - {{ $hijo->nombre }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('categoria_id')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Especificación --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Especificación</label>
        <textarea rows="3" wire:model.defer="especificacion"
            class="w-full rounded-xl border border-gray-300 px-3 py-2 shadow-sm
                   focus:ring-primary focus:border-primary transition"
                   placeholder="Describa la especificación del gasto"></textarea>
        @error('especificacion')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Archivo factura --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Factura (PDF / Imagen)</label>

        <div class="flex items-center gap-3">

            {{-- Botón seleccionar --}}
            <label
                class="px-4 py-2 rounded-xl bg-primary text-white font-medium cursor-pointer 
                   hover:bg-primary/90 transition inline-flex items-center gap-2">
                <i class="mgc_upload_2_line text-lg"></i>
                Seleccionar archivo
                <input type="file" wire:model="factura" class="hidden" />
            </label>

            {{-- Estado del archivo --}}
            <span class="text-sm flex items-center gap-2">

                @if ($factura)
                    <span class="inline-flex items-center gap-2 text-green-600 font-medium">
                        <i class="mgc_check_line text-lg"></i>

                        <span class="truncate max-w-[220px]">
                            {{ $factura->getClientOriginalName() }}
                        </span>
                    </span>
                @else
                    <span class="text-gray-500 italic">
                        Ningún archivo seleccionado
                    </span>
                @endif

            </span>

        </div>

        @error('factura')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>


    {{-- Botones --}}
    <div class="md:col-span-2 flex justify-end gap-3 mt-4">

        <button type="button" wire:click="$dispatch('cerrarModal')"
            class="px-5 py-2 rounded-full bg-gray-100 text-gray-700 font-medium
                   border border-gray-300 hover:bg-gray-200 transition">
            Cancelar
        </button>

        <button type="submit"
            class="px-5 py-2 rounded-full bg-primary text-white font-semibold
                   shadow hover:bg-primary/90 transition">
            Guardar
        </button>

    </div>

</form>
