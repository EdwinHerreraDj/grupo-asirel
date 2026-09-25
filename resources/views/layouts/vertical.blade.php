<!DOCTYPE html>
<html lang="es" data-sidenav-view="{{ $sidenav ?? 'default' }}">

<head>
    @include('layouts.shared/title-meta', ['title' => $title])
    @yield('css')
    @include('layouts.shared/head-css')

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf/notyf.min.js"></script>


    @vite(['node_modules/sweetalert2/dist/sweetalert2.min.css'])

    @livewireStyles
</head>

<body>

    <div class="flex min-h-screen">

        @include('layouts.shared/sidebar')

        <div class="page-content flex flex-col flex-1 min-w-0">

            @include('layouts.shared/topbar')

            <main class="flex-1 overflow-auto p-6">

                @include('layouts.shared/page-title', [
                    'title' => $title,
                    'sub_title' => $sub_title,
                ])

                @yield('content')

            </main>

            @include('layouts.shared/footer')

        </div>

    </div>

    @include('layouts.shared/customizer')
    @include('layouts.shared/footer-scripts')

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/js/app.js', 'resources/js/react/app.jsx'])

    @livewireScripts

    <script>
        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top'
            },
            dismissible: true,
        });

        window.addEventListener('toast', (event) => {
            const {
                type,
                text
            } = event.detail;

            if (type === 'success') {
                notyf.success(text);
            } else if (type === 'error') {
                notyf.error(text);
            } else {
                notyf.open({
                    type: 'info',
                    message: text
                });
            }
        });

        // Sesión caducada: al login con aviso (sin dialogs nativos).
        window.irALoginPorSesionCaducada = () => {
            window.location.href = "{{ route('login') }}?sesion=caducada";
        };

        // Mantiene viva la sesión y detecta si ya ha caducado.
        setInterval(() => {
            fetch("{{ route('ping') }}", {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then((response) => {
                if (response.status === 401 || response.status === 419) {
                    window.irALoginPorSesionCaducada();
                }
            }).catch(() => {});
        }, 5 * 60 * 1000);
    </script>

    <script>
        // Livewire 3: peticiones que fallan por sesión caducada.
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    if (status === 419 || status === 401) {
                        preventDefault();
                        window.irALoginPorSesionCaducada();
                    }
                });
            });
        });
    </script>

    @stack('scripts')

</body>

</html>
