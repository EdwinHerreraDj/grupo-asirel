<div id="modalAgregarFactura" class="hidden fc-modal fc-modal-center sm:fc-modal-lg">
    <div class="fc-modal-content rounded-xl shadow-xl bg-white">

        <div class="flex items-center justify-between border-b p-4">
            <h3 class="text-lg font-semibold">Registrar Factura Recibida</h3>
            <button class="text-gray-500 hover:text-gray-700" data-fc-dismiss>
                <i class="mgc_close_line text-2xl"></i>
            </button>
        </div>

        <div class="p-6">

            {{-- Formulario Livewire --}}
            <form wire:submit.prevent="guardar" enctype="multipart/form-data">

                {{-- Proveedor --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Proveedor</label>
                    <select class="form-select" wire:model="proveedor_id">
                        <option value="">-- Seleccionar proveedor --</option>
                        @foreach ($proveedores as $prov)
                            <option value="{{ $prov->id }}">{{ $prov->nombre }}</option>
                        @endforeach
                    </select>
                    @error('proveedor_id')
                        <span class="text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Oficio --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Oficio</label>
                    <select class="form-select" wire:model="oficio_id">
                        <option value="">-- Seleccionar oficio --</option>
                        @foreach ($oficios as $oficio)
                            <option value="{{ $oficio->id }}">{{ $oficio->nombre }}</option>
                        @endforeach
                    </select>
                    @error('oficio_id')
                        <span class="text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tipo de coste --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Tipo de coste</label>
                    <select class="form-select" wire:model="tipo_coste">
                        <option value="material">Material</option>
                        <option value="mano_obra">Mano de obra</option>
                    </select>
                </div>

                {{-- Concepto --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Concepto</label>
                    <input type="text" class="form-input" wire:model="concepto"
                        placeholder="Pladur, reparación muro...">
                </div>

                {{-- Número factura --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Número factura</label>
                    <input type="text" class="form-input" wire:model="numero_factura">
                </div>

                {{-- Importe --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Importe (€)</label>
                    <input type="number" step="0.01" class="form-input" wire:model="importe">
                    @error('importe')
                        <span class="text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Fechas --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

                    <div>
                        <label class="form-label font-medium">Fecha factura</label>
                        <input type="date" class="form-input" wire:model="fecha_factura">
                    </div>

                    <div>
                        <label class="form-label font-medium">Fecha contable</label>
                        <input type="date" class="form-input" wire:model="fecha_contable">
                    </div>

                    <div>
                        <label class="form-label font-medium">Vencimiento</label>
                        <input type="date" class="form-input" wire:model="vencimiento">
                    </div>

                </div>

                {{-- Tipo de pago --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Tipo de pago</label>
                    <select class="form-select" wire:model="tipo_pago">
                        <option value="">-- Seleccionar --</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="pronto_pago">Pronto pago</option>
                        <option value="confirming">Confirming</option>
                        <option value="pagare">Pagaré</option>
                        <option value="contado">Contado</option>
                    </select>
                </div>

                {{-- Estado --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Estado</label>
                    <select class="form-select" wire:model="estado">
                        <option value="pendiente_de_vencimiento">Pendiente de vencimiento</option>
                        <option value="pagada">Pagada</option>
                        <option value="pendiente_de_emision">Pendiente de emisión</option>
                        <option value="aplazada">Aplazada</option>
                        <option value="impagada">Impagada</option>
                    </select>
                </div>

                {{-- Adjunto --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Adjuntar factura (PDF/Imagen)</label>
                    <input type="file" class="form-input" wire:model="adjunto">
                    @error('adjunto')
                        <span class="text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                {{-- BOTONES --}}
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" data-fc-dismiss
                        class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="px-4 py-2 rounded-full bg-primary text-white font-medium hover:bg-primary/80 transition">
                        Guardar factura
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
