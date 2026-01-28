<div class="space-y-6">

    {{-- BARRA SUPERIOR --}}
    <div class="flex flex-wrap items-end gap-4 bg-gray-50 border border-gray-200 rounded-lg p-4">

        <div class="flex flex-col">
            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">
                Periodo
            </label>

            <input
                type="month"
                wire:model="tmpPeriodo"
                class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-800"
            >
        </div>

        <button
            wire:click="aplicarPeriodo"
            class="bg-gray-900 text-white text-sm font-medium px-5 py-2 rounded-md hover:bg-gray-800 transition">
            Aplicar
        </button>

        <div class="flex-1"></div>

        <button
            wire:click="generarPdf"
            class="bg-red-600 text-white text-sm font-medium px-5 py-2 rounded-md hover:bg-red-700 transition">
            PDF
        </button>
    </div>

    {{-- TABLA --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
        <table class="min-w-full text-sm border-collapse">

            <thead class="bg-gray-100 text-gray-700">
                <tr class="border-b border-gray-300">
                    <th class="px-3 py-3 text-left font-semibold">Oficio</th>
                    <th class="px-3 py-3 text-center font-semibold">Ud</th>
                    <th class="px-3 py-3 text-right font-semibold">Contrato</th>
                    <th class="px-3 py-3 text-right font-semibold">Origen mes ant.</th>
                    <th class="px-3 py-3 text-right font-semibold">Mes</th>
                    <th class="px-3 py-3 text-right font-semibold">A origen</th>
                    <th class="px-3 py-3 text-right font-semibold">Pendiente</th>
                    <th class="px-3 py-3 text-right font-semibold">Precio</th>
                    <th class="px-3 py-3 text-right font-semibold">Importe mes</th>
                    <th class="px-3 py-3 text-right font-semibold">Importe a origen</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse ($filas as $fila)
                    <tr class="hover:bg-gray-50 transition">

                        <td class="px-3 py-2 font-medium text-gray-900">
                            {{ $fila['oficio'] }}
                        </td>

                        <td class="px-3 py-2 text-center text-gray-600">
                            {{ $fila['unidad'] }}
                        </td>

                        <td class="px-3 py-2 text-right">
                            {{ number_format($fila['contrato'], 2, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 text-right text-gray-500">
                            {{ number_format($fila['origen_anterior'], 2, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 text-right">
                            {{ number_format($fila['mes'], 2, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 text-right font-semibold text-gray-900">
                            {{ number_format($fila['a_origen'], 2, ',', '.') }}
                        </td>

                        <td
                            class="px-3 py-2 text-right font-semibold
                            {{ $fila['pendiente'] < 0 ? 'text-red-600' : 'text-green-700' }}">
                            {{ number_format($fila['pendiente'], 2, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 text-right text-gray-700">
                            {{ number_format($fila['precio_unitario'], 2, ',', '.') }} €
                        </td>

                        <td class="px-3 py-2 text-right font-semibold text-gray-900">
                            {{ number_format($fila['importe_mes'], 2, ',', '.') }} €
                        </td>

                        <td class="px-3 py-2 text-right font-semibold text-gray-900">
                            {{ number_format($fila['importe_origen'], 2, ',', '.') }} €
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500 italic">
                            No hay datos para el periodo seleccionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

</div>
