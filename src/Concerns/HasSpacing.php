<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;

trait HasSpacing
{
    public function getSpacingClass(): string
    {
        $spacingMode = $this->get('spacing_mode', 'balanced');

        if ($spacingMode === 'individual') {
            return $this->getIndividualSpacingClass();
        }

        return $this->getBalancedSpacingClass();
    }

    protected function getBalancedSpacingClass(): string
    {
        $spacing = $this->get('spacing', 'md');
        $value = config("atelier.features.spacing.options.{$spacing}.value", 0);

        if ($value === 0) {
            return '';
        }

        return "py-{$value}";
    }

    protected function getIndividualSpacingClass(): string
    {
        $spacingTop = $this->get('spacing_top', 'md');
        $spacingBottom = $this->get('spacing_bottom', 'md');

        $topValue = config("atelier.features.spacing.options.{$spacingTop}.value", 0);
        $bottomValue = config("atelier.features.spacing.options.{$spacingBottom}.value", 0);

        $classes = [];

        if ($topValue > 0) {
            $classes[] = "pt-{$topValue}";
        }

        if ($bottomValue > 0) {
            $classes[] = "pb-{$bottomValue}";
        }

        return implode(' ', $classes);
    }

    public static function getSpacingField(): array
    {
        $spacingOptions = collect(config('atelier.features.spacing.options'))
            ->mapWithKeys(fn($opt, $key) => [$key => $opt['label']]);

        return [
            Select::make('spacing_mode')
                ->label('Spacing Mode')
                ->options([
                    'balanced' => 'Balanced',
                    'individual' => 'Individual',
                ])
                ->default('balanced')
                ->native(false)
                ->live()
                ->helperText('Balanced applies equal top/bottom spacing. Individual allows custom top and bottom spacing.'),

            Select::make('spacing')
                ->label('Spacing')
                ->options($spacingOptions)
                ->default('md')
                ->native(false)
                ->visible(fn (Get $get): bool => $get('spacing_mode') === 'balanced' || $get('spacing_mode') === null),

            Group::make([
                Select::make('spacing_top')
                    ->label('Spacing Top')
                    ->options($spacingOptions)
                    ->default('md')
                    ->native(false),

                Select::make('spacing_bottom')
                    ->label('Spacing Bottom')
                    ->options($spacingOptions)
                    ->default('md')
                    ->native(false),
            ])
                ->columns(2)
                ->visible(fn (Get $get): bool => $get('spacing_mode') === 'individual'),
        ];
    }
}
