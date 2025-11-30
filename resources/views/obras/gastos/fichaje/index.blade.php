@extends('layouts.vertical', ['title' => 'Control de presencia', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])


@section('css')
    @vite(['resources/css/app.css'])
@endsection



@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card p-5">


                <a href="{{ route('obras.gastos', $obra->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium shadow-sm border border-gray-200 
                        hover:bg-gray-200 hover:text-gray-900 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <i class="mgc_arrow_left_line text-lg"></i>
                    Regresar
                </a>

                <button
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/20 text-cyan-700 font-medium border border-cyan-500/30 
                        shadow-sm hover:bg-cyan-600 hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-400/40"
                    data-fc-target="informe" data-fc-type="modal" type="button">
                    <i class="mgc_add_line"></i> Generar informe
                </button>

                <h2 class="text-lg font-semibold text-gray-50 mb-2 bg-primary p-3 rounded mt-3">Control de presencia -
                    {{ $obra->nombre }}</h2>
                <div class="overflow-x-auto">

                    {{-- Modal para generar informe de materiales --}}
                    <div id="informe"
                        class="fixed top-0 left-0 z-50 transition-all duration-500 fc-modal hidden w-full h-full min-h-full items-center fc-modal-open:flex">
                        <div
                            class="fc-modal-open:opacity-100 duration-500 opacity-0 ease-out transition-[opacity] sm:max-w-lg sm:w-full sm:mx-auto  flex-col bg-white border shadow-sm rounded-md dark:bg-slate-800 dark:border-gray-700">
                            <div class="flex justify-between items-center py-2.5 px-4 border-b dark:border-gray-700">
                                <h3 class="font-medium text-gray-800 dark:text-white text-lg">
                                    Informe de Mano de obra {{ $obra->nombre }}
                                </h3>
                                <button
                                    class="inline-flex flex-shrink-0 justify-center items-center h-8 w-8 dark:text-gray-200"
                                    data-fc-dismiss type="button">
                                    <span class="material-symbols-rounded">close</span>
                                </button>
                            </div>




                            <div class="px-4 py-8 overflow-y-auto">
                                <div>

                                    <form method="GET" id="informes-form">

                                        <!-- Selección de mes y año -->
                                        <label for="mes" class="block font-medium mb-2">Mes:</label>
                                        <select name="mes" id="mes"
                                            class="mb-4 block w-full border-gray-300 rounded shadow-sm" required>
                                            @for ($m = 1; $m <= 12; $m++)
                                                <option value="{{ $m }}"
                                                    {{ request('mes') == $m ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                                </option>
                                            @endfor
                                        </select>

                                        <label for="anio" class="block font-medium mb-2">Año:</label>
                                        <select name="anio" id="anio"
                                            class="mb-4 block w-full border-gray-300 rounded shadow-sm" required>
                                            @for ($y = now()->year; $y >= 2022; $y--)
                                                <option value="{{ $y }}"
                                                    {{ request('anio') == $y ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                            @endfor
                                        </select>

                                        <!-- Botones -->
                                        <div class="flex gap-4">

                                            <button type="button" onclick="exportExcel({{ $obra->id }})"
                                                class="w-full bg-green-600 text-white py-2 rounded shadow-sm hover:bg-green-700">
                                                Descargar Excel
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered table-hover mt-3" id="table-docs">
                        <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Mes</th>
                                <th>Tarifa/hora</th>
                                <th>Horas trabajadas</th>
                                <th>Metros realizados</th>
                                <th>Total ganado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resumen as $r)
                                <tr>
                                    <td>{{ $r->empleado_id }}</td>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $r->mes)->translatedFormat('F Y') }}
                                    </td>
                                    <td>
                                        {{ $r->tarifa_hora > 0 ? number_format($r->tarifa_hora, 2) . ' €' : 'Por metros' }}
                                    </td>
                                    <td>{{ number_format($r->horas_trabajadas, 2) }}</td>
                                    <!-- Columna: Metros Realizados -->
                                    <td>
                                        @if ($r->tarifa_hora <= 0 || $r->tarifa_hora === null)
                                            <div class="inline-edit-container">
                                                <span class="display-text text-gray-700 cursor-pointer"
                                                    ondblclick="activarEdicion(this)">
                                                    {{ $r->metros_realizados !== null ? $r->metros_realizados . ' m' : 'Doble clic para ingresar' }}
                                                </span>
                                                <form method="POST" action="{{ route('resumen.update', $r->id) }}"
                                                    class="hidden edit-form mt-1 space-y-1">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="flex items-center space-x-2">
                                                        <input type="number" name="metros_realizados"
                                                            value="{{ $r->metros_realizados !== null ? $r->metros_realizados : '' }}"
                                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                                            step="1" min="0" required>
                                                        <span class="text-sm text-gray-600">m</span>
                                                    </div>
                                                    <button type="submit"
                                                        class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                                        Guardar
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>

                                    <!-- Columna: Total Ganado -->
                                    <td>
                                        @if ($r->tarifa_hora > 0)
                                            <span class="text-gray-800 font-medium">
                                                {{ number_format($r->horas_trabajadas * $r->tarifa_hora, 2) . ' €' }}
                                            </span>
                                        @else
                                            <div class="inline-edit-container">
                                                <span class="display-text text-gray-700  cursor-pointer"
                                                    ondblclick="activarEdicion(this)">
                                                    {{ $r->total_ganado !== null ? number_format($r->total_ganado, 2) . ' €' : 'Doble clic para ingresar' }}
                                                </span>
                                                <form method="POST" action="{{ route('resumen.update', $r->id) }}"
                                                    class="hidden edit-form mt-1 space-y-1">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="flex items-center space-x-2">
                                                        <input type="number" name="total_ganado"
                                                            value="{{ $r->total_ganado !== null ? $r->total_ganado : '' }}"
                                                            class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
                                                            step="0.01" min="0" required>
                                                        <span class="text-sm text-gray-600">€</span>
                                                    </div>
                                                    <button type="submit"
                                                        class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                                        Guardar
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>




                </div>
            </div>
        </div>
    </div>




    </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const swalConfig = {
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            };

            // Selecciona todos los botones con la clase 'delete-material'
            const deleteButtons = document.querySelectorAll('.delete-material');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const gastoVarioId = this.getAttribute('data-id'); // Obtén el ID del material

                    Swal.fire(swalConfig).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-delete');
                            form.action =
                                `/gastos-varios/${gastoVarioId}`; // Configura la acción con el ID
                            form.submit();
                        }
                    });
                });
            });
        });

        function exportExcel(obraId) {
            const mes = document.getElementById('mes').value;
            const anio = document.getElementById('anio').value;

            const url = `/obra/${obraId}/fichajes/informes/excel?mes=${mes}&anio=${anio}`;
            window.location.href = url;
        }

        function activarEdicion(span) {
            const container = span.closest('.inline-edit-container');
            const form = container.querySelector('.edit-form');

            span.classList.add('hidden');
            form.classList.remove('hidden');
            form.querySelector('input').focus();
        }
    </script>
@endsection
