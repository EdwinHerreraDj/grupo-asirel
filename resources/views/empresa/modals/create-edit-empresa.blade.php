<div id="empresa"
    class="fixed top-0 left-0 z-50 hidden w-full h-full items-center justify-center fc-modal fc-modal-open:flex transition-all duration-500 bg-black/40 backdrop-blur-sm">
    <div
        class="fc-modal-open:opacity-100 opacity-0 transition-opacity duration-500 ease-out sm:max-w-lg w-full mx-4 sm:mx-auto flex flex-col bg-white border shadow-lg rounded-xl dark:bg-slate-800 dark:border-gray-700">

        <!-- Header -->
        <div class="flex justify-between items-center py-3 px-5 border-b dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-white text-lg">
                Configuración de los datos de la empresa
            </h3>
            <button
                class="inline-flex items-center justify-center h-8 w-8 rounded-full hover:bg-gray-100 dark:hover:bg-slate-700 dark:text-gray-200 transition-colors"
                data-fc-dismiss
                type="button"
                aria-label="Cerrar">
                <span class="material-symbols-rounded text-xl">close</span>
            </button>
        </div>

        <!-- Contenido -->
        <div class="p-6 overflow-y-auto max-h-[80vh]">
            {{-- Componente Livewire --}}
            <livewire:empresa.empresa-datos />
        </div>

    </div>
</div>
