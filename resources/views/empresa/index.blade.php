@extends('layouts.vertical', ['title' => 'Mi unidad', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">

            {{-- Mensaje de éxito --}}
            @include('./notifications/notyf')



            <div class="card p-10">
                <div>
                    <div class="mb-5">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-1">Mi unidad</h2>
                        <p class="text-gray-600 dark:text-gray-400">Gestiona la información de tu empresa aquí.</p>
                    </div>
                    <div>
                        <button
                            class="inline-flex items-center gap-2 px-4 py-2 mb-4 rounded-full bg-green-500/20 text-green-700 font-medium border border-green-500/30 
                            shadow-sm hover:bg-green-600 hover:text-white transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-green-400/40"
                            data-fc-type="modal" data-fc-target="empresa" type="button">
                            <i class="mgc_settings_5_line"></i> Configuración
                        </button>
                    </div>
                </div>

                <livewire:empresa.empresa-info />

            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-8">

                <!-- Drive App -->
                <a href="{{ route('empresa.driveApp') }}"
                    class="flex flex-col items-center justify-center gap-3 p-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md hover:border-blue-500 transition-all duration-300 group">
                    <i class="mgc_album_2_line text-3xl text-blue-600 group-hover:scale-110 transition-transform"></i>
                    <span class="text-base font-semibold text-gray-800 dark:text-gray-100">Drive App</span>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Accede a los archivos y documentos de la
                        empresa</p>
                </a>

                <!-- Gastos de la empresa -->
                <a href="{{ route('empresa.gastosEmpresa') }}"
                    class="flex flex-col items-center justify-center gap-3 p-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md hover:border-emerald-500 transition-all duration-300 group">
                    <i class="mgc_chart_line_line text-3xl text-emerald-600 group-hover:scale-110 transition-transform"></i>
                    <span class="text-base font-semibold text-gray-800 dark:text-gray-100">Gastos de la Empresa</span>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Consulta y gestiona los gastos generales
                    </p>
                </a>

                <!-- Facturas de Venta -->
                <a href="{{ route('empresa.facturas-ventas') }}"
                    class="flex flex-col items-center justify-center gap-3 p-6 bg-white dark:bg-slate-800
           border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm
           hover:shadow-md hover:border-purple-500 transition-all duration-300 group">
                    <i class="mgc_barcode_line text-3xl text-purple-600 group-hover:scale-110 transition-transform"></i>
                    <span class="text-base font-semibold text-gray-800 dark:text-gray-100">
                        Facturas de Venta
                    </span>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                        Gestión global de facturas emitidas y certificaciones
                    </p>
                </a>


                <!-- Informes -->
                {{--  <a href="{{ route('informes.index') }}"
                    class="flex flex-col items-center justify-center gap-3 p-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md hover:border-indigo-500 transition-all duration-300 group">
                    <i class="mgc_book_5_line text-3xl text-indigo-600 group-hover:scale-110 transition-transform"></i>
                    <span class="text-base font-semibold text-gray-800 dark:text-gray-100">Informes</span>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Visualiza reportes financieros y
                        estadísticos</p>
                </a> --}}

            </div>



        </div>
    </div>

    @include('empresa.modals.create-edit-empresa')
@endsection



@section('script')
    @vite(['resources/js/pages/empresa.js'])
@endsection
