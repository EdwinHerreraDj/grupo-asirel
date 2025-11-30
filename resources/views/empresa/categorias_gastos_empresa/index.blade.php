@extends('layouts.vertical', [
    'title' => 'Categorías de Gastos de la Empresa',
    'sub_title' => 'Obras',
])

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')

    <livewire:empresa.gastos.categorias.index />

@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
@endsection
