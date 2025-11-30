@props([
    'icon' => null,
])

<button 
    {{ $attributes->merge([
        'class' =>
            'inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white font-semibold
             shadow hover:bg-primary/90 transition focus:outline-none focus:ring-2 focus:ring-primary/40'
    ]) }}
>
    @if ($icon)
        <i class="{{ $icon }} text-lg"></i>
    @endif

    {{ $slot }}
</button>
