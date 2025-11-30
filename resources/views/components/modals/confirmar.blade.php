@props([
    'titulo' => '',
    'mensaje' => '',
])
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">

    <div
        class="bg-white w-full max-w-md p-6 rounded-2xl shadow-2xl border border-gray-200 relative 
               animate-[fadeIn_0.25s_ease-out,slideUp_0.25s_ease-out]">


        {{-- HEADER --}}
        <div class="flex items-center gap-4 mb-5 pb-4 border-b border-gray-200">

            <div class="bg-red-100 text-red-600 w-12 h-12 flex items-center justify-center rounded-xl text-3xl">
                <i class="mgc_warning_line"></i>
            </div>

            <div>
                <h3 class="text-xl font-bold text-gray-900">
                    {{ $titulo }}
                </h3>
                <p class="text-sm text-gray-500 mt-1">
                    Acción sensible — revísala antes de continuar
                </p>
            </div>
        </div>

        {{-- CONTENIDO --}}
        <p class="text-gray-700 leading-relaxed mb-8 text-[15px]">
            {!! $mensaje !!}
        </p>

        {{-- ACCIONES --}}
        <div class="flex justify-end gap-3 pt-3">

            {{ $slot }}

        </div>

    </div>
</div>
