<div
    x-data="{
        descargando: false,
        async descargar(url, filename) {
            this.descargando = true;
            // Bloquear scroll del body
            const prevOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            try {
                const res = await fetch(url, {
                    headers: { Accept: 'application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream' },
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const blob = await res.blob();
                const objectUrl = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = objectUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => URL.revokeObjectURL(objectUrl), 1000);
            } catch (e) {
                console.error('Error generando informe', e);
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type: 'error', message: 'Error al generar el informe. Inténtalo de nuevo.' }
                }));
            } finally {
                this.descargando = false;
                document.body.style.overflow = prevOverflow;
            }
        }
    }"
    x-on:descargar-informe.window="descargar($event.detail.url, $event.detail.filename)"
>
    {{-- PRELOADER GENERACIÓN --}}
    <div x-show="descargando" x-cloak x-transition.opacity
        class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm px-4">
        <div class="flex flex-col items-center gap-4 rounded-3xl bg-white px-8 py-7 shadow-2xl max-w-sm w-full text-center">
            <div class="relative h-14 w-14">
                <div class="absolute inset-0 rounded-full border-4 border-slate-200"></div>
                <div class="absolute inset-0 animate-spin rounded-full border-4 border-transparent border-t-cyan-600"></div>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-800">Generando informe…</p>
                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                    Esto puede tardar varios segundos si el rango es amplio.<br>
                    No cierres esta ventana.
                </p>
            </div>
        </div>
    </div>

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

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                            <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                            Empresa
                        </div>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                            Informes globales
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Consulta los datos consolidados de la empresa: liquidación de IVA y análisis bruto de obras.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================
             1. LIQUIDACIÓN DE IVA
             ============================ --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-4 sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 text-white shadow-[0_8px_18px_rgba(8,145,178,0.25)]">
                        <i class="mgc_bill_line text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-slate-900">
                            Liquidación de IVA
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-0.5">
                            Resumen del IVA repercutido (facturas emitidas) y soportado (facturas recibidas)
                            para el periodo seleccionado, con el resultado de la liquidación.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5 space-y-4">
                {{-- Atajos rápidos --}}
                <div class="flex flex-wrap gap-2">
                    <button wire:click="aplicarTrimestreActual"
                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-cyan-300 hover:bg-cyan-50">
                        <i class="mgc_calendar_line text-cyan-600"></i> Trimestre actual
                    </button>
                    <button wire:click="aplicarTrimestreAnterior"
                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-cyan-300 hover:bg-cyan-50">
                        <i class="mgc_arrow_left_line text-cyan-600"></i> Trimestre anterior
                    </button>
                    <button wire:click="aplicarAnioActual"
                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:border-cyan-300 hover:bg-cyan-50">
                        <i class="mgc_calendar_2_line text-cyan-600"></i> Año actual
                    </button>
                </div>

                {{-- Filtros --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Fecha desde
                        </label>
                        <input type="date" wire:model.live="ivaFechaInicio"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Fecha hasta
                        </label>
                        <input type="date" wire:model.live="ivaFechaFin"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Formato
                        </label>
                        <select wire:model.live="ivaFormato"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                            <option value="pdf">PDF (.pdf)</option>
                            <option value="excel">Excel (.xlsx)</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button wire:click="exportarLiquidacionIva"
                            wire:loading.attr="disabled"
                            wire:target="exportarLiquidacionIva"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(8,145,178,0.22)] hover:from-cyan-500 hover:to-blue-500 disabled:opacity-60">
                            <span wire:loading.remove wire:target="exportarLiquidacionIva" class="inline-flex items-center gap-2">
                                <i class="mgc_download_2_line"></i> Generar informe
                            </span>
                            <span wire:loading wire:target="exportarLiquidacionIva" class="inline-flex items-center gap-2">
                                <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Generando…
                            </span>
                        </button>
                    </div>
                </div>

                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-xs text-slate-600 flex items-start gap-2">
                    <i class="mgc_information_line text-slate-500 text-sm mt-0.5"></i>
                    <span>
                        Solo se incluyen facturas emitidas en estado <strong>emitida</strong>,
                        <strong>enviada</strong> o <strong>pagada</strong>, y facturas recibidas excluyendo las
                        devueltas. El informe muestra el desglose por tipo de IVA y el detalle de cada factura.
                    </span>
                </div>
            </div>
        </div>

        {{-- ============================
             2. ANÁLISIS BRUTO DE OBRAS
             ============================ --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-emerald-50/40 px-5 py-4 sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-[0_8px_18px_rgba(5,150,105,0.25)]">
                        <i class="mgc_chart_bar_line text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-slate-900">
                            Análisis bruto de obras
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-0.5">
                            Compara los ingresos facturados contra los costes recibidos por obra,
                            calculando el beneficio bruto y el margen porcentual.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5 space-y-4">
                {{-- Filtros --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Obra
                        </label>
                        <select wire:model.live="abObraSeleccionada"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="todas">Todas las obras</option>
                            @foreach ($obras as $obra)
                                <option value="{{ $obra->id }}">{{ $obra->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Estado
                        </label>
                        <select wire:model.live="abEstadoSeleccionado"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="todas">Todos</option>
                            <option value="planificacion">Planificación</option>
                            <option value="ejecucion">Ejecución</option>
                            <option value="finalizada">Finalizada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Formato
                        </label>
                        <select wire:model.live="abFormato"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="pdf">PDF (.pdf)</option>
                            <option value="excel">Excel (.xlsx)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Fecha desde
                        </label>
                        <input type="date" wire:model.live="abFechaInicio"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Fecha hasta
                        </label>
                        <input type="date" wire:model.live="abFechaFin"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="sm:col-span-2 flex items-end">
                        <button wire:click="exportarAnalisisBrutoObras"
                            wire:loading.attr="disabled"
                            wire:target="exportarAnalisisBrutoObras"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(5,150,105,0.22)] hover:from-emerald-500 hover:to-green-500 disabled:opacity-60">
                            <span wire:loading.remove wire:target="exportarAnalisisBrutoObras" class="inline-flex items-center gap-2">
                                <i class="mgc_download_2_line"></i> Generar informe
                            </span>
                            <span wire:loading wire:target="exportarAnalisisBrutoObras" class="inline-flex items-center gap-2">
                                <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Generando…
                            </span>
                        </button>
                    </div>
                </div>

                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-xs text-slate-600 flex items-start gap-2">
                    <i class="mgc_information_line text-slate-500 text-sm mt-0.5"></i>
                    <span>
                        Los ingresos se calculan sobre las facturas emitidas en estado <strong>emitida</strong>,
                        <strong>enviada</strong> o <strong>pagada</strong>. Los costes son las facturas
                        recibidas imputadas a cada obra (excluidas las devueltas). El margen se calcula sobre
                        el importe base (sin IVA).
                    </span>
                </div>
            </div>
        </div>

        {{-- ============================
             3. RETENCIONES POR OBRA
             ============================ --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-violet-50/40 px-5 py-4 sm:px-6">
                <div class="flex items-start gap-3">
                    <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-[0_8px_18px_rgba(124,58,237,0.25)]">
                        <i class="mgc_safe_alert_line text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-slate-900">
                            Retenciones por obra
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-0.5">
                            Detalle de las retenciones aplicadas en la obra: las que te retiene el cliente
                            (facturas emitidas) y las que retienes a proveedores (facturas recibidas),
                            con el resumen neto.
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Obra <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="retObraSeleccionada"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500">
                            <option value="">— Selecciona una obra —</option>
                            @foreach ($obras as $obra)
                                <option value="{{ $obra->id }}">{{ $obra->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Formato
                        </label>
                        <select wire:model.live="retFormato"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500">
                            <option value="pdf">PDF (.pdf)</option>
                            <option value="excel">Excel (.xlsx)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Fecha desde
                        </label>
                        <input type="date" wire:model.live="retFechaInicio"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 mb-1">
                            Fecha hasta
                        </label>
                        <input type="date" wire:model.live="retFechaFin"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-violet-500 focus:ring-violet-500">
                    </div>
                    <div class="sm:col-span-2 flex items-end">
                        <button wire:click="exportarRetencionesObra"
                            wire:loading.attr="disabled"
                            wire:target="exportarRetencionesObra"
                            @disabled(!$retObraSeleccionada)
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(124,58,237,0.22)] hover:from-violet-500 hover:to-purple-500 disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="exportarRetencionesObra" class="inline-flex items-center gap-2">
                                <i class="mgc_download_2_line"></i> Generar informe
                            </span>
                            <span wire:loading wire:target="exportarRetencionesObra" class="inline-flex items-center gap-2">
                                <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                Generando…
                            </span>
                        </button>
                    </div>
                </div>

                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-xs text-slate-600 flex items-start gap-2">
                    <i class="mgc_information_line text-slate-500 text-sm mt-0.5"></i>
                    <span>
                        Incluye solo facturas con retención mayor a 0. Las emitidas se cuentan en estado
                        <strong>emitida</strong>, <strong>enviada</strong> o <strong>pagada</strong>;
                        las recibidas excluyendo las devueltas. El resumen muestra el neto a favor o en contra.
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
