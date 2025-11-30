@extends('layouts.vertical', ['title' => 'Documentos', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])


@section('content')
    {{-- Mensaje informativo --}}
    <div id="alert-docs" class="relative p-4 bg-purple-50 border-l-4 border-purple-500 text-purple-800 rounded-md mb-4">
        <button onclick="document.getElementById('alert-docs').remove()"
            class="absolute top-2 right-2 text-xl font-bold text-gray-700 hover:text-red-500" aria-label="Cerrar">
            &times;
        </button>

        <h2 class="text-lg font-semibold mb-2">Gestión de documentación</h2>
        <p>
            En esta sección puedes subir y gestionar la <strong>documentación oficial de la obra</strong> (licencias,
            contratos, permisos, etc.).
        </p>
        <p class="mt-2">
            Ten en cuenta que <strong>solo se permite un documento por cada tipo</strong>. Si necesitas subir varias
            versiones o archivos relacionados con un mismo tipo, deberás agruparlos en un <strong>archivo ZIP</strong>.
        </p>
    </div>

    {{-- Modal para ver PDF --}}
    @include('components-vs.modals.visor-pdf')

    {{-- Livewire Component --}}
    <livewire:documentos.obra-documentos :obra="$obra" />
@endsection

@section('script')
    @vite(['resources/js/visor-pdf.js', 'resources/js/pages/obras-docs.js'])
@endsection
