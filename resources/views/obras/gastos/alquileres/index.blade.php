@extends('layouts.vertical', ['title' => 'Gastos Alquileres', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])


@section('css')
    @vite(['resources/css/app.css'])
@endsection



@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">

            {{-- Mensaje de éxito --}}
            @include('./notifications/notyf')

            {{-- Contenido principal --}}



            <div class="card p-10">

                <a href="{{ route('obras.gastos', $obra->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium shadow-sm border border-gray-200 
               hover:bg-gray-200 hover:text-gray-900 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/30">
                    <i class="mgc_arrow_left_line text-lg"></i>
                    Regresar
                </a>
                <button
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-500/20 text-green-700 font-medium border border-green-500/30 
               shadow-sm hover:bg-green-600 hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-green-400/40"
                    data-fc-type="modal" type="button">
                    <i class="mgc_add_line"></i> Agregar Gasto
                </button>

                @include('obras.gastos.alquileres.modal.create')

                <button
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/20 text-cyan-700 font-medium border border-cyan-500/30 
           shadow-sm hover:bg-cyan-600 hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-400/40"
                    data-fc-target="informe" data-fc-type="modal" type="button">
                    <i class="mgc_add_line"></i> Generar informe
                </button>

                {{-- Modal para generar informe de materiales --}}
                <div id="informe"
                    class="fixed top-0 left-0 z-50 transition-all duration-500 fc-modal hidden w-full h-full min-h-full items-center fc-modal-open:flex">
                    <div
                        class="fc-modal-open:opacity-100 duration-500 opacity-0 ease-out transition-[opacity] sm:max-w-lg sm:w-full sm:mx-auto  flex-col bg-white border shadow-sm rounded-md dark:bg-slate-800 dark:border-gray-700">
                        <div class="flex justify-between items-center py-2.5 px-4 border-b dark:border-gray-700">
                            <h3 class="font-medium text-gray-800 dark:text-white text-lg">
                                Informe de Materiales obra {{ $obra->nombre }}
                            </h3>
                            <button class="inline-flex flex-shrink-0 justify-center items-center h-8 w-8 dark:text-gray-200"
                                data-fc-dismiss type="button">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>




                        <div class="px-4 py-8 overflow-y-auto">
                            <div>

                                <form method="GET" id="informes-form">

                                    <!-- Filtro por rango de fechas -->
                                    <label for="fecha_inicio" class="block font-medium mb-2">Fecha inicio:</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400">

                                    <label for="fecha_fin" class="block font-medium mb-2">Fecha fin:</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-200 focus:border-blue-400 mb-4">

                                    <!-- Botón para exportar -->
                                    <div class="flex gap-4">
                                        <button type="button" onclick="exportPDF({{ $obra->id }})"
                                            class="w-full bg-red-600 text-white py-2 rounded shadow-sm hover:bg-red-700">
                                            Descargar PDF
                                        </button>

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



                <div class="mt-3">
                    <h2 class="text-lg font-semibold text-gray-50 mb-2 bg-primary p-3 rounded">Control Gastos Alquileres
                    </h2>
                    <div class="overflow-x-auto">
                        <table id="table-docs" class="display">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripcion</th>
                                    <th>Valor Unitario</th>
                                    <th>Cantidad</th>
                                    <th>Importe</th>
                                    <th>Numero factura</th>
                                    <th>Factura</th>
                                    <th>Fecha</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($obra->alquileres as $alquiler)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $alquiler->nombre }}</td>
                                        <td>{{ $alquiler->descripcion }}</td>
                                        <td>{{ $alquiler->precio_unitario }} €</td>
                                        <td>{{ $alquiler->cantidad }}</td>
                                        <td>{{ number_format($alquiler->importe, 2, ',', '.') }} €</td>
                                        <td>{{ $alquiler->numero_factura }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <x-btns.descargar
                                                    href="{{ asset('storage/' . $alquiler->archivo_factura) }}" />
                                                <x-btns.ver pdf="{{ asset('storage/' . $alquiler->archivo_factura) }}" />
                                            </div>
                                        </td>
                                        <td>
                                            @if ($alquiler->fecha)
                                                {{ \Carbon\Carbon::parse($alquiler->fecha)->format('d/m/Y') }}
                                            @else
                                                No especificada
                                            @endif
                                        </td>

                                        <td>
                                            <x-btns.eliminar class="delete-registro" id="{{ $alquiler->id }}" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modal para ver PDF --}}
            @include('components-vs.modals.visor-pdf')


            @include('obras.components-obras.form-delete')

        </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
    @vite(['resources/js/visor-pdf.js'])
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const swalConfig = {
                title: 'Confirmación de Eliminación',
                text: 'Está a punto de eliminar un registro de forma permanente. ¿Desea continuar con esta acción irreversible?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Eliminar (Irreversible)',
                cancelButtonText: 'Mantener Registro',
            };

            // Selecciona todos los botones con la clase 'delete-material'
            const deleteButtons = document.querySelectorAll('.delete-registro');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const alquilerlId = this.getAttribute('data-id'); // Obtén el ID del material

                    Swal.fire(swalConfig).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-delete');
                            form.action =
                                `/alquileres/${alquilerlId}`; // Configura la acción con el ID
                            form.submit();
                        }
                    });
                });
            });
        });

        /* Funciones para modal de formulario informes */

        function exportPDF(obraId) {
            const form = document.getElementById('informes-form');
            form.action = `/obra/${obraId}/alquileres/informes/pdf`;
            form.submit();
        }

        function exportExcel(obraId) {
            const form = document.getElementById('informes-form');
            form.action = `/obra/${obraId}/alquileres/informes/excel`;
            form.submit();
        }
    </script>
@endsection
