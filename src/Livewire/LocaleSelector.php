<?php

namespace BlackpigCreatif\Atelier\Livewire;

use Livewire\Component;

class LocaleSelector extends Component
{
    public string $currentLocale;

    public function mount(): void
    {
        $this->currentLocale = session(
            'atelier.current_locale',
            config('atelier.default_locale', 'en')
        );
    }

    public function switchLocale(string $locale): void
    {
        $availableLocales = config('atelier.locales', []);

        if (! isset($availableLocales[$locale])) {
            return;
        }

        $this->currentLocale = $locale;
        session(['atelier.current_locale' => $locale]);

        // Dispatch browser event for Alpine.js to catch (x-on:locale-changed.window)
        $this->dispatch('locale-changed', locale: $locale);
    }

    public function render()
    {
        return view('atelier::livewire.locale-selector', [
            'availableLocales' => config('atelier.locales', ['en' => 'English']),
        ]);
    }
}
