{{-- Mensajes de éxito / error --}}
            @if (session('success') || session('error'))
                <script>
                    const notyf = new Notyf({
                        duration: 4000,
                        dismissible: true,
                        position: {
                            x: 'right',
                            y: 'top',
                        },
                    });

                    @if (session('success'))
                        notyf.success(@js(e(session('success'))));
                    @endif

                    @if (session('error'))
                        notyf.error(@js(e(session('error'))));
                    @endif
                </script>
            @endif
