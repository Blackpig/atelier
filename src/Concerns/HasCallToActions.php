<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

trait HasCallToActions
{
    /**
     * Get the CallToActions Repeater field
     *
     * @return Repeater
     */
    public static function getCallToActionsField(): Repeater
    {
        $config = config('atelier.features.button_styles', []);
        $styleOptions = collect($config['options'] ?? [])
            ->mapWithKeys(fn ($opt, $key) => [$key => $opt['label']]);

        $field = Repeater::make('ctas')
            ->label('Call to Actions')
            ->schema([
                TextInput::make('url')
                    ->label('URL')
                    ->required()
                    ->rules([
                        'required',
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                // Allow relative paths starting with /
                                if (str_starts_with($value, '/')) {
                                    return;
                                }

                                // Allow full URLs (must have protocol)
                                if (filter_var($value, FILTER_VALIDATE_URL)) {
                                    return;
                                }

                                $fail('The URL must be a valid URL (https://...) or a relative path starting with /');
                            };
                        },
                    ])
                    ->helperText('Enter a full URL (https://...) or a relative path (/page)')
                    ->columnSpanFull(),

                TextInput::make('icon')
                    ->label('Icon')
                    ->placeholder('heroicon-o-arrow-right')
                    ->helperText('Optional Heroicon name')
                    ->columnSpanFull(),

                Select::make('style')
                    ->label('Button Style')
                    ->options($styleOptions)
                    ->default('primary')
                    ->required()
                    ->native(false),

                Toggle::make('new_tab')
                    ->label('Open in new tab')
                    ->default(false),

                // Label must be LAST so translatable macro works correctly
                TextInput::make('label')
                    ->label('Button Label')
                    ->required()
                    ->translatable()
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->reorderable()
            ->collapsible()
            ->itemLabel(fn (?array $state): string =>
                static::getRepeaterItemLabel($state)
            )
            ->addActionLabel('Add Call to Action')
            ->columnSpanFull()
            ->defaultItems(0);

        // Apply any field configurations from global registry
        $fieldConfig = \BlackpigCreatif\Atelier\Support\BlockFieldConfig::get(static::class, 'ctas');

        if (!empty($fieldConfig)) {
            foreach ($fieldConfig as $method => $value) {
                if (method_exists($field, $method)) {
                    $field = $field->{$method}($value);
                }
            }
        }

        return $field;
    }

    /**
     * Get label for repeater item (handles translatable fields)
     *
     * @param array|null $state
     * @return string
     */
    protected static function getRepeaterItemLabel(?array $state): string
    {
        if (!$state || !isset($state['label'])) {
            return 'Call to Action';
        }

        $label = $state['label'];

        // If label is translatable (array of locales)
        if (is_array($label)) {
            $locale = app()->getLocale();
            $defaultLocale = config('atelier.default_locale', 'en');

            // Try current locale, then default locale, then first available
            return $label[$locale]
                ?? $label[$defaultLocale]
                ?? reset($label)
                ?? 'Call to Action';
        }

        // Simple string label
        return $label ?: 'Call to Action';
    }

    /**
     * Get all CTAs for this block
     *
     * @return array
     */
    public function getCallToActions(): array
    {
        return $this->get('ctas', []);
    }

    /**
     * Check if block has any CTAs
     *
     * @return bool
     */
    public function hasCallToActions(): bool
    {
        $ctas = $this->getCallToActions();
        return !empty($ctas) && is_array($ctas);
    }

    /**
     * Get translated label for a CTA
     *
     * @param array $cta
     * @param string|null $locale
     * @return string
     */
    public function getCallToActionLabel(array $cta, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $label = $cta['label'] ?? [];

        // If label is an array (translatable), get the correct locale
        if (is_array($label)) {
            return $label[$locale] ?? $label[config('atelier.default_locale', 'en')] ?? '';
        }

        // If label is a string, return it directly
        return (string) $label;
    }

    /**
     * Get CSS class for a CTA based on style selection
     *
     * @param string|array $cta CTA array or style key
     * @return string
     */
    public function getCallToActionStyleClass(string|array $cta): string
    {
        $styleKey = is_array($cta) ? ($cta['style'] ?? 'primary') : $cta;

        return config("atelier.features.button_styles.options.{$styleKey}.class", 'btn btn-primary');
    }

    /**
     * Get target attribute for a CTA
     *
     * @param array $cta
     * @return string
     */
    public function getCallToActionTarget(array $cta): string
    {
        return ($cta['new_tab'] ?? false) ? '_blank' : '_self';
    }

    /**
     * Check if URL is external
     *
     * @param string $url
     * @return bool
     */
    public function isExternalUrl(string $url): bool
    {
        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }
}
