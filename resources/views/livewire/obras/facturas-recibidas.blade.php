<div>
    {{-- PRELOADER GENERACIÓN PDF / EXCEL --}}
    <div wire:loading.flex wire:target="generarInformePDF,exportarExcel"
        class="fixed inset-0 z-[10000] items-center justify-center bg-slate-900/60 backdrop-blur-sm"
        style="display: none;">
        <div class="flex flex-col items-center gap-4 rounded-2xl bg-white px-8 py-6 shadow-2xl">
            <div class="relative h-12 w-12">
                <div class="absolute inset-0 rounded-full border-4 border-slate-200"></div>
                <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-cyan-600"></div>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-slate-800">Generando informe…</p>
                <p class="text-xs text-slate-500 mt-0.5">Recopilando datos y aplicando diseño de empresa.</p>
            </div>
        </div>
    </div>

    {{-- CABECERA --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-amber-50/40 px-5 py-5 sm:px-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-100 bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-amber-700">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Gasto
                    </div>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                        Facturas recibidas
                        @if ($globalMode)
                            <span class="text-base font-normal text-slate-400">· global</span>
                        @endif
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 truncate">
                        {{ $obra?->nombre ?? 'Selecciona una obra para empezar' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @unless ($globalMode)
                        <a href="{{ route('unidad') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">
                            <i class="mgc_arrow_left_line"></i>
                            Regresar
                        </a>
                    @endunless

                    <button wire:click="abrirModalInforme"
                        @disabled(!$obra)
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="mgc_file_download_line"></i>
                        Generar informe
                    </button>

                    <button wire:click="abrirFormulario"
                        @disabled(!$obra)
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(234,88,12,0.22)] transition hover:from-amber-400 hover:to-orange-400 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="mgc_add_line"></i>
                        Nueva factura
                    </button>
                </div>
            </div>

            {{-- SELECTOR DE OBRA — combobox con buscador (solo en modo global) --}}
            @if ($globalMode)
                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50/40 p-3 sm:p-4"
                    x-data='{
                        open: false,
                        busqueda: "",
                        highlight: 0,
                        obras: @json($obrasList->map(fn($o) => ["id" => $o->id, "nombre" => $o->nombre, "estado" => $o->estado ? ucfirst($o->estado) : null])->values()),
                        get filtradas() {
                            const t = this.busqueda.trim().toLowerCase();
                            if (!t) return this.obras;
                            return this.obras.filter(o =>
                                (o.nombre || "").toLowerCase().includes(t)
                                || (o.estado || "").toLowerCase().includes(t)
                            );
                        },
                        get seleccionada() {
                            const id = $wire.selectedObraId;
                            if (!id) return null;
                            return this.obras.find(o => String(o.id) === String(id)) || null;
                        },
                        seleccionar(id) {
                            $wire.set("selectedObraId", id);
                            this.open = false;
                            this.busqueda = "";
                        },
                        limpiar() {
                            $wire.set("selectedObraId", null);
                            this.open = false;
                            this.busqueda = "";
                        },
                        abrir() {
                            this.open = true;
                            this.highlight = 0;
                            this.$nextTick(() => this.$refs.input?.focus());
                        },
                        onKey(e) {
                            if (e.key === "Escape") { this.open = false; return; }
                            if (e.key === "ArrowDown") { e.preventDefault(); this.highlight = Math.min(this.highlight + 1, this.filtradas.length - 1); this.scrollIntoView(); return; }
                            if (e.key === "ArrowUp") { e.preventDefault(); this.highlight = Math.max(this.highlight - 1, 0); this.scrollIntoView(); return; }
                            if (e.key === "Enter") { e.preventDefault(); const o = this.filtradas[this.highlight]; if (o) this.seleccionar(o.id); }
                        },
                        scrollIntoView() {
                            this.$nextTick(() => {
                                const el = this.$refs.lista?.querySelector(`[data-idx="${this.highlight}"]`);
                                if (el) el.scrollIntoView({ block: "nearest" });
                            });
                        }
                    }'
                    x-on:click.outside="open = false">

                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                            <i class="mgc_building_2_line mr-1"></i> Obra
                        </span>
                        <span class="text-[11px] text-amber-700/70" x-show="!seleccionada">Selecciona una para empezar</span>
                    </div>

                    <div class="relative">
                        {{-- Trigger --}}
                        <button type="button"
                            x-on:click="open ? open = false : abrir()"
                            :class="open ? 'border-amber-500 ring-2 ring-amber-500/30' : 'border-amber-200 hover:border-amber-400'"
                            class="w-full flex items-center justify-between gap-2 rounded-xl border bg-white px-3 py-2.5 text-left text-sm transition">
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <i class="mgc_building_2_line text-amber-600 shrink-0"></i>
                                <template x-if="seleccionada">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-900 truncate" x-text="seleccionada.nombre"></div>
                                        <div class="text-[11px] text-slate-500" x-show="seleccionada.estado" x-text="seleccionada.estado"></div>
                                    </div>
                                </template>
                                <template x-if="!seleccionada">
                                    <span class="text-slate-400">— Selecciona una obra —</span>
                                </template>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <span x-show="seleccionada"
                                    role="button"
                                    title="Quitar selección"
                                    x-on:click.stop="limpiar()"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                    <i class="mgc_close_line text-sm"></i>
                                </span>
                                <i class="mgc_down_line text-slate-400 transition-transform" :class="open && 'rotate-180'"></i>
                            </div>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="open" x-cloak x-transition.opacity.duration.150ms
                            class="absolute left-0 right-0 mt-2 z-30 rounded-xl border border-slate-200 bg-white shadow-2xl overflow-hidden">

                            {{-- Buscador --}}
                            <div class="p-2 border-b border-slate-100 bg-slate-50/60">
                                <div class="relative">
                                    <i class="mgc_search_line absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input
                                        x-ref="input"
                                        x-model="busqueda"
                                        x-on:input="highlight = 0"
                                        x-on:keydown="onKey($event)"
                                        type="text"
                                        placeholder="Buscar obra…"
                                        class="w-full rounded-lg border-slate-300 pl-9 text-sm focus:border-amber-500 focus:ring-amber-500 bg-white">
                                </div>
                            </div>

                            {{-- Lista --}}
                            <div x-ref="lista" class="max-h-72 overflow-y-auto overscroll-contain">
                                <template x-if="filtradas.length === 0">
                                    <div class="px-4 py-8 text-center text-sm text-slate-500">
                                        <i class="mgc_search_line text-2xl text-slate-300 block mb-2"></i>
                                        Sin coincidencias
                                    </div>
                                </template>

                                <template x-for="(obra, idx) in filtradas" :key="obra.id">
                                    <button type="button"
                                        :data-idx="idx"
                                        x-on:click="seleccionar(obra.id)"
                                        x-on:mouseenter="highlight = idx"
                                        :class="idx === highlight ? 'bg-amber-50' : 'hover:bg-slate-50'"
                                        class="w-full text-left px-4 py-2.5 flex items-center justify-between gap-3 transition">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-slate-900 truncate" x-text="obra.nombre"></p>
                                            <p class="text-[11px] text-slate-500" x-show="obra.estado" x-text="obra.estado"></p>
                                        </div>
                                        <i class="mgc_check_line text-amber-600 shrink-0"
                                            x-show="String($wire.selectedObraId) === String(obra.id)"></i>
                                    </button>
                                </template>
                            </div>

                            {{-- Footer contador --}}
                            <div class="px-3 py-1.5 border-t border-slate-100 bg-slate-50/60 text-[10px] text-slate-500 text-right">
                                <span x-text="filtradas.length"></span> de <span x-text="obras.length"></span>
                                <span x-text="obras.length === 1 ? 'obra' : 'obras'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-2 gap-3 px-5 py-5 sm:px-6 lg:grid-cols-4">
            @php
                $statCards = [
                    ['label' => 'Total facturado', 'valor' => $resumen['total'], 'color' => 'text-slate-900', 'bg' => 'bg-slate-50 border-slate-200'],
                    ['label' => 'Pagado', 'valor' => $resumen['pagadas'], 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50 border-emerald-200'],
                    ['label' => 'Pendiente', 'valor' => $resumen['pendientes'], 'color' => 'text-blue-700', 'bg' => 'bg-blue-50 border-blue-200'],
                    ['label' => 'Impagado', 'valor' => $resumen['impagadas'], 'color' => 'text-red-700', 'bg' => 'bg-red-50 border-red-200'],
                ];
            @endphp
            @foreach ($statCards as $s)
                <div class="rounded-2xl border {{ $s['bg'] }} px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">{{ $s['label'] }}</p>
                    <p class="mt-1 text-xl font-bold {{ $s['color'] }}">
                        {{ number_format($s['valor'], 2, ',', '.') }} €
                    </p>
                </div>
            @endforeach
        </div>

        {{-- FILTROS --}}
        <div class="border-t border-slate-200 bg-slate-50/50 px-5 py-4 sm:px-6">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar concepto/nº…"
                    class="form-input rounded-xl border-slate-300 text-sm lg:col-span-2">

                <select wire:model.live="filtroProveedor" class="form-select rounded-xl border-slate-300 text-sm">
                    <option value="">Todos proveedores</option>
                    @foreach ($proveedores as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filtroOficio" class="form-select rounded-xl border-slate-300 text-sm">
                    <option value="">Todos oficios</option>
                    @foreach ($oficios as $o)
                        <option value="{{ $o->id }}">{{ $o->nombre }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filtroEstado" class="form-select rounded-xl border-slate-300 text-sm">
                    <option value="">Todos estados</option>
                    @foreach ($estados as $key => $meta)
                        <option value="{{ $key }}">{{ $meta['label'] }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filtroTipoCoste" class="form-select rounded-xl border-slate-300 text-sm">
                    <option value="">Todos tipos</option>
                    <option value="material">Material</option>
                    <option value="mano_obra">Mano de obra</option>
                </select>
            </div>

            <div class="mt-3 flex items-center gap-2">
                <button wire:click="aplicarFiltros"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-900">
                    <i class="mgc_filter_line"></i> Aplicar
                </button>
                <button wire:click="limpiarFiltros"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                    <i class="mgc_close_line"></i> Limpiar
                </button>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50/80 text-slate-600">
                    <tr class="border-y border-slate-200">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Proveedor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Concepto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Oficio</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">Base</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide sm:px-5">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide sm:px-5">Estado</th>
                        <th class="w-32 px-4 py-3 text-right sm:px-5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($facturas as $f)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 sm:px-5">
                                <div class="text-sm font-medium text-slate-800">{{ $f->proveedor->nombre ?? '—' }}</div>
                                @if ($f->numero_factura)
                                    <div class="text-xs text-slate-400 font-mono">Nº {{ $f->numero_factura }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 sm:px-5 max-w-xs">
                                <div class="text-sm text-slate-700 truncate" title="{{ $f->concepto }}">
                                    {{ $f->concepto ?: '—' }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $f->tipo_coste === 'material' ? 'Material' : 'Mano de obra' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 sm:px-5">
                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                                    {{ $f->oficio->nombre ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-slate-700 sm:px-5">
                                {{ number_format($f->base_imponible, 2, ',', '.') }} €
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-slate-900 sm:px-5">
                                {{ number_format($f->total, 2, ',', '.') }} €
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 sm:px-5">
                                {{ $f->fecha_factura?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 sm:px-5">
                                <select
                                    x-data
                                    x-on:change="$wire.intentarCambiarEstado({{ $f->id }}, $event.target.value)"
                                    class="form-select rounded-lg border text-xs font-semibold px-2.5 py-1 {{ $f->estadoColor() }}">
                                    @foreach ($estados as $key => $meta)
                                        <option value="{{ $key }}" @selected($f->estado === $key)>{{ $meta['label'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3 text-right sm:px-5">
                                <div class="flex justify-end gap-1">
                                    @if ($f->adjunto)
                                        <a href="{{ asset('storage/' . $f->adjunto) }}" target="_blank"
                                            title="Ver adjunto"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700">
                                            <i class="mgc_attachment_2_line"></i>
                                        </a>
                                    @endif
                                    <button wire:click="editarFactura({{ $f->id }})"
                                        title="Editar"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-cyan-300 hover:bg-cyan-50 hover:text-cyan-700">
                                        <i class="mgc_edit_2_line"></i>
                                    </button>
                                    <button wire:click="confirmarEliminar({{ $f->id }})"
                                        title="Eliminar"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                        <i class="mgc_delete_line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center text-sm text-slate-500 sm:px-5">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <i class="mgc_inbox_line text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-700">Sin facturas registradas</p>
                                        <p class="mt-1 text-sm text-slate-500">Pulsa "Nueva factura" para empezar.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-4 py-3 sm:px-5">
            {{ $facturas->links() }}
        </div>
    </div>

    {{-- MODAL NUEVA / EDITAR --}}
    @if ($showForm)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cerrarFormulario()">
            <div class="w-full max-w-3xl bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between px-6 py-5 border-b border-slate-200">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">
                            {{ $modoEdicion ? 'Editar factura' : 'Nueva factura recibida' }}
                        </h3>
                        <p class="text-sm text-slate-500 mt-1">{{ $obra?->nombre }}</p>
                    </div>
                    <button wire:click="cerrarFormulario" class="text-slate-400 hover:text-slate-600">
                        <i class="mgc_close_line text-xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="guardar" class="px-6 py-5 space-y-5">
                    {{-- Proveedor + Oficio --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Proveedor *</label>
                            <select wire:model="proveedor_id" class="mt-1 form-select w-full rounded-xl">
                                <option value="">Selecciona…</option>
                                @foreach ($proveedores as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                            @error('proveedor_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Oficio *</label>
                            <select wire:model="oficio_id" class="mt-1 form-select w-full rounded-xl">
                                <option value="">Selecciona…</option>
                                @foreach ($oficios as $o)
                                    <option value="{{ $o->id }}">{{ $o->nombre }}</option>
                                @endforeach
                            </select>
                            @error('oficio_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Tipo coste + Nº factura --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Tipo de coste *</label>
                            <select wire:model="tipo_coste" class="mt-1 form-select w-full rounded-xl">
                                <option value="material">Material</option>
                                <option value="mano_obra">Mano de obra</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Nº factura</label>
                            <input type="text" wire:model="numero_factura" class="mt-1 form-input w-full rounded-xl">
                        </div>
                    </div>

                    {{-- Concepto --}}
                    <div>
                        <label class="text-sm font-medium text-slate-700">Concepto</label>
                        <input type="text" wire:model="concepto" class="mt-1 form-input w-full rounded-xl"
                            placeholder="Descripción de la factura">
                    </div>

                    {{-- Fiscal --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Base imponible (€) *</label>
                            <input type="number" step="0.01" min="0" wire:model="base_imponible"
                                class="mt-1 form-input w-full rounded-xl">
                            @error('base_imponible') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">IVA %</label>
                            <input type="number" step="0.01" min="0" wire:model="iva_porcentaje"
                                class="mt-1 form-input w-full rounded-xl">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Retención %</label>
                            <input type="number" step="0.01" min="0" wire:model="retencion_porcentaje"
                                class="mt-1 form-input w-full rounded-xl">
                        </div>
                    </div>

                    {{-- Fechas --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Fecha factura *</label>
                            <input type="date" wire:model="fecha_factura" class="mt-1 form-input w-full rounded-xl">
                            @error('fecha_factura') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Fecha contable</label>
                            <input type="date" wire:model="fecha_contable" class="mt-1 form-input w-full rounded-xl">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Vencimiento</label>
                            <input type="date" wire:model="vencimiento" class="mt-1 form-input w-full rounded-xl">
                        </div>
                    </div>

                    {{-- Pago + Estado --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Tipo de pago</label>
                            <select wire:model="tipo_pago" class="mt-1 form-select w-full rounded-xl">
                                <option value="">—</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="pronto_pago">Pronto pago</option>
                                <option value="confirming">Confirming</option>
                                <option value="pagare">Pagaré</option>
                                <option value="contado">Contado</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Estado *</label>
                            <select wire:model="estado" class="mt-1 form-select w-full rounded-xl">
                                @foreach ($estados as $key => $meta)
                                    <option value="{{ $key }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Adjunto --}}
                    <div>
                        <label class="text-sm font-medium text-slate-700">
                            Adjunto (PDF o imagen, máx 2 MB)
                        </label>
                        <input type="file" wire:model="adjunto"
                            accept="application/pdf,image/jpeg,image/png"
                            class="mt-1 block w-full text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-amber-600">
                        @error('adjunto') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="adjunto" class="mt-1 text-xs text-slate-500">Subiendo adjunto…</div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                        <button type="button" wire:click="cerrarFormulario"
                            class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow hover:from-amber-400 hover:to-orange-400 disabled:opacity-60">
                            <span wire:loading.remove wire:target="guardar">
                                {{ $modoEdicion ? 'Guardar cambios' : 'Crear factura' }}
                            </span>
                            <span wire:loading wire:target="guardar">Guardando…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL ELIMINAR --}}
    @if ($facturaAEliminar)
        <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600">
                        <i class="mgc_warning_line text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Eliminar factura</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Se eliminará la factura y su adjunto asociado.
                        <br><span class="text-red-600 font-medium">No se puede deshacer.</span>
                    </p>
                </div>
                <div class="mt-6 px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button wire:click="cancelarEliminar"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button wire:click="eliminarFactura" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700 disabled:opacity-60">
                        <span wire:loading.remove wire:target="eliminarFactura">Eliminar definitivamente</span>
                        <span wire:loading wire:target="eliminarFactura">Eliminando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL CONFIRMAR CAMBIO DE ESTADO CRÍTICO --}}
    @if ($facturaCambioEstadoId && $estadoPendiente)
        @php
            $metaEstado = $estados[$estadoPendiente] ?? ['label' => $estadoPendiente];
            $esPagada = $estadoPendiente === 'pagada';
        @endphp
        <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full {{ $esPagada ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                        <i class="{{ $esPagada ? 'mgc_check_line' : 'mgc_warning_line' }} text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">
                        Marcar como {{ $metaEstado['label'] }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        @if ($facturaCambioEstado)
                            Factura de <strong>{{ $facturaCambioEstado->proveedor->nombre ?? '—' }}</strong>
                            por <strong>{{ number_format($facturaCambioEstado->total, 2, ',', '.') }} €</strong>.
                            <br>
                        @endif
                        {{ $esPagada
                            ? 'Confirma que esta factura ya está pagada.'
                            : 'Confirma que esta factura no se va a pagar en el vencimiento previsto.' }}
                    </p>
                </div>
                <div class="mt-6 px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button wire:click="cancelarCambioEstado"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button wire:click="confirmarCambioEstado"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white shadow {{ $esPagada ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700' }}">
                        Sí, marcar como {{ $metaEstado['label'] }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL GENERAR INFORME --}}
    @if ($showInformeModal)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cerrarModalInforme()">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="flex items-start justify-between px-6 py-5 border-b border-slate-200">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Generar informe</h3>
                        <p class="text-sm text-slate-500 mt-1">Filtra las facturas y descarga el informe en PDF o Excel.</p>
                    </div>
                    <button wire:click="cerrarModalInforme" class="text-slate-400 hover:text-slate-600">
                        <i class="mgc_close_line text-xl"></i>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <select wire:model="informeProveedor" class="form-select rounded-xl border-slate-300 text-sm">
                            <option value="">Todos proveedores</option>
                            @foreach ($proveedores as $p)
                                <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                        <select wire:model="informeOficio" class="form-select rounded-xl border-slate-300 text-sm">
                            <option value="">Todos oficios</option>
                            @foreach ($oficios as $o)
                                <option value="{{ $o->id }}">{{ $o->nombre }}</option>
                            @endforeach
                        </select>
                        <select wire:model="informeEstado" class="form-select rounded-xl border-slate-300 text-sm">
                            <option value="">Todos estados</option>
                            @foreach ($estados as $key => $meta)
                                <option value="{{ $key }}">{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                        <select wire:model="informeTipoCoste" class="form-select rounded-xl border-slate-300 text-sm">
                            <option value="">Todos tipos</option>
                            <option value="material">Material</option>
                            <option value="mano_obra">Mano de obra</option>
                        </select>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Desde</label>
                            <input type="date" wire:model="informeFechaDesde" class="mt-1 form-input w-full rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Hasta</label>
                            <input type="date" wire:model="informeFechaHasta" class="mt-1 form-input w-full rounded-xl text-sm">
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2">
                    <button wire:click="cerrarModalInforme"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button wire:click="exportarExcel"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 inline-flex items-center gap-2">
                        <i class="mgc_excel_line"></i> Excel
                    </button>
                    <button wire:click="generarInformePDF"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700 inline-flex items-center gap-2">
                        <i class="mgc_file_pdf_line"></i> PDF
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
