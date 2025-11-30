<div id="modalInformeFacturas" class="hidden fc-modal fc-modal-bottom sm:fc-modal-md">
    <div class="fc-modal-content rounded-xl shadow-xl bg-white">

        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-semibold">Generar Informe de Facturas</h3>
            <button class="text-gray-500 hover:text-gray-700" data-fc-dismiss>
                <i class="mgc_close_line text-2xl"></i>
            </button>
        </div>

        <div class="p-6">

            <form id="form-informe" method="POST">
                @csrf

                {{-- Fechas --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                    <div>
                        <label class="form-label font-medium">Desde</label>
                        <input type="date" class="form-input" name="fecha_inicio">
                    </div>

                    <div>
                        <label class="form-label font-medium">Hasta</label>
                        <input type="date" class="form-input" name="fecha_fin">
                    </div>

                </div>

                {{-- Tipo de informe --}}
                <div class="mb-4">
                    <label class="form-label font-medium">Tipo de informe</label>
                    <select class="form-select" name="tipo_informe">
                        <option value="libro">Libro de facturas</option>
                        <option value="tesoreria">Tesorería</option>
                    </select>
                </div>

                {{-- Botones --}}
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" data-fc-dismiss
                        class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="px-4 py-2 rounded-full bg-primary text-white font-medium hover:bg-primary/80 transition">
                        Generar informe
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
