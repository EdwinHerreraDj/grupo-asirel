<div class="space-y-6">
    <div class="card p-6">

        {{-- CABECERA --}}
        <div class="flex items-center gap-2">
            <x-btns.regresar href="{{ route('unidad') }}">
                Regresar
            </x-btns.regresar>
        </div>

        <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4 mt-4">
            Presupuesto de venta de la obra: {{ $obra->nombre }}
        </h2>

        {{-- TABLA DE CAPÍTULOS --}}
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="px-3 py-2 text-left">Capítulo / Oficio</th>
                        <th class="px-3 py-2 text-right">Coste teórico</th>
                        <th class="px-3 py-2 text-right">Total venta</th>
                        <th class="px-3 py-2 text-center w-28">Partidas</th>
                        <th class="px-3 py-2 w-8"></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($oficios as $oficio)
                        @php
                            $capitulo = $capitulos[$oficio->id] ?? null;
                            $totalVenta = (float) ($capitulo?->importe_total ?? 0);
                            $costeTeorico = (float) ($costesTeoricos[$oficio->id] ?? 0);
                            $numPartidas = $capitulo?->partidas->count() ?? 0;
                            $abierto = $capituloActivoId === $oficio->id;

                            $estado = 'ok';
                            if ($totalVenta > 0 && $costeTeorico == 0) {
                                $estado = 'sin_coste';
                            } elseif ($totalVenta > $costeTeorico && $costeTeorico > 0) {
                                $estado = 'venta_mayor';
                            } elseif ($totalVenta < $costeTeorico && $totalVenta > 0) {
                                $estado = 'venta_menor';
                            }
                        @endphp

                        {{-- FILA CAPÍTULO — solo esta fila tiene wire:click --}}
                        <tr wire:key="capitulo-{{ $oficio->id }}"
                            class="border-t cursor-pointer transition-colors
            hover:bg-gray-50
            @if ($abierto) bg-blue-50
            @elseif ($estado === 'venta_mayor') bg-emerald-50
            @elseif ($estado === 'venta_menor') bg-amber-50
            @elseif ($estado === 'sin_coste') bg-sky-50 @endif">
                            {{-- Click solo en las celdas del capítulo, no en toda la fila --}}
                            <td class="px-3 py-3 font-medium text-gray-800"
                                wire:click="toggleCapitulo({{ $oficio->id }})">
                                <div class="flex items-center gap-2">
                                    <i class="mgc_{{ $abierto ? 'up' : 'right' }}_line text-gray-400 text-xs"></i>
                                    {{ $oficio->nombre }}
                                </div>
                            </td>

                            <td class="px-3 py-3 text-right text-gray-600"
                                wire:click="toggleCapitulo({{ $oficio->id }})">
                                {{ number_format($costeTeorico, 2, ',', '.') }} €
                            </td>

                            <td class="px-3 py-3 text-right" wire:click="toggleCapitulo({{ $oficio->id }})">
                                <span class="font-semibold">
                                    {{ number_format($totalVenta, 2, ',', '.') }} €
                                </span>

                                @if ($estado === 'venta_mayor')
                                    <span
                                        class="ml-1 inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                    bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="mgc_arrow_up_line"></i> Venta &gt; coste
                                    </span>
                                @elseif ($estado === 'venta_menor')
                                    <span
                                        class="ml-1 inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                    bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="mgc_arrow_down_line"></i> Venta &lt; coste
                                    </span>
                                @elseif ($estado === 'sin_coste')
                                    <span
                                        class="ml-1 inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                    bg-sky-100 text-sky-800 border border-sky-200">
                                        <i class="mgc_information_line"></i> Sin coste teórico
                                    </span>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-center" wire:click="toggleCapitulo({{ $oficio->id }})">
                                <span class="text-xs text-gray-500">
                                    {{ $numPartidas }} partida{{ $numPartidas !== 1 ? 's' : '' }}
                                </span>
                            </td>

                            <td class="px-3 py-3 text-right text-gray-400"
                                wire:click="toggleCapitulo({{ $oficio->id }})">
                                <i class="mgc_{{ $abierto ? 'up' : 'down' }}_line"></i>
                            </td>
                        </tr>

                        {{-- FILA PARTIDAS — sin wire:click, completamente aislada --}}
                        @if ($abierto && $capitulo)
                            <tr wire:key="partidas-row-{{ $oficio->id }}">
                                <td colspan="5" class="bg-gray-50 border-t border-blue-100 px-4 py-4">
                                    @livewire('obras.presupuesto-venta-partidas', ['capitulo' => $capitulo], key('partidas-' . $oficio->id))
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>

                {{-- TOTALES --}}
                <tfoot class="bg-gray-100 border-t-2 border-gray-300">
                    <tr>
                        <td class="px-3 py-3 font-semibold text-gray-700">Total</td>
                        <td class="px-3 py-3 text-right font-semibold text-gray-700">
                            {{ number_format(array_sum($costesTeoricos), 2, ',', '.') }} €
                        </td>
                        <td class="px-3 py-3 text-right font-bold text-primary">
                            {{ number_format($capitulos->sum('importe_total'), 2, ',', '.') }} €
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ACCIONES --}}
        <div class="flex justify-end gap-3 mt-6">
            <button wire:click="descargarInforme"
                class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 text-sm">
                Descargar informe
            </button>
        </div>

    </div>
</div>
