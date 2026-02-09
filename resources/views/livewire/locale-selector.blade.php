<div class="flex items-center justify-between gap-4 px-6 py-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 rounded-t-xl mb-4">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
        </svg>
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
            Translation
        </span>
    </div>

    <div class="flex gap-2">
        @foreach($availableLocales as $locale => $label)
            <button
                type="button"
                wire:click="switchLocale('{{ $locale }}')"
                @class([
                    'px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                    'bg-primary-600 text-white shadow-sm ring-2 ring-primary-300 dark:ring-primary-500' => $locale === $currentLocale,
                    'bg-gray-200 dark:bg-gray-700 text-white hover:bg-gray-300 dark:hover:bg-gray-600' => $locale !== $currentLocale,
                ])
            >
                {{ $label }} @if($locale === $currentLocale) ✓ @endif
            </button>
        @endforeach
    </div>
</div>
