<div class="app-menu">

    <!-- Sidenav Brand Logo -->
    <a href="{{ route('any', 'index') }}" class="logo-box">
        <div class="logo-light">
            <img src="/images/logo-light.png" class="h-16" alt="Light logo">
            <img src="/images/logo-sm.png" class="logo-sm" alt="Small logo">
        </div>

        <div class="logo-dark">
            <img src="/images/logo-dark.png" class="h-16" alt="Dark logo">
            <img src="/images/logo-sm.png" class="logo-sm" alt="Small logo">
        </div>
    </a>

    <!-- Toggle Button -->
    <button id="button-hover-toggle" class="absolute top-5 end-2 rounded-full p-1.5">
        <span class="sr-only">Menu Toggle Button</span>
        <i class="mgc_round_line text-xl"></i>
    </button>

    <!-- Menu -->
    <div class="srcollbar" data-simplebar>
        <ul class="menu" data-fc-type="accordion">

            <li class="menu-title">Menu</li>

            <!-- Mi Unidad -->
            <li class="menu-item">
                <a href="{{ route('empresa.index') }}" class="menu-link">
                    <span class="menu-icon">
                        <i class="mgc_building_4_line"></i>
                    </span>
                    <span class="menu-text">Mi unidad</span>
                </a>
            </li>

            <!-- Obras -->
            <li class="menu-item">
                <a href="{{ route('unidad') }}" class="menu-link">
                    <span class="menu-icon">
                        <i class="mgc_greatwall_line"></i>
                    </span>
                    <span class="menu-text">Obras</span>
                </a>
            </li>

            @if (Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin')

                <!-- Alta Obras -->
                <li class="menu-item">
                    <a href="{{ route('obras.create') }}" class="menu-link">
                        <span class="menu-icon">
                            <i class="mgc_building_2_line"></i>
                        </span>
                        <span class="menu-text">Alta Obras</span>
                    </a>
                </li>

                <!-- Proveedores -->
                <li class="menu-item">
                    <a href="{{ route('proveedores') }}" class="menu-link">
                        <span class="menu-icon">
                            <i class="mgc_truck_line"></i>
                        </span>
                        <span class="menu-text">Proveedores</span>
                    </a>
                </li>

                <!-- Clientes -->
                <li class="menu-item">
                    <a href="{{ route('clientes') }}" class="menu-link">
                        <span class="menu-icon">
                            <i class="mgc_user_follow_line"></i>
                        </span>
                        <span class="menu-text">Clientes</span>
                    </a>
                </li>

                <li class="menu-item">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <span class="menu-icon">
                            <i class="mgc_user_3_line"></i>
                        </span>
                        <span class="menu-text">Usuarios</span>
                    </a>
                </li>

                <!-- Usuarios (Collapse original Frost) -->
                {{-- <li class="menu-item">
                    <a href="javascript:void(0);" data-fc-type="collapse" class="menu-link">
                        <span class="menu-icon">
                            <i class="mgc_user_3_line"></i>
                        </span>
                        <span class="menu-text">Usuarios</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="sub-menu">
                        <li class="menu-item">
                            <a href="{{ route('users.index') }}" class="menu-link">
                                <span class="menu-text">Users</span>
                            </a>
                        </li>

                        @if (Auth::user()->role === 'super_admin')
                            <li class="menu-item">
                                <a href="{{ route('login.logs') }}" class="menu-link">
                                    <span class="menu-text">Registros Users</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li> --}}

            @endif

        </ul>
    </div>
</div>
