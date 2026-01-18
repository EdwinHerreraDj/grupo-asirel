@extends('layouts.vertical', ['title' => 'Clientes', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">

            @livewire('clientes.index')
        </div>
    </div>
@endsection
