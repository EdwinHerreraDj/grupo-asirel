@extends('layouts.vertical', ['title' => 'Apariencia del panel', 'sub_title' => 'Configuración', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    @php
        $imagenes = [
            [
                'campo' => 'logo',
                'titulo' => 'Logo del menú',
                'texto' => 'Se ve arriba del menú lateral cuando está abierto y en la barra superior.',
                'medidas' => 'Recomendado: 320 × 80 px (se muestra a 54 px de alto). PNG con fondo transparente.',
                'formatos' => 'PNG, JPG o WEBP · máx. 2 MB',
                'actual' => $apariencia->logo_url,
                'defecto' => '/images/logo-dark.png',
                'fondo' => 'bg-white',
                'alto' => 'h-14',
            ],
            [
                'campo' => 'logo_pequeno',
                'titulo' => 'Icono del menú plegado',
                'texto' => 'Se ve cuando el menú está plegado. Si no pones favicon, también se usa en la pestaña del navegador.',
                'medidas' => 'Recomendado: cuadrado de 128 × 128 px (se muestra a 40 px). PNG con fondo transparente.',
                'formatos' => 'PNG, JPG o WEBP · máx. 1 MB',
                'actual' => $apariencia->url('logo_pequeno'),
                'defecto' => '/images/logo-sm.png',
                'fondo' => 'bg-white',
                'alto' => 'h-10',
            ],
            [
                'campo' => 'favicon',
                'titulo' => 'Favicon',
                'texto' => 'El icono de la pestaña del navegador y de los accesos directos.',
                'medidas' => 'Recomendado: cuadrado de 32 × 32 o 48 × 48 px. Si lo dejas vacío se usa el icono del menú plegado.',
                'formatos' => 'PNG, JPG, WEBP o ICO · máx. 512 KB',
                'actual' => $apariencia->url('favicon'),
                'defecto' => '/images/favicon.ico',
                'fondo' => 'bg-slate-100',
                'alto' => 'h-8',
            ],
        ];
    @endphp

    <div class="grid grid-cols-12">
        <div class="col-span-12">

            {{-- CABECERA --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)] mb-4">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                        <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                        Configuración
                    </div>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">Apariencia del panel</h2>
                    <p class="mt-1 max-w-3xl text-sm text-slate-500">
                        Las imágenes de la aplicación: el logo del menú, el icono cuando el menú está plegado y el
                        favicon. El <strong>logo de la empresa</strong> no se toca aquí: ese se usa solo en los PDFs e
                        informes y se cambia en <a href="{{ route('empresa.configuracion') }}"
                            class="font-semibold text-primary hover:underline">Configuración → Empresa</a>.
                    </p>
                </div>

                @if (session('apariencia_ok'))
                    <div class="mx-5 mt-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 sm:mx-6">
                        <i class="mgc_check_circle_line text-lg"></i>
                        <div class="text-sm">
                            <p class="font-semibold">{{ session('apariencia_ok') }}</p>
                            <p>Si no ves el cambio en la pestaña del navegador, recarga con Ctrl + F5.</p>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mx-5 mt-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700 sm:mx-6">
                        <i class="mgc_warning_line text-lg"></i>
                        <div class="text-sm">
                            <p class="font-semibold">Revisa las imágenes</p>
                            <ul class="mt-1 list-inside list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- FORMULARIO --}}
                <form method="POST" action="{{ route('configuracion.apariencia.update') }}"
                    enctype="multipart/form-data" class="px-5 py-5 sm:px-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                        @foreach ($imagenes as $img)
                            <section class="flex flex-col overflow-hidden rounded-2xl border border-slate-200">
                                <header class="border-b border-slate-100 bg-slate-50/60 px-4 py-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <h3 class="text-sm font-semibold text-slate-900">{{ $img['titulo'] }}</h3>
                                        @if ($img['actual'])
                                            <span
                                                class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                Personalizado
                                            </span>
                                        @else
                                            <span
                                                class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-500">
                                                Por defecto
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $img['texto'] }}</p>
                                </header>

                                <div class="flex flex-1 flex-col gap-3 p-4">
                                    {{-- Vista previa --}}
                                    <div
                                        class="flex min-h-[5.5rem] items-center justify-center rounded-xl border border-slate-200 {{ $img['fondo'] }} p-3">
                                        <img src="{{ $img['actual'] ?? $img['defecto'] }}"
                                            alt="{{ $img['titulo'] }}"
                                            class="{{ $img['alto'] }} w-auto max-w-full object-contain">
                                    </div>

                                    <p class="text-xs text-slate-500">{{ $img['medidas'] }}</p>

                                    <label class="mt-auto block">
                                        <span class="mb-1 block text-xs font-medium text-slate-600">Cambiar imagen</span>
                                        <input type="file" name="{{ $img['campo'] }}"
                                            accept="{{ $img['campo'] === 'favicon' ? 'image/png,image/jpeg,image/webp,image/x-icon,.ico' : 'image/png,image/jpeg,image/webp' }}"
                                            class="block w-full cursor-pointer rounded-xl border border-slate-300 text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-l-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2.5 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                                        <span class="mt-1 block text-[11px] text-slate-400">{{ $img['formatos'] }}</span>
                                    </label>
                                </div>

                                @if ($img['actual'])
                                    <footer class="border-t border-slate-100 px-4 py-2.5">
                                        <button type="submit" form="quitar-{{ $img['campo'] }}"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700">
                                            <i class="mgc_delete_line"></i> Quitar y usar la de por defecto
                                        </button>
                                    </footer>
                                @endif
                            </section>
                        @endforeach
                    </div>

                    <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                        <a href="{{ route('empresa.configuracion') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:bg-[#245ec9]">
                            <i class="mgc_check_line"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            {{-- CÓMO SE VE --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h3 class="flex items-center gap-2 font-semibold text-slate-900">
                        <i class="mgc_eye_line text-lg text-cyan-600"></i> Cómo queda
                    </h3>
                </div>
                <div class="grid grid-cols-1 gap-4 px-5 py-5 sm:px-6 lg:grid-cols-3">
                    {{-- Menú abierto --}}
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Menú abierto</p>
                        <div class="flex h-24 w-full items-center rounded-2xl border border-slate-200 bg-white px-4">
                            <img src="{{ $apariencia->logo_url ?? '/images/logo-dark.png' }}" alt="Logo del menú"
                                class="h-[54px] w-auto max-w-[70%] object-contain">
                        </div>
                    </div>
                    {{-- Menú plegado --}}
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Menú plegado</p>
                        <div class="flex h-24 w-full items-center justify-center rounded-2xl border border-slate-200 bg-white">
                            <img src="{{ $apariencia->icono_url ?? '/images/logo-sm.png' }}" alt="Icono del menú plegado"
                                class="h-10 w-auto object-contain">
                        </div>
                    </div>
                    {{-- Pestaña del navegador --}}
                    <div>
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Pestaña del navegador</p>
                        <div class="flex h-24 w-full items-center rounded-2xl border border-slate-200 bg-slate-100 p-3">
                            <div class="flex w-full items-center gap-2 rounded-t-xl bg-white px-3 py-2 shadow-sm">
                                <img src="{{ $apariencia->favicon_url ?? '/images/favicon.ico' }}" alt="Favicon"
                                    class="h-4 w-4 object-contain">
                                <span class="truncate text-xs text-slate-600">{{ $empresa?->nombre ?? 'Obras' }}</span>
                                <i class="mgc_close_line ml-auto text-xs text-slate-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Formularios de "quitar" (fuera del formulario principal) --}}
    @foreach ($imagenes as $img)
        @if ($img['actual'])
            <form id="quitar-{{ $img['campo'] }}" method="POST"
                action="{{ route('configuracion.apariencia.eliminar', $img['campo']) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    @endforeach
@endsection
