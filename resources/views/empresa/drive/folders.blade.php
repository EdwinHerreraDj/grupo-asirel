@extends('layouts.vertical', ['title' => 'Drive App', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            {{-- Boton para regresar --}}


            <div class="card p-10">
                <div>
                    <a href="{{ route('empresa.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 text-gray-700 font-medium shadow-sm border border-gray-200 
               hover:bg-gray-200 hover:text-gray-900 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary/30 mb-7">
                        <i class="mgc_arrow_left_line text-lg"></i>
                        Regresar
                    </a>
                    <hr class="mb-3">
                    <div class="mb-5">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-1">Drive App</h2>
                        <p class="text-gray-600 dark:text-gray-400">Gestiona las carpetas y archivos de tu empresa aquí.</p>
                    </div>
                </div>

                <livewire:drive.folder-manager />

            </div>

        </div>
    </div>
@endsection

@section('script')
    <script>
        window.addEventListener('toast', e => {
            const {
                type,
                text
            } = e.detail;
            if (type === 'success') {
                Notyf.success(text);
            } else if (type === 'error') {
                Notyf.error(text);
            }
        });
    </script>
@endsection
