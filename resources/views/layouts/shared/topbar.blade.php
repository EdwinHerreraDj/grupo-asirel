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
            <button data-fc-type="dropdown" data-fc-placement="bottom-end" type="button" class="nav-link">
                <img src="/images/users/user-6.jpg" alt="user-image" class="rounded-full h-10 w-10 object-cover">
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
