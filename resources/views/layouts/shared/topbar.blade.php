@php
    $userName = session('user_name') ?? auth()->user()?->name ?? 'Usuario';

    // Iniciales: primera letra del primer nombre + primera del segundo nombre.
    // Si solo hay una palabra, primeras 2 letras.
    $partes = preg_split('/\s+/', trim($userName));
    $userInitials = mb_strtoupper(mb_substr($partes[0] ?? 'U', 0, 1));
    if (count($partes) > 1 && !empty($partes[1])) {
        $userInitials .= mb_strtoupper(mb_substr($partes[1], 0, 1));
    } elseif (mb_strlen($partes[0] ?? '') > 1) {
        $userInitials .= mb_strtoupper(mb_substr($partes[0], 1, 1));
    }

    // Color determinístico por usuario (mismo nombre → mismo color)
    $avatarPalette = [
        'bg-red-500', 'bg-orange-500', 'bg-amber-500',
        'bg-lime-600', 'bg-emerald-500', 'bg-teal-500',
        'bg-cyan-600', 'bg-sky-600', 'bg-blue-600',
        'bg-indigo-500', 'bg-violet-500', 'bg-purple-500',
        'bg-fuchsia-500', 'bg-pink-500', 'bg-rose-500',
    ];
    $userAvatarBg = $avatarPalette[abs(crc32($userName)) % count($avatarPalette)];
@endphp
<!-- Topbar Start -->
<header class="app-header flex items-center justify-between px-4 py-2">

    <!-- IZQUIERDA -->
    <div class="flex items-center gap-3 min-w-0">
        <!-- Sidenav Menu Toggle Button -->
        <button id="button-toggle-menu" class="nav-link p-2 shrink-0">
            <span class="sr-only">Menu Toggle Button</span>
            <span class="flex items-center justify-center h-6 w-6">
                <i class="mgc_menu_line text-xl"></i>
            </span>
        </button>

        <!-- Topbar Brand Logo -->
        <a href="{{ route('any', 'index') }}" class="logo-box shrink-0">
            <!-- Light Brand Logo -->
            <div class="logo-light">
                <img src="/images/logo-light.png" class="logo-lg h-6" alt="Light logo">
                <img src="/images/logo-sm.png" class="logo-sm" alt="Small logo">
            </div>

            <!-- Dark Brand Logo -->
            <div class="logo-dark">
                <img src="/images/logo-dark.png" class="logo-lg h-6" alt="Dark logo">
                <img src="/images/logo-sm.png" class="logo-sm" alt="Small logo">
            </div>
        </a>
    </div>

    <!-- DERECHA -->
    <div class="flex items-center gap-2 md:gap-3 shrink-0">
        <!-- Fullscreen Toggle Button -->
        <div class="hidden md:flex">
            <button data-toggle="fullscreen" type="button" class="nav-link p-2">
                <span class="sr-only">Fullscreen Mode</span>
                <span class="flex items-center justify-center h-6 w-6">
                    <i class="mgc_fullscreen_line text-2xl"></i>
                </span>
            </button>
        </div>

        <!-- Light/Dark Toggle Button -->
        <button id="light-dark-mode" type="button" class="nav-link p-2">
            <span class="sr-only">Light/Dark Mode</span>
            <span class="flex items-center justify-center h-6 w-6">
                <i class="mgc_moon_line text-2xl"></i>
            </span>
        </button>

        <!-- Nombre usuario -->
        <div class="hidden sm:block text-sm font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">
            {{ session('user_name') }}
        </div>

        <!-- Profile Dropdown Button -->
        <div class="relative">
            <button data-fc-type="dropdown" data-fc-placement="bottom-end" type="button" class="nav-link"
                aria-label="Menú de usuario">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full {{ $userAvatarBg }} text-white text-sm font-semibold shadow-sm ring-2 ring-white dark:ring-gray-800 select-none"
                    title="{{ $userName }}">
                    {{ $userInitials ?: 'U' }}
                </span>
            </button>

            <div
                class="fc-dropdown fc-dropdown-open:opacity-100 hidden opacity-0 w-44 z-50 transition-[margin,opacity] duration-300 mt-2 bg-white shadow-lg border rounded-lg p-2 border-gray-200 dark:border-gray-700 dark:bg-gray-800 right-0">
                <a class="flex items-center py-2 px-3 rounded-md text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                    href="{{ route('second', ['auth', 'login']) }}">
                    <i class="mgc_lock_line me-2"></i>
                    <span>Lock Screen</span>
                </a>

                <hr class="my-2 -mx-2 border-gray-200 dark:border-gray-700">

                <a class="flex items-center py-2 px-3 rounded-md text-sm text-gray-800 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                    href="{{ route('logout_action') }}">
                    <i class="mgc_exit_line me-2"></i>
                    <span>Log Out</span>
                </a>
            </div>
        </div>
    </div>

</header>
<!-- Topbar End -->
