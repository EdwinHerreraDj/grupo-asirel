{{-- Mensaje de éxito --}}
            @if (session('success'))
                <script>
                    const notyf = new Notyf({
                        duration: 4000,
                        dismissible: true,
                        position: {
                            x: 'right',
                            y: 'top',
                        },
                    });

                    // Mostrar mensaje de éxito
                    notyf.success('{{ session('success') }}');
                </script>
            @endif