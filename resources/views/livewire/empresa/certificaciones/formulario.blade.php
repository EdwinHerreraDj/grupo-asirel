<div class="overflow-y-auto">

    <form wire:submit.prevent="guardar" enctype="multipart/form-data" class="space-y-5">

        <!-- FECHA INGRESO -->
        <div>
            <label class="block font-medium mb-1">Fecha certificación *</label>
            <input type="date" wire:model.defer="fecha_ingreso"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            @error('fecha_ingreso')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- FECHA CONTABLE -->
        <div>
            <label class="block font-medium mb-1">Fecha contable</label>
            <input type="date" wire:model.defer="fecha_contable"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            @error('fecha_contable')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- NÚMERO CERTIFICACIÓN -->
        <div>
            <label class="block font-medium mb-1">Número certificación</label>
            <input type="text" wire:model.defer="numero_certificacion"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                placeholder="Ej: 001/2024">
            @error('numero_certificacion')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- TOTAL -->
        <div>
            <label class="block font-medium mb-1">Total (€) *</label>
            <input type="number" step="0.01" wire:model.defer="total"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                placeholder="Ej: 1500.00">
            @error('total')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- OFICIO -->
        <div>
            <label class="block font-medium mb-1">Oficio *</label>
            <select wire:model.defer="obra_gasto_categoria_id"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                <option value="">-- Seleccionar oficio --</option>
                @foreach ($oficios as $oficio)
                    <option value="{{ $oficio->id }}">{{ $oficio->nombre }}</option>
                @endforeach
            </select>
            @error('obra_gasto_categoria_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- TIPO DOCUMENTO -->
        <div>
            <label class="block font-medium mb-1">Tipo documento *</label>
            <select wire:model.live="tipo_documento"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                <option value="">-- Seleccionar tipo --</option>
                <option value="certificacion">Certificación</option>
                <option value="factura">Factura</option>
            </select>
            @error('tipo_documento')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- FECHA VENCIMIENTO (si factura) -->
        @if ($tipo_documento === 'factura')
            <div>
                <label class="block font-medium mb-1">Fecha vencimiento *</label>
                <input type="date" wire:model.defer="fecha_vencimiento"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                @error('fecha_vencimiento')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <!-- ESPECIFICACIÓN -->
        <div>
            <label class="block font-medium mb-1">Especificación</label>
            <textarea wire:model.defer="especificacion" rows="3"
                class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                placeholder="Especifica detalles adicionales..."></textarea>
            @error('especificacion')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- ADJUNTO -->
        <div x-data="{
            progreso: 0,
            cargando: false
        }" x-on:livewire-upload-start="cargando = true"
            x-on:livewire-upload-finish="cargando = false; progreso = 0" x-on:livewire-upload-error="cargando = false"
            x-on:livewire-upload-progress="progreso = $event.detail.progress">

            <label class="block font-medium mb-2">Documento adjunto (PDF/Imagen)</label>

            <div class="flex items-center gap-3">
                <!-- Botón de selección -->
                <label
                    class="px-4 py-2 rounded-xl bg-primary text-white font-medium cursor-pointer hover:bg-primary/90 transition">
                    Seleccionar archivo
                    <input type="file" wire:model="adjunto" class="hidden">
                </label>

                <!-- Nombre del archivo -->
                <span class="text-sm flex items-center gap-2">
                    @if ($adjunto)
                        <span class="inline-flex items-center gap-2 text-green-600 font-medium">
                            <i class="mgc_check_line text-lg"></i>
                            <span class="truncate max-w-[200px]">
                                {{ $adjunto->getClientOriginalName() }}
                            </span>
                        </span>
                    @else
                        <span class="text-gray-500 italic">Ningún archivo seleccionado</span>
                    @endif
                </span>
            </div>

            <!-- Barra de progreso -->
            <template x-if="cargando">
                <div class="mt-3 w-full">
                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-2 bg-primary rounded-full transition-all duration-200"
                            :style="`width: ${progreso}%`"></div>
                    </div>
                    <p class="text-xs text-primary font-medium mt-1" x-text="progreso + '%'"></p>
                </div>
            </template>

            @error('adjunto')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>


        <!-- BOTONES -->
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" wire:click="$dispatch('cerrarModal')"
                class="px-4 py-2 bg-gray-200 rounded-xl hover:bg-gray-300 transition">
                Cancelar
            </button>

            <button type="submit"
                class="px-5 py-2.5 rounded-xl bg-primary text-white font-semibold shadow-md hover:bg-primary/90 transition">
                Guardar
            </button>
        </div>

    </form>

</div>
