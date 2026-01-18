<div>

    {{-- CABECERA --}}
    <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
        <div class="flex gap-2">
            <x-btns.regresar href="{{ route('obras.certificaciones', $obraId) }}">
                Volver a certificaciones
            </x-btns.regresar>
        </div>
        <div>

            {{-- Contexto de obra (si lo estás pasando) --}}
            @if (!empty($obra))
                <p class="text-sm text-gray-500 mt-1">
                    Obra: <span
                        class="font-semibold text-gray-700">{{ $obra->titulo ?? ($obra->nombre ?? '#' . $obra->id) }}</span>
                </p>
            @endif
        </div>
    </div>

    {{-- INFO / AYUDA --}}
    <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl p-4 text-sm mb-6">
        <div class="flex items-start gap-3">
            <div class="mt-0.5">
                <i class="mgc_information_line text-lg"></i>
            </div>
            <div class="space-y-1">
                <p class="font-semibold">Qué hace esta pantalla</p>
                <p>
                    Solo se muestran certificaciones <span class="font-semibold">aceptadas</span> y <span
                        class="font-semibold">pendientes</span>.
                    Al facturar, se emite la factura y las certificaciones quedan marcadas como <span
                        class="font-semibold">facturadas</span>.
                </p>
            </div>
        </div>
    </div>

    <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4">
        Certificaciones pendientes de facturar
    </h2>

    {{-- TABLA --}}

    <x-tablas.table>

        <x-slot name="columns">
            <th class="px-3 py-2 w-44 text-left">Nº Certificación</th>
            <th class="px-3 py-2 text-left">Cliente</th>
            <th class="px-3 py-2 w-32 text-right">Base</th>
            <th class="px-3 py-2 w-32 text-right">Total</th>
            <th class="px-3 py-2 w-32 text-center">Acción</th>
        </x-slot>

        <x-slot name="rows">
            @forelse ($certificaciones as $cert)
                <tr class="border-b hover:bg-gray-50 transition">

                    {{-- Nº Certificación --}}
                    <td class="px-3 py-2">
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                     bg-gray-100 text-gray-800 border border-gray-200">
                            {{ $cert->numero_certificacion }}
                        </span>
                    </td>

                    {{-- Cliente --}}
                    <td class="px-3 py-2">
                        <div class="flex flex-col min-w-0">
                            <span class="font-medium text-gray-800 truncate">
                                {{ $cert->cliente->nombre ?? '—' }}
                            </span>

                            @if (!empty($cert->cliente?->nif))
                                <span class="text-xs text-gray-500 truncate">
                                    NIF: {{ $cert->cliente->nif }}
                                </span>
                            @endif
                        </div>
                    </td>

                    {{-- Base --}}
                    <td class="px-3 py-2 text-right font-medium text-gray-700 whitespace-nowrap">
                        {{ number_format($cert->base, 2, ',', '.') }} €
                    </td>

                    {{-- Total --}}
                    <td class="px-3 py-2 text-right font-semibold text-primary whitespace-nowrap">
                        {{ number_format($cert->total, 2, ',', '.') }} €
                    </td>

                    {{-- Acción --}}
                    <td class="px-3 py-2 text-center">
                        <button wire:click="confirmarFacturacion('{{ $cert->numero_certificacion }}')"
                            class="inline-flex items-center justify-center gap-2 px-3 py-1.5 rounded-xl
                                   bg-primary text-white text-xs font-semibold
                                   hover:bg-primary/90 transition shadow-sm">
                            <i class="mgc_document_3_line text-base"></i>
                            Facturar
                        </button>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                        No hay certificaciones pendientes de facturar en esta obra.
                    </td>
                </tr>
            @endforelse
        </x-slot>

    </x-tablas.table>



    {{-- MODAL CONFIRMACIÓN (con resumen real) --}}
    @if ($showConfirmModal)
        <div class="fixed top-0 left-0 w-screen h-screen z-[99999] bg-black/50 backdrop-blur-sm">
            <div class="w-full h-full flex items-center justify-center p-4">

                <div
                    class="bg-white w-full max-w-3xl rounded-2xl border border-gray-200
                        shadow-[0_15px_40px_rgba(0,0,0,0.25)]
                        overflow-hidden max-h-[92vh] flex flex-col">

                    {{-- Header --}}
                    <div class="flex items-start gap-3 p-5 border-b bg-white">
                        <div
                            class="bg-primary/10 text-primary w-11 h-11 flex items-center justify-center rounded-xl shrink-0">
                            <i class="mgc_document_3_line text-xl"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-gray-900 leading-tight">
                                Emitir factura
                            </h3>
                            <p class="text-sm text-gray-500 truncate">
                                Certificación {{ $numeroCertificacionSeleccionada }}
                            </p>
                        </div>

                        <button wire:click="cancelarFacturacion"
                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                               text-gray-500 hover:text-red-600 hover:bg-red-50 transition">
                            <i class="mgc_close_line text-xl"></i>
                        </button>
                    </div>

                    @if ($modalError)
                        <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 text-sm">
                            <div class="flex items-start gap-2">
                                <i class="mgc_file_warning_line text-lg mt-0.5"></i>
                                <span class="font-medium">{{ $modalError }}</span>
                            </div>
                        </div>
                    @endif


                    {{-- Body (scroll interno) --}}
                    <div class="p-6 space-y-5 text-sm text-gray-700 overflow-y-auto">

                        {{-- Resumen superior --}}
                        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <p class="text-xs text-gray-500">Capítulos incluidos</p>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ $resumenFactura['capitulos'] ?? 0 }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl
                                             bg-yellow-100 text-yellow-800 text-xs font-semibold border border-yellow-200">
                                        <i class="mgc_alert_line text-base"></i>
                                        Acción irreversible
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Aviso --}}
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-2xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-yellow-100 flex items-center justify-center shrink-0">
                                    <i class="mgc_alert_line text-lg text-yellow-700"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="font-semibold text-sm">
                                        Esta acción emite la factura y marca todas las certificaciones asociadas como
                                        facturadas.
                                    </p>
                                    <p class="text-xs text-yellow-800 mt-1">
                                        No se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Serie de facturación --}}
                        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4">
                            <label class="block text-xs font-medium text-gray-500 mb-1">
                                Serie de facturación <span class="text-red-600">*</span>
                            </label>

                            <select wire:model="serieSeleccionada"
                                class="w-full rounded-xl border-gray-300 text-sm
                                focus:ring-primary focus:border-primary">
                                <option value="">— Selecciona una serie —</option>

                                @foreach ($series as $serie)
                                    <option value="{{ $serie->serie }}">
                                        {{ $serie->serie }} (último nº {{ $serie->ultimo_numero }})
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        {{-- Resumen fiscal --}}
                        @if (!empty($resumenFactura))
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4">
                                    <p class="text-xs text-gray-500">Cliente</p>
                                    <p class="font-semibold text-gray-900 truncate">
                                        {{ $resumenFactura['cliente'] ?? '—' }}
                                    </p>
                                </div>

                                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4">
                                    <p class="text-xs text-gray-500">Base</p>
                                    <p class="font-semibold text-gray-900">
                                        {{ number_format($resumenFactura['base'] ?? 0, 2, ',', '.') }} €
                                    </p>
                                </div>

                                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4">
                                    <p class="text-xs text-gray-500">IVA</p>
                                    <p class="font-semibold text-blue-700">
                                        {{ number_format($resumenFactura['iva'] ?? 0, 2, ',', '.') }} €
                                    </p>
                                </div>

                                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4">
                                    <p class="text-xs text-gray-500">IRPF</p>
                                    <p class="font-semibold text-red-600">
                                        -{{ number_format($resumenFactura['ret'] ?? 0, 2, ',', '.') }} €
                                    </p>
                                </div>

                                <div class="bg-white border border-gray-200 rounded-2xl p-4 md:col-span-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs text-gray-500">TOTAL</p>
                                            <p class="text-2xl font-bold text-primary leading-tight">
                                                {{ number_format($resumenFactura['total'] ?? 0, 2, ',', '.') }} €
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">Estado</p>
                                            <p class="text-sm font-semibold text-gray-800">
                                                Se emitirá directamente
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 p-5 border-t bg-gray-50">
                        <x-btns.cancelar wire:click="cancelarFacturacion" class="w-full sm:w-auto">
                            Cancelar
                        </x-btns.cancelar>

                        <button wire:click="emitirFactura"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl
                               bg-primary text-white text-sm font-semibold
                               hover:bg-primary/90 active:scale-[0.98]
                               focus:outline-none focus:ring-2 focus:ring-primary/40
                               transition shadow w-full sm:w-auto">
                            <i class="mgc_check_line text-base"></i>
                            Emitir factura
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif



</div>
