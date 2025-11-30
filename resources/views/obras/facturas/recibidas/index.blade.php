@extends('layouts.vertical', [
    'title' => 'Facturas Recibidas',
    'sub_title' => 'Obras',
])

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
    {{-- Renderizamos el componente Livewire --}}
    @livewire('obras.facturas-recibidas', ['obra' => $obra])
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
    @vite(['resources/js/visor-pdf.js'])
@endsection


