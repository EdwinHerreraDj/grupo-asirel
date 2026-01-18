@extends('layouts.vertical', ['title' => 'Detalle Certificación', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12 card p-10">
            @livewire('empresa.certificaciones.detalle', ['certificacionId' => $certificacion->id])
        </div>
    </div>
@endsection
