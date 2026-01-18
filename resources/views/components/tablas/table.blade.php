<div class="relative border shadow-lg rounded-2xl w-full overflow-x-auto mt-6">
    <table {{ $attributes->merge([
        'class' => 'w-full table-fixed divide-y divide-gray-200'
    ]) }}>

        <thead class="bg-gray-100 text-left text-gray-700 text-sm">
            <tr>
                {{ $columns }}
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 text-sm">
            {{ $rows }}
        </tbody>

    </table>
    
    {{ $pagination ?? '' }}
</div>