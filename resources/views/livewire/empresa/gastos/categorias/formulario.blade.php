<div>
    <form wire:submit.prevent="guardar" class="space-y-5">

        {{-- Preview código --}}
        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Código autogenerado</p>
                <p class="mt-0.5 font-mono font-bold text-slate-900 text-base">{{ $codigo ?? '—' }}</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium
                {{ $nivel === 1 ? 'border-cyan-200 bg-cyan-50 text-cyan-700' : 'border-slate-200 bg-white text-slate-600' }}">
                <i class="{{ $nivel === 1 ? 'mgc_folder_2_fill' : 'mgc_subdirectory_line' }}"></i>
                Nivel {{ $nivel }} · {{ $nivel === 1 ? 'Padre' : 'Subcategoría' }}
            </span>
        </div>

        {{-- Categoría padre --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Categoría padre</label>
            <select wire:model.live="parent_id"
                class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500 disabled:bg-slate-50 disabled:text-slate-500"
                @if ($tieneHijos) disabled @endif>
                <option value="">— Sin padre (categoría principal) —</option>
                @foreach ($categoriasPadre as $p)
                    <option value="{{ $p->id }}">{{ $p->codigo }} · {{ $p->nombre }}</option>
                @endforeach
            </select>
            @if ($tieneHijos)
                <p class="mt-1 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 flex items-start gap-2">
                    <i class="mgc_information_line mt-0.5"></i>
                    <span>No puedes cambiar el padre porque esta categoría tiene subcategorías.</span>
                </p>
            @endif
        </div>

        {{-- Nombre --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                Nombre <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model.defer="nombre"
                placeholder="Ej: Suministros de oficina"
                class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
            @error('nombre') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Descripción --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
            <textarea wire:model.defer="descripcion" rows="3"
                placeholder="Breve descripción de qué tipo de gastos entran en esta categoría…"
                class="w-full rounded-xl border-slate-300 text-sm resize-none focus:border-cyan-500 focus:ring-cyan-500"></textarea>
        </div>

        {{-- Botones --}}
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-200">
            <button type="button" wire:click="$parent.cerrarModal()"
                class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                Cancelar
            </button>
            <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow hover:from-cyan-500 hover:to-blue-500">
                <i class="mgc_save_line"></i>
                {{ $modo === 'editar' ? 'Guardar cambios' : 'Crear categoría' }}
            </button>
        </div>
    </form>
</div>
