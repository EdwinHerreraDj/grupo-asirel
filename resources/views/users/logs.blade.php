@extends('layouts.vertical', ['title' => 'Accesos', 'sub_title' => 'Administración', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    @php
        $input = 'w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary';
        $estados = [
            'abierta' => ['texto' => 'Abierta', 'clases' => 'border-emerald-200 bg-emerald-50 text-emerald-700', 'icono' => 'mgc_check_circle_line'],
            'cerrada' => ['texto' => 'Cerrada', 'clases' => 'border-slate-200 bg-slate-50 text-slate-600', 'icono' => 'mgc_exit_line'],
            'caducada' => ['texto' => 'Caducada', 'clases' => 'border-amber-200 bg-amber-50 text-amber-700', 'icono' => 'mgc_time_line'],
        ];
    @endphp

    <div x-data="{ purgar: false }" class="space-y-4">
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">

            {{-- CABECERA --}}
            <div class="border-b border-slate-800 cabecera-panel cabecera-panel-violet px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-violet-200">
                            <span class="h-2 w-2 rounded-full bg-violet-300"></span>
                            Administración
                        </div>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight text-white">Accesos</h2>
                        <p class="mt-1 text-sm text-slate-300">
                            Quién ha entrado, desde dónde y cuánto duró la sesión.
                            <a href="{{ route('users.index') }}" class="font-semibold text-violet-200 hover:underline">Ver los usuarios</a>
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" x-on:click="purgar = true"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-semibold text-slate-100 transition hover:bg-white/20">
                            <i class="mgc_delete_line"></i> Limpiar antiguos
                        </button>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="mx-5 mt-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 sm:mx-6">
                    <i class="mgc_check_circle_line text-lg"></i>
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            {{-- CIFRAS --}}
            <div class="grid grid-cols-2 gap-3 px-5 py-5 sm:px-6 lg:grid-cols-4">
                <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-cyan-700">Accesos hoy</p>
                    <p class="mt-1 text-xl font-bold text-cyan-900">{{ $stats['hoy'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Personas hoy</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">{{ $stats['usuarios_hoy'] }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-emerald-700">Sesiones abiertas</p>
                    <p class="mt-1 text-xl font-bold text-emerald-900">{{ $stats['abiertas'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Registrados</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                </div>
            </div>

            {{-- FILTROS --}}
            <form method="GET" action="{{ route('login.logs') }}"
                class="grid grid-cols-1 gap-2 border-t border-slate-200 px-5 py-4 sm:grid-cols-2 sm:px-6 xl:grid-cols-6">
                <div class="relative xl:col-span-2">
                    <i class="mgc_search_line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" name="buscar" value="{{ $filtros['buscar'] ?? '' }}"
                        placeholder="Nombre, correo o IP…" class="{{ $input }} pl-9">
                </div>
                <select name="usuario" class="{{ $input }}">
                    <option value="">Todos los usuarios</option>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" @selected((string) ($filtros['usuario'] ?? '') === (string) $usuario->id)>
                            {{ $usuario->name }}
                        </option>
                    @endforeach
                </select>
                <select name="estado" class="{{ $input }}">
                    <option value="">Cualquier estado</option>
                    @foreach ($estados as $valor => $datos)
                        <option value="{{ $valor }}" @selected(($filtros['estado'] ?? '') === $valor)>{{ $datos['texto'] }}</option>
                    @endforeach
                </select>
                <input type="date" name="desde" value="{{ $filtros['desde'] ?? '' }}" class="{{ $input }}" aria-label="Desde">
                <input type="date" name="hasta" value="{{ $filtros['hasta'] ?? '' }}" class="{{ $input }}" aria-label="Hasta">

                <div class="flex gap-2 sm:col-span-2 xl:col-span-6">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="mgc_filter_line"></i> Filtrar
                    </button>
                    @if (array_filter($filtros))
                        <a href="{{ route('login.logs') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-600 shadow-sm transition hover:bg-slate-50">
                            <i class="mgc_close_line"></i> Quitar filtros
                        </a>
                    @endif
                </div>
            </form>

            {{-- LISTA --}}
            @if ($accesos->isEmpty())
                <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <i class="mgc_history_line text-2xl"></i>
                    </div>
                    <p class="mt-3 font-semibold text-slate-700">Ningún acceso con estos filtros</p>
                </div>
            @else
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Usuario</th>
                                <th class="px-3 py-3">Entrada</th>
                                <th class="px-3 py-3">Salida</th>
                                <th class="px-3 py-3">Duración</th>
                                <th class="px-3 py-3">Estado</th>
                                <th class="hidden px-3 py-3 lg:table-cell">Desde</th>
                                <th class="px-6 py-3">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($accesos as $acceso)
                                @php $estado = $estados[$acceso->estado]; @endphp
                                <tr class="transition hover:bg-slate-50/60">
                                    <td class="px-6 py-3">
                                        <p class="font-semibold text-slate-800">{{ $acceso->user?->name ?? 'Usuario eliminado' }}</p>
                                        <p class="break-all text-xs text-slate-500">{{ $acceso->user?->email }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-slate-600">
                                        {{ $acceso->logged_in_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-slate-600">
                                        {{ $acceso->logged_out_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-slate-600">
                                        {{ $acceso->duracion ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center gap-1 whitespace-nowrap rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $estado['clases'] }}">
                                            <i class="{{ $estado['icono'] }}"></i> {{ $estado['texto'] }}
                                        </span>
                                    </td>
                                    <td class="hidden whitespace-nowrap px-3 py-3 text-slate-600 lg:table-cell">
                                        {{ $acceso->navegador }} · {{ $acceso->sistema }}
                                        <span class="block text-xs text-slate-400">{{ $acceso->dispositivo }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-3 font-mono text-xs text-slate-500">
                                        {{ $acceso->ip_address ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Tarjetas (móvil) --}}
                <ul class="divide-y divide-slate-100 md:hidden">
                    @foreach ($accesos as $acceso)
                        @php $estado = $estados[$acceso->estado]; @endphp
                        <li class="px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="break-words font-semibold text-slate-800">{{ $acceso->user?->name ?? 'Usuario eliminado' }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $acceso->logged_in_at?->format('d/m/Y H:i') }}
                                        @if ($acceso->duracion)
                                            · {{ $acceso->duracion }}
                                        @endif
                                    </p>
                                    <p class="mt-0.5 text-xs text-slate-400">
                                        {{ $acceso->navegador }} · {{ $acceso->dispositivo }} ·
                                        <span class="font-mono">{{ $acceso->ip_address }}</span>
                                    </p>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $estado['clases'] }}">
                                    <i class="{{ $estado['icono'] }}"></i> {{ $estado['texto'] }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if ($accesos->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                        {{ $accesos->links() }}
                    </div>
                @endif
            @endif

            <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500 sm:px-6">
                Una sesión aparece como <strong>abierta</strong> mientras no se cierre y no pasen
                {{ $minutosSesion }} minutos desde la entrada; después se marca como <strong>caducada</strong>,
                porque al caducar sola no queda registro de la salida.
            </p>
        </div>

        {{-- MODAL: LIMPIAR ANTIGUOS --}}
        <template x-if="purgar">
            <div class="fixed inset-0 z-[999] flex items-end justify-center bg-slate-900/60 backdrop-blur-sm sm:items-center sm:px-4 sm:py-6"
                role="dialog" aria-modal="true" x-on:keydown.escape.window="purgar = false">
                <div class="w-full max-w-md overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl sm:rounded-3xl">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                        <h3 class="text-lg font-semibold text-slate-900">Limpiar accesos antiguos</h3>
                    </div>
                    <form method="POST" action="{{ route('login.logs.purgar') }}">
                        @csrf
                        <div class="space-y-3 px-5 py-5 text-sm text-slate-600 sm:px-6">
                            <p>Se borrarán los accesos con más de los meses que indiques. Los usuarios no se tocan.</p>
                            <label class="block">
                                <span class="mb-1.5 block text-sm font-medium text-slate-700">Borrar los anteriores a</span>
                                <select name="meses" class="{{ $input }}">
                                    <option value="6">6 meses</option>
                                    <option value="12" selected>12 meses</option>
                                    <option value="24">24 meses</option>
                                    <option value="36">36 meses</option>
                                </select>
                            </label>
                        </div>
                        <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                            <button type="button" x-on:click="purgar = false"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(225,29,72,0.22)] transition hover:from-rose-500 hover:to-red-500">
                                <i class="mgc_delete_line"></i> Borrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
@endsection
