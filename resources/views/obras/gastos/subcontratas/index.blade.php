@extends('layouts.vertical', ['title' => 'Subcontratas', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])


@section('css')
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
                    <i class="mgc_add_line"></i> Agregar Subcontrata
                </button>

                {{-- Modal para crear Subcontrata --}}
                @include('obras.gastos.subcontratas.modal.create')

                <button
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/20 text-cyan-700 font-medium border border-cyan-500/30 
                    shadow-sm hover:bg-cyan-600 hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-cyan-400/40"
                    data-fc-target="informe" data-fc-type="modal" type="button">
                    <i class="mgc_add_line"></i> Generar informe
                </button>

                {{-- Modal para generar informe de materiales --}}
                @include('obras.gastos.subcontratas.modal.informe')



                {{-- TABLA --}}
                <div class="mt-3">
                    <h2 class="text-lg font-semibold text-gray-50 mb-2 bg-primary p-3 rounded">Control de Subcontratas</h2>
                    <div class="overflow-x-auto">
                        <table id="table-docs" class="display">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripcion</th>
                                    <th>Importe</th>
                                    <th>Fecha</th>
                                    <th>Archivo Factura</th>
                                    <th>Archivo Contrato</th>
                                    <th>Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($obra->subcontratas as $subcontrata)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $subcontrata->nombre }}</td>
                                        <td>{{ $subcontrata->descripcion }}</td>
                                        <td>{{ number_format($subcontrata->importe, 2, ',', '.') }} €</td>
                                        <td>{{ \Carbon\Carbon::parse($subcontrata->fecha)->format('d/m/Y') }}</td>
                                        <td>
                                            <x-btns.descargar
                                                href="{{ asset('storage/' . $subcontrata->archivo_factura) }}" />
                                            <x-btns.ver pdf="{{ asset('storage/' . $subcontrata->archivo_factura) }}" />
                                        </td>
                                        <td>
                                            <x-btns.descargar
                                                href="{{ asset('storage/' . $subcontrata->archivo_contrato) }}" />
                                            <x-btns.ver pdf="{{ asset('storage/' . $subcontrata->archivo_contrato) }}" />
                                        </td>
                                        <td>
                                            <x-btns.eliminar class="delete-registro" id="{{ $subcontrata->id }}" />
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
    @vite(['resources/js/visor-pdf.js', 'resources/js/pages/highlight.js'])
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
                    const subcontrataId = this.getAttribute('data-id'); // Obtén el ID del material

                    Swal.fire(swalConfig).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('form-delete');
                            form.action = `/subcontratas/${subcontrataId}`;
                            form.submit();
                        }
                    });
                });
            });
        });

        /* Funciones para modal de formulario informes */

        function exportPDF(obraId) {
            const form = document.getElementById('informes-form');
            form.action = `/obra/${obraId}/subcontrata/informes/pdf`;
            form.submit();
        }

        function exportExcel(obraId) {
            const form = document.getElementById('informes-form');
            form.action = `/obra/${obraId}/subcontratas/informes/excel`;
            form.submit();
        }
    </script>
@endsection
