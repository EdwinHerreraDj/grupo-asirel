<div>
    <div class="space-y-4">

        {{-- CABECERA --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.06)]">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-5 py-5 sm:px-6">

                <div class="flex items-center gap-3 mb-4">
                    <a href="{{ route('empresa.gastosEmpresa') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900">
                        <i class="mgc_arrow_left_line text-lg"></i>
                        <span class="hidden sm:inline">Gastos</span>
                    </a>
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                            <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                            Configuración
                        </div>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                            Categorías de gastos
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Organiza los gastos en una estructura jerárquica con códigos contables autogenerados.
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                        <button wire:click="crearCategoriaPadre"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-[0_8px_20px_rgba(8,145,178,0.22)] transition hover:from-cyan-500 hover:to-blue-500">
                            <i class="mgc_add_line"></i> Nueva categoría
                        </button>
                    </div>
                </div>
            </div>

            {{-- STATS --}}
            @php
                $totalPadres = collect($categorias)->count();
                $totalHijas  = collect($categorias)->sum(fn($p) => $p->children->count());
            @endphp
            <div class="grid grid-cols-2 gap-3 px-5 py-5 sm:px-6">
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Categorías padre</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">{{ $totalPadres }}</p>
                </div>
                <div class="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-cyan-700">Subcategorías</p>
                    <p class="mt-1 text-xl font-bold text-cyan-800">{{ $totalHijas }}</p>
                </div>
            </div>
        </div>

        {{-- LISTADO JERÁRQUICO --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm p-4 sm:p-5">

            @forelse ($categorias as $padre)
                <div class="mb-3 last:mb-0 overflow-hidden rounded-2xl border border-slate-200 bg-white">

                    {{-- PADRE --}}
                    <div class="flex flex-col gap-3 bg-gradient-to-r from-slate-50 to-white border-b border-slate-200 p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                                <i class="mgc_folder_2_fill text-xl"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-xs font-semibold text-slate-500">{{ $padre->codigo }}</span>
                                    <span class="inline-flex items-center rounded-full border border-cyan-100 bg-cyan-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-cyan-700">
                                        Padre
                                    </span>
                                    @if ($padre->children->count() > 0)
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[10px] text-slate-600">
                                            {{ $padre->children->count() }} {{ $padre->children->count() === 1 ? 'subcategoría' : 'subcategorías' }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-0.5 font-semibold text-slate-900 truncate">{{ $padre->nombre }}</p>
                                @if ($padre->descripcion)
                                    <p class="text-xs text-slate-500 truncate">{{ $padre->descripcion }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <button wire:click="crearSubcategoria({{ $padre->id }})"
                                title="Añadir subcategoría"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-cyan-700 hover:border-cyan-300 hover:bg-cyan-50">
                                <i class="mgc_add_line"></i>
                            </button>
                            <button wire:click="editar({{ $padre->id }})"
                                title="Editar"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50">
                                <i class="mgc_edit_2_line"></i>
                            </button>
                            <button wire:click="confirmarEliminar({{ $padre->id }})"
                                title="Eliminar"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 hover:border-red-300 hover:bg-red-50">
                                <i class="mgc_delete_line"></i>
                            </button>
                        </div>
                    </div>

                    {{-- HIJAS --}}
                    @if ($padre->children->count() > 0)
                        <div class="divide-y divide-slate-100">
                            @foreach ($padre->children as $hijo)
                                <div class="flex flex-col gap-2 p-3 sm:flex-row sm:items-center sm:justify-between sm:gap-3 sm:p-4 sm:pl-12 hover:bg-slate-50/60 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                            <i class="mgc_folders_line text-base"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-xs font-semibold text-slate-500">{{ $hijo->codigo }}</span>
                                            </div>
                                            <p class="mt-0.5 text-sm font-medium text-slate-900 truncate">{{ $hijo->nombre }}</p>
                                            @if ($hijo->descripcion)
                                                <p class="text-xs text-slate-500 truncate">{{ $hijo->descripcion }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button wire:click="editar({{ $hijo->id }})"
                                            title="Editar"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50">
                                            <i class="mgc_edit_2_line"></i>
                                        </button>
                                        <button wire:click="confirmarEliminar({{ $hijo->id }})"
                                            title="Eliminar"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-600 hover:border-red-300 hover:bg-red-50">
                                            <i class="mgc_delete_line"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-4 py-3 text-xs text-slate-400 italic border-t border-dashed border-slate-200 sm:pl-12">
                            Sin subcategorías.
                            <button wire:click="crearSubcategoria({{ $padre->id }})"
                                class="ml-1 font-medium text-cyan-700 hover:underline">
                                Añadir
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-12 text-center text-sm text-slate-500">
                    <div class="flex flex-col items-center gap-2">
                        <i class="mgc_folder_line text-3xl text-slate-400"></i>
                        <p>No hay categorías creadas.</p>
                        <button wire:click="crearCategoriaPadre"
                            class="mt-2 inline-flex items-center gap-2 rounded-xl bg-slate-900 text-white text-xs font-semibold px-3 py-2 hover:bg-slate-800">
                            <i class="mgc_add_line"></i> Crear la primera
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL FORMULARIO --}}
    @if ($mostrarModal)
        <div class="fixed inset-0 z-[9999] bg-black/50 backdrop-blur-sm flex items-center justify-center px-4 py-6"
            x-data x-on:keydown.escape.window="$wire.cerrarModal()">
            <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden max-h-[92vh] flex flex-col">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-50 via-white to-cyan-50/40 px-6 py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-cyan-100 bg-cyan-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-cyan-700">
                                <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                {{ $modoFormulario === 'editar' ? 'Editar' : ($modoFormulario === 'crear_sub' ? 'Subcategoría' : 'Nueva') }}
                            </div>
                            <h3 class="mt-2 text-lg font-semibold text-slate-900">
                                {{ $modoFormulario === 'editar' ? 'Editar categoría' : ($modoFormulario === 'crear_sub' ? 'Nueva subcategoría' : 'Nueva categoría') }}
                            </h3>
                            <p class="text-sm text-slate-500 mt-0.5">
                                El código contable se genera automáticamente.
                            </p>
                        </div>
                        <button wire:click="cerrarModal"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                            <i class="mgc_close_line text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="overflow-y-auto px-6 py-5">
                    <livewire:empresa.gastos.categorias.formulario
                        :id="$categoria_id"
                        :modo="$modoFormulario"
                        :parentId="$categoriaCrearEnPadre"
                        :esModal="true"
                        :key="'cat-form-' . ($categoria_id ?? 'new') . '-' . ($categoriaCrearEnPadre ?? 'root')" />
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL ELIMINAR --}}
    @if ($mostrarModalEliminar)
        <div class="fixed inset-0 z-[10000] bg-black/60 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-6 pt-6 text-center">
                    <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100 text-red-600">
                        <i class="mgc_warning_line text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">Eliminar categoría</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        No se podrá eliminar si tiene subcategorías o gastos asociados.
                    </p>
                </div>
                <div class="mt-6 px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                    <button wire:click="$set('mostrarModalEliminar', false)"
                        class="px-4 py-2 rounded-xl text-sm font-medium border border-slate-300 text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button wire:click="eliminar"
                        class="px-4 py-2 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
