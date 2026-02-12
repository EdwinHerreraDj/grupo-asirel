@extends('layouts.vertical', [
    'title' => 'Proveedores',
    'sub_title' => 'Obras',
])



@section('content')
    {{-- Renderizamos el componente Livewire --}}
    {{-- @livewire('proveedores.index') --}}
    {{-- @livewire('proveedores.formulario') --}}
    <div id="react-proveedores"></div>
@endsection



