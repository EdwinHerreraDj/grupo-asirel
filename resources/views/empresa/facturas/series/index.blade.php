@extends('layouts.vertical', [
    'title' => 'Facturas Series',
    'sub_title' => 'Obras',
])

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12 card p-10">
            @livewire('empresa.facturas.series.index')
        </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
    @vite(['resources/js/visor-pdf.js'])
@endsection
