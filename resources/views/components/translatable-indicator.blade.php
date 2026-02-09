@php
    $currentLocale = strtoupper(session('atelier.current_locale', config('atelier.default_locale', 'en')));
@endphp

<div class="flex items-center gap-1.5 text-primary-600 dark:text-primary-400">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
    </svg>
    <span class="text-xs font-semibold px-1.5 py-0.5 bg-primary-100 dark:bg-primary-900/30 rounded">
        {{ $currentLocale }}
    </span>
</div>
