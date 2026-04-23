<form wire:submit.prevent="guardar" class="space-y-5">

    {{-- Concepto --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Concepto <span class="text-red-500">*</span>
        </label>
        <input type="text" wire:model.defer="concepto"
            placeholder="Ej: Alquiler oficina febrero"
            class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
        @error('concepto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Número factura --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Número factura</label>
            <input type="text" wire:model.defer="numero_factura"
                placeholder="Ej: A-2026/042"
                class="w-full rounded-xl border-slate-300 text-sm font-mono focus:border-cyan-500 focus:ring-cyan-500">
            @error('numero_factura') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Importe --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Importe (€) <span class="text-red-500">*</span>
            </label>
            <input type="number" step="0.01" min="0" wire:model.defer="importe"
                placeholder="0,00"
                class="w-full rounded-xl border-slate-300 text-sm font-mono focus:border-cyan-500 focus:ring-cyan-500">
            @error('importe') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Fecha factura --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Fecha factura <span class="text-red-500">*</span>
            </label>
            <input type="date" wire:model.defer="fecha_factura"
                class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
            @error('fecha_factura') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Fecha contable --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha contable</label>
            <input type="date" wire:model.defer="fecha_contable"
                class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
            @error('fecha_contable') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Fecha vencimiento --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Vencimiento</label>
            <input type="date" wire:model.defer="fecha_vencimiento"
                class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
            @error('fecha_vencimiento') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Categoría --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Categoría <span class="text-red-500">*</span>
        </label>
        <select wire:model.defer="categoria_id"
            class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
            <option value="">— Seleccionar categoría —</option>
            @foreach ($categoriasPadre as $padre)
                <optgroup label="{{ $padre->codigo }} - {{ $padre->nombre }}">
                    @foreach ($padre->children as $hijo)
                        <option value="{{ $hijo->id }}">— {{ $hijo->codigo }} - {{ $hijo->nombre }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('categoria_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Especificación --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Especificación</label>
        <textarea rows="3" wire:model.defer="especificacion"
            placeholder="Detalle adicional del gasto…"
            class="w-full rounded-xl border-slate-300 text-sm resize-none focus:border-cyan-500 focus:ring-cyan-500"></textarea>
        @error('especificacion') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Factura archivo --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Factura (PDF / imagen)</label>

        <label class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-slate-50/40 hover:border-cyan-400 hover:bg-cyan-50/40 transition">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 shrink-0">
                <i class="mgc_upload_2_line text-xl"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-700">
                    {{ $factura ? 'Archivo seleccionado' : 'Arrastra un archivo o haz clic para seleccionar' }}
                </p>
                @if ($factura)
                    <p class="text-xs text-emerald-700 truncate flex items-center gap-1.5 mt-0.5">
                        <i class="mgc_check_circle_line"></i>
                        <span class="truncate">{{ $factura->getClientOriginalName() }}</span>
                    </p>
                @else
                    <p class="text-xs text-slate-500 mt-0.5">PDF o imagen · máx 4 MB</p>
                @endif
            </div>
            <input type="file" wire:model="factura" class="hidden" accept="application/pdf,image/*">
        </label>

        <div wire:loading wire:target="factura" class="text-xs text-slate-500 mt-2">Subiendo archivo…</div>

        @error('factura') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Botones --}}
    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-200">
        <button type="button" wire:click="$dispatch('cerrarModal')"
            class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
            Cancelar
        </button>
        <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow hover:from-cyan-500 hover:to-blue-500">
            <i class="mgc_save_line"></i> Guardar gasto
        </button>
    </div>
</form>
