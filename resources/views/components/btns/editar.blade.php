@props([
    'title' => 'Editar registro',
    'id' => null,
])

<button type="button"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center w-9 h-9 rounded-full
            bg-blue-100 text-blue-700 border border-blue-200
            hover:bg-blue-200 hover:border-blue-300
            transition-all duration-200 shadow-sm',
    ]) }}
    title="{{ $title }}"
    data-id="{{ $id }}"
>
    <i class="mgc_edit_2_line text-lg"></i>
</button>
