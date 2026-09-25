@extends('layouts.vertical', ['title' => 'Mi perfil', 'sub_title' => 'Cuenta', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('content')
    @php
        $rotulo = match ($usuario->role) {
            'super_admin' => 'Súper administrador',
            'admin' => 'Administrador',
            default => 'Usuario',
        };
        $input = 'w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary';
        $etiqueta = 'mb-1.5 block text-sm font-medium text-slate-700';
    @endphp

    <div class="space-y-4">

        {{-- CABECERA + DATOS (ocupa el ancho disponible) --}}
        <div class="grid grid-cols-1 gap-4 2xl:grid-cols-3">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)] 2xl:col-span-2">
                <div class="relative bg-slate-950 px-5 py-6 sm:px-6">
                    <div class="pointer-events-none absolute inset-0 overflow-hidden"
                        style="background:
                            radial-gradient(70% 90% at 88% 8%, rgba(48,115,241,.45) 0%, rgba(48,115,241,0) 62%),
                            radial-gradient(60% 80% at 8% 100%, rgba(14,165,233,.28) 0%, rgba(14,165,233,0) 60%);">
                    </div>

                    <div class="relative flex flex-col items-center gap-4 text-center sm:flex-row sm:text-left">
                        @if ($usuario->avatar_url)
                            <img src="{{ $usuario->avatar_url }}" alt="{{ $usuario->name }}"
                                class="h-20 w-20 shrink-0 rounded-2xl object-cover ring-2 ring-white/20">
                        @else
                            <span
                                class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-2xl font-bold text-white ring-2 ring-white/20">
                                {{ $usuario->iniciales }}
                            </span>
                        @endif

                        <div class="min-w-0">
                            <h2 class="break-words text-2xl font-semibold tracking-tight text-white">{{ $usuario->name }}</h2>
                            <p class="mt-0.5 break-all text-sm text-slate-300">{{ $usuario->email }}</p>
                            <span
                                class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-xs font-semibold text-slate-100 ring-1 ring-white/20">
                                <i class="mgc_user_3_line"></i> {{ $rotulo }}
                            </span>
                        </div>
                    </div>
                </div>

                @if (session('perfil_ok'))
                    <div class="mx-5 mt-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 sm:mx-6">
                        <i class="mgc_check_circle_line text-lg"></i>
                        <p class="font-semibold">{{ session('perfil_ok') }}</p>
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

                {{-- DATOS Y FOTO --}}
                <form method="POST" action="{{ route('perfil.actualizar') }}" enctype="multipart/form-data"
                    class="px-5 py-5 sm:px-6">
                    @csrf

                    <h3 class="flex items-center gap-2 font-semibold text-slate-900">
                        <i class="mgc_user_3_line text-lg text-cyan-600"></i> Mis datos
                    </h3>

                    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <div class="space-y-4 lg:col-span-2">
                            <div>
                                <label for="name" class="{{ $etiqueta }}">Nombre <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $usuario->name) }}"
                                    maxlength="30" required class="{{ $input }}">
                            </div>
                            <div>
                                <label for="email" class="{{ $etiqueta }}">Correo electrónico <span class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}"
                                    required autocomplete="email" class="{{ $input }}">
                                <p class="mt-1 text-xs text-slate-500">Con este correo entras en la aplicación.</p>
                            </div>
                        </div>

                        {{-- Foto --}}
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-sm font-medium text-slate-700">Foto</p>
                            <div class="mt-3 flex items-center gap-3">
                                @if ($usuario->avatar_url)
                                    <img src="{{ $usuario->avatar_url }}" alt="Tu foto"
                                        class="h-16 w-16 rounded-xl object-cover ring-1 ring-slate-200">
                                @else
                                    <span class="flex h-16 w-16 items-center justify-center rounded-xl bg-slate-100 text-lg font-bold text-slate-500">
                                        {{ $usuario->iniciales }}
                                    </span>
                                @endif
                                <p class="text-xs text-slate-500">
                                    Cuadrada, 256 × 256 px.<br>PNG, JPG o WEBP · máx. 2 MB
                                </p>
                            </div>

                            <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp"
                                class="mt-3 block w-full cursor-pointer rounded-xl border border-slate-300 text-sm text-slate-600 file:mr-3 file:cursor-pointer file:rounded-l-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2.5 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200">

                            @if ($usuario->avatar)
                                <button type="submit" form="quitar-avatar"
                                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700">
                                    <i class="mgc_delete_line"></i> Quitar la foto
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:bg-[#245ec9]">
                            <i class="mgc_check_line"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            {{-- CONTRASEÑA --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)] 2xl:col-span-1">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <h3 class="flex items-center gap-2 font-semibold text-slate-900">
                        <i class="mgc_lock_line text-lg text-cyan-600"></i> Cambiar la contraseña
                    </h3>
                    <p class="mt-0.5 text-sm text-slate-500">
                        Al cambiarla se cierran las sesiones abiertas en otros navegadores.
                    </p>
                </div>

                <form method="POST" action="{{ route('perfil.contrasena') }}" class="px-5 py-5 sm:px-6">
                    @csrf

                    {{-- En pantallas anchas esta tarjeta va en una columna estrecha:
                         los campos se apilan. --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 2xl:grid-cols-1">
                        <div>
                            <label for="contrasena_actual" class="{{ $etiqueta }}">Contraseña actual <span class="text-red-500">*</span></label>
                            <input type="password" id="contrasena_actual" name="contrasena_actual" required
                                autocomplete="current-password" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="password" class="{{ $etiqueta }}">Nueva contraseña <span class="text-red-500">*</span></label>
                            <input type="password" id="password" name="password" required autocomplete="new-password"
                                class="{{ $input }}">
                            <p class="mt-1 text-xs text-slate-500">Mínimo 8 caracteres.</p>
                        </div>
                        <div>
                            <label for="password_confirmation" class="{{ $etiqueta }}">Repite la nueva <span class="text-red-500">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                autocomplete="new-password" class="{{ $input }}">
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <i class="mgc_lock_line"></i> Cambiar contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($usuario->avatar)
        <form id="quitar-avatar" method="POST" action="{{ route('perfil.avatar.eliminar') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
