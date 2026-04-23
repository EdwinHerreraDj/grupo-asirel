<div>
    <div class="space-y-4">

    {{-- CABECERA --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">

            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('empresa.index') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">
                    <i class="mgc_arrow_left_line text-lg"></i>
                    <span class="hidden sm:inline">Empresa</span>
                </a>
            </div>

            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Empresa
                    </div>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                        Gastos generales de la empresa
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Registra y controla los gastos globales de la empresa (no imputables a obra).
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    <a href="{{ route('categorias.empresa.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        <i class="mgc_classify_2_line"></i> Categorías
                    </a>
                    <button wire:click="abrirModal"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(8,145,178,0.22)] transition hover:from-cyan-500 hover:to-blue-500">
                        <i class="mgc_add_line"></i> Nuevo gasto
                    </button>
                </div>
            </div>
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-2 gap-3 px-5 py-5 sm:grid-cols-4 sm:px-6">
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Gastos filtrados</p>
                <p class="mt-1 text-xl font-bold text-slate-900">{{ number_format($stats['total_count'], 0, ',', '.') }}</p>
            </div>
            <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-cyan-700">Importe filtrado</p>
                <p class="mt-1 text-xl font-bold text-cyan-800">{{ number_format($stats['total_importe'], 2, ',', '.') }} €</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-emerald-700">Este mes</p>
                <p class="mt-1 text-xl font-bold text-emerald-800">{{ number_format($stats['mes_actual'], 2, ',', '.') }} €</p>
            </div>
            <div class="rounded-2xl border {{ $stats['vencidos_count'] > 0 ? 'border-red-200 bg-red-50' : 'border-slate-200 bg-slate-50/70' }} px-4 py-3">
                <p class="text-[11px] font-semibold uppercase tracking-[0.12em] {{ $stats['vencidos_count'] > 0 ? 'text-red-700' : 'text-slate-500' }}">Vencidos</p>
                <p class="mt-1 text-xl font-bold {{ $stats['vencidos_count'] > 0 ? 'text-red-800' : 'text-slate-700' }}">
                    {{ number_format($stats['vencidos_count'], 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-4">
            <i class="mgc_filter_line text-slate-500"></i>
            <h3 class="text-sm font-semibold text-slate-700">Filtros</h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

            {{-- Buscar --}}
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Buscar</label>
                <div class="relative">
                    <i class="mgc_search_line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text"
                        wire:model.live.debounce.400ms="pendingSearch"
                        placeholder="Concepto, nº factura, descripción…"
                        class="w-full rounded-xl border-slate-300 pl-9 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                </div>
            </div>

            {{-- Categoría --}}
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Categoría</label>
                <select wire:model.live="pendingCategoria"
                    class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">Todas</option>
                    @foreach ($categoriasPadre as $padre)
                        <optgroup label="{{ $padre->codigo }} - {{ $padre->nombre }}">
                            @foreach ($padre->children as $hijo)
                                <option value="{{ $hijo->id }}">— {{ $hijo->codigo }} - {{ $hijo->nombre }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            {{-- Estado vencimiento --}}
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Vencimiento</label>
                <select wire:model.live="pendingEstadoVencimiento"
                    class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">Todos</option>
                    <option value="vencidos">Vencidos</option>
                    <option value="proximos">Próximos (≤ 15 días)</option>
                    <option value="sin_fecha">Sin fecha</option>
                </select>
            </div>

            {{-- Fecha desde --}}
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Fecha factura desde</label>
                <input type="date" wire:model.defer="pendingFechaDesde"
                    class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
            </div>

            {{-- Fecha hasta --}}
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Fecha factura hasta</label>
                <input type="date" wire:model.defer="pendingFechaHasta"
                    class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
            </div>

            {{-- Importe mín --}}
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Importe mín. (€)</label>
                <input type="number" step="0.01" min="0" wire:model.defer="pendingImporteMin"
                    class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                    placeholder="0,00">
            </div>

            {{-- Importe máx --}}
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">Importe máx. (€)</label>
                <input type="number" step="0.01" min="0" wire:model.defer="pendingImporteMax"
                    class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                    placeholder="Sin límite">
            </div>

            {{-- Acciones filtros --}}
            <div class="sm:col-span-2 lg:col-span-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1 border-t border-slate-100">
                <button wire:click="limpiarFiltros"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white text-slate-600 text-sm px-4 py-2.5 hover:bg-slate-50">
                    <i class="mgc_broom_line"></i> Limpiar
                </button>
                <button wire:click="aplicarFiltros"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-semibold px-4 py-2.5 hover:bg-slate-800">
                    <i class="mgc_filter_line"></i> Aplicar filtros
                </button>
            </div>
        </div>
    </div>

    {{-- TABLA / LISTADO --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        {{-- Desktop --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50/80 text-slate-600">
                    <tr class="border-b border-slate-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Nº Factura</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Concepto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Categoría</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">F. factura</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Vencimiento</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide">Importe</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide">Factura</th>
                        <th class="w-20 px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($gastos as $gasto)
                        @php
                            $vencido = $gasto->fecha_vencimiento && \Carbon\Carbon::parse($gasto->fecha_vencimiento)->isPast();
                            $proximo = $gasto->fecha_vencimiento
                                && !\Carbon\Carbon::parse($gasto->fecha_vencimiento)->isPast()
                                && \Carbon\Carbon::parse($gasto->fecha_vencimiento)->diffInDays(now()) <= 15;
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $gasto->numero_factura ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $gasto->concepto }}</div>
                                @if ($gasto->especificacion)
                                    <div class="text-xs text-slate-500 truncate max-w-xs">{{ $gasto->especificacion }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($gasto->categoria)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700">
                                        <span class="font-mono text-[10px] text-slate-500">{{ $gasto->categoria->codigo }}</span>
                                        {{ $gasto->categoria->nombre }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ $gasto->fecha_factura ? \Carbon\Carbon::parse($gasto->fecha_factura)->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($gasto->fecha_vencimiento)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-xs
                                        {{ $vencido ? 'border-red-200 bg-red-50 text-red-700' : ($proximo ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-white text-slate-600') }}">
                                        @if ($vencido)<i class="mgc_warning_line"></i>@elseif($proximo)<i class="mgc_time_line"></i>@endif
                                        {{ \Carbon\Carbon::parse($gasto->fecha_vencimiento)->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                {{ number_format($gasto->importe, 2, ',', '.') }} €
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($gasto->factura_url)
                                    <div class="flex justify-center gap-1">
                                        <a href="{{ asset('storage/' . $gasto->factura_url) }}" target="_blank"
                                            title="Ver"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50">
                                            <i class="mgc_eye_2_line"></i>
                                        </a>
                                        <a href="{{ asset('storage/' . $gasto->factura_url) }}" download
                                            title="Descargar"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50">
                                            <i class="mgc_download_2_line"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="confirmarEliminar({{ $gasto->id }})"
                                    title="Eliminar"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 hover:border-red-300 hover:bg-red-50">
                                    <i class="mgc_delete_line"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="mgc_inbox_line text-3xl text-slate-400"></i>
                                    <p>No se encontraron gastos con los filtros aplicados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile / Tablet cards --}}
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse ($gastos as $gasto)
                @php
                    $vencido = $gasto->fecha_vencimiento && \Carbon\Carbon::parse($gasto->fecha_vencimiento)->isPast();
                    $proximo = $gasto->fecha_vencimiento
                        && !\Carbon\Carbon::parse($gasto->fecha_vencimiento)->isPast()
                        && \Carbon\Carbon::parse($gasto->fecha_vencimiento)->diffInDays(now()) <= 15;
                @endphp
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 truncate">{{ $gasto->concepto }}</p>
                            @if ($gasto->numero_factura)
                                <p class="text-xs text-slate-500 font-mono">Nº {{ $gasto->numero_factura }}</p>
                            @endif
                        </div>
                        <p class="shrink-0 text-base font-bold text-slate-900 whitespace-nowrap">
                            {{ number_format($gasto->importe, 2, ',', '.') }} €
                        </p>
                    </div>

                    @if ($gasto->especificacion)
                        <p class="text-xs text-slate-500">{{ $gasto->especificacion }}</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @if ($gasto->categoria)
                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-slate-700">
                                <span class="font-mono text-[10px] text-slate-500">{{ $gasto->categoria->codigo }}</span>
                                {{ $gasto->categoria->nombre }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-0.5 text-slate-600">
                            <i class="mgc_calendar_line"></i>
                            {{ $gasto->fecha_factura ? \Carbon\Carbon::parse($gasto->fecha_factura)->format('d/m/Y') : '—' }}
                        </span>
                        @if ($gasto->fecha_vencimiento)
                            <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5
                                {{ $vencido ? 'border-red-200 bg-red-50 text-red-700' : ($proximo ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-slate-200 bg-white text-slate-600') }}">
                                @if ($vencido)<i class="mgc_warning_line"></i>@elseif($proximo)<i class="mgc_time_line"></i>@else<i class="mgc_calendar_time_line"></i>@endif
                                Vence {{ \Carbon\Carbon::parse($gasto->fecha_vencimiento)->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
                        @if ($gasto->factura_url)
                            <a href="{{ asset('storage/' . $gasto->factura_url) }}" target="_blank"
                                class="flex-1 min-w-0 inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50">
                                <i class="mgc_eye_2_line"></i> Ver factura
                            </a>
                            <a href="{{ asset('storage/' . $gasto->factura_url) }}" download
                                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:border-slate-300 hover:bg-slate-50">
                                <i class="mgc_download_2_line"></i>
                            </a>
                        @endif
                        <button wire:click="confirmarEliminar({{ $gasto->id }})"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-100">
                            <i class="mgc_delete_line"></i> Eliminar
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-sm text-slate-500">
                    <div class="flex flex-col items-center gap-2">
                        <i class="mgc_inbox_line text-3xl text-slate-400"></i>
                        <p>No se encontraron gastos con los filtros aplicados.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        @if ($gastos->hasPages())
            <div class="border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:px-5">
                {{ $gastos->links() }}
            </div>
        @endif
    </div>

    </div>

    {{-- MODAL FORMULARIO --}}
    @if ($showModal)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cerrarModal()">
            <div class="w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[92vh] flex flex-col">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-6 py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                Nuevo
                            </div>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900">Registrar gasto</h3>
                            <p class="text-sm text-slate-500 mt-0.5">Los campos con * son obligatorios.</p>
                        </div>
                        <button wire:click="cerrarModal"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                            <i class="mgc_close_line text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="overflow-y-auto px-6 py-5">
                    @livewire('empresa.gastos.formulario', [], key('form-gasto'))
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL ELIMINAR --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600">
                        <i class="mgc_warning_line text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Eliminar gasto</h3>
                    <p class="mt-2 text-sm text-slate-600">Esta acción no se puede deshacer. También se eliminará el archivo adjunto si existe.</p>
                </div>
                <div class="mt-6 px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center sm:justify-end gap-2 sm:gap-3">
                    <button wire:click="cancelarEliminar"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button wire:click="eliminar"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700">
                        Eliminar gasto
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
