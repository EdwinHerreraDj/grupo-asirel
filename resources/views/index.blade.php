@extends('layouts.vertical', ['title' => 'Inicio', 'sub_title' => 'Menu', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-6 mb-6">
        <div class="col-span-1">
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="w-12 h-12 flex justify-center items-center rounded text-primary bg-primary/25">
                                <i class="mgc_document_2_line text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <h5 class="mb-1">Obras activas</h5>
                            <p>{{$totalObra}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-1">
            <div class="card">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="w-12 h-12 flex justify-center items-center rounded text-success bg-success/25">
                                <i class="mgc_group_line text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <h5 class="mb-1">Total Usuarios</h5>
                            <p>{{ $totalUsers }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- Grid End -->

    <div class="grid grid-cols-1 gap-6 mb-6">
        <div class="card p-5">
            <div class="bg-blue-100 dark:bg-gray-900 p-6 rounded-2xl shadow-md text-center">
                <h1 class="text-2xl md:text-3xl font-semibold text-gray-800 dark:text-white">
                    Bienvenido a <span class="text-blue-600 dark:text-blue-400">Alminares</span>: 
                    Tu plataforma para la gestión eficiente de proyectos de infraestructura.
                </h1>
                <p class="mt-2 text-lg text-gray-700 dark:text-gray-300">
                    Hola, <span class="font-bold text-blue-700 dark:text-blue-300">{{ session('user_name') }}</span>.  
                    ¡Nos alegra verte de nuevo! 🚀
                </p>
            </div>            
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/pages/dashboard.js')
@endsection
