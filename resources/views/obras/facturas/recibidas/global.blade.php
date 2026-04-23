@extends('layouts.vertical', [
    'title' => 'Facturas Recibidas',
    'sub_title' => 'Obras',
])

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            {{-- Modo global: el componente se monta sin obra y muestra el selector --}}
            @livewire('obras.facturas-recibidas')
        </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
    @vite(['resources/js/visor-pdf.js'])
@endsection
