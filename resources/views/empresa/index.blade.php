@extends('layouts.vertical', ['title' => 'Mi unidad', 'sub_title' => 'Pages', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    <div class="grid grid-cols-12">
        <div class="col-span-12">

            @include('./notifications/notyf')

            {{-- CABECERA --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)] mb-6">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Mi unidad
                    </div>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                        Accesos rápidos
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Atajos a los módulos de gestión global de la empresa.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 px-5 py-5 sm:grid-cols-3 sm:px-6">
                    {{-- Drive --}}
                    <a href="{{ route('empresa.driveApp') }}"
                        class="group flex flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 group-hover:scale-110 transition-transform">
                            <i class="mgc_album_2_line text-2xl"></i>
                        </div>
                        <span class="text-base font-semibold text-slate-800">Drive</span>
                        <p class="text-center text-sm text-slate-500">
                            Accede a archivos y documentos de la empresa.
                        </p>
                    </a>

                    {{-- Gastos --}}
                    <a href="{{ route('empresa.gastosEmpresa') }}"
                        class="group flex flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 group-hover:scale-110 transition-transform">
                            <i class="mgc_chart_line_line text-2xl"></i>
                        </div>
                        <span class="text-base font-semibold text-slate-800">Gastos de la empresa</span>
                        <p class="text-center text-sm text-slate-500">
                            Consulta y gestiona los gastos generales.
                        </p>
                    </a>

                    {{-- Facturas de venta --}}
                    <a href="{{ route('empresa.facturas-ventas') }}"
                        class="group flex flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-purple-300 hover:shadow-md">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 group-hover:scale-110 transition-transform">
                            <i class="mgc_barcode_line text-2xl"></i>
                        </div>
                        <span class="text-base font-semibold text-slate-800">Facturas de venta</span>
                        <p class="text-center text-sm text-slate-500">
                            Gestión global de facturas emitidas y certificaciones.
                        </p>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
