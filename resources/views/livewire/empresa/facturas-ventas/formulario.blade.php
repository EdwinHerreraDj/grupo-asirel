<div >

    <form wire:submit.prevent="guardar" class="space-y-10">

        {{-- ======================
        DATOS GENERALES
        ======================= --}}
        <section class="bg-white rounded-2xl border p-6 space-y-6">

            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                Datos generales
            </h4>

            {{-- SERIE --}}
            <div>
                <label class="block text-sm font-medium mb-1">
                    Serie <span class="text-red-500">*</span>
                </label>
                <select
                    wire:model.defer="serie"
                    @disabled(!$editable)
                    class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                >
                    <option value="">— Selecciona una serie —</option>
                    @foreach ($series as $s)
                        <option value="{{ $s->serie }}">
                            {{ $s->serie }} (último nº {{ $s->ultimo_numero }})
                        </option>
                    @endforeach
                </select>
                @error('serie')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- FECHAS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm mb-1">
                        Fecha emisión <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="date"
                        wire:model.defer="fecha_emision"
                        @disabled(!$editable)
                        class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                    >
                </div>

                <div>
                    <label class="block text-sm mb-1">
                        Fecha contable <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="date"
                        wire:model.defer="fecha_contable"
                        @disabled(!$editable)
                        class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                    >
                </div>

                <div>
                    <label class="block text-sm mb-1">Vencimiento</label>
                    <input
                        type="date"
                        wire:model.defer="vencimiento"
                        @disabled(!$editable)
                        class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                    >
                </div>
            </div>
        </section>

        {{-- ======================
        CLIENTE / OBRA
        ======================= --}}
        <section class="bg-white rounded-2xl border p-6 space-y-6">

            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                Relación
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">
                        Cliente <span class="text-red-500">*</span>
                    </label>
                    <select
                        wire:model.defer="cliente_id"
                        @disabled(!$editable)
                        class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                    >
                        <option value="">— Seleccionar cliente —</option>
                        @foreach ($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                        @endforeach
                    </select>
                    @error('cliente_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm mb-1">Obra</label>
                    <select
                        wire:model.defer="obra_id"
                        @disabled(!$editable)
                        class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                    >
                        <option value="">— Sin obra asociada —</option>
                        @foreach ($obras as $o)
                            <option value="{{ $o->id }}">{{ $o->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        {{-- ======================
        CONFIGURACIÓN FISCAL
        ======================= --}}
        <section class="bg-gray-50 rounded-2xl border p-6 space-y-6">

            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                Configuración fiscal
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

                <div>
                    <label class="block text-sm mb-1">IVA %</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model.defer="iva_porcentaje"
                        @disabled(!$editable)
                        class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                    >
                </div>

                <div>
                    <label class="block text-sm mb-1">Retención %</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model.defer="retencion_porcentaje"
                        @disabled(!$editable)
                        class="w-full rounded-xl border-gray-300 focus:ring-primary focus:border-primary"
                    >
                </div>

                <div class="text-sm text-gray-500 leading-snug">
                    Los importes se calcularán automáticamente a partir de las líneas de factura.
                </div>
            </div>
        </section>

        {{-- ======================
        OBSERVACIONES
        ======================= --}}
        <section class="bg-white rounded-2xl border p-6 space-y-2">
            <label class="block text-sm font-medium">
                Observaciones
            </label>
            <textarea
                wire:model.defer="observaciones"
                rows="3"
                @disabled(!$editable)
                class="w-full rounded-xl border-gray-300 resize-none focus:ring-primary focus:border-primary"
            ></textarea>
        </section>

        {{-- ======================
        ACCIONES
        ======================= --}}
        <div class="flex justify-end gap-3 pt-6 border-t">

            <button
                type="button"
                wire:click="$dispatch('cerrarModalForm')"
                class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200"
            >
                Cancelar
            </button>

            <button
                type="submit"
                @disabled(!$editable)
                class="px-6 py-2 rounded-xl bg-primary text-white
                       hover:bg-primary/90 disabled:opacity-50"
            >
                Guardar
            </button>

        </div>

    </form>
</div>
