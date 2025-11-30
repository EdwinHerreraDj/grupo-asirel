@props([
    'icon' => 'mgc_add_line',
    'color' => 'green', // permite "primary", "red", "blue", etc.
])

@php
    // Mapa rápido de colores corporativos
    $bg = "bg-{$color}-500/20";
    $text = "text-{$color}-700";
    $border = "border border-{$color}-500/30";
    $hoverBg = "hover:bg-{$color}-600";
@endphp

<button
    {{ $attributes->merge([
        'class' => "inline-flex items-center gap-2 px-4 py-2 rounded-full font-medium shadow-sm
                 {$bg} {$text} {$border}
                 {$hoverBg} hover:text-white transition-all duration-300
                 focus:outline-none focus:ring-2 focus:ring-{$color}-400/40",
    ]) }}>
    <i class="{{ $icon }} text-lg"></i>
    {{ $slot }}
</button>
