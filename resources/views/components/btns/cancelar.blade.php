@props([
    'icon' => 'mgc_close_line',
])

<button
    {{ $attributes->merge([
        'class' => 'inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 text-gray-700 font-medium 
                 hover:bg-gray-200 border border-gray-300 shadow-sm active:scale-[0.98] transition',
    ]) }}>
    @if ($icon)
        <i class="{{ $icon }} text-lg"></i>
    @endif

    {{ $slot }}
</button>
