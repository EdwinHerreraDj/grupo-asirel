@extends('layouts.vertical', ['title' => 'Usuarios', 'sub_title' => 'Administración', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    @php
        $roles = [
            \App\Models\User::ROLE_SUPER_ADMIN => 'Súper administrador',
            \App\Models\User::ROLE_ADMIN => 'Administrador',
            \App\Models\User::ROLE_USER => 'Usuario',
        ];
        $rolClases = [
            \App\Models\User::ROLE_SUPER_ADMIN => 'border-violet-200 bg-violet-50 text-violet-700',
            \App\Models\User::ROLE_ADMIN => 'border-cyan-200 bg-cyan-50 text-cyan-700',
            \App\Models\User::ROLE_USER => 'border-slate-200 bg-slate-50 text-slate-600',
        ];
        $asignables = $actor->isSuperAdmin()
            ? $roles
            : array_diff_key($roles, [\App\Models\User::ROLE_SUPER_ADMIN => null]);
        $input = 'w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary';
        $etiqueta = 'mb-1.5 block text-sm font-medium text-slate-700';
    @endphp

    <div x-data="{ modal: null, usuario: {}, borrar: null }" class="space-y-4">

        {{-- CABECERA --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div class="border-b border-slate-800 cabecera-panel cabecera-panel-violet px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-violet-200">
                            <span class="h-2 w-2 rounded-full bg-violet-300"></span>
                            Administración
                        </div>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight text-white">Usuarios</h2>
                        <p class="mt-1 text-sm text-slate-300">
                            Quién entra en la aplicación y con qué permisos.
                            <a href="{{ route('login.logs') }}" class="font-semibold text-violet-200 hover:underline">Ver los accesos</a>
                        </p>
                    </div>

                    <button type="button" x-on:click="usuario = {}; modal = 'crear'"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:bg-[#245ec9]">
                        <i class="mgc_user_add_line"></i> Nuevo usuario
                    </button>
                </div>
            </div>

            {{-- AVISOS --}}
            @if (session('success'))
                <div class="mx-5 mt-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 sm:mx-6">
                    <i class="mgc_check_circle_line text-lg"></i>
                    <p class="font-semibold">{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="mx-5 mt-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:mx-6">
                    <i class="mgc_warning_line text-lg"></i>
                    <p class="font-semibold">{{ session('error') }}</p>
                </div>
            @endif
            @if ($errors->any())
                <div class="mx-5 mt-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 sm:mx-6">
                    <i class="mgc_warning_line text-lg"></i>
                    <div>
                        <p class="font-semibold">Revisa los datos</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- CIFRAS --}}
            <div class="grid grid-cols-2 gap-3 px-5 py-5 sm:px-6 lg:grid-cols-4">
                @foreach ([['Total', $stats['total'], 'slate'], ['Súper administradores', $stats['super_admins'], 'violet'], ['Administradores', $stats['admins'], 'cyan'], ['Usuarios', $stats['usuarios'], 'slate']] as [$texto, $valor, $tono])
                    <div class="rounded-2xl border {{ $tono === 'violet' ? 'border-violet-200 bg-violet-50' : ($tono === 'cyan' ? 'border-cyan-200 bg-cyan-50' : 'border-slate-200 bg-slate-50/70') }} px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">{{ $texto }}</p>
                        <p class="mt-1 text-xl font-bold text-slate-900">{{ $valor }}</p>
                    </div>
                @endforeach
            </div>

            {{-- FILTROS --}}
            <form method="GET" action="{{ route('users.index') }}"
                class="grid grid-cols-1 gap-2 border-t border-slate-200 px-5 py-4 sm:grid-cols-[1fr_minmax(0,14rem)_auto] sm:px-6">
                <div class="relative">
                    <i class="mgc_search_line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" name="buscar" value="{{ $filtros['buscar'] ?? '' }}"
                        placeholder="Buscar por nombre o correo…" class="{{ $input }} pl-9">
                </div>
                <select name="rol" class="{{ $input }}">
                    <option value="">Todos los roles</option>
                    @foreach ($roles as $valor => $texto)
                        <option value="{{ $valor }}" @selected(($filtros['rol'] ?? '') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <button type="submit"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:flex-none">
                        <i class="mgc_search_line"></i> Buscar
                    </button>
                    @if (($filtros['buscar'] ?? '') !== '' || ($filtros['rol'] ?? '') !== '')
                        <a href="{{ route('users.index') }}" title="Quitar filtros"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-600 shadow-sm transition hover:bg-slate-50">
                            <i class="mgc_close_line"></i>
                        </a>
                    @endif
                </div>
            </form>

            {{-- LISTA --}}
            @if ($users->isEmpty())
                <div class="flex flex-col items-center justify-center px-4 py-12 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <i class="mgc_user_3_line text-2xl"></i>
                    </div>
                    <p class="mt-3 font-semibold text-slate-700">Ningún usuario coincide</p>
                    <p class="mt-1 text-sm text-slate-500">Prueba con otra búsqueda o quita los filtros.</p>
                </div>
            @else
                {{-- Tabla (escritorio) --}}
                <div class="hidden overflow-x-auto md:block">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Usuario</th>
                                <th class="px-3 py-3">Rol</th>
                                <th class="px-3 py-3">Último acceso</th>
                                <th class="hidden px-3 py-3 lg:table-cell">Alta</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users as $user)
                                @include('users.partials.fila', ['user' => $user])
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Tarjetas (móvil) --}}
                <ul class="divide-y divide-slate-100 md:hidden">
                    @foreach ($users as $user)
                        @include('users.partials.tarjeta', ['user' => $user])
                    @endforeach
                </ul>

                @if ($users->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                        {{ $users->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- MODAL: CREAR / EDITAR --}}
        <template x-if="modal">
            <div class="fixed inset-0 z-[999] flex items-end justify-center bg-slate-900/60 backdrop-blur-sm sm:items-center sm:px-4 sm:py-6"
                role="dialog" aria-modal="true" x-on:keydown.escape.window="modal = null">
                <div class="flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl sm:rounded-3xl">
                    <div class="shrink-0 border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-violet-50/40 px-5 py-4 sm:px-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full border border-violet-100 bg-violet-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-violet-700">
                                    <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                                    <span x-text="modal === 'crear' ? 'Nuevo' : 'Editar'"></span>
                                </div>
                                <h3 class="mt-2 text-lg font-semibold text-slate-900"
                                    x-text="modal === 'crear' ? 'Nuevo usuario' : usuario.name"></h3>
                            </div>
                            <button type="button" x-on:click="modal = null" aria-label="Cerrar"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                <i class="mgc_close_line text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <form method="POST" x-bind:action="modal === 'crear' ? '{{ route('users.store') }}' : '{{ url('users') }}/' + usuario.id"
                        class="overflow-y-auto overscroll-contain px-5 py-5 sm:px-6">
                        @csrf
                        <template x-if="modal === 'editar'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="space-y-4">
                            <div>
                                <label class="{{ $etiqueta }}">Nombre <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-bind:value="usuario.name ?? ''" maxlength="30" required
                                    placeholder="Nombre y apellidos" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $etiqueta }}">Correo electrónico <span class="text-red-500">*</span></label>
                                <input type="email" name="email" x-bind:value="usuario.email ?? ''" required
                                    placeholder="nombre@empresa.com" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $etiqueta }}">Rol <span class="text-red-500">*</span></label>
                                <select name="role" class="{{ $input }}" required>
                                    @foreach ($asignables as $valor => $texto)
                                        <option value="{{ $valor }}" x-bind:selected="(usuario.role ?? 'user') === '{{ $valor }}'">
                                            {{ $texto }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-slate-500">
                                    Administrador: gestiona obras, facturación, informes y personal.
                                    Usuario: solo consulta lo suyo.
                                </p>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="{{ $etiqueta }}">
                                        Contraseña
                                        <span x-show="modal === 'crear'" class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="password" autocomplete="new-password"
                                        x-bind:required="modal === 'crear'" class="{{ $input }}">
                                    <p class="mt-1 text-xs text-slate-500">
                                        Mínimo 8 caracteres.
                                        <span x-show="modal === 'editar'">Déjala vacía para no cambiarla.</span>
                                    </p>
                                </div>
                                <div>
                                    <label class="{{ $etiqueta }}">Repite la contraseña</label>
                                    <input type="password" name="password_confirmation" autocomplete="new-password"
                                        x-bind:required="modal === 'crear'" class="{{ $input }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button" x-on:click="modal = null"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:bg-[#245ec9]">
                                <i class="mgc_check_line"></i>
                                <span x-text="modal === 'crear' ? 'Crear usuario' : 'Guardar cambios'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- MODAL: BORRAR --}}
        <template x-if="borrar">
            <div class="fixed inset-0 z-[999] flex items-end justify-center bg-slate-900/60 backdrop-blur-sm sm:items-center sm:px-4 sm:py-6"
                role="dialog" aria-modal="true" x-on:keydown.escape.window="borrar = null">
                <div class="w-full max-w-md overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl sm:rounded-3xl">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                        <h3 class="text-lg font-semibold text-slate-900">Eliminar usuario</h3>
                    </div>
                    <div class="px-5 py-5 text-sm text-slate-600 sm:px-6">
                        <p>Se eliminará <strong x-text="borrar.name"></strong> (<span x-text="borrar.email"></span>).</p>
                        <p class="mt-2">No podrá volver a entrar. Sus accesos anteriores se conservan en el registro.</p>
                    </div>
                    <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                        <button type="button" x-on:click="borrar = null"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            Cancelar
                        </button>
                        <form method="POST" x-bind:action="'{{ url('users') }}/' + borrar.id">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(225,29,72,0.22)] transition hover:from-rose-500 hover:to-red-500">
                                <i class="mgc_delete_line"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection
