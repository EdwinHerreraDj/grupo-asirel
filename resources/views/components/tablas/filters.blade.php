<div class="bg-gray-50 border shadow-lg rounded-2xl border-gray-200 p-6">
    @isset($title)
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            {{ $title }}
        </h3>
    @endisset

    {{ $slot }}
</div>
