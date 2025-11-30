@props([
    'icon'  => 'mgc_close_line',
])

<button 

    {{ $attributes->merge([
        'class' =>
            'inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-medium
             border border-gray-300 shadow-sm hover:bg-gray-200 hover:text-gray-900
             transition focus:outline-none focus:ring-2 focus:ring-primary/30'
    ]) }}
>
    @if ($icon)
        <i class="{{ $icon }} text-lg"></i>
    @endif

    {{ $slot }}
</button>
