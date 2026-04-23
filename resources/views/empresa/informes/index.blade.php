@extends('layouts.vertical', ['title' => 'Informes', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <livewire:informes.informes-general />
@endsection
