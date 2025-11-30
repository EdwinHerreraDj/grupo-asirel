<!DOCTYPE html>
<html lang="es">

<head>
    @include('layouts.shared/title-meta', ['title' => 'Login'])

    @include('layouts.shared/head-css')
</head>

<body>

    <div class="bg-gradient-to-r from-rose-100 to-teal-100 dark:from-gray-700 dark:via-gray-900 dark:to-black">


        <div class="h-screen w-screen flex justify-center items-center">

            <div class="2xl:w-1/4 lg:w-1/3 md:w-1/2 w-full">
                <div class="w-full max-w-md">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

                        <!-- Logo -->
                        <div class="p-8">
                            <a href="{{ route('any', 'index') }}" class="block mb-10 text-center">
                                <img class="h-14 mx-auto block dark:hidden" src="/images/logo-dark.png" alt="">
                                <img class="h-14 mx-auto hidden dark:block" src="/images/logo-light.png" alt="">
                            </a>

                            <!-- Error -->
                            @if ($errors->has('login'))
                                <div
                                    class="mb-6 flex items-start gap-3 bg-red-50 border border-red-300 text-red-700 p-4 rounded-lg">
                                    <i class="mgc_warning_line text-xl"></i>
                                    <div>
                                        <p class="font-semibold text-sm">Error</p>
                                        <p class="text-sm">{{ $errors->first('login') }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Formulario -->
                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <!-- Email -->
                                <div class="mb-5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Email
                                    </label>
                                    <input type="email" name="email"
                                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-primary focus:border-primary"
                                        placeholder="Ingrese su email" value="{{ old('email') }}">
                                </div>

                                <!-- Password -->
                                <div class="mb-5">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Contraseña
                                    </label>
                                    <input type="password" name="password"
                                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-primary focus:border-primary"
                                        placeholder="Ingrese su contraseña">
                                </div>

                                <!-- Opciones -->
                                <div class="flex items-center justify-between mb-6">
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600">
                                        Recordar sesión
                                    </label>

                                    <a href="{{ route('second', ['auth', 'recoverpw']) }}"
                                        class="text-sm text-primary hover:underline">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                </div>

                                <!-- Botón -->
                                <button
                                    class="w-full py-3 rounded-xl bg-primary text-white font-semibold shadow hover:opacity-90 transition">
                                    Acceder
                                </button>
                            </form>

                            <!-- Footer -->
                            <p class="text-center mt-8 text-sm text-gray-500 dark:text-gray-400">
                                Soporte técnico ®
                                <a href="{{ route('register') }}" class="text-primary font-semibold">
                                    Alminares S.L
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>

</html>
