<div class="space-y-6">
    <div class="card p-6">
        <div class="flex items-center gap-2">
            <x-btns.regresar href="{{ route('unidad') }}">
                Regresar
            </x-btns.regresar>
        </div>

        <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4 mt-4">
            Presupuesto de venta de la obra: {{ $obra->nombre }}
        </h2>

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-left">Oficio</th>
                        <th class="px-3 py-2 text-left">Unidad</th>
                        <th class="px-3 py-2 text-right">Cantidad</th>
                        <th class="px-3 py-2 text-right">Precio unit.</th>
                        <th class="px-3 py-2 text-right">Coste teórico</th>
                        <th class="px-3 py-2 text-right">Total venta</th>
                        <th class="px-3 py-2 text-left">Observaciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($oficios as $oficio)
                        @php
                            $row = $presupuestos[$oficio->id] ?? [];

                            $cantidad = (float) ($row['cantidad'] ?? 0);
                            $precio = (float) ($row['precio_unitario'] ?? 0);
                            $totalVenta = $cantidad * $precio;

                            $costeTeorico = (float) ($costesTeoricos[$oficio->id] ?? 0);

                            // Estado visual
                            $estado = 'ok';
                            if ($totalVenta > 0 && $costeTeorico == 0) {
                                $estado = 'sin_coste';
                            } elseif ($totalVenta > $costeTeorico && $costeTeorico > 0) {
                                $estado = 'venta_mayor';
                            } elseif ($totalVenta < $costeTeorico && $totalVenta > 0) {
                                $estado = 'venta_menor';
                            }
                        @endphp

                        <tr
                            class="border-t
                        @if ($estado === 'venta_mayor') bg-emerald-50
                        @elseif($estado === 'venta_menor') bg-amber-50
                        @elseif($estado === 'sin_coste') bg-sky-50 @endif
                    ">
                            <td class="px-3 py-2 font-medium text-gray-800">
                                {{ $oficio->nombre }}
                            </td>

                            <td class="px-3 py-2">
                                <input type="text" class="w-20 border rounded px-2 py-1"
                                    wire:model.defer="presupuestos.{{ $oficio->id }}.unidad">
                            </td>

                            <td class="px-3 py-2 text-right">
                                <input type="number" step="0.001" class="w-24 border rounded px-2 py-1 text-right"
                                    wire:model.defer="presupuestos.{{ $oficio->id }}.cantidad">
                            </td>

                            <td class="px-3 py-2 text-right">
                                <input type="number" step="0.0001" class="w-24 border rounded px-2 py-1 text-right"
                                    wire:model.defer="presupuestos.{{ $oficio->id }}.precio_unitario">
                            </td>

                            <td class="px-3 py-2 text-right text-gray-700">
                                {{ number_format($costeTeorico, 2, ',', '.') }} €
                            </td>

                            <td class="px-3 py-2 text-right font-semibold">
                                {{ number_format($totalVenta, 2, ',', '.') }} €

                                {{-- Avisos --}}
                                @if ($estado === 'venta_mayor')
                                    <div
                                        class="mt-1 inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                            bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="mgc_arrow_up_line"></i>
                                        Venta &gt; coste
                                    </div>
                                @elseif($estado === 'venta_menor')
                                    <div
                                        class="mt-1 inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                            bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="mgc_arrow_down_line"></i>
                                        Venta &lt; coste
                                    </div>
                                @elseif($estado === 'sin_coste')
                                    <div
                                        class="mt-1 inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                            bg-sky-100 text-sky-800 border border-sky-200">
                                        <i class="mgc_information_line"></i>
                                        Sin coste teórico
                                    </div>
                                @endif
                            </td>

                            <td class="px-3 py-2">
                                <input type="text" class="w-full border rounded px-2 py-1"
                                    wire:model.defer="presupuestos.{{ $oficio->id }}.observaciones">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button wire:click="descargarInforme" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">
                Descargar informe
            </button>

            <button wire:click="guardar" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Guardar presupuesto
            </button>
        </div>

    </div>

</div>
