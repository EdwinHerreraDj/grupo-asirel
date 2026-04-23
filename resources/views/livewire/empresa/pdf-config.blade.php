@php
    $label = 'block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1';
    $seccion = 'text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700 flex items-center gap-2 mb-3';
    $dot = 'h-1.5 w-1.5 rounded-full bg-cyan-500';
@endphp

<div>
    <form wire:submit.prevent="guardar" class="space-y-5">

        {{-- COLORES --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
            <p class="{{ $seccion }}"><span class="{{ $dot }}"></span> Colores</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">Color principal</label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="color_primario"
                            class="h-11 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                        <input type="text" wire:model.live="color_primario"
                            placeholder="#111827" maxlength="7"
                            class="flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2 font-mono text-sm uppercase text-slate-800 shadow-sm focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100 focus:outline-none">
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Se usa en cabeceras, totales y títulos.</p>
                    @error('color_primario')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="{{ $label }}">Color secundario</label>
                    <div class="flex items-center gap-3">
                        <input type="color" wire:model.live="color_secundario"
                            class="h-11 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                        <input type="text" wire:model.live="color_secundario"
                            placeholder="#d1d5db" maxlength="7"
                            class="flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2 font-mono text-sm uppercase text-slate-800 shadow-sm focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100 focus:outline-none">
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Bordes de tabla, líneas suaves y etiquetas.</p>
                    @error('color_secundario')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Preview --}}
            <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-2">Previsualización</p>
                <div class="space-y-2">
                    <div class="rounded-lg px-4 py-2 text-sm font-semibold text-white"
                        style="background-color: {{ $color_primario }};">
                        Cabecera de tabla / total destacado
                    </div>
                    <div class="rounded-lg px-4 py-2 text-sm text-slate-800"
                        style="border: 1px solid {{ $color_secundario }}; border-left: 3px solid {{ $color_primario }};">
                        Fila de contenido con borde secundario
                    </div>
                </div>
            </div>
        </div>

        {{-- OPCIONES --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
            <p class="{{ $seccion }}"><span class="{{ $dot }}"></span> Opciones</p>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" wire:model.live="mostrar_logo_pdf"
                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-200">
                <span>
                    <span class="text-sm font-medium text-slate-800">Mostrar logo en los PDFs</span>
                    <span class="block text-xs text-slate-500">Si no tienes logo subido, no aparecerá nada aunque esté activo.</span>
                </span>
            </label>
        </div>

        {{-- PIE --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
            <p class="{{ $seccion }}"><span class="{{ $dot }}"></span> Pie de página personalizado</p>
            <textarea wire:model="pie_pdf" rows="3" maxlength="500"
                placeholder="Ej: Inscrita en el Registro Mercantil de Granada, Tomo 1234, Folio 56 · CIF B12345678"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100 focus:outline-none"></textarea>
            <p class="mt-1 text-xs text-slate-500">Opcional. Aparece al final del PDF, debajo de los datos de empresa.</p>
            @error('pie_pdf')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- FOOTER --}}
        <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-200">
            <button type="button" wire:click="restablecerDefaults"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition">
                <i class="mgc_refresh_2_line"></i>
                Restablecer valores por defecto
            </button>

            <button type="submit" wire:loading.attr="disabled" wire:target="guardar"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow-[0_8px_20px_rgba(37,99,235,0.22)] transition hover:from-cyan-500 hover:to-blue-500 disabled:opacity-60">
                <span wire:loading.remove wire:target="guardar">
                    <i class="mgc_check_line me-1"></i> Guardar diseño
                </span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </button>
        </div>
    </form>
</div>
