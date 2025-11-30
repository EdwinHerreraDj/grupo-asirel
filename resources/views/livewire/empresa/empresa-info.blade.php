<div
    class="p-6 bg-white dark:bg-slate-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 transition-all">
    @if ($empresa)
        <!-- Header -->
        <div
            class="flex flex-col sm:flex-row items-center sm:items-start gap-5 border-b border-gray-200 dark:border-gray-700 pb-5 mb-5">
            @if ($empresa->logo)
                <img src="{{ Storage::url($empresa->logo) }}" alt="Logo empresa"
                    class="w-20 h-20 rounded-full object-cover ring-2 ring-blue-100 dark:ring-blue-900/40">
            @else
                <div
                    class="w-20 h-20 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 ring-2 ring-gray-200 dark:ring-gray-600">
                    <span class="material-symbols-rounded text-4xl">business</span>
                </div>
            @endif

            <div class="text-center sm:text-left">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ $empresa->nombre }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    CIF: <span
                        class="font-medium text-gray-700 dark:text-gray-300">{{ $empresa->cif ?? 'Sin CIF' }}</span>
                </p>
                @if ($empresa->sitio_web)
                    <a href="{{ $empresa->sitio_web }}" target="_blank"
                        class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 text-sm mt-2 hover:underline">
                        <i class="mgc_link_2_line text-base"></i> {{ $empresa->sitio_web }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Datos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm text-gray-700 dark:text-gray-300">
            <div><span class="font-medium text-gray-800 dark:text-gray-200">Dirección:</span>
                {{ $empresa->direccion ?? '-' }}</div>
            <div><span class="font-medium text-gray-800 dark:text-gray-200">Código Postal:</span>
                {{ $empresa->codigo_postal ?? '-' }}</div>
            <div><span class="font-medium text-gray-800 dark:text-gray-200">Ciudad:</span> {{ $empresa->ciudad ?? '-' }}
            </div>
            <div><span class="font-medium text-gray-800 dark:text-gray-200">Provincia:</span>
                {{ $empresa->provincia ?? '-' }}</div>
            <div><span class="font-medium text-gray-800 dark:text-gray-200">Teléfono:</span>
                {{ $empresa->telefono ?? '-' }}</div>
            <div><span class="font-medium text-gray-800 dark:text-gray-200">Email:</span>
                <a href="mailto:{{ $empresa->email }}" class="hover:underline text-blue-600 dark:text-blue-400">
                    {{ $empresa->email ?? '-' }}
                </a>
            </div>
        </div>
    @else
        <div class="text-center py-10 text-gray-500 dark:text-gray-400">
            <i class="mgc_building_2_line text-4xl mb-2"></i>
            <p>No hay datos de empresa registrados.</p>
        </div>
    @endif
</div>
