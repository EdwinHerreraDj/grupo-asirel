<div>
    <div class="space-y-5">

        {{-- Botón principal --}}
        <div>
            {{-- Navegación de niveles --}}
            <div class="flex flex-col gap-4 mb-6">

                {{-- FILA 1: Ruta + acciones principales --}}
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">

                    {{-- IZQUIERDA: ruta / atrás --}}
                    <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200 min-w-0">
                        @if ($currentFolderId !== 0)
                            <button wire:click="subirNivel"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-300 bg-white
                           hover:bg-blue-50 hover:border-blue-300 text-gray-700 hover:text-blue-600
                           dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 dark:hover:text-blue-400
                           transition shrink-0">
                                <i class="mgc_arrow_left_line text-base"></i>
                                <span class="hidden sm:inline">Atrás</span>
                            </button>

                            <span class="text-gray-300 dark:text-gray-600 shrink-0">/</span>

                            <div class="flex items-center gap-1 text-blue-600 dark:text-blue-400 truncate min-w-0">
                                <i class="mgc_folder_open_line text-base shrink-0"></i>
                                <span class="truncate">{{ $currentFolder->nombre }}</span>
                            </div>
                        @else
                            <div class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                                <i class="mgc_home_3_line text-lg"></i>
                                <span>Raíz</span>
                            </div>
                        @endif
                    </div>

                    {{-- DERECHA: acciones --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">

                        <button wire:click="abrirCaducidades"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl
                       bg-orange-100 text-orange-700 border border-orange-200
                       hover:bg-orange-200 transition w-full sm:w-auto">
                            <i class="mgc_alert_line text-lg"></i>
                            <span class="whitespace-nowrap">Documentos por vencer</span>
                        </button>

                        @if ($currentFolderId !== 0)
                            <div class="w-full sm:w-auto">
                                @livewire('drive.file-manager', ['carpetaId' => $currentFolderId], key('file-manager-' . $currentFolderId))
                            </div>
                        @endif

                        <button wire:click="toggleFormulario"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl
                       bg-blue-600 text-white text-sm font-semibold shadow-sm
                       hover:bg-blue-700 active:scale-[0.98]
                       focus:outline-none focus:ring-2 focus:ring-blue-400/60
                       transition w-full sm:w-auto">
                            <i class="mgc_add_circle_line text-lg"></i>
                            <span>{{ $mostrarFormulario ? 'Cancelar' : 'Nueva carpeta' }}</span>
                        </button>

                    </div>
                </div>

                {{-- FILA 2: buscador --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full">
                    <div class="relative flex-1 min-w-0">
                        <input type="text" wire:model.defer="search" placeholder="Buscar archivos o carpetas..."
                            class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-300 dark:border-gray-700 rounded-xl
                       bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100
                       focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" />

                        <i class="mgc_search_2_line absolute left-3 top-3 text-gray-400 dark:text-gray-500 text-lg"></i>

                        @if ($search)
                            <button wire:click="$set('search', '')"
                                class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                                <i class="mgc_close_circle_line text-lg"></i>
                            </button>
                        @endif
                    </div>

                    <button wire:click="buscar"
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl
                   bg-blue-600 text-white text-sm font-semibold shadow-sm
                   hover:bg-blue-700 focus:ring-2 focus:ring-blue-400/60 transition
                   w-full sm:w-auto whitespace-nowrap">
                        <i class="mgc_search_2_line"></i>
                        Buscar
                    </button>
                </div>

            </div>


            {{-- Separador --}}
            <div class="border-b border-gray-200 dark:border-gray-700 my-6"></div>





            @if ($showCaducidades)
                <div class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center px-4">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-5xl
                             max-h-[90vh] overflow-hidden flex flex-col">


                        {{-- Header --}}
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                Documentos con caducidad
                            </h2>

                            <button wire:click="cerrarCaducidades">
                                <i class="mgc_close_line text-xl"></i>
                            </button>
                        </div>

                        {{-- Filtro --}}
                        <div class="mb-4">
                            <select wire:model.live="filtroCaducidad"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                <option value="all">Todos</option>
                                <option value="expired">Vencidos</option>
                                <option value="2weeks">Vencen en 2 semanas</option>
                                <option value="2months">Vencen en 2 meses</option>
                                <option value="4months">Vencen en 4 meses</option>
                                <option value="6months">Vencen en 6 meses</option>
                            </select>
                        </div>

                        {{-- Tabla --}}
                        <div class="overflow-x-auto overflow-y-auto">
                            <table class="w-full text-sm">
                                <thead class="text-left text-gray-500 dark:text-gray-400 border-b">
                                    <tr>
                                        <th class="py-2">Documento</th>
                                        <th class="py-2">Tipo</th>
                                        <th class="py-2">Ruta</th>
                                        <th class="py-2">Fecha caducidad</th>
                                        <th class="py-2">Estado</th>
                                        <th class="py-2 text-center">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($this->documentosCaducidad as $doc)
                                        @php
                                            $fecha = \Carbon\Carbon::parse($doc->fecha_caducidad);
                                            $dias = now()->diffInDays($fecha, false);
                                        @endphp

                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                            {{-- Documento --}}
                                            <td class="py-2 font-medium text-gray-800 dark:text-gray-100">
                                                {{ $doc->nombre }}
                                            </td>

                                            {{-- Tipo --}}
                                            <td class="uppercase text-xs text-gray-600 dark:text-gray-300">
                                                {{ $doc->tipo }}
                                            </td>

                                            {{-- Ruta --}}
                                            <td class="text-xs text-gray-600 dark:text-gray-300">
                                                {{ $this->rutaDeCarpeta($doc->folder_id) }}
                                            </td>

                                            {{-- Fecha --}}
                                            <td class="text-sm">
                                                {{ $fecha->format('d/m/Y') }}
                                            </td>

                                            {{-- Estado --}}
                                            <td
                                                class="font-semibold
                                    @if ($fecha->isPast()) text-red-600
                                    @elseif ($dias <= 14) text-yellow-600
                                    @else text-green-600 @endif
                                ">
                                                @if ($fecha->isPast())
                                                    Vencido
                                                @elseif ($dias <= 14)
                                                    Próximo
                                                @else
                                                    En regla
                                                @endif
                                            </td>

                                            {{-- Acciones --}}
                                            <td class="flex items-center justify-center gap-3 py-2">
                                                @if (in_array($doc->tipo, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                    <button type="button"
                                                        class="open-pdf-modal text-blue-600 hover:text-blue-800"
                                                        data-pdf="{{ route('drive.ver', $doc->id) }}" title="Ver">
                                                        <i class="mgc_eye_2_line text-lg"></i>
                                                    </button>
                                                @endif

                                                <a href="{{ route('files.descargar', $doc->id) }}"
                                                    class="text-gray-600 hover:text-gray-900" title="Descargar">
                                                    <i class="mgc_download_2_line text-lg"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-6 text-center text-gray-500">
                                                No hay documentos para este filtro
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            @endif







            {{-- Formulario de nueva carpeta --}}
            @if ($mostrarFormulario)
                <div
                    class="relative p-5 border border-blue-200 rounded-xl shadow-md 
                    bg-white dark:bg-gray-800 dark:border-blue-900/40 
                        animate-fadeIn overflow-hidden transition-all duration-300 mb-10">

                    {{-- Línea lateral decorativa --}}
                    <div class="absolute left-0 top-0 h-full w-1 bg-blue-500/80 rounded-l-xl"></div>

                    {{-- Encabezado --}}
                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="flex items-center justify-center w-9 h-9 rounded-lg 
                       bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-300">
                            <i class="mgc_directory_line text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Crear nueva carpeta
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Organiza y gestiona tus archivos</p>
                        </div>
                    </div>

                    {{-- Input + Botones --}}
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        {{-- Input --}}
                        <input type="text" wire:model.defer="nuevoNombre"
                            class="w-full px-3.5 py-2 border border-gray-300 rounded-md text-gray-800 dark:text-white 
                       dark:bg-gray-800 dark:border-gray-600 placeholder-gray-400
                       focus:ring-1 focus:ring-blue-500 focus:border-blue-500 
                       transition-all duration-200 text-sm"
                            placeholder="Ejemplo: Documentación 2025">

                        {{-- Botones --}}
                        <div class="flex gap-2">
                            {{-- Guardar --}}
                            <button wire:click="crearCarpeta"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-md
                           bg-blue-600 text-white text-sm font-medium
                           hover:bg-blue-700 active:scale-[0.98]
                           focus:outline-none focus:ring-2 focus:ring-blue-300 
                           transition-all duration-200">
                                <i class="mgc_check_fill text-base"></i>
                                Guardar
                            </button>

                            {{-- Cancelar --}}
                            <button wire:click="toggleFormulario"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-md
                           border border-gray-300 bg-white text-gray-700 text-sm font-medium
                           hover:bg-gray-50 active:scale-[0.98]
                           dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600
                           dark:hover:bg-gray-700 focus:outline-none focus:ring-1 focus:ring-gray-400 
                           transition-all duration-200">
                                <i class="mgc_close_fill text-base"></i>
                                Cancelar
                            </button>
                        </div>
                    </div>

                    {{-- Error --}}
                    @error('nuevoNombre')
                        <p class="text-red-600 text-xs font-medium mt-2 dark:text-red-400 flex items-center gap-1.5">
                            <i class="mgc_warning_line text-sm"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
            @endif




            {{-- Listado de carpetas --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
                @foreach ($folders as $folder)
                    <div class="group relative p-4 rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-blue-50/40 
                    dark:from-gray-800 dark:to-gray-700 dark:border-gray-700 
                        shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 cursor-pointer"
                        wire:click="abrirCarpeta({{ $folder->id }})" x-data="{ open: false }"
                        :class="open ? 'z-[9999]' : 'z-0'">

                        {{-- Icono --}}
                        <div
                            class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 text-blue-600 
                            dark:bg-blue-900/60 dark:text-blue-300 shadow-inner mb-3 cursor-pointer hover:scale-105 transition-transform duration-200">
                            <i class="mgc_folder_2_fill text-3xl"></i>
                        </div>

                        {{-- Si está renombrando --}}
                        @if ($renamingId === $folder->id)
                            <div class="mt-1" @click.stop>
                                {{-- Campo de edición --}}
                                <input type="text" wire:model.defer="nombreEditado"
                                    wire:keydown.enter="guardarRenombrar"
                                    wire:keydown.escape="$set('renamingId', null)"
                                    class="w-full px-3.5 py-2 border border-gray-300 rounded-md text-sm text-gray-800 dark:text-white 
                        dark:bg-gray-800 dark:border-gray-600 placeholder-gray-400
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                        shadow-sm transition-all duration-200"
                                    placeholder="Nuevo nombre de carpeta..." autofocus>

                                {{-- Botones --}}
                                <div class="flex gap-2 mt-3">
                                    {{-- Guardar --}}
                                    <button wire:click.stop="guardarRenombrar"
                                        class="inline-flex items-center justify-center gap-1.5 px-4 py-1.5 rounded-md
                            bg-blue-600 text-white text-sm font-medium
                            hover:bg-blue-700 active:scale-[0.98]
                            focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 
                            transition-all duration-200">
                                        <i class="mgc_check_fill text-base"></i>
                                        Guardar
                                    </button>

                                    {{-- Cancelar --}}
                                    <button wire:click.stop="$set('renamingId', null)"
                                        class="inline-flex items-center justify-center gap-1.5 px-4 py-1.5 rounded-md border text-sm font-medium
                            border-gray-300 text-gray-700 bg-white hover:bg-gray-50 active:scale-[0.98]
                            dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700
                            focus:outline-none focus:ring-1 focus:ring-gray-400 
                            transition-all duration-200">
                                        <i class="mgc_close_line text-base"></i>
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- Nombre --}}
                            <p class="font-medium text-gray-800 dark:text-gray-100 truncate cursor-pointer">
                                {{ $folder->nombre }}
                            </p>

                            {{-- Fecha --}}
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $folder->created_at->diffForHumans() }}
                            </p>

                            {{-- Menú de acciones --}}
                            <div class="absolute top-3 right-3" @click.outside="open = false">
                                {{-- Botón de tres puntos --}}
                                <button @click.stop="open = !open" wire:ignore.self
                                    class="w-8 h-8 flex items-center justify-center rounded-full 
                        bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 
                        hover:bg-gray-200 dark:hover:bg-gray-700 shadow-sm transition">
                                    <i class="mgc_more_2_fill text-lg"></i>
                                </button>

                                {{-- Menú desplegable --}}
                                <div x-show="open" x-transition.opacity.duration.150ms @click.stop
                                    class="absolute right-0 mt-2 w-44 z-[99999]
                        bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                        rounded-xl shadow-xl ring-1 ring-black/5 overflow-hidden text-sm"
                                    style="display: none;">

                                    {{-- Encabezado opcional --}}
                                    <div
                                        class="px-3 py-2 text-[11px] uppercase tracking-wide text-gray-400 dark:text-gray-500 font-semibold">
                                        Acciones
                                    </div>

                                    <div class="border-t border-gray-100 dark:border-gray-700/50"></div>

                                    {{-- Mover --}}
                                    <button wire:click="abrirMover({{ $folder->id }}, 'folder')"
                                        @click="open = false"
                                        class="flex items-center gap-2 w-full px-4 py-2.5 text-left 
                            hover:bg-blue-50 dark:hover:bg-blue-900/30 text-gray-700 dark:text-gray-200 
                            transition duration-150">
                                        <i class="mgc_arrow_right_line text-blue-500 text-base"></i>
                                        <span>Mover</span>
                                    </button>

                                    {{-- Descargar ZIP --}}
                                    <a href="{{ route('folders.descargarCarpeta', $folder->id) }}"
                                        class="flex items-center gap-2 px-4 py-2.5 
                            hover:bg-green-50 dark:hover:bg-green-900/30 text-gray-700 dark:text-gray-200 
                            transition duration-150">
                                        <i class="mgc_archive_line text-green-500 text-base"></i>
                                        <span>Descargar ZIP</span>
                                    </a>

                                    {{-- Renombrar --}}
                                    <button wire:click.stop="iniciarRenombrar({{ $folder->id }})"
                                        @click="open = false"
                                        class="flex items-center gap-2 w-full px-4 py-2.5 
                            hover:bg-yellow-50 dark:hover:bg-yellow-900/30 text-gray-700 dark:text-gray-200 
                            transition duration-150">
                                        <i class="mgc_edit_2_line text-yellow-500 text-base"></i>
                                        <span>Renombrar</span>
                                    </button>

                                    {{-- Eliminar --}}
                                    <button wire:click.stop="confirmarEliminar({{ $folder->id }})"
                                        @click="open = false"
                                        class="flex items-center gap-2 w-full px-4 py-2.5 
                            text-red-600 hover:bg-red-50 dark:text-red-400 
                            dark:hover:bg-red-900/30 transition duration-150">
                                        <i class="mgc_delete_2_line text-red-500 text-base"></i>
                                        <span>Eliminar</span>
                                    </button>
                                </div>

                            </div>
                        @endif
                    </div>
                @endforeach

                {{--  Archivos --}}
                @foreach ($files as $file)
                    @php
                        $extension = strtolower(pathinfo($file->nombre, PATHINFO_EXTENSION));

                        $iconos = [
                            'pdf' => [
                                'icon' => 'mgc_pdf_fill',
                                'color' => 'text-red-600 bg-red-100 dark:bg-red-900/40 dark:text-red-300',
                            ],
                            'doc' => [
                                'icon' => 'mgc_doc_fill',
                                'color' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300',
                            ],
                            'docx' => [
                                'icon' => 'mgc_doc_fill',
                                'color' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/40 dark:text-blue-300',
                            ],
                            'xls' => [
                                'icon' => 'mgc_xls_fill',
                                'color' => 'text-green-600 bg-green-100 dark:bg-green-900/40 dark:text-green-300',
                            ],
                            'xlsx' => [
                                'icon' => 'mgc_xls_fill',
                                'color' => 'text-green-600 bg-green-100 dark:bg-green-900/40 dark:text-green-300',
                            ],
                            'ppt' => [
                                'icon' => 'mgc_ppt_fill',
                                'color' => 'text-orange-600 bg-orange-100 dark:bg-orange-900/40 dark:text-orange-300',
                            ],
                            'pptx' => [
                                'icon' => 'mgc_ppt_line',
                                'color' => 'text-orange-600 bg-orange-100 dark:bg-orange-900/40 dark:text-orange-300',
                            ],
                            'jpg' => [
                                'icon' => 'mgc_pic_2_fill',
                                'color' => 'text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300',
                            ],
                            'jpeg' => [
                                'icon' => 'mgc_pic_2_fill',
                                'color' => 'text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300',
                            ],
                            'png' => [
                                'icon' => 'mgc_pic_fill',
                                'color' => 'text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300',
                            ],
                            'gif' => [
                                'icon' => 'mgc_photo_album_fill',
                                'color' => 'text-pink-600 bg-pink-100 dark:bg-pink-900/40 dark:text-pink-300',
                            ],
                            'mp4' => [
                                'icon' => 'mgc_video_fill',
                                'color' => 'text-purple-600 bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300',
                            ],
                            'mov' => [
                                'icon' => 'mgc_video_fill',
                                'color' => 'text-purple-600 bg-purple-100 dark:bg-purple-900/40 dark:text-purple-300',
                            ],
                            'zip' => [
                                'icon' => 'mgc_drawer_2_fill',
                                'color' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-300',
                            ],
                            'rar' => [
                                'icon' => 'mgc_drawer_2_fill',
                                'color' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-300',
                            ],
                            'txt' => [
                                'icon' => 'mgc_copy_line',
                                'color' => 'text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300',
                            ],
                            'default' => [
                                'icon' => 'mgc_copy_line',
                                'color' => 'text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-300',
                            ],
                        ];

                        $icono = $iconos[$extension] ?? $iconos['default'];
                    @endphp

                    <div class="group relative p-4 rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 
                dark:from-gray-800 dark:to-gray-700 dark:border-gray-700 
                shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300"
                        x-data="{ open: false }" :class="open ? 'z-[9999]' : 'z-0'">

                        {{-- Ícono dinámico --}}
                        <div
                            class="flex items-center justify-center w-12 h-12 rounded-xl shadow-inner mb-3 {{ $icono['color'] }}">
                            <i class="{{ $icono['icon'] }} text-3xl"></i>
                        </div>

                        <p
                            class="text-xs mt-2 flex items-center font-medium
                @if ($file->fecha_caducidad && \Carbon\Carbon::parse($file->fecha_caducidad)->isPast()) text-red-600 
                @elseif ($file->fecha_caducidad && \Carbon\Carbon::parse($file->fecha_caducidad)->diffInDays(now()) <= 7) text-yellow-600 
                @else text-gray-500 dark:text-gray-400 @endif">

                            @if ($file->fecha_caducidad)
                                @if (\Carbon\Carbon::parse($file->fecha_caducidad)->isPast())
                                    <i class="mgc_alert_fill text-[13px]"></i>
                                @elseif (\Carbon\Carbon::parse($file->fecha_caducidad)->diffInDays(now()) <= 7)
                                    <i class="mgc_timer_line text-[13px]"></i>
                                @else
                                    <i class="mgc_calendar_line text-[13px]"></i>
                                @endif

                                Vence:
                                <span class="ml-1 font-semibold">
                                    {{ \Carbon\Carbon::parse($file->fecha_caducidad)->format('d/m/Y') }}
                                </span>
                            @endif
                        </p>

                        {{-- Nombre --}}
                        @if ($renamingFileId === $file->id)
                            <div class="mt-2" @click.stop>
                                <input type="text" wire:model.defer="nombreEditadoFile"
                                    wire:keydown.enter="guardarRenombrarArchivo"
                                    wire:keydown.escape="cancelarRenombrarArchivo"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm
                   dark:bg-gray-800 dark:border-gray-600 dark:text-white
                   focus:ring-2 focus:ring-blue-500"
                                    placeholder="Nuevo nombre..." autofocus>

                                @error('nombreEditadoFile')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror

                                <div class="flex gap-2 mt-2">
                                    <button wire:click="guardarRenombrarArchivo"
                                        class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-sm">
                                        Guardar
                                    </button>

                                    <button wire:click="cancelarRenombrarArchivo"
                                        class="px-3 py-1.5 rounded-md border border-gray-300 text-sm dark:border-gray-600 dark:text-gray-200">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        @else
                            <p class="font-medium text-gray-800 dark:text-gray-100 truncate">
                                {{ $file->nombre }}
                            </p>
                        @endif


                        {{-- Tamaño --}}
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ number_format($file->tamaño / 1024, 1) }} KB
                        </p>

                        {{-- Menú de opciones --}}
                        <div class="absolute top-3 right-3">
                            <div class="relative">
                                <button @click.stop="open = !open"
                                    class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <i class="mgc_more_2_fill text-gray-600 dark:text-gray-300"></i>
                                </button>

                                {{-- Menú desplegable --}}
                                <div x-show="open" @click.outside="open = false" x-transition
                                    class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg z-[99999]"
                                    style="display: none;">
                                    <ul class="text-sm text-gray-700 dark:text-gray-200">
                                        {{-- Mover --}}
                                        <li>
                                            <button wire:click="abrirMover({{ $file->id }}, 'file')"
                                                @click="open = false"
                                                class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                <i class="mgc_arrow_right_line mr-2"></i> Mover
                                            </button>
                                        </li>

                                        <li>
                                            <button wire:click.stop="iniciarRenombrarArchivo({{ $file->id }})"
                                                @click="open = false"
                                                class="w-full text-left px-4 py-2 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 transition">
                                                <i class="mgc_edit_2_line mr-2"></i> Renombrar
                                            </button>
                                        </li>


                                        @if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp']))
                                            <li>
                                                <button type="button" @click="open = false"
                                                    class="open-pdf-modal w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                                    data-pdf="{{ route('drive.ver', $file->id) }}">
                                                    <i class="mgc_eye_2_line mr-2"></i> Ver
                                                </button>
                                            </li>
                                        @endif

                                        {{-- Descargar --}}
                                        <li>
                                            <a href="{{ route('files.descargar', $file->id) }}" target="_blank"
                                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                <i class="mgc_download_2_line mr-2"></i> Descargar
                                            </a>
                                        </li>

                                        @if ($file->tipo === 'zip')
                                            <li>
                                                <button wire:click="descomprimirZip({{ $file->id }})"
                                                    @click="open = false"
                                                    class="w-full text-left px-4 py-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition">
                                                    <i class="mgc_archive_line mr-2"></i> Extrarer
                                                </button>
                                            </li>
                                        @endif

                                        {{-- Eliminar --}}
                                        <li>
                                            <button wire:click="confirmarEliminarArchivo({{ $file->id }})"
                                                @click="open = false"
                                                class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-800/40 transition">
                                                <i class="mgc_delete_2_line mr-2"></i> Eliminar
                                            </button>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Mensaje de no resultados --}}
                @if ($folders->isEmpty() && $files->isEmpty())
                    <div class="col-span-full text-center text-gray-500 dark:text-gray-400 py-10">
                        <i class="mgc_inbox_2_line text-5xl mb-3 text-gray-400 dark:text-gray-500"></i>
                        @if (trim($search) !== '')
                            <p class="text-sm">No se encontraron resultados para “{{ $search }}”.</p>
                        @else
                            <p class="text-sm">No hay contenido en esta carpeta.</p>
                        @endif
                    </div>
                @endif
            </div>

        </div>




        {{-- Animación --}}
        <style>
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-5px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fadeIn {
                animation: fadeIn 0.2s ease-in-out;
            }
        </style>

        {{-- Modal de confirmación de eliminado Carpetas --}}
        @if ($showDeleteModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center overflow-hidden" style="inset:0;"
                aria-modal="true" role="dialog" wire:ignore.self>
                {{-- Fondo oscuro que cubre TODO el viewport --}}
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
                    wire:click="cancelarEliminar">
                </div>

                {{-- Contenedor del modal --}}
                <div
                    class="relative w-full max-w-md mx-4 rounded-2xl border border-gray-200 dark:border-gray-700
                   bg-white dark:bg-gray-800 shadow-2xl animate-[fadeIn_.15s_ease-out]">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-xl flex items-center justify-center
                            bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300">
                                <i class="mgc_warning_fill text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Confirmar eliminación
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Esta acción no se puede deshacer.
                                </p>
                            </div>
                        </div>

                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                            ¿Seguro que deseas eliminar la carpeta
                            <span class="font-medium text-gray-900 dark:text-white">"{{ $deletingName }}"</span>?
                        </div>

                        {{-- Mensaje de error dentro del modal --}}
                        @if ($deleteError)
                            <p class="mt-4 text-sm font-medium text-red-600 dark:text-red-400 flex items-center gap-2">
                                <i class="mgc_warning_line text-base"></i> {{ $deleteError }}
                            </p>
                        @endif

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50
                           dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                                wire:click="cancelarEliminar">
                                Cancelar
                            </button>
                            <button
                                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center gap-2"
                                wire:click="eliminarConfirmado">
                                <i class="mgc_delete_2_fill text-lg"></i>
                                Sí, eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal de confirmación de eliminado Archivos --}}
        @if ($showDeleteFileModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center overflow-hidden" style="inset:0;"
                aria-modal="true" role="dialog" wire:ignore.self>
                {{-- Fondo oscuro con desenfoque --}}
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
                    wire:click="cancelarEliminarArchivo">
                </div>

                {{-- Contenedor del modal --}}
                <div
                    class="relative w-full max-w-md mx-4 rounded-2xl border border-gray-200 dark:border-gray-700
                   bg-white dark:bg-gray-800 shadow-2xl animate-[fadeIn_.15s_ease-out]">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-xl flex items-center justify-center
                            bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300">
                                <i class="mgc_warning_fill text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Confirmar eliminación
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Esta acción eliminará el archivo permanentemente.
                                </p>
                            </div>
                        </div>

                        {{-- Contenido del mensaje --}}
                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                            @if ($deleteFileError)
                                <p class="font-medium text-red-600 dark:text-red-400 flex items-center gap-2">
                                    <i class="mgc_warning_line text-base"></i> {{ $deleteFileError }}
                                </p>
                            @else
                                ¿Seguro que deseas eliminar el archivo
                                <span class="font-medium text-gray-900 dark:text-white">
                                    "{{ $deletingFileName }}"
                                </span>?
                            @endif
                        </div>

                        {{-- Botones de acción --}}
                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button
                                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50
                           dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700 transition"
                                wire:click="cancelarEliminarArchivo">
                                Cancelar
                            </button>
                            <button
                                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center gap-2"
                                wire:click="eliminarArchivoConfirmado">
                                <i class="mgc_delete_2_fill text-lg"></i>
                                Sí, eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(4px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            </style>
        @endif

        {{-- Modal para mover carpetas --}}
        @if ($showMoveModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center" aria-modal="true" role="dialog">
                {{-- Fondo semitransparente --}}
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showMoveModal', false)">
                </div>

                {{-- Contenedor del modal --}}
                <div
                    class="relative w-full max-w-md mx-4 rounded-2xl border border-gray-200 dark:border-gray-700
                    bg-white dark:bg-gray-800 shadow-2xl animate-[fadeIn_.15s_ease-out] p-6">

                    {{-- Cabecera --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <i class="mgc_arrow_right_line mr-1.5 text-blue-600 dark:text-blue-400"></i>
                            Mover {{ $movingType === 'file' ? 'archivo' : 'carpeta' }}
                        </h3>
                        <button wire:click="$set('showMoveModal', false)"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                            <i class="mgc_close_line text-xl"></i>
                        </button>
                    </div>

                    {{-- Texto de ayuda --}}
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 leading-relaxed">
                        Selecciona la carpeta destino donde quieres mover
                        {{ $movingType === 'file' ? 'este archivo' : 'esta carpeta' }}.
                    </p>

                    {{-- Select de destino --}}
                    <div class="relative">
                        <i class="mgc_folder_2_line absolute left-3 top-2.5 text-gray-400 dark:text-gray-500"></i>
                        <select wire:model="destinationFolderId"
                            class="w-full pl-10 border border-gray-300 dark:border-gray-600 rounded-xl py-2.5 text-sm
                           bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                            <option value="">-- Selecciona carpeta --</option>
                            <option value="0">Mover a la raíz</option>
                            @foreach ($this->obtenerCarpetasJerarquicas() as $folder)
                                <option value="{{ $folder['id'] }}">
                                    {{ str_repeat('⎯⎯ ', $folder['nivel'] ?? 0) . $folder['nombre'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pie con botones --}}
                    <div class="flex justify-end gap-3 mt-6">
                        <button wire:click="$set('showMoveModal', false)"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-300 
                           dark:border-gray-600 text-gray-700 dark:text-gray-200 
                           hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <i class="mgc_close_line text-base"></i> Cancelar
                        </button>

                        <button wire:click="moverElemento"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 
                           text-white font-medium hover:bg-blue-700 active:scale-[0.98] 
                           focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 
                           transition">
                            <i class="mgc_arrow_right_up_line text-base"></i> Confirmar
                        </button>
                    </div>
                </div>
            </div>
        @endif



    </div>
    @include('components-vs.modals.visor-pdf')
</div>
