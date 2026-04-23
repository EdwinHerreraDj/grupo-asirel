@extends('layouts.vertical', [
    'title' => 'Tareas',
    'sub_title' => 'Operativa',
])

@section('css')
    @vite(['resources/css/app.css'])
@endsection

@section('content')
    <div id="react-tareas"></div>
@endsection

@section('script')
    @vite(['resources/js/pages/highlight.js'])
@endsection
