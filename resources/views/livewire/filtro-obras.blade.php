<div>
    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-2">

        <a href="{{ route('obras.create') }}" class="btn bg-primary/10 text-primary hover:bg-primary hover:text-white">
            <i class="mgc_add_line me-2"></i>Añadir obra
        </a>

        <div class="flex flex-col sm:flex-row justify-between items-center gap-3">


            <div class="flex items-center gap-2">
                <label for="estado" class="text-sm font-medium text-gray-600">Filtrado de obras:</label>
                <select wire:model.live="estado"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Todas</option>
                    <option value="planificacion">Planificación</option>
                    <option value="ejecucion">Ejecución</option>
                    <option value="finalizada">Finalizada</option>
                </select>

            </div>
        </div>
    </div>


    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">

        @if ($obras->isEmpty())
            <div
                class="text-center p-6 bg-gradient-to-b from-gray-50 to-white rounded-xl shadow-sm border border-gray-200 mt-4">
                <div class="flex flex-col items-center justify-center">
                    <i class="mgc_greatwall_line text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-700 text-lg font-medium">
                        No se han encontrado obras
                        @if ($estado)
                            en <span class="text-gray-900 font-semibold">{{ ucfirst($estado) }}</span>.
                        @else
                            registradas actualmente.
                        @endif
                    </p>
                    <p class="text-gray-500 text-sm mt-1">Puedes crear una nueva obra desde el botón “Añadir obra”.
                    </p>
                </div>
            </div>
        @else
            @foreach ($obras as $obra)
                <div class="card">
                    <div class="card-header">
                        <div class="flex justify-between items-center">
                            <h5 class="card-title">{{ $obra->nombre }}</h5>
                            <div class="bg-success text-xs text-white rounded-md py-1 px-1.5 font-medium"
                                role="alert">
                                <span>{{ $obra->estado }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <div class="py-3 px-6">
                            <h5 class="my-2">
                                <p><strong>Inicio:</strong> {{ $obra->fecha_inicio }}</p>
                                <p><strong>Final obra:</strong> {{ $obra->fecha_fin }}</p>
                            </h5>

                            <p class="text-gray-500 text-sm mb-5">{{ $obra->descripcion }}</p>

                            <!-- Resumen Presupuesto / Ventas / Gastos -->
                            <div class="bg-gray-100 p-4 rounded-lg mb-4">
                                <h6 class="font-semibold mb-3">
                                    Presupuesto:
                                    <strong>{{ number_format($obra->importe_presupuestado, 2, ',', '.') }} €</strong>
                                </h6>

                                <p class="mb-2"><strong>Venta:</strong>
                                    {{ number_format($obra->total_ventas, 2, ',', '.') }} €
                                </p>

                                <p class="mb-2"><strong>Gasto:</strong>
                                    {{ number_format($obra->total_gastos, 2, ',', '.') }} €
                                </p>

                                @php
                                    $resultado = $obra->total_ventas - $obra->total_gastos;
                                    $resultadoColor = $resultado >= 0 ? 'text-green-600' : 'text-red-600';
                                @endphp

                                <p class="mb-2">
                                    <strong>Resultado:</strong>
                                    <span class="{{ $resultadoColor }}">
                                        {{ number_format($resultado, 2, ',', '.') }} €
                                    </span>
                                </p>
                            </div>


                            {{--  <p class="mb-5">Informe general: </p> --}}

                            {{-- <div class="flex flex-wrap gap-3">
                            
                                <a href="{{ route('obra.informe.general', $obra->id) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-white bg-red-500 hover:bg-red-600 transition-colors duration-300">
                                    Descargar
                                    <img src="/images/icons/pdf.svg" alt="Icon PDF" class="w-5 h-5">
                                </a>

                        
                                <a href="{{ route('obras.informeGeneralExcel', $obra->id) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-600 text-white hover:bg-green-700 transition-all duration-300">
                                    Descargar
                                    <img src="/images/icons/excel.svg" alt="Icon Excel" class="w-5 h-5">
                                </a>
                            </div> --}}

                        </div>

                        <!-- Botonera inferior -->
                        <div class="flex items-center justify-between p-3 border-t border-gray-200 bg-gray-50">
                            <button wire:click="$set('obraAccionesId', {{ $obra->id }})"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium
               text-gray-600
               hover:text-gray-900 hover:bg-white
               border border-transparent hover:border-gray-300
               transition-all">
                                <i class="mgc_more_2_line text-base"></i>
                                Acciones
                            </button>
                            <span class="text-xs text-gray-400">
                                ID #{{ $obra->id }}
                            </span>


                        </div>






                        <!-- Balance y Documentación -->
                        <div class="border-t p-5 border-gray-300 dark:border-gray-700">

                            <!-- Balance -->
                            <div class="grid lg:grid-cols-2 gap-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-medium">Balance de la obra</span>
                                    <i class="mgc_greatwall_line text-lg text-gray-600"></i>
                                </div>

                                <div class="flex items-center gap-1">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                        <div class="h-1.5 rounded-full 
                                        @if ($obra->balance >= 80) bg-green-500
                                        @elseif($obra->balance >= 50) bg-yellow-500
                                        @else bg-red-500 @endif"
                                            style="width: {{ $obra->balance }}%;">
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ number_format($obra->balance, 0) }}%
                                    </span>
                                </div>
                            </div>

                            <hr class="mt-4 border-gray-300 dark:border-gray-700">

                            <!-- Documentación -->
                            <div class="grid lg:grid-cols-2 gap-4 mt-4">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-medium">Documentación</span>
                                    <i class="mgc_book_5_line text-lg text-gray-600"></i>
                                </div>

                                <div class="flex items-center gap-1">
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                        <div class="h-1.5 rounded-full 
                                        @if ($obra->progreso_documentos >= 80) bg-green-500
                                        @elseif($obra->progreso_documentos >= 50) bg-yellow-500
                                        @else bg-red-500 @endif"
                                            style="width: {{ $obra->progreso_documentos }}%;">
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ number_format($obra->progreso_documentos, 0) }}%
                                    </span>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            @endforeach
        @endif

        @if ($obraAccionesId)
            @php
                $obraSeleccionada = $obras->firstWhere('id', $obraAccionesId);
            @endphp

            <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4"
                x-data x-on:keydown.escape.window="$wire.cerrarAcciones()">

                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-200 p-6">

                    {{-- HEADER --}}
                    <div class="flex items-start justify-between mb-5">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                Acciones de obra
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $obraSeleccionada->nombre ?? '—' }}
                            </p>
                        </div>

                        <button wire:click="cerrarAcciones" class="text-gray-400 hover:text-gray-600">
                            <i class="mgc_close_line text-xl"></i>
                        </button>
                    </div>

                    {{-- ACCIONES --}}
                    <div class="grid grid-cols-1 gap-3">

                        <a href="{{ route('obras.gastos', $obraAccionesId) }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg border hover:bg-amber-50">
                            <i class="mgc_receive_money_line text-amber-600"></i>
                            <span>Gastos</span>
                        </a>

                        <a href="{{ route('obras.presupuesto-venta', $obraAccionesId) }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg border hover:bg-violet-50">
                            <i class="mgc_align_bottom_line text-violet-600"></i>
                            <span>Presupuesto de venta</span>
                        </a>

                        <a href="{{ route('obras.certificaciones', $obraAccionesId) }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg border hover:bg-emerald-50">
                            <i class="mgc_bank_line text-emerald-600"></i>
                            <span>Ventas / Certificaciones</span>
                        </a>

                        <a href="{{ route('obras.documentos', $obraAccionesId) }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg border hover:bg-sky-50">
                            <i class="mgc_file_check_line text-sky-600"></i>
                            <span>Documentos</span>
                        </a>

                        <a href="{{ route('obras.edit', $obraAccionesId) }}"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg border hover:bg-indigo-50">
                            <i class="mgc_edit_2_line text-indigo-600"></i>
                            <span>Editar obra</span>
                        </a>

                        <button wire:click="abrirEliminar({{ $obraAccionesId }})"
                            class="flex items-center gap-3 px-4 py-3 rounded-lg border
           text-red-600 hover:bg-red-50">
                            <i class="mgc_close_circle_line"></i>
                            <span>Eliminar obra</span>
                        </button>


                    </div>
                </div>
            </div>
        @endif

        @if ($obraAEliminarId)
            <div
                class="fixed inset-0 z-[10000]
           bg-black/60 backdrop-blur-sm
           flex items-center justify-center px-4">

                <div
                    class="w-full max-w-md
               bg-white rounded-3xl shadow-2xl
               border border-gray-200 overflow-hidden">

                    {{-- HEADER --}}
                    <div class="px-6 pt-6 text-center">
                        <div
                            class="mx-auto mb-4
                       flex items-center justify-center
                       w-14 h-14 rounded-full
                       bg-red-100 text-red-600">
                            <i class="mgc_warning_line text-3xl"></i>
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900">
                            Eliminar obra
                        </h3>

                        <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                            Esta acción eliminará la obra y
                            <span class="font-semibold text-gray-800">
                                toda su información asociada
                            </span>.
                            <br>
                            <span class="text-red-600 font-medium">
                                No se puede deshacer.
                            </span>
                        </p>
                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="mt-6 px-6 py-4
                   bg-gray-50 border-t border-gray-200
                   flex items-center justify-end gap-3">

                        <button wire:click="cancelarEliminar"
                            class="px-4 py-2 rounded-xl text-sm font-medium
                       border border-gray-300
                       text-gray-700
                       hover:bg-gray-100
                       transition">
                            Cancelar
                        </button>

                        <button wire:click="eliminarObra"
                            class="px-4 py-2 rounded-xl text-sm font-semibold
                       bg-red-600 text-white
                       hover:bg-red-700
                       active:scale-[0.97]
                       transition-all shadow-sm">
                            Eliminar definitivamente
                        </button>
                    </div>

                </div>
            </div>
        @endif

    </div>
