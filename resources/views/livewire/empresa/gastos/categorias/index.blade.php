<div>

    {{-- LISTADO JERÁRQUICO --}}
    <div class="bg-white p-6 rounded-xl shadow border border-gray-200 space-y-4">
        <h2 class="text-xl font-semibold text-gray-700 flex items-center gap-2 mb-4 ">
            <i class="mgc_folder_open_line text-primary text-2xl"></i>
            Categorías de Gastos
        </h2>

        <div class="flex items-center gap-3 mb-4">

            {{-- Regresar --}}
            <a href="{{ route('empresa.gastosEmpresa') }}"
                class="px-4 py-2 rounded-full bg-gray-200 text-gray-700 font-medium hover:bg-gray-300 transition flex items-center gap-2">
                <i class="mgc_arrow_left_line text-lg"></i>
                Regresar a mi unidad
            </a>

            {{-- Nueva categoría --}}
            <button wire:click="crearCategoriaPadre"
                class="px-4 py-2 rounded-full bg-primary text-white font-medium hover:bg-primary/90 transition flex items-center gap-2">
                <i class="mgc_add_line text-lg"></i>
                Nueva categoría
            </button>

        </div>

        @forelse ($categorias as $padre)

            <!-- CATEGORÍA PADRE -->
            <div class="rounded-lg border border-gray-200 bg-gray-50">

                <div class="flex items-center justify-between py-2 px-3">

                    <div class="flex items-center gap-3">
                        <i class="mgc_folder_2_line text-primary text-xl"></i>

                        <span class="font-semibold text-gray-800">
                            {{ $padre->codigo }} — {{ $padre->nombre }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">

                        <!-- Crear subcategoría -->
                        <button wire:click="crearSubcategoria({{ $padre->id }})"
                            class="text-blue-600 hover:text-blue-800 transition">
                            <i class="mgc_add_circle_line text-xl"></i>
                        </button>

                        <!-- Editar -->
                        <button wire:click="editar({{ $padre->id }})"
                            class="text-green-600 hover:text-green-800 transition">
                            <i class="mgc_edit_line text-xl"></i>
                        </button>

                        <!-- Eliminar -->
                        <button wire:click="confirmarEliminar({{ $padre->id }})"
                            class="text-red-600 hover:text-red-800 transition">
                            <i class="mgc_delete_line text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- SUBCATEGORÍAS -->
                @if ($padre->children->count() > 0)
                    <div class="ml-6 mt-2 mb-3 space-y-2">

                        @foreach ($padre->children as $hijo)
                            <div
                                class="flex items-center justify-between py-2 px-3 rounded-lg bg-white border border-gray-200">

                                <div class="flex items-center gap-3">
                                    <i class="mgc_subtract_line text-gray-500 text-xl"></i>

                                    <span class="text-gray-700">
                                        {{ $hijo->codigo }} — {{ $hijo->nombre }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">

                                    <!-- Editar -->
                                    <button wire:click="editar({{ $hijo->id }})"
                                        class="text-green-600 hover:text-green-800 transition">
                                        <i class="mgc_edit_line text-xl"></i>
                                    </button>

                                    <!-- Eliminar -->
                                    <button wire:click="confirmarEliminar({{ $hijo->id }})"
                                        class="text-red-600 hover:text-red-800 transition">
                                        <i class="mgc_delete_line text-xl"></i>
                                    </button>

                                </div>

                            </div>
                        @endforeach

                    </div>
                @endif

            </div>

        @empty
            <p class="text-gray-500 text-center py-6">No hay categorías creadas.</p>
        @endforelse

    </div>



    {{-- MODAL FORMULARIO CREAR EDITAR --}}
    @if ($mostrarModal)
        <div class="fixed inset-0 bg-black/50 z-[999] flex items-center justify-center p-4">

            <div class="bg-white w-full max-w-xl rounded-xl shadow-2xl p-6 relative">

                {{-- Botón cerrar --}}
                <button wire:click="cerrarModal"
                    class="absolute top-3 right-4 text-gray-600 hover:text-red-600 text-2xl">
                    &times;
                </button>

                {{-- Formulario Livewire --}}
                <livewire:empresa.gastos.categorias.formulario :id="$categoria_id" :modo="$modoFormulario" :parentId="$categoriaCrearEnPadre"
                    :esModal="true" />

            </div>
        </div>
    @endif

    @if ($mostrarModalEliminar)
        <x-modals.confirmar titulo="Confirmar eliminación"
            mensaje="¿Estás seguro de que deseas eliminar esta categoría? <strong class='text-red-600'>Esta acción no se puede deshacer.</strong>"
            icono="mgc_warning_line" :accion="'eliminar'" :parametro="$categoriaEliminarId" state="mostrarModalEliminar" />
    @endif


</div>
