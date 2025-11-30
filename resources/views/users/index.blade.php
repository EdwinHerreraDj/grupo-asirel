@extends('layouts.vertical', ['title' => 'Users', 'sub_title' => 'Paginas', 'mode' => $mode ?? '', 'demo' => $demo ?? ''])

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    {{-- Boton para crear un suario --}}
    <button onclick="openModal('addUserModal')"
        class="flex items-center mb-4 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-opacity-75">
        <!-- Icono -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Agregar Usuario
    </button>

    {{-- Mensaje de éxito --}}
    @include('notifications.notyf')

    {{-- Mensaje informativo --}}
    <div id="alert-usuario" class="relative p-4 bg-amber-50 border-l-4 border-amber-500 text-amber-800 rounded-md mb-4">
        <button onclick="document.getElementById('alert-usuario').remove()"
            class="absolute top-2 right-2 text-xl font-bold text-amber-700 hover:text-red-600" aria-label="Cerrar">
            &times;
        </button>

        <h2 class="text-lg font-semibold mb-2">Alta de nuevo usuario</h2>
        <p>
            En esta página puedes <strong>registrar un nuevo usuario</strong> para el sistema. Asegúrate de rellenar
            correctamente
            todos los campos requeridos, especialmente el <strong>rol</strong> del usuario (administrador o empleado).
        </p>
        <p class="mt-2">
            Si el usuario va a fichar en obras, deberá estar vinculado a una empresa y tendrá acceso a su zona de control de
            presencia.
            En cambio, si se trata de un administrador, podrá gestionar empleados, obras e informes generales.
        </p>
    </div>



    {{-- Tabla de usuarios --}}
    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12">
            <div class="card p-6">
                <h2 class="text-xl font-semibold mb-4">Usuarios</h2>
                <div class="overflow-x-auto">
                    <table id="users-table" class="display">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha de creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role ?? 'N/A' }}</td>
                                    <td>{{ $user->created_at }}</td>
                                    <td>
                                        <div class="actions">
                                            <button
                                                onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role }}')"
                                                title="Editar usuario"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-full 
                                                bg-blue-100 text-blue-700 border border-blue-200 
                                                hover:bg-blue-200 hover:border-blue-300 transition-all duration-200 shadow-sm">
                                                <i class="mgc_edit_2_line text-lg"></i>
                                            </button>


                                            <button
                                                class="delete-user inline-flex items-center justify-center w-9 h-9 rounded-full 
                                                bg-red-100 text-red-700 border border-red-200 
                                                hover:bg-red-200 hover:border-red-300 transition-all duration-200 shadow-sm"
                                                data-user-id="{{ $user->id }}" title="Eliminar usuario">
                                                <i class="mgc_delete_2_line text-lg"></i>
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    {{-- Modal para crear usuario --}}
    <div id="addUserModal"
        class="fixed inset-0 z-50 {{ $errors->any() ? 'flex' : 'hidden' }} items-center justify-center 
           bg-black/50 backdrop-blur-sm transition-all duration-300">
        <div
            class="bg-white rounded-2xl shadow-xl w-full max-w-lg border border-gray-100 transform scale-95 animate-fadeIn">
            <!-- Header -->
            <div class="flex justify-between items-center p-5 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="mgc_user_add_line text-blue-600 text-xl"></i>
                    Agregar Usuario
                </h3>
                <button onclick="closeModal('addUserModal')"
                    class="text-gray-400 hover:text-gray-600 transition-colors text-2xl leading-none">
                    &times;
                </button>
            </div>

            <!-- Formulario -->
            <div class="p-6">
                <form id="addUserForm" method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <!-- Nombre -->
                    <div class="mb-5">
                        <label for="user_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" id="user_name" name="name" value="{{ old('name') }}" required
                            placeholder="Nombre y apellido"
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-800 shadow-sm 
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all 
                               @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Correo Electrónico -->
                    <div class="mb-5">
                        <label for="user_email" class="block text-sm font-medium text-gray-700 mb-1">Correo
                            Electrónico</label>
                        <input type="email" id="user_email" name="email" value="{{ old('email') }}" required
                            placeholder="example@gmail.com"
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-800 shadow-sm 
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all 
                               @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-5">
                        <label for="user_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input type="password" id="user_password" name="password" required placeholder="••••••••"
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-800 shadow-sm 
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all 
                               @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="mb-5">
                        <label for="user_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmar Contraseña
                        </label>
                        <input type="password" id="user_password_confirmation" name="password_confirmation" required
                            placeholder="Repetir contraseña"
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-800 shadow-sm 
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all 
                               @error('password_confirmation') border-red-500 @enderror">
                        @error('password_confirmation')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rol -->
                    <div class="mb-6">
                        <label for="user_role" class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <select id="user_role" name="role" required
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 text-gray-800 shadow-sm 
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all 
                               @error('role') border-red-500 @enderror">
                            <option value="">Seleccione un rol</option>
                            <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin
                            </option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                        </select>
                        @error('role')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeModal('addUserModal')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-gray-300 text-gray-700 
                               hover:bg-gray-100 hover:text-gray-900 transition-all">
                            <i class="mgc_close_line text-lg"></i> Cancelar
                        </button>

                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-600 text-white font-medium 
                               shadow-sm hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-all">
                            <i class="mgc_save_3_fill text-lg"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>




    {{-- Modal para editar un usuario --}}
    <div id="editUserModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
            <div class="flex justify-between items-center p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="mgc_user_settings_line text-blue-500 text-xl"></i>
                    Editar Usuario
                </h2>
                <button class="text-gray-400 hover:text-gray-600" onclick="closeModal('editUserModal')">&times;</button>
            </div>
            <div class="p-6">
                <form id="editUserForm" method="POST">
                    @csrf
                    @method('PUT')



                    <!-- Nombre -->
                    <div class="mb-5">
                        <label for="edit_user_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" id="edit_user_name" name="name" required
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2 text-gray-800 shadow-sm 
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                    </div>

                    <!-- Correo Electrónico -->
                    <div class="mb-5">
                        <label for="edit_user_email" class="block text-sm font-medium text-gray-700 mb-1">Correo
                            Electrónico</label>
                        <input type="email" id="edit_user_email" name="email" required
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2 text-gray-800 shadow-sm 
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                    </div>

                    <!-- Contraseña -->
                    <div class="mb-5">
                        <label for="edit_user_password" class="block text-sm font-medium text-gray-700 mb-1">Nueva
                            Contraseña</label>
                        <input type="password" id="edit_user_password" name="password" placeholder="••••••••"
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2 text-gray-800 shadow-sm 
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="mb-5">
                        <label for="edit_user_password_confirmation"
                            class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                        <input type="password" id="edit_user_password_confirmation" name="password_confirmation"
                            placeholder="Repetir contraseña"
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2 text-gray-800 shadow-sm 
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <span id="passError" class="text-red-600 text-sm hidden mt-1">Las contraseñas no coinciden</span>
                    </div>

                    <!-- Rol -->
                    <div class="mb-6">
                        <label for="edit_user_role" class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <select id="edit_user_role" name="role" required
                            class="block w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2 text-gray-800 shadow-sm 
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeModal('editUserModal')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-gray-300 text-gray-700 
                   hover:bg-gray-100 hover:text-gray-900 transition-all">
                            <i class="mgc_close_line text-lg"></i> Cancelar
                        </button>

                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-600 text-white font-medium 
                   shadow-sm hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-all">
                            <i class="mgc_save_3_fill text-lg"></i> Guardar Cambios
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection


@section('script')
    @vite(['resources/js/pages/highlight.js'])
    @vite(['resources/js/table-users.js'])
@endsection
