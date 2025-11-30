@extends('layouts.vertical', ['title' => 'Gastos Varios', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])


@section('css')
    @vite(['node_modules/sweetalert2/dist/sweetalert2.min.css'])
    @vite(['resources/css/app.css'])
@endsection



@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">

            {{-- Mensaje de éxito --}}
            @include('./notifications/notyf')

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
                    <i class="mgc_add_line"></i> Agregar Gasto Varios
                </button>

                
                @include('obras.gastos.gastos-varios.modal.create')


                <button
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/20 text-cyan-700 font-medium border border-cyan-500/30 
                    shadow-sm hover:bg-cyan-600 hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-400/40"
                    data-fc-target="informe" data-fc-type="modal" type="button">
                    <i class="mgc_add_line"></i> Generar informe
                </button>

                {{-- Modal para generar informe de materiales --}}
                @include('obras.gastos.gastos-varios.modal.informe')

                {{-- Tabla --}}
                <div class="mt-3">
                    <h2 class="text-lg font-semibold text-gray-50 mb-2 bg-primary p-3 rounded">Gastos Varios</h2>
                    <div class="overflow-x-auto">
                        <table id="table-docs" class="display">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo de Gasto</th>
                                    <th>Fecha</th>
                                    <th>Descripcion</th>
                                    <th>Importe</th>
                                    <th>Numero de Factura</th>
                                    <th>Factura</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($obra->gastosVarios as $gastoVario)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $gastoVario->tipo }}</td>
                                        <td>{{ \Carbon\Carbon::parse($gastoVario->fecha)->format('d/m/Y') }}</td>
                                        <td>{{ $gastoVario->descripcion }}</td>
                                        <td>{{ number_format($gastoVario->importe, 2, ',', '.') }} €</td>
                                        <td>{{ $gastoVario->numero_factura }}</td>
                                        <td>
                                            <x-btns.descargar href="{{ asset('storage/' . $gastoVario->archivo_factura) }}" />
                                            <x-btns.ver pdf="{{ asset('storage/' . $gastoVario->archivo_factura) }}" />
                                        </td>


                                        <td>
                                            <x-btns.eliminar class="delete-registro" id="{{ $gastoVario->id }}" />
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

            {{-- Formulario para Eliminar gasto material --}}
          @include('obras.components-obras.form-delete')

        </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js', 'resources/js/visor-pdf.js'])
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
                    const gastoVarioId = this.getAttribute('data-id');

                    Swal.fire(swalConfig).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-delete');
                            form.action =
                                `/gastos-varios/${gastoVarioId}`;
                            form.submit();
                        }
                    });
                });
            });
        });


        /* Funciones para modal de formulario informes */

        function exportPDF(obraId) {
            const form = document.getElementById('informes-form');
            form.action = `/obra/${obraId}/gastosvarios/informes/pdf`;
            form.submit();
        }

        function exportExcel(obraId) {
            const form = document.getElementById('informes-form');
            form.action = `/obra/${obraId}/gastosvarios/informes/excel`;
            form.submit();
        }
    </script>
@endsection


{{-- scrip-bottom nos permite incrustra el js --}}
@section('script-bottom')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/dt-2.1.8/datatables.min.js"></script>
    @vite(['resources/js/pages/tables-datatable.js'])
@endsection
