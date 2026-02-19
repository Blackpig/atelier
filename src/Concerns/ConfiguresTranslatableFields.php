<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Group;

trait ConfiguresTranslatableFields
{
    public static function configureTranslatableMacro(): void
    {
        if (Field::hasMacro('translatable')) {
            return; // Already registered
        }

        Field::macro('translatable', function () {
            /** @var Field $this */
            $originalField = $this;
            $fieldName = $this->getName();
            $availableLocales = config('app.locales', ['en' => 'English']);
            $defaultLocale = config('atelier.default_locale', 'en');

            // Create a group that will contain locale-specific field instances
            $localeFields = [];

            foreach ($availableLocales as $locale => $label) {
                // Clone the field for each locale
                $localeField = clone $originalField;

                // Update the field name to include locale: headline.en, headline.fr
                $localeField->statePath($fieldName.'.'.$locale);

                // Add visual indicator with icon and locale badge
                $localeField->hint('['.strtoupper($locale).']');
                $localeField->hintIcon('heroicon-m-globe-alt');
                $localeField->hintColor('primary');
                $localeField->extraInputAttributes([
                    'class' => 'border-l-2 border-l-primary-400',
                ]);

                // Wrap the ENTIRE field in a container with x-show
                $fieldWrapper = Group::make([$localeField])
                    ->extraAttributes([
                        'x-show' => "currentLocale === '{$locale}'",
                        'x-cloak' => true,
                    ])
                    ->columnSpan('full')
                    ->columns(1)
                    ->gap(false);

                $localeFields[] = $fieldWrapper;
            }

            // Return a Group containing all locale-specific field wrappers
            // The global LocaleSelector will control which one is visible via Alpine
            return Group::make($localeFields)
                ->extraAttributes([
                    'x-data' => "{ currentLocale: '".session('atelier.current_locale', $defaultLocale)."' }",
                    'x-on:locale-changed.window' => 'currentLocale = $event.detail.locale',
                ])
                ->columnSpanFull()
                ->gap(false);
        });
    }
}
