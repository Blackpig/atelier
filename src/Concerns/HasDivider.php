<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;

trait HasDivider
{
    public function getDividerClass(): string
    {
        $divider = $this->get('divider', 'none');

        return config("atelier.features.dividers.options.{$divider}.class", '');
    }

    public function getDividerToBackground(): ?string
    {
        $divider = $this->get('divider', 'none');

        // If no divider is selected, return null
        if ($divider === 'none' || empty($divider)) {
            return null;
        }

        // Get the selected "to background" color
        $toBackground = $this->get('divider_to_background');

        if (! $toBackground) {
            return null;
        }

        // Convert bg-* class to text-* class for SVG fill
        $bgClass = config("atelier.features.backgrounds.options.{$toBackground}.class", '');

        // Extract just the background color part and convert to text color
        // e.g., "bg-white" -> "text-white", "bg-primary-500 text-white" -> "text-primary-500"
        if (preg_match('/bg-([^\s]+)/', $bgClass, $matches)) {
            return 'text-'.$matches[1];
        }

        return 'text-white';
    }

    /**
     * Check if block has a divider
     */
    public function hasDivider(): bool
    {
        $divider = $this->get('divider', 'none');

        return $divider !== 'none' && ! empty($divider);
    }

    /**
     * Get the divider component name for dynamic component rendering
     */
    public function getDividerComponent(): ?string
    {
        if (! $this->hasDivider()) {
            return null;
        }

        $divider = $this->get('divider');

        // Map config keys to component names
        $componentMap = [
            'wave' => 'atelier::dividers.wave',
            'curve-up' => 'atelier::dividers.curve-up',
            'curve-down' => 'atelier::dividers.curve-down',
            'diagonal-left-right' => 'atelier::dividers.diagonal-lr',
            'diagonal-right-left' => 'atelier::dividers.diagonal-rl',
            'triangle' => 'atelier::dividers.triangle',
        ];

        return $componentMap[$divider] ?? null;
    }

    public static function getDividerField(): array
    {
        $dividerOptions = collect(config('atelier.features.dividers.options'))
            ->mapWithKeys(fn ($opt, $key) => [$key => $opt['label']]);

        $backgroundOptions = collect(config('atelier.features.backgrounds.options'))
            ->mapWithKeys(fn ($opt, $key) => [$key => $opt['label']]);

        return [
            Select::make('divider')
                ->label('Block Divider')
                ->options($dividerOptions)
                ->default('none')
                ->native(false)
                ->live()
                ->helperText('Add a decorative divider at the bottom of this block.'),

            Select::make('divider_to_background')
                ->label('To Background Color')
                ->options($backgroundOptions)
                ->default('white')
                ->native(false)
                ->visible(fn (Get $get): bool => $get('divider') !== 'none' && $get('divider') !== null)
                ->helperText('Select the background color of the section below this divider.'),
        ];
    }
}
