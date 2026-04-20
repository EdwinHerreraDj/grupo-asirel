<div>
    @if ($abierto)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cerrar()">

            <div class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="flex items-start justify-between px-6 py-5 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Gestionar tipos de documento</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Añade o quita tipos del catálogo. Los tipos en uso no se pueden eliminar.
                        </p>
                    </div>
                    <button type="button" wire:click="cerrar" class="text-gray-400 hover:text-gray-600">
                        <i class="mgc_close_line text-xl"></i>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5">
                    {{-- Añadir nuevo --}}
                    <form wire:submit.prevent="agregar" class="flex flex-col sm:flex-row gap-2">
                        <div class="flex-1">
                            <input type="text" wire:model="nuevoTipo" placeholder="Nombre del nuevo tipo"
                                class="form-input w-full" maxlength="255">
                            @error('nuevoTipo')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                            class="btn bg-primary text-white hover:bg-primary/90 shrink-0">
                            <i class="mgc_add_line me-1"></i> Añadir
                        </button>
                    </form>

                    {{-- Listado --}}
                    <div class="border border-gray-200 rounded-xl divide-y divide-gray-100 max-h-80 overflow-y-auto">
                        @forelse ($tipos as $tipo)
                            <div class="flex items-center justify-between gap-3 px-4 py-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $tipo->nombre }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if ($tipo->documentos_count > 0)
                                            En uso en {{ $tipo->documentos_count }}
                                            {{ $tipo->documentos_count === 1 ? 'documento' : 'documentos' }}
                                        @else
                                            Sin uso
                                        @endif
                                    </p>
                                </div>

                                @if ($tipo->documentos_count > 0)
                                    <span class="text-xs text-gray-400 italic">No eliminable</span>
                                @else
                                    <button type="button" wire:click="confirmarEliminar({{ $tipo->id }})"
                                        class="text-red-600 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50"
                                        title="Eliminar tipo">
                                        <i class="mgc_delete_line"></i>
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="px-4 py-6 text-center text-sm text-gray-500">
                                No hay tipos definidos.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end">
                    <button type="button" wire:click="cerrar"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        {{-- Confirmaci\u00f3n de eliminar tipo --}}
        @if ($tipoAEliminarId)
            <div class="fixed inset-0 z-[10001] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
                <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 pt-6 text-center">
                        <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600">
                            <i class="mgc_warning_line text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Eliminar tipo</h3>
                        <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                            Se eliminará del catálogo el tipo
                            <span class="font-semibold text-gray-800">{{ $tipoAEliminarNombre }}</span>.
                            <br>
                            <span class="text-red-600 font-medium">No se puede deshacer.</span>
                        </p>
                    </div>

                    <div class="mt-6 px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                        <button type="button" wire:click="cancelarEliminar"
                            class="px-4 py-2 rounded-xl text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                            Cancelar
                        </button>
                        <button type="button" wire:click="eliminar" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700 active:scale-[0.97] transition-all shadow-sm disabled:opacity-60">
                            <span wire:loading.remove wire:target="eliminar">Eliminar</span>
                            <span wire:loading wire:target="eliminar">Eliminando…</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
