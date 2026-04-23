<div>

    {{-- CABECERA --}}
    <div class=" overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">

            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('empresa.facturas-ventas') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">
                    <i class="mgc_arrow_left_line text-lg"></i>
                    <span class="hidden sm:inline">Facturas</span>
                </a>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Configuración
                    </div>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                        Series de facturación
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Define prefijos y controla la numeración correlativa de las facturas de venta.
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    <button wire:click="nuevaSerie"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(8,145,178,0.22)] transition hover:from-cyan-500 hover:to-blue-500">
                        <i class="mgc_add_line"></i> Nueva serie
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="mt-5 rounded-3xl border border-slate-200 bg-white shadow-sm p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                    Buscar serie
                </label>
                <div class="relative">
                    <i class="mgc_search_line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text"
                        wire:model.live.debounce.400ms="tmpSerie"
                        placeholder="Ej: FV, A, B…"
                        class="w-full rounded-xl border-slate-300 pl-9 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                    Estado
                </label>
                <select wire:model.live="tmpEstado"
                    class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <option value="">Todas</option>
                    <option value="1">Activas</option>
                    <option value="0">Inactivas</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button wire:click="aplicarFiltros"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 text-white text-sm font-semibold px-4 py-2.5 hover:bg-slate-800">
                    <i class="mgc_filter_line"></i> Filtrar
                </button>
                <button wire:click="limpiarFiltros"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-600 text-sm px-3 py-2.5 hover:bg-slate-50"
                    title="Limpiar filtros">
                    <i class="mgc_broom_line"></i>
                </button>
            </div>

        </div>
    </div>

    {{-- TABLA --}}
    <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50/80 text-slate-600">
                    <tr class="border-b border-slate-200">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Serie</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide">Último nº</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wide">Estado</th>
                        <th class="w-52 px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($series as $s)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-3">
                                <div class="inline-flex items-center gap-2">
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 font-semibold text-xs">
                                        {{ strtoupper(substr($s->serie, 0, 2)) }}
                                    </span>
                                    <span class="font-mono font-semibold text-slate-900">{{ $s->serie }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-slate-700">
                                {{ number_format($s->ultimo_numero, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if ($s->activa)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Activa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Inactiva
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <button wire:click="editar({{ $s->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50">
                                        <i class="mgc_edit_2_line"></i> Editar
                                    </button>
                                    <button wire:click="toggleActiva({{ $s->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium {{ $s->activa ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                        <i class="{{ $s->activa ? 'mgc_pause_circle_line' : 'mgc_play_circle_line' }}"></i>
                                        {{ $s->activa ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center text-sm text-slate-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="mgc_inbox_line text-3xl text-slate-400"></i>
                                    <p>No hay series de facturación creadas.</p>
                                    <button wire:click="nuevaSerie"
                                        class="mt-2 inline-flex items-center gap-2 rounded-xl bg-slate-900 text-white text-xs font-semibold px-3 py-2 hover:bg-slate-800">
                                        <i class="mgc_add_line"></i> Crear la primera
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse ($series as $s)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-cyan-50 text-cyan-700 font-semibold text-xs">
                                {{ strtoupper(substr($s->serie, 0, 2)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-mono font-semibold text-slate-900 truncate">{{ $s->serie }}</p>
                                <p class="text-xs text-slate-500">Último nº: <span class="font-mono text-slate-700">{{ number_format($s->ultimo_numero, 0, ',', '.') }}</span></p>
                            </div>
                        </div>
                        @if ($s->activa)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 shrink-0">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Activa
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600 shrink-0">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Inactiva
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button wire:click="editar({{ $s->id }})"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50">
                            <i class="mgc_edit_2_line"></i> Editar
                        </button>
                        <button wire:click="toggleActiva({{ $s->id }})"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border px-3 py-2 text-xs font-medium {{ $s->activa ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                            <i class="{{ $s->activa ? 'mgc_pause_circle_line' : 'mgc_play_circle_line' }}"></i>
                            {{ $s->activa ? 'Desactivar' : 'Activar' }}
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-sm text-slate-500">
                    <div class="flex flex-col items-center gap-2">
                        <i class="mgc_inbox_line text-3xl text-slate-400"></i>
                        <p>No hay series creadas.</p>
                        <button wire:click="nuevaSerie"
                            class="mt-2 inline-flex items-center gap-2 rounded-xl bg-slate-900 text-white text-xs font-semibold px-3 py-2 hover:bg-slate-800">
                            <i class="mgc_add_line"></i> Crear la primera
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ========================================
         MODAL NUEVA / EDITAR SERIE
         ======================================== --}}
    @if ($mostrarModal)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cancelarEdicion()">
            <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-6 py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                {{ $editandoId ? 'Editar' : 'Nueva' }}
                            </div>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900">
                                {{ $editandoId ? 'Editar serie' : 'Nueva serie' }}
                            </h3>
                            <p class="text-sm text-slate-500 mt-0.5">
                                El último número es el correlativo desde el que se emitirá la próxima factura.
                            </p>
                        </div>
                        <button wire:click="cancelarEdicion"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                            <i class="mgc_close_line text-lg"></i>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="guardar" class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Serie *</label>
                            <input type="text" wire:model.defer="serie"
                                placeholder="Ej: FV"
                                class="mt-1 w-full rounded-xl border-slate-300 text-sm font-mono focus:border-cyan-500 focus:ring-cyan-500">
                            @error('serie') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-700">Último número *</label>
                            <input type="number" wire:model.defer="ultimo_numero" min="0"
                                class="mt-1 w-full rounded-xl border-slate-300 text-sm font-mono focus:border-cyan-500 focus:ring-cyan-500">
                            @error('ultimo_numero') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800 flex items-start gap-2">
                        <i class="mgc_information_line text-amber-600 text-sm mt-0.5"></i>
                        <span>La próxima factura emitida con esta serie usará el número <strong>{{ (int)$ultimo_numero + 1 }}</strong>.</span>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 pt-3 border-t border-slate-200">
                        <button type="button" wire:click="cancelarEdicion"
                            class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow hover:from-cyan-500 hover:to-blue-500">
                            <i class="mgc_save_line"></i>
                            {{ $editandoId ? 'Guardar cambios' : 'Crear serie' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
