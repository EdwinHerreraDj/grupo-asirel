@props([
    'title' => 'Eliminar registro',
    'id' => null,
])

<button type="button"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center w-9 h-9 rounded-full 
            bg-red-100 text-red-700 border border-red-200 
            hover:bg-red-200 hover:border-red-300 transition-all duration-200 shadow-sm',
    ]) }}
    title="{{ $title }}"  data-id="{{ $id }}">
    <i class="mgc_delete_2_line text-lg"></i>
</button>
