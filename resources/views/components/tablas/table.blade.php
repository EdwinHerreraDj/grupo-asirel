<div class="bg-white rounded-2xl shadow-lg border border-gray-200 mt-6 overflow-hidden">

    {{-- FILTROS --}}
    <div class="bg-gray-50 border-b border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            {{ $title ?? 'Filtros de búsqueda' }}
        </h3>

        <div>
            {{ $filters }}
        </div>
    </div>

    {{-- TABLA --}}
    <div class="overflow-x-auto">

        <table class="min-w-full divide-y divide-gray-200 shadow rounded-lg overflow-hidden">

            {{-- CABECERA --}}
            <thead class="bg-gray-100 text-left text-gray-700 text-sm">
                <tr>
                    {{ $columns }}
                </tr>
            </thead>

            {{-- CONTENIDO --}}
            <tbody class="divide-y divide-gray-200 text-sm">
                {{ $rows }}
            </tbody>

        </table>
    </div>

    {{-- PAGINACIÓN --}}
    <div class="p-4 border-t border-gray-200">
        {{ $pagination ?? '' }}
    </div>

</div>


{{-- <x-tablas.table>


    <x-slot name="filters">
    </x-slot>


    <x-slot name="columns">
    </x-slot>


    <x-slot name="rows">
    </x-slot>


    <x-slot name="pagination">
    </x-slot>
</x-tablas.table> --}}
