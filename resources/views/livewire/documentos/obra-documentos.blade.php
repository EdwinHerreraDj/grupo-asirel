<div>
    <div class="p-6 bg-white dark:bg-slate-800 rounded-lg shadow-sm border dark:border-gray-700">
        <h1 class="text-lg font-semibold text-gray-100 mb-4 bg-primary p-3 rounded">
            Documentación Obra | {{ $obra->nombre }}
        </h1>

        <form wire:submit.prevent="subirDocumento"
            class="space-y-6 bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <!-- Tipo de Documento -->
            <div>
                <label for="tipo" class="block font-medium text-gray-700 dark:text-gray-200 mb-2">Tipo de
                    Documento:</label>
                <select id="tipo" wire:model="tipo"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400 bg-gray-50 dark:bg-slate-900 text-gray-700 dark:text-gray-200 transition">
                    <option value="">Seleccione un tipo</option>
                    <option>ADJUDICACIÓN</option>
                    <option>CONTRATO</option>
                    <option>PLAN DE SEGURIDAD Y SALUD</option>
                    <option>PLAN DE GESTIÓN DE RESIDUOS</option>
                    <option>ACTA DE REPLANTEO</option>
                    <option>ACTA DE RECEPCIÓN</option>
                    <option>APROBACIÓN PLAN DE SEGURIDAD Y SALUD</option>
                    <option>APROBACIÓN PLAN DE GESTIÓN DE RESIDUOS</option>
                    <option>APERTURA CENTRO DE TRABAJO</option>
                </select>
                @error('tipo')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Archivo -->
            <div>
                <label for="archivo" class="block font-medium text-gray-700 dark:text-gray-200 mb-2">Archivo:</label>
                <input type="file" id="archivo" wire:model="archivo"
                    class="block w-full text-sm text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-slate-900
                   focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-400
                   file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                   file:text-sm file:font-medium file:bg-blue-600 file:text-white
                   hover:file:bg-blue-700 transition-all">
                @error('archivo')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror

                <div wire:loading wire:target="archivo" class="w-full mt-2">
                    <div class="h-1.5 w-full bg-blue-100 rounded-full overflow-hidden">
                        <div class="h-1.5 bg-blue-600 animate-[progress_1.5s_linear_infinite]"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Subiendo archivo...</p>

                    <style>
                        @keyframes progress {
                            0% {
                                width: 0%;
                            }

                            50% {
                                width: 70%;
                            }

                            100% {
                                width: 100%;
                            }
                        }
                    </style>
                </div>



            </div>

            <!-- Botón -->
            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i class="mgc_upload_2_line text-lg"></i>
                    Subir Documento
                </button>
            </div>
        </form>

    </div>

    <div class="mt-8 p-6 bg-white dark:bg-slate-800 rounded-lg shadow-sm border dark:border-gray-700">
        <h2 class="text-lg font-semibold mb-4">Documentos Subidos</h2>

        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                <tr>
                    <th class="px-3 py-2 text-left">ID</th>
                    <th class="px-3 py-2 text-left">Tipo</th>
                    <th class="px-3 py-2 text-left">Fecha</th>
                    <th class="px-3 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documentos as $i => $doc)
                    <tr class="border-b dark:border-gray-700">
                        <td class="px-3 py-2">{{ $i + 1 }}</td>
                        <td class="px-3 py-2">{{ $doc->tipo }}</td>
                        <td class="px-3 py-2">{{ $doc->created_at->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-center flex justify-center gap-2">
                            <a data-pdf="{{ asset('storage/' . $doc->archivo) }}"
                                class="open-pdf-modal inline-flex items-center justify-center w-9 h-9 
                            bg-blue-600 text-white rounded-full shadow-sm hover:bg-blue-700 
                                hover:shadow-md transition-all duration-200 cursor-pointer"
                                title="Ver documento PDF">
                                <i class="mgc_eye_2_fill text-lg"></i>
                            </a>


                            <a href="{{ asset('storage/' . $doc->archivo) }}" target="_blank"
                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                Descargar
                            </a>
                            <button type="button" onclick="confirmarEliminacion({{ $doc->id }})"
                                class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">No hay documentos subidos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
