@props([
    'icon' => 'mgc_delete_2_line',
])

<button
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 text-white font-semibold
                 hover:bg-red-700 shadow-sm active:scale-[0.98] transition',
    ]) }}>
    @if ($icon)
        <i class="{{ $icon }} text-lg"></i>
    @endif

    {{ $slot }}
</button>
