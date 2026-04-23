@extends('layouts.vertical', [
    'title' => 'Coste Teórico',
    'sub_title' => 'Planificación',
])

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
    <div id="react-coste-teorico-global"
        data-obras="{{ $obras->toJson() }}"
        data-url-regresar="{{ route('unidad') }}"></div>
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
@endsection
