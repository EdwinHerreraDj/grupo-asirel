<div>
    {{-- BARRA SUPERIOR --}}
    <div class="flex flex-col gap-3 mb-4 lg:flex-row lg:items-center lg:justify-between">
        <button type="button" x-data x-on:click="$dispatch('abrir-form-obra')"
            class="btn bg-primary/10 text-primary hover:bg-primary hover:text-white shrink-0 self-start lg:self-auto">
            <i class="mgc_add_line me-2"></i>Añadir obra
        </button>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3 lg:flex-1 lg:justify-end">
            {{-- Buscador --}}
            <div class="relative w-full sm:w-72">
                <i class="mgc_search_line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Buscar obra por nombre…"
                    class="w-full pl-9 pr-9 py-2 rounded-lg border border-gray-300 bg-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                @if ($search !== '')
                    <button type="button"
                        wire:click="$set('search', '')"
                        title="Limpiar búsqueda"
                        class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex h-6 w-6 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                        <i class="mgc_close_line text-sm"></i>
                    </button>
                @endif
            </div>

            {{-- Estado --}}
            <div class="flex items-center gap-2">
                <label for="estado" class="text-sm font-medium text-gray-600 hidden sm:inline">Estado:</label>
                <select id="estado" wire:model.live="estado"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Todas</option>
                    <option value="planificacion">Planificación</option>
                    <option value="ejecucion">Ejecución</option>
                    <option value="en_pausa">En pausa</option>
                    <option value="finalizada">Finalizada</option>
                </select>
            </div>
        </div>
    </div>

    {{-- GRID DE CARDS --}}
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">

        @if ($obras->isEmpty())
            <div
                class="col-span-full text-center p-6 bg-gradient-to-b from-gray-50 to-white rounded-xl shadow-sm border border-gray-200 mt-4">
                <div class="flex flex-col items-center justify-center">
                    <i class="mgc_greatwall_line text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-700 text-lg font-medium">
                        No se han encontrado obras
                        @if ($estado)
                            en <span class="text-gray-900 font-semibold">{{ ucfirst($estado) }}</span>.
                        @else
                            registradas actualmente.
                        @endif
                    </p>
                    <p class="text-gray-500 text-sm mt-1">Puedes crear una nueva obra desde el botón “Añadir obra”.</p>
                </div>
            </div>
        @else
            @foreach ($obras as $obra)
                @php
                    $estadoClases = match ($obra->estado) {
                        'planificacion' => 'bg-amber-100 text-amber-700 ring-amber-200',
                        'ejecucion' => 'bg-blue-100 text-blue-700 ring-blue-200',
                        'en_pausa' => 'bg-orange-100 text-orange-700 ring-orange-200',
                        'finalizada' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                        default => 'bg-gray-100 text-gray-700 ring-gray-200',
                    };
                    $estadoLabel = match ($obra->estado) {
                        'planificacion' => 'Planificación',
                        'ejecucion' => 'En ejecución',
                        'en_pausa' => 'En pausa',
                        'finalizada' => 'Finalizada',
                        default => ucfirst($obra->estado),
                    };
                    $tipoLabel = $obra->tipo === 'contratista' ? 'Contratista' : 'Subcontratista';
                    $tipoClases = $obra->tipo === 'contratista'
                        ? 'bg-violet-100 text-violet-700 ring-violet-200'
                        : 'bg-cyan-100 text-cyan-700 ring-cyan-200';
                    $resultado = $obra->total_ventas - $obra->total_gastos;
                    $resultadoColor = $resultado >= 0 ? 'text-emerald-600' : 'text-red-600';
                    $colorBarra = fn($pct) => $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500');
                @endphp

                <div class="card flex flex-col overflow-visible">
                    {{-- HEADER --}}
                    <div class="card-header flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h5 class="card-title truncate">{{ $obra->nombre }}</h5>
                            <p class="text-xs text-gray-400 mt-0.5">ID #{{ $obra->id }}</p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0 flex-wrap">
                            <span
                                class="text-xs font-medium px-2.5 py-1 rounded-full ring-1 {{ $tipoClases }}">
                                {{ $tipoLabel }}
                            </span>
                            <span
                                class="text-xs font-medium px-2.5 py-1 rounded-full ring-1 {{ $estadoClases }}">
                                {{ $estadoLabel }}
                            </span>

                            {{-- DROPDOWN ACCIONES --}}
                            <div class="relative" x-data="{ open: false }"
                                x-on:keydown.escape.window="open = false">
                                <button type="button" x-on:click="open = !open"
                                    class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition"
                                    :class="open && 'bg-gray-100 text-gray-900'"
                                    aria-label="Acciones">
                                    <i class="mgc_more_2_line text-lg"></i>
                                </button>

                                <div x-show="open" x-on:click.outside="open = false"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute right-0 mt-2 w-56 z-50 bg-white rounded-xl shadow-lg ring-1 ring-black/5 py-1"
                                    style="display: none;">

                                    <a href="{{ route('obras.facturas-recibidas', $obra->id) }}"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-amber-50">
                                        <i class="mgc_receive_money_line text-amber-600"></i>
                                        <span>Facturas recibidas</span>
                                    </a>

                                    <a href="{{ route('obras.coste-teorico', $obra->id) }}"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-cyan-50">
                                        <i class="mgc_chart_line_line text-cyan-600"></i>
                                        <span>Coste teórico</span>
                                    </a>

                                    <a href="{{ route('obras.presupuesto-venta', $obra->id) }}"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-violet-50">
                                        <i class="mgc_align_bottom_line text-violet-600"></i>
                                        <span>Presupuesto de venta</span>
                                    </a>

                                    <a href="{{ route('obras.certificaciones', $obra->id) }}"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50">
                                        <i class="mgc_bank_line text-emerald-600"></i>
                                        <span>Certificaciones</span>
                                    </a>

                                    <a href="{{ route('obras.documentos', $obra->id) }}"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-sky-50">
                                        <i class="mgc_file_check_line text-sky-600"></i>
                                        <span>Documentos</span>
                                    </a>

                                    <button type="button"
                                        x-on:click="open = false; $dispatch('abrir-form-obra', { id: {{ $obra->id }} })"
                                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 text-left">
                                        <i class="mgc_edit_2_line text-indigo-600"></i>
                                        <span>Editar obra</span>
                                    </button>

                                    <div class="my-1 border-t border-gray-100"></div>

                                    <button type="button"
                                        x-on:click="open = false; $wire.abrirEliminar({{ $obra->id }})"
                                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 text-left">
                                        <i class="mgc_close_circle_line"></i>
                                        <span>Eliminar obra</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CUERPO --}}
                    <div class="px-6 py-4 flex-1">
                        {{-- Fechas --}}
                        <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                            <div class="flex items-center gap-1.5">
                                <i class="mgc_calendar_line text-gray-400"></i>
                                <span><strong class="text-gray-700">Inicio:</strong> {{ $obra->fecha_inicio ?? '—' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="mgc_calendar_check_line text-gray-400"></i>
                                <span><strong class="text-gray-700">Fin:</strong> {{ $obra->fecha_fin ?? '—' }}</span>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        @if ($obra->descripcion)
                            <p class="text-gray-500 text-sm mb-4 line-clamp-2">{{ $obra->descripcion }}</p>
                        @endif

                        {{-- Grid de importes --}}
                        <div class="grid grid-cols-2 gap-3 bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Presupuesto</p>
                                <p class="text-base font-semibold text-gray-900">
                                    {{ number_format($obra->importe_presupuestado, 2, ',', '.') }} €
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Resultado</p>
                                <p class="text-base font-semibold {{ $resultadoColor }}">
                                    {{ number_format($resultado, 2, ',', '.') }} €
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Venta</p>
                                <p class="text-sm font-medium text-gray-700">
                                    {{ number_format($obra->total_ventas, 2, ',', '.') }} €
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Gasto</p>
                                <p class="text-sm font-medium text-gray-700">
                                    {{ number_format($obra->total_gastos, 2, ',', '.') }} €
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER: BARRAS DE PROGRESO --}}
                    <div class="border-t border-gray-200 px-6 py-4 space-y-3">
                        {{-- Balance --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 flex items-center gap-1.5">
                                    <i class="mgc_greatwall_line"></i> Balance
                                </span>
                                <span class="text-xs font-semibold text-gray-700">
                                    {{ number_format($obra->balance, 0) }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full transition-all {{ $colorBarra($obra->balance) }}"
                                    style="width: {{ $obra->balance }}%;">
                                </div>
                            </div>
                        </div>

                        {{-- Documentación --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600 flex items-center gap-1.5">
                                    <i class="mgc_book_5_line"></i> Documentación
                                </span>
                                <span class="text-xs font-semibold text-gray-700">
                                    {{ number_format($obra->progreso_documentos, 0) }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full transition-all {{ $colorBarra($obra->progreso_documentos) }}"
                                    style="width: {{ $obra->progreso_documentos }}%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        {{-- MODAL CONFIRMAR ELIMINAR --}}
        @if ($obraAEliminarId)
            <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
                <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-gray-200 overflow-hidden">
                    <div class="px-6 pt-6 text-center">
                        <div
                            class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600">
                            <i class="mgc_warning_line text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Eliminar obra</h3>
                        <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                            Esta acción eliminará la obra y
                            <span class="font-semibold text-gray-800">toda su información asociada</span>.
                            <br>
                            <span class="text-red-600 font-medium">No se puede deshacer.</span>
                        </p>
                    </div>

                    <div class="mt-6 px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                        <button wire:click="cancelarEliminar"
                            class="px-4 py-2 rounded-xl text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                            Cancelar
                        </button>
                        <button wire:click="eliminarObra"
                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700 active:scale-[0.97] transition-all shadow-sm">
                            Eliminar definitivamente
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <livewire:obras.form-modal />
</div>
