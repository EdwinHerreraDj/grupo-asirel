<!DOCTYPE html>
<html lang="es">

<head>
    @include('layouts.shared/title-meta', ['title' => 'Acceso'])

    @include('layouts.shared/head-css')
</head>

<body class="bg-white dark:bg-slate-900">

    @php
        // El logo del panel (Configuración → Apariencia); el de la empresa es
        // solo para los PDFs.
        $logo = $panel?->logo_url ?? asset('images/logo-alminares-500x500.png');
    @endphp

    <div class="flex min-h-screen w-full">

        {{-- ================== PANEL IZQUIERDO (marca) ================== --}}
        <div class="relative hidden w-1/2 overflow-hidden bg-slate-950 lg:flex lg:flex-col xl:w-[55%]">

            {{-- Resplandor de fondo --}}
            <div class="pointer-events-none absolute inset-0"
                style="background:
                    radial-gradient(60% 55% at 78% 18%, rgba(48,115,241,.45) 0%, rgba(48,115,241,0) 60%),
                    radial-gradient(55% 45% at 15% 92%, rgba(14,165,233,.30) 0%, rgba(14,165,233,0) 60%);">
            </div>

            <div class="relative flex h-full flex-col justify-between px-10 py-10 xl:px-14">

                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#8fb4fa]">
                    Gestión de obras
                </p>

                <div class="my-8">
                    {{-- Ilustración: grúa torre y edificio en construcción --}}
                    <svg viewBox="0 0 420 340" class="h-auto w-full max-w-[420px]" role="img"
                        aria-label="Grúa torre junto a un edificio en construcción">
                        <defs>
                            <linearGradient id="obraCielo" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1e293b" stop-opacity=".55" />
                                <stop offset="100%" stop-color="#0f172a" stop-opacity=".15" />
                            </linearGradient>
                        </defs>

                        {{-- Lienzo --}}
                        <rect x="8" y="8" width="404" height="324" rx="22" fill="url(#obraCielo)" stroke="#334155"
                            stroke-opacity=".7" />

                        {{-- Edificio: plantas terminadas --}}
                        <g>
                            @foreach ([258, 224, 190] as $y)
                                <rect x="130" y="{{ $y }}" width="180" height="34" rx="3" fill="#1e293b"
                                    stroke="#475569" />
                                @foreach ([142, 186, 230, 274] as $i => $x)
                                    <rect x="{{ $x }}" y="{{ $y + 8 }}" width="28" height="18" rx="2"
                                        fill="{{ $loop->index % 2 ? '#3073F1' : '#5b8def' }}" fill-opacity=".85" />
                                @endforeach
                            @endforeach
                        </g>

                        {{-- Planta en ejecución --}}
                        <rect x="130" y="156" width="180" height="34" rx="3" fill="#0f172a" stroke="#E2A907"
                            stroke-opacity=".9" />
                        <rect x="142" y="164" width="28" height="18" rx="2" fill="#E2A907" fill-opacity=".85" />
                        <rect x="186" y="164" width="28" height="18" rx="2" fill="#E2A907" fill-opacity=".45" />
                        <rect x="230" y="164" width="28" height="18" rx="2" fill="none" stroke="#E2A907"
                            stroke-opacity=".5" />
                        <rect x="274" y="164" width="28" height="18" rx="2" fill="none" stroke="#E2A907"
                            stroke-opacity=".5" />

                        {{-- Planta por levantar: solo estructura --}}
                        <g stroke="#64748b" stroke-opacity=".8" stroke-dasharray="5 5">
                            <rect x="130" y="122" width="180" height="34" rx="3" fill="none" />
                        </g>
                        <g fill="#475569">
                            <rect x="134" y="122" width="7" height="34" rx="2" />
                            <rect x="215" y="122" width="7" height="34" rx="2" />
                            <rect x="299" y="122" width="7" height="34" rx="2" />
                        </g>

                        {{-- Grúa torre --}}
                        <g stroke="#94a3b8" stroke-width="3" stroke-linecap="round" fill="none">
                            {{-- Mástil --}}
                            <path d="M64 292V96M88 292V96" />
                            <path d="M64 268l24-26M88 268l-24-26M64 216l24-26M88 216l-24-26M64 164l24-26M88 164l-24-26" />
                            {{-- Punta y tirantes --}}
                            <path d="M76 96V60" />
                            <path d="M76 60 42 88M76 60l178 28" />
                            {{-- Pluma y contrapluma --}}
                            <path d="M34 88h300" stroke-width="5" />
                            <path d="M112 88l24 12M160 88l24 12M208 88l24 12M256 88l24 12" stroke-width="2"
                                stroke-opacity=".6" />
                            {{-- Cable y gancho --}}
                            <path d="M262 92v72" stroke-width="2" />
                        </g>
                        <rect x="24" y="76" width="26" height="24" rx="4" fill="#475569" />
                        <rect x="72" y="100" width="34" height="22" rx="4" fill="#475569" />
                        <rect x="238" y="164" width="48" height="14" rx="3" fill="#E2A907" />

                        {{-- Suelo y acopio de material --}}
                        <path d="M28 292h364" stroke="#475569" stroke-width="3" stroke-linecap="round" />
                        <g fill="#334155">
                            <rect x="330" y="274" width="52" height="8" rx="2" />
                            <rect x="336" y="264" width="40" height="8" rx="2" />
                        </g>
                        <g fill="#1CB454" fill-opacity=".85">
                            <rect x="340" y="254" width="32" height="6" rx="2" />
                        </g>
                    </svg>
                </div>

                <div>
                    <h1 class="text-3xl font-semibold leading-tight tracking-tight text-white xl:text-4xl">
                        Cada obra,
                        <span class="block text-[#6ea8ff]">bajo control.</span>
                    </h1>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-300">
                        Presupuestos, certificaciones, gastos y facturación de todas tus obras en un solo lugar.
                    </p>

                    <ul class="mt-8 space-y-5">
                        @foreach ([['mgc_building_2_line', 'Obras y presupuestos', 'Capítulos, partidas y coste teórico al día.'], ['mgc_task_2_line', 'Certificaciones y facturas', 'Certifica, factura y controla los cobros.'], ['mgc_group_line', 'Gastos y personal', 'Materiales, subcontratas, alquileres y recursos humanos.']] as [$icono, $titulo, $texto])
                            <li class="flex items-start gap-4">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-[#8fb4fa] ring-1 ring-white/10">
                                    <i class="{{ $icono }} text-lg"></i>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-white">{{ $titulo }}</span>
                                    <span class="block text-sm text-slate-400">{{ $texto }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <p class="mt-10 text-xs text-slate-500">Alminares &copy; {{ date('Y') }}</p>
            </div>
        </div>

        {{-- ================== PANEL DERECHO (formulario) ================== --}}
        <div class="flex w-full flex-col justify-center px-6 py-10 sm:px-10 lg:w-1/2 xl:w-[45%]">
            <div class="mx-auto w-full max-w-sm">

                <a href="{{ route('home') }}" class="mb-10 block">
                    <img src="{{ $logo }}" alt="{{ $empresa?->nombre ?? 'Logo' }}"
                        class="mx-auto h-24 w-auto object-contain">
                </a>

                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">Bienvenido de nuevo</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inicia sesión para continuar</p>

                {{-- Error de credenciales --}}
                @if ($errors->has('login'))
                    <div
                        class="mt-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                        <i class="mgc_warning_line text-lg"></i>
                        <div class="text-sm">
                            <p class="font-semibold">No se pudo iniciar sesión</p>
                            <p>{{ $errors->first('login') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Sesión caducada (redirigido desde la app) --}}
                @if (request('sesion') === 'caducada' && !$errors->any())
                    <div
                        class="mt-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300">
                        <i class="mgc_time_line text-lg"></i>
                        <div class="text-sm">
                            <p class="font-semibold">Sesión caducada</p>
                            <p>Tu sesión ha caducado. Vuelve a iniciar sesión.</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Correo electrónico
                        </label>
                        <div class="relative">
                            <i
                                class="mgc_mail_line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                autofocus autocomplete="username" placeholder="nombre@empresa.com"
                                class="w-full rounded-xl border-slate-300 py-3 pl-10 pr-3 text-sm shadow-sm focus:border-primary focus:ring-primary dark:border-slate-600 dark:bg-slate-800 dark:text-white
                                    @error('email') border-red-400 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div>
                        <label for="password"
                            class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Contraseña
                        </label>
                        <div class="relative">
                            <i
                                class="mgc_lock_line pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" id="password" name="password" required
                                autocomplete="current-password" placeholder="••••••••"
                                class="w-full rounded-xl border-slate-300 py-3 pl-10 pr-11 text-sm shadow-sm focus:border-primary focus:ring-primary dark:border-slate-600 dark:bg-slate-800 dark:text-white
                                    @error('password') border-red-400 @enderror">
                            <button type="button" id="verContrasena" aria-label="Mostrar la contraseña"
                                class="absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-700">
                                <i class="mgc_eye_line" id="iconoContrasena"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Recordar sesión --}}
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                            class="rounded border-slate-300 text-primary focus:ring-primary dark:border-slate-600">
                        Recordar sesión
                    </label>

                    <button type="submit"
                        class="w-full rounded-xl bg-primary py-3 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:bg-[#245ec9] focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                        Iniciar sesión
                    </button>
                </form>

                <p class="mt-8 text-center text-xs text-slate-400">
                    Acceso restringido al personal autorizado
                </p>
                <p class="mt-1 text-center text-xs text-slate-400">
                    Soporte técnico <span class="font-semibold text-primary">Alminares S.L.</span>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Mostrar u ocultar la contraseña.
        document.getElementById('verContrasena')?.addEventListener('click', function() {
            const campo = document.getElementById('password');
            const icono = document.getElementById('iconoContrasena');
            const oculta = campo.type === 'password';

            campo.type = oculta ? 'text' : 'password';
            icono.className = oculta ? 'mgc_eye_close_line' : 'mgc_eye_line';
            this.setAttribute('aria-label', oculta ? 'Ocultar la contraseña' : 'Mostrar la contraseña');
            campo.focus();
        });
    </script>

</body>

</html>
