<!DOCTYPE html>
<html lang="es" data-sidenav-view="{{ $sidenav ?? 'default' }}">

<head>
    @include('layouts.shared/title-meta', ['title' => $title])
    @yield('css')
    @include('layouts.shared/head-css')

    <link href="https://cdn.datatables.net/v/dt/dt-2.1.8/datatables.min.css" rel="stylesheet">
    <!-- CSS de Notyf -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf/notyf.min.css">
    <!-- JS de Notyf -->
    <script src="https://cdn.jsdelivr.net/npm/notyf/notyf.min.js"></script>
    <!-- CSS de SweetAlert2 -->
    @vite(['node_modules/sweetalert2/dist/sweetalert2.min.css'])

    @livewireStyles

</head>

<body>

    <div class="flex wrapper">

        @include('layouts.shared/sidebar')

        <div class="page-content">

            @include('layouts.shared/topbar')

            <main class="flex-grow p-6">

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
    <script src="https://cdn.datatables.net/v/dt/dt-2.1.8/datatables.min.js"></script>
    @vite(['resources/js/pages/tables-datatable.js', 'resources/js/app.js', 'resources/js/react/app.jsx'])
    
    @livewireScripts

    <script>
        // Crear instancia global de Notyf
        const notyf = new Notyf({
            duration: 3000,
            position: {
                x: 'right',
                y: 'top'
            },
            dismissible: true,
        });

        // Escuchar los eventos Livewire tipo "toast"
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

        // Mantener sesión viva mientras la pestaña esté abierta
        setInterval(() => {
            fetch("{{ route('ping') }}", {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        }, 5 * 60 * 1000); // cada 5 minutos
    </script>

    <script>
        document.addEventListener('livewire:load', () => {
            Livewire.onError((status) => {
                if (status === 419) {
                    alert('Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.');
                    window.location.href = "{{ route('login') }}";
                    return false;
                }
            });
        });
    </script>



</body>

</html>
