<div class="space-y-3">

    {{-- TABLA DE PARTIDAS --}}
    @if (count($partidas) > 0)
        <div class="overflow-x-auto rounded border border-gray-200">
            <table class="min-w-full text-sm">
                <thead class="bg-white text-gray-600 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-left w-24">Código</th>
                        <th class="px-3 py-2 text-left">Descripción</th>
                        <th class="px-3 py-2 text-center w-20">Unidad</th>
                        <th class="px-3 py-2 text-right w-28">Medición</th>
                        <th class="px-3 py-2 text-right w-32">Precio unit.</th>
                        <th class="px-3 py-2 text-right w-32">Importe</th>
                        <th class="px-3 py-2 w-20"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($partidas as $partida)
                        <tr wire:key="partida-{{ $partida['id'] }}" class="border-t hover:bg-white transition-colors">

                            @if ($editandoId === $partida['id'])
                                {{-- FILA EN EDICIÓN --}}
                                <td class="px-2 py-2">
                                    <input type="text" wire:model="form.codigo" placeholder="Cód."
                                        class="w-full border rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="text" wire:model="form.descripcion" placeholder="Descripción"
                                        class="w-full border rounded px-2 py-1 text-sm">
                                    @error('form.descripcion')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-2 py-2">
                                    <input type="text" wire:model="form.unidad" placeholder="ud"
                                        class="w-full border rounded px-2 py-1 text-sm text-center">
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" step="0.0001" wire:model="form.medicion" placeholder="0"
                                        class="w-full border rounded px-2 py-1 text-sm text-right">
                                    @error('form.medicion')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-2 py-2">
                                    <input type="number" step="0.0001" wire:model="form.precio_unitario"
                                        placeholder="0.00" class="w-full border rounded px-2 py-1 text-sm text-right">
                                    @error('form.precio_unitario')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-2 py-2 text-right text-gray-400 text-sm">
                                    {{-- Importe calculado en tiempo real con Alpine --}}
                                    <span x-data="{
                                        get importe() {
                                            let m = parseFloat($wire.form.medicion) || 0;
                                            let p = parseFloat($wire.form.precio_unitario) || 0;
                                            return (m * p).toFixed(2);
                                        }
                                    }" x-text="importe + ' €'"></span>
                                </td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-1 justify-end">
                                        <button wire:click="guardarPartida"
                                            class="text-xs bg-primary text-white px-2 py-1 rounded hover:bg-primary/90">
                                            Guardar
                                        </button>
                                        <button wire:click="cancelar"
                                            class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded hover:bg-gray-300">
                                            Cancelar
                                        </button>
                                    </div>
                                </td>
                            @else
                                {{-- FILA NORMAL --}}
                                <td class="px-3 py-2 text-gray-500">
                                    {{ $partida['codigo'] ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-gray-800">
                                    {{ $partida['descripcion'] }}
                                </td>
                                <td class="px-3 py-2 text-center text-gray-600">
                                    {{ $partida['unidad'] ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700">
                                    {{ number_format($partida['medicion'], 4, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right text-gray-700">
                                    {{ number_format($partida['precio_unitario'], 4, ',', '.') }} €
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-gray-800">
                                    {{ number_format($partida['importe'], 2, ',', '.') }} €
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex gap-2 justify-end">
                                        <button wire:click="editarPartida({{ $partida['id'] }})"
                                            class="text-primary text-xs hover:underline">
                                            Editar
                                        </button>
                                        <button wire:click="eliminarPartida({{ $partida['id'] }})"
                                            wire:confirm="¿Eliminar esta partida?"
                                            class="text-red-500 text-xs hover:underline">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            @endif

                        </tr>
                    @endforeach

                    {{-- FILA NUEVA PARTIDA --}}
                    @if ($creandoNueva)
                        <tr class="border-t bg-blue-50">
                            <td class="px-2 py-2">
                                <input type="text" wire:model="form.codigo" placeholder="Cód."
                                    class="w-full border rounded px-2 py-1 text-sm">
                            </td>
                            <td class="px-2 py-2">
                                <input type="text" wire:model="form.descripcion" placeholder="Descripción *"
                                    class="w-full border rounded px-2 py-1 text-sm">
                                @error('form.descripcion')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-2 py-2">
                                <input type="text" wire:model="form.unidad" placeholder="ud"
                                    class="w-full border rounded px-2 py-1 text-sm text-center">
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" step="0.0001" wire:model="form.medicion" placeholder="0"
                                    class="w-full border rounded px-2 py-1 text-sm text-right">
                                @error('form.medicion')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-2 py-2">
                                <input type="number" step="0.0001" wire:model="form.precio_unitario"
                                    placeholder="0.00" class="w-full border rounded px-2 py-1 text-sm text-right">
                                @error('form.precio_unitario')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-2 py-2 text-right text-gray-400 text-sm">
                                <span x-data="{
                                    get importe() {
                                        let m = parseFloat($wire.form.medicion) || 0;
                                        let p = parseFloat($wire.form.precio_unitario) || 0;
                                        return (m * p).toFixed(2);
                                    }
                                }" x-text="importe + ' €'"></span>
                            </td>
                            <td class="px-2 py-2">
                                <div class="flex gap-1 justify-end">
                                    <button wire:click="guardarPartida"
                                        class="text-xs bg-primary text-white px-2 py-1 rounded hover:bg-primary/90">
                                        Guardar
                                    </button>
                                    <button wire:click="cancelar"
                                        class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded hover:bg-gray-300">
                                        Cancelar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endif

                </tbody>

                {{-- TOTAL DEL CAPÍTULO --}}
                <tfoot class="border-t-2 border-gray-200 bg-white">
                    <tr>
                        <td colspan="5" class="px-3 py-2 text-right text-sm font-semibold text-gray-700">
                            Total capítulo
                        </td>
                        <td class="px-3 py-2 text-right font-bold text-primary">
                            {{ number_format(collect($partidas)->sum('importe'), 2, ',', '.') }} €
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <p class="text-sm text-gray-500 italic text-center py-3">
            No hay partidas en este capítulo todavía.
        </p>
    @endif

    {{-- BOTÓN NUEVA PARTIDA --}}
    @if (!$creandoNueva && is_null($editandoId))
        <div class="flex justify-end">
            <button wire:click="nuevaPartida"
                class="text-sm bg-primary text-white px-3 py-1.5 rounded hover:bg-primary/90 flex items-center gap-1">
                <i class="mgc_add_line"></i>
                Nueva partida
            </button>
        </div>
    @endif

</div>
