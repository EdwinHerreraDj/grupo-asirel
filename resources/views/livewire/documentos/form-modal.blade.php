<div>
    @if ($abierto)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cerrar()">

            <div class="w-full max-w-xl bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="flex items-start justify-between px-6 py-5 border-b border-gray-200">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $modo === 'reemplazar' ? 'Reemplazar documento' : 'Subir documento' }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $modo === 'reemplazar'
                                ? 'El archivo anterior se eliminará al confirmar.'
                                : 'Solo se permite un documento por tipo. Formato PDF hasta 20 MB.' }}
                        </p>
                    </div>
                    <button type="button" wire:click="cerrar" class="text-gray-400 hover:text-gray-600">
                        <i class="mgc_close_line text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="px-6 py-5 space-y-5">
                    {{-- Tipo --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700">Tipo de documento</label>
                        <select wire:model="tipoId"
                            class="mt-1 form-select w-full"
                            @if ($modo === 'reemplazar') disabled @endif>
                            <option value="">Selecciona un tipo</option>
                            @foreach ($tipos as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                        @error('tipoId')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Archivo / drag&drop --}}
                    <div x-data="{
                            arrastrando: false,
                            nombreArchivo: '',
                            tamano: '',
                            errorLocal: '',
                            formato(bytes) {
                                if (!bytes) return '';
                                const kb = bytes / 1024;
                                return kb >= 1024 ? (kb / 1024).toFixed(1) + ' MB' : Math.round(kb) + ' KB';
                            },
                            reset() {
                                this.nombreArchivo = '';
                                this.tamano = '';
                                if (this.$refs.input) this.$refs.input.value = '';
                            },
                            validar(file) {
                                this.errorLocal = '';
                                if (!file) return;
                                if (file.type !== 'application/pdf') {
                                    this.errorLocal = 'Solo se permiten archivos PDF.';
                                    this.reset();
                                    return;
                                }
                                if (file.size > 20 * 1024 * 1024) {
                                    this.errorLocal = 'El archivo supera los 20 MB.';
                                    this.reset();
                                    return;
                                }
                                this.nombreArchivo = file.name;
                                this.tamano = this.formato(file.size);
                            }
                        }"
                        x-on:livewire-upload-finish="arrastrando = false"
                        class="relative">

                        <label class="text-sm font-medium text-gray-700 block mb-1">Archivo PDF</label>

                        <label for="archivo-input"
                            x-on:dragover.prevent="arrastrando = true"
                            x-on:dragleave.prevent="arrastrando = false"
                            x-on:drop.prevent="
                                arrastrando = false;
                                if ($event.dataTransfer.files.length) {
                                    const dt = new DataTransfer();
                                    dt.items.add($event.dataTransfer.files[0]);
                                    $refs.input.files = dt.files;
                                    $refs.input.dispatchEvent(new Event('change', { bubbles: true }));
                                    validar($event.dataTransfer.files[0]);
                                }
                            "
                            :class="arrastrando ? 'border-primary bg-primary/5' : 'border-gray-300 bg-gray-50'"
                            class="flex flex-col items-center justify-center gap-2 p-6 border-2 border-dashed rounded-xl cursor-pointer transition">

                            <template x-if="!nombreArchivo">
                                <div class="text-center">
                                    <i class="mgc_upload_2_line text-3xl text-gray-400 mb-1"></i>
                                    <p class="text-sm text-gray-600">
                                        Arrastra el PDF aquí o <span class="text-primary font-medium">haz clic</span>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">Máximo 20 MB</p>
                                </div>
                            </template>

                            <template x-if="nombreArchivo">
                                <div class="text-center w-full">
                                    <div class="flex items-center justify-center gap-2 text-red-600 mb-1">
                                        <i class="mgc_file_pdf_line text-2xl"></i>
                                        <span class="text-sm font-medium text-gray-800 truncate max-w-[280px]"
                                            x-text="nombreArchivo"></span>
                                    </div>
                                    <p class="text-xs text-gray-500" x-text="tamano"></p>
                                    <p class="text-xs text-primary mt-1">Haz clic para cambiar</p>
                                </div>
                            </template>
                        </label>

                        <input id="archivo-input" type="file" accept="application/pdf,.pdf"
                            wire:model="archivo" x-ref="input" class="hidden"
                            x-on:change="validar($event.target.files[0])">

                        {{-- Progreso de subida --}}
                        <div wire:loading wire:target="archivo" class="mt-2">
                            <div class="h-1.5 w-full bg-blue-100 rounded-full overflow-hidden">
                                <div class="h-full bg-primary animate-pulse"
                                    x-data="{}"
                                    x-on:livewire-upload-progress="$el.style.width = $event.detail.progress + '%'"
                                    style="width: 0%; transition: width 0.2s;">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Subiendo archivo…</p>
                        </div>

                        <template x-if="errorLocal">
                            <p class="text-red-600 text-xs mt-2" x-text="errorLocal"></p>
                        </template>

                        @error('archivo')
                            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Fecha vencimiento --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700">Fecha de vencimiento (opcional)</label>
                        <input type="date" wire:model="fechaVencimiento" class="mt-1 form-input w-full">
                        <p class="text-xs text-gray-400 mt-1">Déjala vacía si el documento no caduca.</p>
                        @error('fechaVencimiento')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="cerrar"
                            class="px-4 py-2 rounded-xl text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="guardar,archivo"
                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-primary text-white hover:bg-primary/90 active:scale-[0.97] transition-all shadow-sm disabled:opacity-60">
                            <span wire:loading.remove wire:target="guardar">
                                {{ $modo === 'reemplazar' ? 'Reemplazar' : 'Subir documento' }}
                            </span>
                            <span wire:loading wire:target="guardar">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
