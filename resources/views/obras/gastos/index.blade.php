@extends('layouts.vertical', ['title' => 'Gastos obra', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div id="alert-gastos" class="relative p-4 bg-green-50 border-l-4 border-green-600 text-green-800 rounded-md mb-4">
        <button onclick="document.getElementById('alert-gastos').remove()"
            class="absolute top-2 right-2 text-xl font-bold text-green-700 hover:text-red-500" aria-label="Cerrar">
            &times;
        </button>

        <h2 class="text-lg font-semibold mb-2">Gestión de gastos y ventas</h2>
        <p>
            En esta sección puedes acceder al registro detallado de los distintos <strong>gastos e ingresos</strong>
            asociados a la obra:
        </p>
        <ul class="mt-2 list-disc list-inside">
            <li><strong>Gastos</strong>: materiales, alquileres, subcontratas, mano de obra y otros.</li>
        </ul>
        <p class="mt-2">
            Mantener actualizados estos apartados es fundamental para el <strong>control económico y el análisis
                financiero</strong> de cada proyecto.
        </p>
    </div>


    <div class="grid grid-cols-12">
        <div class="col-span-12">


            <div class="card p-5">
                <h1>Gastos</h1>


                <div class="container grid grid-cols-4 gap-4 p-4">
                    {{-- Componentes de ERP Asirel --}}
                    <div>
                        <a href="{{ route('obras.facturas-recibidas', $obra->id) }}"
                            class="block bg-blue-500 hover:bg-blue-600 text-white font-bold text-center py-6 rounded-lg shadow-md transition-all duration-200">
                            <i class="mgc_codepen_line text-2xl mb-2 inline-block"></i>
                            <span class="block mt-2">Facturas Recibidas</span>
                        </a>
                    </div>


                    {{-- <div>
                        <a href="{{ route('obras.gastos.materiales', $obra->id) }}"
                            class="block bg-blue-500 hover:bg-blue-600 text-white font-bold text-center py-6 rounded-lg shadow-md transition-all duration-200">
                            <i class="mgc_codepen_line text-2xl mb-2 inline-block"></i>
                            <span class="block mt-2">Materiales</span>
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('obras.gastos.alquileres', $obra->id) }}"
                            class="block bg-green-500 hover:bg-green-600 text-white font-bold text-center py-6 rounded-lg shadow-md transition-all duration-200">
                            <i class="mgc_kingkey_100_tower_line text-2xl mb-2 inline-block"></i>
                            <span class="block mt-2">Alquileres</span>
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('obras.gastos.subcontratas', $obra->id) }}"
                            class="block bg-yellow-500 hover:bg-yellow-600 text-white font-bold text-center py-6 rounded-lg shadow-md transition-all duration-200">
                            <i class="mgc_file_more_line text-2xl mb-2 inline-block"></i>
                            <span class="block mt-2">Subcontratas</span>
                        </a>
                    </div> --}}
                    {{-- <div>
                        <a href="{{ route('obras.gastos-varios', $obra->id) }}"
                            class="block bg-red-500 hover:bg-red-600 text-white font-bold text-center py-6 rounded-lg shadow-md transition-all duration-200">
                            <i class="mgc_gas_station_line text-2xl mb-2 inline-block"></i>
                            <span class="block mt-2">Gastos varios</span>
                        </a>
                    </div> --}}
                </div>
                <hr>
               {{--  <h1 class="mt-5">Venta | Certificaciones</h1> --}}
                {{-- <div class="container grid grid-cols-4 gap-4 p-4"> --}}
                    {{--  <div>
                        <a href="{{ route('obras.ventas', $obra->id) }}"
                            class="block bg-purple-400 hover:bg-purple-500 text-white font-bold text-center py-6 rounded-lg shadow-md transition-all duration-200">
                            <i class="mgc_pig_money_line text-2xl mb-2 inline-block"></i>
                            <span class="block mt-2">Venta</span>
                        </a>
                    </div> --}}
                  {{--   <a href="{{ route('obras.certificaciones', $obra->id) }}"
                        class="block bg-purple-400 hover:bg-purple-500 text-white font-bold text-center py-6 rounded-lg shadow-md transition-all duration-200">
                        <i class="mgc_pig_money_line text-2xl mb-2 inline-block"></i>
                        <span class="block mt-2">Certificaciones</span>
                    </a> --}}
              {{--   </div> --}}
                <hr>
                <h1 class="mt-5">Control de Presencia | Mano de Obra</h1>
                <div class="container grid grid-cols-4 gap-4 p-4">
                    <div>
                        <a href="{{ route('obras.fichajes', $obra->id) }}"
                            class="block bg-rose-400 hover:bg-rose-500 text-white font-bold text-center py-6 rounded-lg shadow-md transition-all duration-200">
                            <i class="mgc_black_board_2_line text-2xl mb-2 inline-block"></i>
                            <span class="block mt-2">Fichaje</span>
                        </a>
                    </div>
                </div>



            </div>

        </div>
    </div>
@endsection
