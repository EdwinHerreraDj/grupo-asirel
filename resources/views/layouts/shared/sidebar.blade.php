<div class="app-menu">

    <!-- Sidenav Brand Logo -->
    <a href="{{ route('any', 'index') }}" class="logo-box">
        <div class="logo-light">
            <img src="/images/logo-light.png" class="logo-lg" alt="Light logo">
            <img src="/images/logo-sm.png" class="logo-sm" alt="Small logo">
        </div>

        <div class="logo-dark">
            <img src="/images/logo-dark.png" class="logo-lg" alt="Dark logo">
            <img src="/images/logo-sm.png" class="logo-sm" alt="Small logo">
        </div>
    </a>

    <!-- Menu -->
    <div class="scrollbar" data-simplebar>
        <ul class="menu" data-fc-type="accordion">

            <li class="menu-title">Menu</li>

            <li class="menu-item">
                <a href="{{ route('empresa.index') }}" class="menu-link">
                    <span class="menu-icon">
                        <i class="mgc_building_4_line"></i>
                    </span>
                    <span class="menu-text">Mi unidad</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('unidad') }}" class="menu-link">
                    <span class="menu-icon">
                        <i class="mgc_greatwall_line"></i>
                    </span>
                    <span class="menu-text">Obras</span>
                </a>
            </li>

            @if (Auth::user()->role === 'admin' || Auth::user()->role === 'super_admin')

                <li class="menu-item">
                    <a href="{{ route('unidad') }}" class="menu-link">
                        <span class="menu-icon">
                            <i class="mgc_building_2_line"></i>
                        </span>
                        <span class="menu-text">Alta Obras</span>
                    </a>
                </li>

                <li class="menu-item">
                    <a href="{{ route('proveedores') }}" class="menu-link">
                        <span class="menu-icon">
                            <i class="mgc_truck_line"></i>
                        </span>
                        <span class="menu-text">Proveedores</span>
                    </a>
                </li>

                <li class="menu-item">
                    <a href="{{ route('clientes') }}" class="menu-link">
                        <span class="menu-icon">
                            <i class="mgc_user_follow_line"></i>
                        </span>
                        <span class="menu-text">Clientes</span>
                    </a>
                </li>

                <!-- Submenu -->
                <li class="menu-item">
                    <a href="javascript:void(0);" data-fc-type="collapse" class="menu-link">
                        <span class="menu-icon">
                            <i class="mgc_user_3_line"></i>
                        </span>
                        <span class="menu-text">Usuarios</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="sub-menu hidden">
                        <li class="menu-item">
                            <a href="{{ route('users.index') }}" class="menu-link">
                                <span class="menu-text">Listado de usuarios</span>
                            </a>
                        </li>

                        @if (Auth::user()->role === 'super_admin')
                            <li class="menu-item">
                                <a href="{{ route('login.logs') }}" class="menu-link">
                                    <span class="menu-text">Registros de acceso</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

            @endif

        </ul>
    </div>

    <style>
        .menu>.menu-item {
            display: block;
            width: 100%;
        }

        .menu>.menu-item>.menu-link {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .sub-menu {
            width: 100%;
            padding-left: 2.5rem;
            margin-top: 0.25rem;
        }

        .sub-menu .menu-item {
            display: block;
            width: 100%;
        }

        .sub-menu .menu-link {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 0.5rem 0.75rem;
        }

        .menu-arrow {
            margin-left: auto;
        }
    </style>
</div>
