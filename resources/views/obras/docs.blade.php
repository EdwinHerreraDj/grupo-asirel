@extends('layouts.vertical', ['title' => 'Documentos', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <livewire:documentos.index :obra="$obra" />
@endsection
