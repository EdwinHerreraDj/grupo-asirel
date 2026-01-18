<div>
    {{-- Botón abrir modal --}}
    <button wire:click="openFileUploadModal"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition">
        <i class="mgc_upload_2_line"></i> Subir archivo
    </button>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center px-3 sm:px-4" x-data
            x-on:keydown.escape.window="$wire.closeModal()">

            {{-- CONTENEDOR --}}
            <div
                class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full
                       max-w-md sm:max-w-lg md:max-w-xl
                       max-h-[92vh] sm:max-h-[90vh]
                       overflow-hidden
                       animate-fadeIn">

                {{-- SCROLL INTERNO --}}
                <div class="p-4 sm:p-6 overflow-y-auto max-h-[92vh] sm:max-h-[90vh]">

                    {{-- Cabecera --}}
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <h2
                            class="text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <i class="mgc_upload_2_line text-blue-600"></i>
                            Subir archivos
                        </h2>

                        <button wire:click="closeModal" wire:loading.attr="disabled"
                            class="shrink-0 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <i class="mgc_close_line text-xl"></i>
                        </button>
                    </div>

                    {{-- Formulario --}}
                    <form wire:submit.prevent="subirArchivo" class="space-y-5 sm:space-y-6">

                        <input type="hidden" wire:model.defer="carpetaId">

                        {{-- DROPZONE --}}
                        <div x-data="{
                            isDropping: false,
                            filesCount: 0,
                            handleDrop(e) {
                                this.isDropping = false;
                                this.filesCount = e.dataTransfer.files.length;
                        
                                this.$refs.fileInput.files = e.dataTransfer.files;
                        
                                // 🔥 Esto es lo que hace que Livewire se entere
                                this.$refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                            },
                            handleFileSelect(e) {
                                this.filesCount = e.target.files.length;
                            }
                        }" x-on:dragover.prevent="isDropping = true"
                            x-on:dragleave.prevent="isDropping = false" x-on:drop.prevent="handleDrop($event)"
                            :class="isDropping ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-500' : ''"
                            class="relative border-2 border-dashed rounded-xl
                                   px-4 sm:px-6 py-6 sm:py-8
                                   text-center cursor-pointer
                                   transition border-gray-300 dark:border-gray-600
                                   hover:border-blue-500 dark:hover:border-blue-400">

                            <input x-ref="fileInput" type="file" multiple wire:model="archivo"
                                wire:loading.attr="disabled" class="absolute inset-0 opacity-0 cursor-pointer"
                                @change="handleFileSelect" />


                            {{-- Estado inicial --}}
                            <template x-if="filesCount === 0 && !isDropping">
                                <div class="flex flex-col items-center">
                                    <i class="mgc_upload_2_line text-blue-500 text-3xl sm:text-4xl mb-2"></i>
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                        Arrastra archivos aquí o haz clic para seleccionarlos
                                    </p>
                                </div>
                            </template>

                            {{-- Estado arrastrando --}}
                            <template x-if="isDropping">
                                <div class="flex flex-col items-center">
                                    <i class="mgc_cloud_upload_fill text-blue-600 text-4xl sm:text-5xl mb-2"></i>
                                    <p class="text-xs sm:text-sm font-semibold text-blue-600">
                                        Suelta los archivos para subirlos
                                    </p>
                                </div>
                            </template>

                            {{-- Archivos seleccionados --}}
                            <template x-if="filesCount > 0 && !isDropping">
                                <div class="flex flex-col items-center">
                                    <i class="mgc_file_line text-blue-500 text-3xl sm:text-4xl mb-1"></i>
                                    <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-300">
                                        <strong x-text="filesCount"></strong>
                                        archivo(s) seleccionados
                                    </p>
                                </div>
                            </template>
                        </div>

                        @error('archivo')
                            <p class="text-red-600 text-xs">{{ $message }}</p>
                        @enderror

                        {{-- Caducidad --}}
                        <div class="flex items-start gap-3">
                            <input id="caducidad" type="checkbox" wire:model="tieneCaducidad"
                                class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="caducidad"
                                class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 select-none leading-5">
                                ¿Aplicar fecha de caducidad a todos?
                            </label>
                        </div>

                        {{-- Fecha --}}
                        <div class="transition-opacity duration-200"
                            :class="$wire.tieneCaducidad ? 'opacity-100' : 'opacity-40 pointer-events-none'">

                            <label class="block text-xs sm:text-sm font-semibold mb-1">
                                Fecha de caducidad
                            </label>

                            <input type="date" wire:model="fechaCaducidad" :disabled="!$wire.tieneCaducidad"
                                class="w-full text-sm rounded-lg border-gray-300
                                       dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100
                                       focus:ring-2 focus:ring-blue-500">
                        </div>

                        @error('fechaCaducidad')
                            <p class="text-red-600 text-xs">{{ $message }}</p>
                        @enderror

                        {{-- Botones --}}
                        <div
                            class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-5
                                   border-t border-gray-200 dark:border-gray-700">

                            <button type="button" wire:click="closeModal" wire:loading.attr="disabled"
                                class="w-full sm:w-auto px-4 py-2 text-sm rounded-lg border border-gray-300
                                       dark:border-gray-600 dark:text-gray-200
                                       hover:bg-gray-100 dark:hover:bg-gray-700">
                                Cancelar
                            </button>

                            <button type="submit" wire:loading.attr="disabled" wire:target="archivo,subirArchivo"
                                class="w-full sm:w-auto relative inline-flex items-center justify-center gap-2 px-5 py-2.5
                                       bg-blue-600 text-white text-sm font-semibold rounded-lg
                                       shadow-sm transition hover:bg-blue-700
                                       focus:ring-2 focus:ring-blue-500
                                       disabled:opacity-60 disabled:cursor-not-allowed">

                                <span wire:loading.remove wire:target="archivo,subirArchivo"
                                    class="inline-flex items-center gap-2">
                                    <i class="mgc_upload_2_line text-base"></i>
                                    Subir archivos
                                </span>

                                <span wire:loading wire:target="archivo,subirArchivo"
                                    class="inline-flex items-center gap-2">
                                    <i class="mgc_loading_line animate-spin text-lg"></i>
                                    Cargando…
                                </span>
                            </button>

                        </div>

                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.25s ease-out;
        }
    </style>
</div>
