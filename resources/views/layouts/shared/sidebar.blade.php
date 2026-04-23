<div class="app-menu">

    {{-- LOGO --}}
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

    @php
        $user = auth()->user();
        $isAdmin = $user && in_array($user->role, ['admin', 'super_admin']);
        $isSuperAdmin = $user && $user->role === 'super_admin';

        $gastosEmpresaActive = request()->routeIs('empresa.gastosEmpresa')
            || request()->routeIs('categorias.empresa.index');

        $facturacionActive = request()->routeIs('facturas-recibidas.global')
            || request()->routeIs('empresa.facturas-ventas*')
            || request()->routeIs('empresa.facturas-series');

        $planificacionActive = request()->routeIs('coste-teorico.global')
            || request()->routeIs('presupuesto-venta.global');

        $usuariosActive = request()->routeIs('users.*')
            || request()->routeIs('login.logs');

        // Clases base reutilizables
        $linkBase = 'sidebar-link group flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium transition';
        $linkIdle = 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';
        $linkActive = 'active bg-cyan-50 text-cyan-700 shadow-sm';

        $subBase = 'sidebar-sublink flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition';
        $subIdle = 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';
        $subActive = 'active bg-cyan-50 text-cyan-700 font-semibold';

        $seccion = 'sidebar-section-title px-3 pt-5 pb-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400';
    @endphp

    {{-- MENÚ --}}
    <div class="scrollbar" data-simplebar>
        <nav class="sidebar-nav px-3 py-3">

            {{-- ================ OPERATIVA ================ --}}
            <p class="{{ $seccion }}">Operativa</p>
            <ul class="space-y-0.5">
                <li>
                    <a href="{{ route('unidad') }}"
                        class="{{ $linkBase }} {{ request()->routeIs('unidad') ? $linkActive : $linkIdle }}">
                        <i class="mgc_greatwall_line text-lg shrink-0"></i>
                        <span class="sidebar-text">Obras</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('tareas.index') }}"
                        class="{{ $linkBase }} {{ request()->routeIs('tareas.index') ? $linkActive : $linkIdle }}">
                        <i class="mgc_task_2_line text-lg shrink-0"></i>
                        <span class="sidebar-text">Tareas</span>
                    </a>
                </li>

                {{-- Submenú: Planificación (coste teórico + presupuesto venta) --}}
                <li x-data="{ open: @json($planificacionActive) }">
                    <button type="button" x-on:click="open = !open"
                        :class="open ? 'bg-slate-100 text-slate-900' : ''"
                        class="{{ $linkBase }} {{ $planificacionActive ? 'text-slate-900' : $linkIdle }}">
                        <i class="mgc_chart_line_line text-lg shrink-0"></i>
                        <span class="sidebar-text flex-1 text-left">Planificación</span>
                        <i class="sidebar-chevron mgc_down_line text-sm transition-transform"
                            :class="open && 'rotate-180'"></i>
                    </button>

                    <ul x-show="open" x-transition
                        class="sidebar-submenu mt-1 ml-2 space-y-0.5 border-l border-slate-200 pl-4"
                        style="display: {{ $planificacionActive ? 'block' : 'none' }};">
                        <li>
                            <a href="{{ route('coste-teorico.global') }}"
                                class="{{ $subBase }} {{ request()->routeIs('coste-teorico.global') ? $subActive : $subIdle }}">
                                <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                Coste teórico
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('presupuesto-venta.global') }}"
                                class="{{ $subBase }} {{ request()->routeIs('presupuesto-venta.global') ? $subActive : $subIdle }}">
                                <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                Presupuesto de venta
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Submenú: Facturación (recibidas + ventas + series) --}}
                <li x-data="{ open: @json($facturacionActive) }">
                    <button type="button" x-on:click="open = !open"
                        :class="open ? 'bg-slate-100 text-slate-900' : ''"
                        class="{{ $linkBase }} {{ $facturacionActive ? 'text-slate-900' : $linkIdle }}">
                        <i class="mgc_bill_line text-lg shrink-0"></i>
                        <span class="sidebar-text flex-1 text-left">Facturación</span>
                        <i class="sidebar-chevron mgc_down_line text-sm transition-transform"
                            :class="open && 'rotate-180'"></i>
                    </button>

                    <ul x-show="open" x-transition
                        class="sidebar-submenu mt-1 ml-2 space-y-0.5 border-l border-slate-200 pl-4"
                        style="display: {{ $facturacionActive ? 'block' : 'none' }};">
                        <li>
                            <a href="{{ route('facturas-recibidas.global') }}"
                                class="{{ $subBase }} {{ request()->routeIs('facturas-recibidas.global') ? $subActive : $subIdle }}">
                                <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                Facturas recibidas
                            </a>
                        </li>
                        @if ($isAdmin)
                            <li>
                                <a href="{{ route('empresa.facturas-ventas') }}"
                                    class="{{ $subBase }} {{ request()->routeIs('empresa.facturas-ventas*') ? $subActive : $subIdle }}">
                                    <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                    Facturas de venta
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('empresa.facturas-series') }}"
                                    class="{{ $subBase }} {{ request()->routeIs('empresa.facturas-series') ? $subActive : $subIdle }}">
                                    <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                    Series de facturación
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            </ul>

            {{-- ================ DIRECTORIO ================ --}}
            @if ($isAdmin)
                <p class="{{ $seccion }}">Directorio</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('clientes') }}"
                            class="{{ $linkBase }} {{ request()->routeIs('clientes') ? $linkActive : $linkIdle }}">
                            <i class="mgc_user_follow_line text-lg shrink-0"></i>
                            <span class="sidebar-text">Clientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('proveedores') }}"
                            class="{{ $linkBase }} {{ request()->routeIs('proveedores') ? $linkActive : $linkIdle }}">
                            <i class="mgc_truck_line text-lg shrink-0"></i>
                            <span class="sidebar-text">Proveedores</span>
                        </a>
                    </li>
                </ul>
            @endif

            {{-- ================ EMPRESA ================ --}}
            <p class="{{ $seccion }}">Empresa</p>
            <ul class="space-y-0.5">
                <li>
                    <a href="{{ route('empresa.index') }}"
                        class="{{ $linkBase }} {{ request()->routeIs('empresa.index') ? $linkActive : $linkIdle }}">
                        <i class="mgc_building_4_line text-lg shrink-0"></i>
                        <span class="sidebar-text">Mi unidad</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('empresa.driveApp') }}"
                        class="{{ $linkBase }} {{ request()->routeIs('empresa.driveApp') ? $linkActive : $linkIdle }}">
                        <i class="mgc_folder_open_line text-lg shrink-0"></i>
                        <span class="sidebar-text">Drive</span>
                    </a>
                </li>

                @if ($isAdmin)
                    {{-- Submen\u00fa: Gastos empresa --}}
                    <li x-data="{ open: @json($gastosEmpresaActive) }">
                        <button type="button" x-on:click="open = !open"
                            :class="open ? 'bg-slate-100 text-slate-900' : ''"
                            class="{{ $linkBase }} {{ $gastosEmpresaActive ? 'text-slate-900' : $linkIdle }}">
                            <i class="mgc_bank_card_line text-lg shrink-0"></i>
                            <span class="sidebar-text flex-1 text-left">Gastos empresa</span>
                            <i class="sidebar-chevron mgc_down_line text-sm transition-transform"
                                :class="open && 'rotate-180'"></i>
                        </button>

                        <ul x-show="open" x-transition
                            class="sidebar-submenu mt-1 ml-2 space-y-0.5 border-l border-slate-200 pl-4"
                            style="display: {{ $gastosEmpresaActive ? 'block' : 'none' }};">
                            <li>
                                <a href="{{ route('empresa.gastosEmpresa') }}"
                                    class="{{ $subBase }} {{ request()->routeIs('empresa.gastosEmpresa') ? $subActive : $subIdle }}">
                                    <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                    Gastos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('categorias.empresa.index') }}"
                                    class="{{ $subBase }} {{ request()->routeIs('categorias.empresa.index') ? $subActive : $subIdle }}">
                                    <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                    Categorías
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>

            {{-- ================ INFORMES ================ --}}
            @if ($isAdmin)
                <p class="{{ $seccion }}">Informes</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('informes.index') }}"
                            class="{{ $linkBase }} {{ request()->routeIs('informes.*') ? $linkActive : $linkIdle }}">
                            <i class="mgc_chart_bar_line text-lg shrink-0"></i>
                            <span class="sidebar-text">Informes globales</span>
                        </a>
                    </li>
                </ul>
            @endif

            {{-- ================ CONFIGURACIÓN ================ --}}
            @if ($isAdmin)
                <p class="{{ $seccion }}">Configuración</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('empresa.configuracion') }}"
                            class="{{ $linkBase }} {{ request()->routeIs('empresa.configuracion') ? $linkActive : $linkIdle }}">
                            <i class="mgc_building_2_line text-lg shrink-0"></i>
                            <span class="sidebar-text">Empresa</span>
                        </a>
                    </li>
                </ul>
            @endif

            {{-- ================ ADMINISTRACIÓN ================ --}}
            @if ($isAdmin)
                <p class="{{ $seccion }}">Administración</p>
                <ul class="space-y-0.5">
                    <li x-data="{ open: @json($usuariosActive) }">
                        <button type="button" x-on:click="open = !open"
                            :class="open ? 'bg-slate-100 text-slate-900' : ''"
                            class="{{ $linkBase }} {{ $usuariosActive ? 'text-slate-900' : $linkIdle }}">
                            <i class="mgc_user_3_line text-lg shrink-0"></i>
                            <span class="sidebar-text flex-1 text-left">Usuarios</span>
                            <i class="sidebar-chevron mgc_down_line text-sm transition-transform"
                                :class="open && 'rotate-180'"></i>
                        </button>

                        <ul x-show="open" x-transition
                            class="sidebar-submenu mt-1 ml-2 space-y-0.5 border-l border-slate-200 pl-4"
                            style="display: {{ $usuariosActive ? 'block' : 'none' }};">
                            <li>
                                <a href="{{ route('users.index') }}"
                                    class="{{ $subBase }} {{ request()->routeIs('users.*') ? $subActive : $subIdle }}">
                                    <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                    Listado de usuarios
                                </a>
                            </li>
                            @if ($isSuperAdmin)
                                <li>
                                    <a href="{{ route('login.logs') }}"
                                        class="{{ $subBase }} {{ request()->routeIs('login.logs') ? $subActive : $subIdle }}">
                                        <span class="h-1 w-1 rounded-full bg-current opacity-60"></span>
                                        Registros de acceso
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                </ul>
            @endif

            <div class="pb-6"></div>
        </nav>
    </div>

    {{-- =======================================================
         MODO COLAPSADO (data-sidenav-view="sm")
         Cuando el usuario pulsa el bot\u00f3n hamburguesa en desktop, el
         theme pone `data-sidenav-view="sm"` en <html>. Aqu\u00ed ocultamos
         los textos, t\u00edtulos de secci\u00f3n, chevrons y submen\u00fas, y
         centramos los iconos para que no se vean solapados.
         ======================================================= --}}
    <style>
        html[data-sidenav-view="sm"] .app-menu .sidebar-section-title,
        html[data-sidenav-view="sm"] .app-menu .sidebar-text,
        html[data-sidenav-view="sm"] .app-menu .sidebar-chevron {
            display: none !important;
        }

        html[data-sidenav-view="sm"] .app-menu .sidebar-submenu {
            display: none !important;
        }

        html[data-sidenav-view="sm"] .app-menu .sidebar-link {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            gap: 0;
        }

        html[data-sidenav-view="sm"] .app-menu .sidebar-nav {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        html[data-sidenav-view="sm"] .app-menu .sidebar-link > i {
            margin: 0;
        }
    </style>
</div>
