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


                            <p class="mb-5">Informe general: </p>

                            <div class="flex flex-wrap gap-3">
                                <!-- PDF -->
                                <a href="{{ route('obra.informe.general', $obra->id) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-white bg-red-500 hover:bg-red-600 transition-colors duration-300">
                                    Descargar
                                    <img src="/images/icons/pdf.svg" alt="Icon PDF" class="w-5 h-5">
                                </a>

                                <!-- Excel -->
                                <a href="{{ route('obras.informeGeneralExcel', $obra->id) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-600 text-white hover:bg-green-700 transition-all duration-300">
                                    Descargar
                                    <img src="/images/icons/excel.svg" alt="Icon Excel" class="w-5 h-5">
                                </a>
                            </div>

                        </div>

                        <!-- Botonera inferior -->
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 p-3 border-t border-gray-300 dark:border-gray-700">

                            <a class="btn flex-1 sm:flex-none text-center bg-secondary/25 text-secondary hover:bg-secondary hover:text-white"
                                href="{{ route('obras.gastos', $obra->id) }}">
                                <i class="mgc_receive_money_line m-1"></i>Gastos
                            </a>

                            <a class="btn flex-1 sm:flex-none text-center bg-info/25 text-info hover:bg-info hover:text-white"
                                href="{{ route('obras.documentos', $obra->id) }}">
                                <i class="mgc_file_check_line m-1"></i>Docs
                            </a>

                            <a class="btn flex-1 sm:flex-none text-center bg-warning/25 text-warning hover:bg-warning hover:text-white"
                                href="{{ route('obras.edit', $obra->id) }}">
                                <i class="mgc_edit_2_line m-1"></i>Editar
                            </a>

                            <form id="delete-form-{{ $obra->id }}"
                                action="{{ route('obras.destroy', $obra->id) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>

                            <a type="button"
                                class="btn flex-1 sm:flex-none text-center cursor-pointer bg-danger/25 text-danger hover:bg-danger hover:text-white"
                                onclick="confirmDelete({{ $obra->id }})">
                                <i class="mgc_close_circle_line mr-1"></i>Eliminar
                            </a>
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

    </div>
