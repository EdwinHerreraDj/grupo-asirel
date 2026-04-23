<div id="empresa"
    class="fixed top-0 left-0 z-50 hidden w-full h-full items-center justify-center fc-modal fc-modal-open:flex transition-all duration-500 bg-black/50 backdrop-blur-sm overflow-y-auto">
    <div
        class="fc-modal-open:opacity-100 opacity-0 transition-opacity duration-500 ease-out sm:max-w-3xl w-full mx-4 my-8 flex flex-col bg-white border border-slate-200 shadow-2xl rounded-3xl overflow-hidden">

        {{-- HEADER --}}
        <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Configuración
                    </div>
                    <h3 class="mt-2 text-lg font-semibold text-slate-900">
                        Datos de la empresa
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Información que aparecerá en las cabeceras y pies de los PDFs.
                    </p>
                </div>
                <button
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                    data-fc-dismiss type="button" aria-label="Cerrar">
                    <i class="mgc_close_line text-lg"></i>
                </button>
            </div>
        </div>

        {{-- CONTENIDO --}}
        <div class="px-5 py-5 sm:px-6 overflow-y-auto max-h-[75vh]">
            <livewire:empresa.empresa-datos />
        </div>
    </div>
</div>
