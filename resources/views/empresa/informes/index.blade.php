@extends('layouts.vertical', ['title' => 'Informes', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <a href="{{ route('empresa.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium shadow-sm border border-gray-200 
               hover:bg-gray-200 hover:text-gray-900 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/30 mb-7">
                <i class="mgc_arrow_left_line text-lg"></i>
                Regresar
            </a>
            <livewire:informes.informes-general>
        </div>
    </div>
@endsection
