@props([
    'href' => null, // si se pasa, usa <a>
    'icon' => 'mgc_arrow_left_line',
    'color' => 'gray', // permite cambiar colores si lo deseas
])

@php
    $baseClasses = "inline-flex items-center gap-2 px-4 py-2 rounded-full font-medium shadow-sm
                    bg-{$color}-100 text-{$color}-700 border border-{$color}-200
                    hover:bg-{$color}-200 hover:text-{$color}-900
                    transition-all duration-300
                    focus:outline-none focus:ring-2 focus:ring-primary/30";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        <i class="{{ $icon }} text-lg"></i>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $baseClasses]) }}>
        <i class="{{ $icon }} text-lg"></i>
        {{ $slot }}
    </button>
@endif
