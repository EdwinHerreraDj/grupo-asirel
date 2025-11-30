@extends('layouts.vertical', ['title' => 'Gastos empresa', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12 card p-10">

            <livewire:empresa.gastos.index />
        </div>
    </div>
@endsection

@section('script')
    @vite(['resources/js/visor-pdf.js'])
@endsection
