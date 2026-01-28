<div>

    {{-- ======================
        CABECERA + BOTÓN NUEVO
    ======================= --}}
    <div class="flex flex-wrap items-center gap-3 mb-7">

        {{-- REGRESAR --}}
        <x-btns.regresar href="{{ route('unidad') }}">
            Regresar
        </x-btns.regresar>

        <x-btns.agregar wire:click="abrirModal">
            Nueva certificación
        </x-btns.agregar>


    </div>

    <h2 class="text-xl font-semibold text-white bg-primary p-4 rounded-lg shadow mb-4">
        Certificaciones registradas
    </h2>

    {{-- ======================
        TABLA DE CERTIFICACIONES
    ======================= --}}

    {{-- Filtros de búsqueda --}}
    <x-tablas.filters title="Filtros de búsqueda">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

            <!-- Oficio -->
            <div>
                <label class=" text-gray-700 mb-1 block">Oficio</label>
                <select wire:model.defer="pendingOficio"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    <option value="">Todos</option>
                    @foreach ($oficios as $oficio)
                        <option value="{{ $oficio->id }}">{{ $oficio->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Cliente -->
            <div>
                <label class=" text-gray-700 mb-1 block">Cliente</label>
                <select wire:model.defer="pendingCliente"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    <option value="">Todos</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                    @endforeach
                </select>
            </div>


            <!-- Estado certificación -->
            <div>
                <label class=" text-gray-700 mb-1 block">Estado certificación</label>
                <select wire:model.defer="pendingEstadoCertificacion"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
                    <option value="">Todos</option>
                    <option value="enviada">Enviada</option>
                    <option value="aceptada">Aceptada</option>
                    <option value="facturada">Facturada</option>
                </select>
            </div>

            <!-- Fecha desde -->
            <div>
                <label class=" text-gray-700 mb-1 block">Fecha desde</label>
                <input type="date" wire:model.defer="pendingFechaDesde"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            </div>

            <!-- Fecha hasta -->
            <div>
                <label class=" text-gray-700 mb-1 block">Fecha hasta</label>
                <input type="date" wire:model.defer="pendingFechaHasta"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary">
            </div>

            <!-- Buscador -->
            <div>
                <label class=" text-gray-700 mb-1 block">Buscar</label>
                <input type="text" wire:model.defer="pendingSearch"
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                    placeholder="Nº certificación...">
            </div>

            <!-- Botones -->
            <div class="md:col-span-5 flex justify-end gap-3 mt-2">
                <button wire:click="limpiarFiltros"
                    class="px-4 py-2 rounded-xl bg-gray-100 border border-gray-300 text-gray-700 hover:bg-gray-200 transition">
                    Limpiar
                </button>

                <button wire:click="aplicarFiltros"
                    class="px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary/90 transition shadow-sm">
                    Aplicar filtros
                </button>
            </div>

        </div>
    </x-tablas.filters>



    <div class="mt-6 flex items-center justify-end gap-3">

        {{-- Acción secundaria --}}
        <a wire:click="abrirComparativa"
            class="inline-flex items-center gap-2
               px-4 py-2.5 rounded-xl
               text-sm font-medium
               bg-indigo-50 text-indigo-700
               border border-indigo-200
               hover:bg-indigo-100
               active:scale-[0.98]
               transition-all duration-150
               focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:ring-offset-2 cursor-pointer">
            <i class="mgc_document_3_line text-base"></i>
            Comparativa mensual
        </a>

        {{-- Acción principal --}}
        <a href="{{ route('empresa.certificaciones.facturar', ['obra' => $obraId]) }}"
            class="inline-flex items-center justify-center gap-2
               px-6 py-3 rounded-2xl
               bg-emerald-600/10 text-emerald-700
               border border-emerald-600/30
               text-sm font-semibold
               hover:bg-emerald-600 hover:text-white hover:border-emerald-600
               active:scale-[0.98]
               transition-all duration-200
               focus:outline-none focus:ring-2 focus:ring-emerald-400/50 focus:ring-offset-2">
            <i class="mgc_counter_2_line text-lg"></i>
            Facturar certificaciones
        </a>

    </div>


    @if ($mostrarComparativa)
        <div class="mt-6 bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    Comparativa mensual
                </h3>

                <button wire:click="cerrarComparativa" class="text-sm text-gray-500 hover:text-gray-700">
                    Cerrar
                </button>

            </div>

            @livewire('empresa.certificaciones.comparativa-mensual', ['obraId' => $obraId], key('comparativa-' . $obraId))
        </div>
    @endif


    {{-- Tabla --}}

    <x-tablas.table>


        <x-slot name="columns">
            <th class="px-4 py-2 w-36 text-left">Nº Certificación</th>
            <th class="px-4 py-2 w-28 text-left">Fecha cert.</th>
            <th class="px-4 py-2 w-28 text-left">Fecha cont.</th>
            <th class="px-4 py-2 w-40 text-left">Cliente</th>
            <th class="px-4 py-2 w-32 text-left">Oficio</th>
            <th class="px-4 py-2 w-28 text-left">Estado cert.</th>
            <th class="px-4 py-2 w-28 text-left">Estado factura</th>
            <th class="px-4 py-2 w-28 text-left">Iva</th>
            <th class="px-4 py-2 w-28 text-left">IRPF/Retención</th>
            <th class="px-4 py-2 w-28 text-left">Base Imponible</th>
            <th class="px-4 py-2 w-28 text-left">Total</th>
            <th class="px-4 py-2 w-24 text-center">Acciones</th>
        </x-slot>


        <x-slot name="rows">
            @forelse ($certificaciones as $cert)
                <tr class="border-b hover:bg-gray-50 transition">

                    <td class="px-4 py-2">
                        {{ $cert->numero_certificacion ?? '-' }}
                    </td>

                    <td class="px-4 py-2">
                        {{ $cert->fecha_ingreso ? \Carbon\Carbon::parse($cert->fecha_ingreso)->format('d/m/Y') : '-' }}
                    </td>

                    <td class="px-4 py-2">
                        {{ $cert->fecha_contable ? \Carbon\Carbon::parse($cert->fecha_contable)->format('d/m/Y') : '-' }}
                    </td>


                    <td class="px-4 py-2">
                        {{ $cert->cliente->nombre ?? '-' }}
                    </td>

                    <td class="px-4 py-2">
                        {{ $cert->oficio->nombre ?? '-' }}
                    </td>

                    <td class="px-4 py-2 capitalize">
                        <span class="px-2 py-1 rounded-md text-xs font-semibold bg-gray-100 text-gray-700">
                            {{ $cert->estado_certificacion }}
                        </span>
                    </td>

                    <td class="px-4 py-2 capitalize">
                        <span class="px-2 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-700">
                            {{ $cert->estado_factura ?? '—' }}
                        </span>
                    </td>

                    <td class="px-4 py-2 font-semibold">
                        {{ $cert->iva_porcentaje ?? '0' }} %
                    </td>

                    <td class="px-4 py-2 font-semibold">
                        {{ $cert->retencion_porcentaje ?? '0' }} %
                    </td>

                    <td class="px-4 py-2 font-semibold">
                        {{ number_format($cert->base_imponible, 2, ',', '.') }} €
                    </td>

                    <td class="px-4 py-2 font-semibold">
                        {{ number_format($cert->total, 2, ',', '.') }} €
                    </td>

                    <td class="px-4 py-3 text-center">
                        <button wire:click="abrirAcciones({{ $cert->id }})"
                            class="p-2 rounded-full hover:bg-gray-100
               focus:outline-none focus:ring-2 focus:ring-blue-500"
                            title="Acciones">
                            <i class="mgc_more_2_line text-xl"></i>
                        </button>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-4 py-4 text-center text-gray-500">
                        No hay certificaciones registradas.
                    </td>
                </tr>
            @endforelse
        </x-slot>



        <x-slot name="pagination">
            {{ $certificaciones->links() }}
        </x-slot>
    </x-tablas.table>




    {{-- ======================
        MODAL FORMULARIO
    ======================= --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

            <div
                class="bg-white w-full max-w-3xl rounded-2xl shadow-[0_15px_40px_rgba(0,0,0,0.2)]
                   border border-gray-200 overflow-hidden animate-[fadeIn_0.25s_ease-out,slideUp_0.3s_ease-out]
                   max-h-[92vh] flex flex-col">

                <!-- CABECERA -->
                <div class="flex items-center gap-3 p-5 border-b bg-white sticky top-0 z-20 shadow-sm">

                    <div
                        class="bg-primary/10 text-primary w-12 h-12 flex items-center justify-center rounded-xl text-2xl">
                        <i class="mgc_file_check_line"></i>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-800">
                        Registrar factura recibida
                    </h3>

                    <!-- Botón cerrar -->
                    <button wire:click="cerrarModal"
                        class="ml-auto text-gray-500 hover:text-red-600 transition text-3xl leading-none">
                        &times;
                    </button>
                </div>

                <!-- CONTENIDO (scroll interno) -->
                <div class="p-6 overflow-y-auto flex-1">
                    @livewire('empresa.certificaciones.formulario', ['obraId' => $obraId], key('form-certificacion'))
                </div>

            </div>
        </div>
    @endif


    @if ($modoNuevoCapitulo)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">

            <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl p-6 space-y-5">

                <h3 class="text-lg font-semibold text-gray-800">
                    Nuevo capítulo
                    <span class="text-primary font-bold">
                        {{ $numero_certificacion }}
                    </span>
                </h3>

                {{-- INFO CONTEXTUAL --}}
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Cliente:</span><br>
                        {{ optional($clientes->firstWhere('id', $cliente_id))->nombre }}
                    </div>
                    <div>
                        <span class="font-medium">IVA / Retención:</span><br>
                        {{ $iva_porcentaje }} % / {{ $retencion_porcentaje }} %
                    </div>
                </div>

                {{-- OFICIO --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Oficio</label>
                    <select wire:model.defer="obra_gasto_categoria_id" class="w-full rounded-xl border-gray-300">
                        <option value="">— Selecciona oficio —</option>
                        @foreach ($oficios as $oficio)
                            <option value="{{ $oficio->id }}">{{ $oficio->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button wire:click="resetFormularioCapitulo" class="px-4 py-2 rounded-xl bg-gray-200">
                        Cancelar
                    </button>

                    <button wire:click="guardarNuevoCapitulo" class="px-4 py-2 rounded-xl bg-primary text-white">
                        Crear capítulo
                    </button>
                </div>

            </div>
        </div>
    @endif


    {{-- ======================
        MODAL ELIMINAR
    ======================= --}}
    @if ($showDeleteModal)
        <x-modals.confirmar titulo="Confirmar eliminación"
            mensaje="¿Seguro que deseas eliminar esta certificación?<br>Esta acción no se puede deshacer."
            wire-close="wire:click=&quot;$set('showDeleteModal', false)&quot;">

            {{-- Botón CANCELAR --}}
            <x-btns.cancelar wire:click="$set('showDeleteModal', false)">
                Cancelar
            </x-btns.cancelar>


            {{-- Botón ELIMINAR --}}
            <x-btns.danger wire:click="eliminar">
                Eliminar
            </x-btns.danger>

        </x-modals.confirmar>
    @endif

    {{-- ======================
        MODAL ACCIONES
    ======================= --}}

    @if ($modalAcciones)
        <div class="fixed inset-0 z-50 flex items-center justify-center">

            {{-- Fondo --}}
            <div class="absolute inset-0 bg-black/40" wire:click="cerrarAcciones"></div>

            {{-- Modal --}}
            <div
                class="relative z-10 w-full max-w-sm
                    bg-white rounded-2xl shadow-xl
                    p-6 space-y-4">
                <div class="border-b pb-3 mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Acciones certificación
                        <span class="text-primary font-bold">
                            {{ $numeroCertificacionActiva ?? '—' }}
                        </span>
                    </h3>

                </div>


                <div class="space-y-2">

                    <button wire:click="verCertificacionDetalles({{ $certificacionActiva }})"
                        class="w-full flex items-center gap-3 px-4 py-3
                           rounded-xl bg-gray-100 hover:bg-gray-200
                           text-gray-800 font-medium">
                        <i class="mgc_eye_2_line text-lg"></i>
                        Agregar detalle
                    </button>


                    <button wire:click="abrirInforme('{{ $numeroCertificacionActiva }}')"
                        class="w-full flex items-center gap-3 px-4 py-3
               rounded-xl bg-emerald-50 hover:bg-emerald-100
               text-emerald-700 font-medium">
                        <i class="mgc_file_import_line text-lg"></i>
                        Generar informe
                    </button>



                    @if ($estadoFacturaActiva !== 'facturada')
                        <button wire:click="nuevoCapitulo({{ $certificacionActiva }})"
                            class="w-full flex items-center gap-3 px-4 py-3
               rounded-xl bg-blue-50 hover:bg-blue-100
               text-blue-700 font-medium">
                            <i class="mgc_add_circle_line text-lg"></i>
                            Nuevo capítulo
                        </button>
                        <button wire:click="confirmarEliminar({{ $certificacionActiva }})"
                            class="w-full flex items-center gap-3 px-4 py-3
                            rounded-xl bg-red-50 hover:bg-red-100
                            text-red-600 font-medium">
                            <i class="mgc_delete_2_line text-lg"></i>
                            Eliminar
                        </button>
                    @endif


                </div>

                <div class="pt-4 text-right">
                    <button wire:click="cerrarAcciones"
                        class="px-4 py-2 rounded-lg text-sm
                           text-gray-600 hover:text-gray-800">
                        Cancelar
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ======================
        MODAL INFORME
    ======================= --}}
    @if ($showInformeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">

            <div class="absolute inset-0 bg-black/40" wire:click="$set('showInformeModal', false)"></div>

            <div class="relative z-10 w-full max-w-lg bg-white rounded-2xl shadow-xl p-6 space-y-5">

                <h3 class="text-lg font-semibold">
                    Informe certificación {{ $numeroCertificacionInforme }}
                </h3>

                <div
                    class="bg-blue-50 border border-blue-200 text-blue-800
                     rounded-xl p-3 text-sm">
                    <i class="mgc_information_line mr-2"></i>
                    Este informe es solo informativo y no tiene validez fiscal.
                </div>


                <div class="space-y-2">
                    @foreach ($capitulosInforme as $cap)
                        <label
                            class="flex items-center justify-between gap-3
                              p-3 border rounded-xl cursor-pointer hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" wire:click="toggleCapituloInforme({{ $cap['id'] }})"
                                    @checked(in_array($cap['id'], $capitulosSeleccionadosInforme))>

                                <span class="font-medium">{{ $cap['oficio'] }}</span>
                            </div>

                            <span class="font-semibold text-sm">
                                {{ number_format($cap['total'], 2, ',', '.') }} €
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-between text-sm">
                    <button wire:click="seleccionarTodosInforme" class="text-blue-600 hover:underline">
                        Seleccionar todos
                    </button>

                    <button wire:click="limpiarInforme" class="text-gray-500 hover:underline">
                        Limpiar
                    </button>
                </div>

                <div class="bg-gray-50 border rounded-xl p-4 text-sm flex justify-between">
                    <span>Total seleccionado</span>
                    <span class="font-semibold">
                        {{ number_format($totalInforme, 2, ',', '.') }} €
                    </span>
                </div>

                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showInformeModal', false)" class="px-4 py-2 rounded-xl bg-gray-100">
                        Cancelar
                    </button>

                    <button wire:click="generarInformePDF"
                        class="px-5 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700">
                        Generar PDF
                    </button>


                </div>

            </div>
        </div>
    @endif







    {{-- ======================
        MODAL VISOR PDF
    ======================= --}}

    @include('components-vs.modals.visor-pdf')



</div>
