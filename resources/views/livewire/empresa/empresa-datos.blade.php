@php
    $label = 'block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1';
    $input = 'w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 placeholder:text-slate-400 shadow-sm focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100 focus:outline-none transition';
    $seccion = 'text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700 flex items-center gap-2 mb-3';
    $dot = 'h-1.5 w-1.5 rounded-full bg-cyan-500';
@endphp

<div>
    <form wire:submit.prevent="guardar" enctype="multipart/form-data" class="space-y-6">

        {{-- ==================== IDENTIFICACIÓN ==================== --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
            <p class="{{ $seccion }}"><span class="{{ $dot }}"></span> Identificación</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="nombre"
                        placeholder="Ej: Construcciones Alminares S.L."
                        class="{{ $input }}">
                    @error('nombre')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="{{ $label }}">CIF / NIF</label>
                    <input type="text" wire:model="cif" placeholder="Ej: B12345678" class="{{ $input }}">
                </div>
            </div>
        </div>

        {{-- ==================== CONTACTO ==================== --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
            <p class="{{ $seccion }}"><span class="{{ $dot }}"></span> Contacto</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="{{ $label }}">Email</label>
                    <input type="email" wire:model="email" placeholder="info@miempresa.com"
                        class="{{ $input }}">
                    @error('email')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="{{ $label }}">Teléfono</label>
                    <input type="text" wire:model="telefono" placeholder="+34 958 123 456"
                        class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Sitio web</label>
                    <input type="url" wire:model="sitio_web" placeholder="https://miempresa.com"
                        class="{{ $input }}">
                    @error('sitio_web')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ==================== DIRECCIÓN ==================== --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
            <p class="{{ $seccion }}"><span class="{{ $dot }}"></span> Dirección</p>
            <div class="space-y-4">
                <div>
                    <label class="{{ $label }}">Dirección</label>
                    <input type="text" wire:model="direccion" placeholder="Calle Real, 25, 3ºB"
                        class="{{ $input }}">
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="{{ $label }}">Código postal</label>
                        <input type="text" wire:model="codigo_postal" placeholder="18001"
                            class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Ciudad</label>
                        <input type="text" wire:model="ciudad" placeholder="Granada"
                            class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Provincia</label>
                        <input type="text" wire:model="provincia" placeholder="Granada"
                            class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">País</label>
                        <input type="text" wire:model="pais" placeholder="España"
                            class="{{ $input }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== LOGO ==================== --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
            <p class="{{ $seccion }}"><span class="{{ $dot }}"></span> Logo</p>

            <div class="flex flex-col sm:flex-row gap-5 items-start">
                {{-- Preview --}}
                <div class="shrink-0">
                    <div class="flex h-28 w-28 items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white overflow-hidden">
                        @if ($empresa && $empresa->logo)
                            <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo actual"
                                class="h-full w-full object-contain p-2">
                        @else
                            <div class="text-center text-slate-400">
                                <i class="mgc_image_2_line text-3xl"></i>
                                <p class="text-xs mt-1">Sin logo</p>
                            </div>
                        @endif
                    </div>
                    <p class="mt-2 text-center text-xs text-slate-500">
                        {{ $empresa && $empresa->logo ? 'Logo actual' : 'Sin logo' }}
                    </p>
                </div>

                {{-- Upload --}}
                <div class="flex-1 w-full">
                    <label for="logo"
                        class="flex flex-col items-center justify-center gap-2 p-5 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-white hover:border-cyan-400 hover:bg-cyan-50/40 transition">
                        <i class="mgc_upload_2_line text-2xl text-slate-400"></i>
                        <p class="text-sm text-slate-600">
                            Arrastra un logo o <span class="text-cyan-600 font-semibold">selecciónalo</span>
                        </p>
                        <p class="text-xs text-slate-400">JPG, PNG o WEBP · máx. 2 MB · recomendado 500×500 px</p>
                    </label>
                    <input type="file" id="logo" wire:model="logo" class="hidden"
                        accept="image/jpeg,image/png,image/webp">

                    <div wire:loading wire:target="logo" class="mt-2 text-xs text-slate-500">
                        Subiendo logo…
                    </div>

                    @error('logo')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ==================== DESCRIPCIÓN ==================== --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
            <p class="{{ $seccion }}"><span class="{{ $dot }}"></span> Descripción</p>
            <textarea wire:model="descripcion" rows="3"
                placeholder="Breve descripción de la empresa (opcional, se muestra en algunos informes)."
                class="{{ $input }}"></textarea>
        </div>

        {{-- ==================== FOOTER ==================== --}}
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-200">
            <button type="button" data-fc-dismiss
                class="px-4 py-2.5 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100 transition">
                Cancelar
            </button>
            <button type="submit" wire:loading.attr="disabled" wire:target="guardar"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-cyan-600 to-blue-600 text-white shadow-[0_8px_20px_rgba(37,99,235,0.22)] transition hover:from-cyan-500 hover:to-blue-500 disabled:opacity-60">
                <span wire:loading.remove wire:target="guardar">
                    <i class="mgc_check_line me-1"></i>
                    {{ $modoEdicion ? 'Guardar cambios' : 'Crear empresa' }}
                </span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </button>
        </div>
    </form>
</div>
