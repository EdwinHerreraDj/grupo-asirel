<div>
    @if ($abierto)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cerrar()">

            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">

                {{-- HEADER --}}
                <div class="flex items-start justify-between px-6 py-5 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $obraId ? 'Editar obra' : 'Nueva obra' }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $obraId ? 'Modifica los datos de la obra.' : 'Completa los datos básicos de la obra.' }}
                        </p>
                    </div>

                    <button type="button" wire:click="cerrar" class="text-gray-400 hover:text-gray-600">
                        <i class="mgc_close_line text-xl"></i>
                    </button>
                </div>

                {{-- FORM --}}
                <form wire:submit.prevent="guardar" class="px-6 py-5 space-y-5">

                    {{-- Nombre --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700">Nombre de la obra</label>
                        <input type="text" wire:model.defer="nombre" class="mt-1 form-input w-full"
                            placeholder="Ej. Reforma calle mayor" autofocus>
                        @error('nombre')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Estado --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Estado</label>
                            <select wire:model.defer="estado" class="mt-1 form-select w-full">
                                <option value="planificacion">Planificación</option>
                                <option value="ejecucion">Ejecución</option>
                                <option value="finalizada">Finalizada</option>
                            </select>
                            @error('estado')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Importe presupuestado --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Importe presupuestado (€)</label>
                            <input type="number" step="0.01" min="0" wire:model.defer="importe_presupuestado"
                                class="mt-1 form-input w-full" placeholder="0.00">
                            @error('importe_presupuestado')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Fecha inicio --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Fecha de inicio</label>
                            <input type="date" wire:model.defer="fecha_inicio" class="mt-1 form-input w-full">
                            @error('fecha_inicio')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fecha fin --}}
                        <div>
                            <label class="text-sm font-medium text-gray-700">Fecha de fin</label>
                            <input type="date" wire:model.defer="fecha_fin" class="mt-1 form-input w-full">
                            @error('fecha_fin')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700">Descripción</label>
                        <textarea wire:model.defer="descripcion" rows="3" class="mt-1 form-textarea w-full"
                            placeholder="Notas u observaciones sobre la obra"></textarea>
                        @error('descripcion')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- FOOTER --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="cerrar"
                            class="px-4 py-2 rounded-xl text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                            Cancelar
                        </button>

                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-primary text-white hover:bg-primary/90 active:scale-[0.97] transition-all shadow-sm disabled:opacity-60">
                            <span wire:loading.remove wire:target="guardar">
                                {{ $obraId ? 'Guardar cambios' : 'Crear obra' }}
                            </span>
                            <span wire:loading wire:target="guardar">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
