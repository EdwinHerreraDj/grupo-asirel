@extends('layouts.vertical', [
    'title' => 'Presupuesto de Venta',
    'sub_title' => 'Obras',
])

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
    @livewire('obras.presupuesto-venta', ['obra' => $obra], key('presupuesto-venta-' . $obra->id))
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
@endsection
