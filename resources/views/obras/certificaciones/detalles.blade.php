@extends('layouts.vertical', [
    'title' => 'Detalle Certificación',
    'sub_title' => 'Obras',
])

@section('content')
    <div id="react-certificaciones-detalle" data-certificacion-id="{{ $certificacion->id }}"
        data-url-volver="{{ route('obras.certificaciones', $certificacion->obra_id) }}"></div>
@endsection
