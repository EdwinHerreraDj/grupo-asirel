<div id="pdfModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex justify-center overflow-y-auto">
    <div
        class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-5xl h-[90vh] mt-10 mb-10 flex flex-col overflow-hidden border border-gray-200 dark:border-gray-700">

        <!-- Header del visor -->
        <div
            class="flex items-center justify-between bg-gray-100 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200">
                <i class="mgc_file_pdf_line text-red-600 text-xl"></i>
                <span id="pdfFileName" class="text-sm font-medium truncate max-w-[200px]">Visor de PDF</span>
            </div>
            <button id="closePdfModal"
                class="text-gray-500 hover:text-red-500 text-xl font-bold transition-all duration-200">
                &times;
            </button>
        </div>

        <!-- Contenedor del PDF -->
        <div class="flex-1 bg-gray-50 dark:bg-gray-950 relative">
            <iframe id="pdfViewer" src="" class="w-full h-full rounded-b-xl" frameborder="0"></iframe>
        </div>

    </div>
</div>
