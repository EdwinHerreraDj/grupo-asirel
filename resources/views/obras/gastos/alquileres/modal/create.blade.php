<div
    class="fixed top-0 left-0 z-50 transition-all duration-500 fc-modal hidden w-full h-full min-h-full items-center fc-modal-open:flex">
    <div
        class="fc-modal-open:opacity-100 duration-500 opacity-0 ease-out transition-[opacity] sm:max-w-lg sm:w-full sm:mx-auto  flex-col bg-white border shadow-sm rounded-md dark:bg-slate-800 dark:border-gray-700">
        <div class="flex justify-between items-center py-2.5 px-4 border-b dark:border-gray-700">
            <h3 class="font-medium text-gray-800 dark:text-white text-lg">
                Gasto de Alquileres obra {{ $obra->nombre }}
            </h3>
            <button class="inline-flex flex-shrink-0 justify-center items-center h-8 w-8 dark:text-gray-200"
                data-fc-dismiss type="button">
                <span class="material-symbols-rounded">close</span>
            </button>
        </div>


        <div class="px-4 py-8 overflow-y-auto">
            <div>
                <form id="form" action="{{ route('alquileres.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="obra_id" value="{{ $obra->id }}">

                    <label for="nombre" class="font-medium text-gray-700">Tipo de alquiler:</label>
                    <input
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400 mb-5"
                        type="text" name="nombre" id="nombre" placeholder="Nombre para el alquiler" required>

                    <div id="descripcion-container">
                        <label for="descripcion" class="font-medium text-gray-700">Especifique el tipo de
                            alquiler:</label>
                        <input
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400 mb-5"
                            type="text" name="descripcion" id="descripcion" placeholder="Campo opcional">
                    </div>

                    <label for="fecha" class="font-medium text-gray-700">Fecha del gasto:</label>
                    <input
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400 mb-5"
                        type="date" name="fecha" id="fecha" required>

                    <label for="precio_unitario" class="font-medium text-gray-700">Precio Unitario:</label>
                    <input
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400 mb-5"
                        type="number" step="0.01" name="precio_unitario" id="precio_unitario" placeholder="(100000)"
                        required>

                    <label for="cantidad" class="font-medium text-gray-700">Cantidad:</label>
                    <input
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400 mb-5"
                        type="number" name="cantidad" id="cantidad" placeholder="(100000)" required>

                    <label for="numero_factura" class="font-medium text-gray-700">Número de factura:</label>
                    <input
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400 mb-5"
                        type="text" name="numero_factura" id="numero_factura" placeholder="Número de factura"
                        required>

                    <label for="archivo_factura" class="font-medium text-gray-700">Factura:</label>
                    <div class="relative mb-5">
                        <input
                            class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50
                   focus:outline-none focus:ring focus:ring-blue-200 focus:border-blue-400
                   file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                   file:text-sm file:font-medium file:bg-blue-600 file:text-white
                   hover:file:bg-blue-700 transition-all"
                            type="file" name="archivo_factura" id="archivo_factura"
                            accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                    application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,
                    application/x-rar-compressed,application/zip">
                    </div>

                    <div class="flex justify-end items-center gap-4 p-4 border-t dark:border-slate-700">
                        <button
                            class="py-2 px-5 inline-flex justify-center items-center gap-2 rounded dark:text-gray-200 border dark:border-slate-700 font-medium hover:bg-slate-100 hover:dark:bg-slate-700 transition-all"
                            data-fc-dismiss type="button">Cancelar</button>
                        <button id="submit"
                            class="py-2.5 px-4 inline-flex justify-center items-center gap-2 rounded bg-primary hover:bg-primary-600 text-white"
                            type="submit">Generar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
