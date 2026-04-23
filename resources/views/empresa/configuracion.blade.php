@extends('layouts.vertical', ['title' => 'Configuración de empresa', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">

            @include('./notifications/notyf')

            {{-- CABECERA --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)] mb-4">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0">
                            <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                Configuración
                            </div>
                            <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                                Datos de la empresa
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Estos datos se usan en las cabeceras y pies de los PDFs (facturas, certificaciones, informes).
                            </p>
                        </div>

                        <div>
                            <button
                                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(37,99,235,0.22)] transition hover:from-cyan-500 hover:to-blue-500"
                                data-fc-type="modal" data-fc-target="empresa" type="button">
                                <i class="mgc_edit_2_line text-base"></i>
                                Editar datos
                            </button>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-5 sm:px-6">
                    <livewire:empresa.empresa-info />
                </div>
            </div>

            {{-- ========== DISEÑO DE PDFs ========== --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)] mb-4">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Diseño
                    </div>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                        Diseño de PDFs e informes
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Personaliza colores, visibilidad del logo y un pie de página opcional. Estos ajustes se
                        aplicarán progresivamente a todos los PDFs del sistema.
                    </p>
                </div>

                <div class="px-5 py-5 sm:px-6">
                    <livewire:empresa.pdf-config />
                </div>
            </div>
        </div>
    </div>

    @include('empresa.modals.create-edit-empresa')
@endsection

@section('script')
    @vite(['resources/js/pages/empresa.js'])
@endsection
