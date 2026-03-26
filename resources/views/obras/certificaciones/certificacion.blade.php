@extends('layouts.vertical', [
    'title' => 'Certificaciones',
    'sub_title' => 'Obras',
])

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
    <div id="react-certificaciones-listado" data-obra-id="{{ $obra->id }}"></div>
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
@endsection
