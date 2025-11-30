@extends('layouts.vertical', [
    'title' => 'Proveedores',
    'sub_title' => 'Obras',
])

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
    {{-- Renderizamos el componente Livewire --}}
    @livewire('proveedores.index')
    {{-- @livewire('proveedores.formulario') --}}
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
@endsection


