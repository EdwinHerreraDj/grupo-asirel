<div>
    {{-- HEADER --}}
    <div class="card p-5 mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <i class="mgc_folder_open_line text-primary"></i>
                Documentación de la obra
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ $obra->nombre }}</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" x-data x-on:click="$dispatch('abrir-gestion-tipos')"
                class="btn btn-sm bg-gray-100 text-gray-700 hover:bg-gray-200">
                <i class="mgc_settings_3_line me-1"></i> Gestionar tipos
            </button>
            <button type="button" x-data x-on:click="$dispatch('abrir-form-documento')"
                class="btn btn-sm bg-primary/10 text-primary hover:bg-primary hover:text-white">
                <i class="mgc_upload_2_line me-1"></i> Subir documento
            </button>
        </div>
    </div>

    {{-- BANNER INFORMATIVO --}}
    <div x-data="{ abierto: true }" x-show="abierto"
        class="relative p-4 bg-purple-50 border-l-4 border-purple-500 text-purple-800 rounded-lg mb-4">
        <button type="button" x-on:click="abierto = false"
            class="absolute top-2 right-2 text-gray-500 hover:text-red-500" aria-label="Cerrar">
            <i class="mgc_close_line text-lg"></i>
        </button>
        <h2 class="font-semibold mb-1">Gestión de documentación</h2>
        <p class="text-sm">
            Solo se permite <strong>un documento por tipo</strong> (sube un ZIP si necesitas agrupar varios).
            Si ya existe un documento del tipo, la acción será <strong>reemplazar</strong>.
            Formato admitido: <strong>PDF</strong> (hasta 20 MB).
        </p>
    </div>

    {{-- TIPOS PENDIENTES --}}
    @if ($tiposPendientes->isNotEmpty())
        <div class="card p-5 mb-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <i class="mgc_alarm_2_line text-amber-500"></i>
                Tipos pendientes de subir
                <span class="text-xs font-normal text-gray-400">({{ $tiposPendientes->count() }})</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach ($tiposPendientes as $tipo)
                    <div class="group flex items-center justify-between gap-3 p-3 rounded-xl
                        border border-dashed border-gray-300 bg-gray-50 hover:bg-amber-50 hover:border-amber-300
                        transition">
                        <div class="flex items-center gap-2 min-w-0">
                            <i class="mgc_file_new_line text-gray-400 text-lg shrink-0"></i>
                            <span class="text-sm text-gray-700 truncate">{{ $tipo->nombre }}</span>
                        </div>
                        <button type="button" x-data
                            x-on:click="$dispatch('abrir-form-documento', { tipoId: {{ $tipo->id }} })"
                            class="text-xs font-medium text-primary hover:underline shrink-0">
                            Subir
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- DOCUMENTOS SUBIDOS --}}
    <div class="card p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="mgc_check_circle_line text-emerald-500"></i>
            Documentos subidos
            <span class="text-xs font-normal text-gray-400">({{ $documentos->count() }})</span>
        </h3>

        @if ($documentos->isEmpty())
            <div class="text-center py-10 text-gray-500">
                <i class="mgc_folder_open_line text-4xl text-gray-300 mb-2"></i>
                <p class="text-sm">Todavía no se ha subido ningún documento.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($documentos as $doc)
                    @php
                        $url = asset('storage/' . $doc->archivo);
                        $nombre = $doc->nombre_original ?? basename($doc->archivo);
                        $vencido = $doc->fecha_vencimiento && $doc->fecha_vencimiento->isPast();
                        $porVencer = $doc->fecha_vencimiento && ! $vencido && $doc->fecha_vencimiento->diffInDays(now()) <= 30;
                        $tamanoKb = $doc->size ? round($doc->size / 1024) : null;
                        $tamanoTexto = $tamanoKb ? ($tamanoKb >= 1024 ? round($tamanoKb / 1024, 1) . ' MB' : $tamanoKb . ' KB') : null;
                    @endphp

                    <div class="border border-gray-200 rounded-xl p-4 bg-white hover:shadow-md transition flex flex-col gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                                <i class="mgc_file_pdf_line text-xl"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-xs uppercase tracking-wide text-gray-400 font-semibold">
                                    {{ $doc->documentoTipo->nombre ?? $doc->tipo }}
                                </p>
                                <p class="text-sm font-medium text-gray-800 truncate" title="{{ $nombre }}">
                                    {{ $nombre }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $doc->created_at->format('d/m/Y') }}
                                    @if ($tamanoTexto)
                                        · {{ $tamanoTexto }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($doc->fecha_vencimiento)
                            <div class="text-xs px-2 py-1 rounded-md
                                @if ($vencido) bg-red-50 text-red-700
                                @elseif($porVencer) bg-amber-50 text-amber-700
                                @else bg-emerald-50 text-emerald-700 @endif">
                                <i class="mgc_calendar_line me-1"></i>
                                @if ($vencido)
                                    Vencido el {{ $doc->fecha_vencimiento->format('d/m/Y') }}
                                @elseif($porVencer)
                                    Vence el {{ $doc->fecha_vencimiento->format('d/m/Y') }}
                                @else
                                    Vence {{ $doc->fecha_vencimiento->format('d/m/Y') }}
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center gap-1 mt-auto">
                            <button type="button" x-data
                                x-on:click="$dispatch('abrir-visor', { url: '{{ $url }}', nombre: @js($nombre) })"
                                class="flex-1 btn btn-sm bg-blue-50 text-blue-700 hover:bg-blue-100"
                                title="Ver">
                                <i class="mgc_eye_2_line"></i>
                            </button>

                            <a href="{{ $url }}" download="{{ $nombre }}"
                                class="flex-1 btn btn-sm bg-gray-100 text-gray-700 hover:bg-gray-200 flex items-center justify-center"
                                title="Descargar">
                                <i class="mgc_download_2_line"></i>
                            </a>

                            <button type="button" x-data
                                x-on:click="$dispatch('abrir-form-documento', { documentoId: {{ $doc->id }} })"
                                class="flex-1 btn btn-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100"
                                title="Reemplazar">
                                <i class="mgc_refresh_2_line"></i>
                            </button>

                            <button type="button" wire:click="abrirEliminar({{ $doc->id }})"
                                class="flex-1 btn btn-sm bg-red-50 text-red-700 hover:bg-red-100"
                                title="Eliminar">
                                <i class="mgc_delete_line"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- MODAL ELIMINAR --}}
    @if ($documentoAEliminarId)
        <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600">
                        <i class="mgc_warning_line text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Eliminar documento</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                        El archivo se eliminará definitivamente del sistema.
                        <br>
                        <span class="text-red-600 font-medium">No se puede deshacer.</span>
                    </p>
                </div>

                <div class="mt-6 px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                    <button wire:click="cancelarEliminar"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                        Cancelar
                    </button>
                    <button wire:click="eliminar" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700 active:scale-[0.97] transition-all shadow-sm disabled:opacity-60">
                        <span wire:loading.remove wire:target="eliminar">Eliminar definitivamente</span>
                        <span wire:loading wire:target="eliminar">Eliminando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- VISOR PDF (Alpine, sin server state) --}}
    <div x-data="{ abierto: false, url: '', nombre: '' }"
        x-on:abrir-visor.window="abierto = true; url = $event.detail.url; nombre = $event.detail.nombre"
        x-on:keydown.escape.window="abierto = false; url = ''"
        x-show="abierto" x-cloak
        class="fixed inset-0 z-[9998] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
        x-on:click.self="abierto = false; url = ''">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col overflow-hidden border border-gray-200">
            <div class="flex items-center justify-between bg-gray-50 px-4 py-3 border-b border-gray-200">
                <div class="flex items-center gap-2 text-gray-700 min-w-0">
                    <i class="mgc_file_pdf_line text-red-600 text-xl"></i>
                    <span class="text-sm font-medium truncate" x-text="nombre"></span>
                </div>
                <button type="button" x-on:click="abierto = false; url = ''"
                    class="text-gray-500 hover:text-red-500 text-2xl leading-none">&times;</button>
            </div>
            <div class="flex-1 bg-gray-100">
                <iframe x-bind:src="url" class="w-full h-full" frameborder="0"></iframe>
            </div>
        </div>
    </div>

    {{-- Subcomponentes --}}
    <livewire:documentos.form-modal :obra="$obra" />
    <livewire:documentos.tipos-modal />
</div>
