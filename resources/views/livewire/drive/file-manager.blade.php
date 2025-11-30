<div>
    {{-- Botón abrir modal --}}
    <button wire:click="openFileUploadModal"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition">
        <i class="mgc_upload_2_line"></i> Subir archivo
    </button>

    {{-- Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center px-4" x-data
            x-on:keydown.escape.window="$wire.closeModal()">

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 relative animate-fadeIn">

                {{-- Cabecera --}}
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <i class="mgc_upload_2_line text-blue-600"></i> Subir archivo
                    </h2>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <i class="mgc_close_line text-xl"></i>
                    </button>
                </div>

                {{-- Formulario --}}
                <form wire:submit.prevent="subirArchivo" class="space-y-6">

                    {{-- ID oculto --}}
                    <input type="hidden" wire:model.live="carpetaId">

                    {{-- Subida de archivo con arrastrar y soltar --}}
                    <div x-data="{
                        isDropping: false,
                        fileName: null,
                        handleDrop(e) {
                            this.isDropping = false;
                            const files = e.dataTransfer.files;
                            if (files.length > 0) {
                                this.fileName = files[0].name;
                                $wire.upload('archivo', files[0]);
                            }
                        },
                        handleFileSelect(e) {
                            const file = e.target.files[0];
                            if (file) {
                                this.fileName = file.name;
                            }
                        }}" x-on:dragover.prevent="isDropping = true"
                        x-on:dragleave.prevent="isDropping = false" x-on:drop.prevent="handleDrop($event)"
                        class="relative border-2 border-dashed rounded-xl px-6 py-8 text-center transition
                          cursor-pointer select-none
                        border-gray-300 dark:border-gray-600
                        hover:border-blue-500 dark:hover:border-blue-400"
                        :class="isDropping ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-500' : ''">
                        <input type="file" id="archivo" wire:model="archivo" x-on:change="handleFileSelect"
                            class="absolute inset-0 opacity-0 cursor-pointer" />

                        {{-- Estado por defecto --}}
                        <template x-if="!fileName">
                            <div class="flex flex-col items-center">
                                <i class="mgc_upload_2_line text-blue-500 text-3xl mb-2"></i>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Arrastra tu archivo aquí o haz clic para seleccionarlo
                                </p>
                            </div>
                        </template>

                        {{-- Estado mientras se arrastra --}}
                        <template x-if="isDropping">
                            <div class="flex flex-col items-center">
                                <i class="mgc_cloud_upload_fill text-blue-600 text-4xl mb-2"></i>
                                <p class="text-sm font-semibold text-blue-600">
                                    Suelta el archivo para subirlo
                                </p>
                            </div>
                        </template>

                        {{-- Previsualización del archivo seleccionado --}}
                        <template x-if="fileName">
                            <div class="flex flex-col items-center mt-2">
                                <i class="mgc_file_line text-blue-500 text-3xl mb-1"></i>
                                <p class="text-sm text-gray-700 dark:text-gray-300 truncate max-w-[250px]">
                                    <strong>Archivo seleccionado:</strong> <span x-text="fileName"></span>
                                </p>

                                {{-- Mensaje de archivo listo --}}
                                <div class="mt-2 text-green-600 text-sm font-medium"
                                    x-show="$wire.entangle('archivo').length > 0" x-transition>
                                    ✅ Archivo listo para subir
                                </div>
                            </div>
                        </template>
                    </div>

                    @error('archivo')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror

                    {{-- ⏳ Caducidad --}}
                    <div class="flex items-center gap-3">
                        <input id="caducidad" type="checkbox" wire:model.live="tieneCaducidad"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0">
                        <label for="caducidad" class="text-sm text-gray-700 dark:text-gray-300 select-none">
                            ¿Tiene fecha de caducidad?
                        </label>
                    </div>

                    {{-- Fecha de caducidad --}}
                    @if ($tieneCaducidad)
                        <div>
                            <label for="fechaCaducidad"
                                class="block text-sm font-semibold text-gray-800 dark:text-gray-100 mb-1">
                                Fecha de caducidad
                            </label>
                            <input id="fechaCaducidad" type="date" wire:model="fechaCaducidad"
                                class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            @error('fechaCaducidad')
                                <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- ⚙️ Botones --}}
                    <div class="flex justify-end gap-3 pt-5 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" wire:click="closeModal"
                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Cancelar
                        </button>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="subirArchivo">Subir</span>
                            <span wire:loading wire:target="subirArchivo" class="flex items-center gap-2">
                                <i class="mgc_loading_line animate-spin text-lg"></i> Cargando...
                            </span>
                        </button>
                    </div>

                </form>



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

{{-- Animación --}}
