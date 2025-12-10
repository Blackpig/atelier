<?php

namespace Blackpigcreatif\Atelier\Concerns;

use Filament\Forms\Components\Select;

trait HasSpacing
{
    public function getSpacingClass(): string
    {
        $spacing = $this->get('spacing', 'md');
        return config("atelier.features.spacing.options.{$spacing}.class", '');
    }
    
    public static function getSpacingField(): Select
    {
        return Select::make('spacing')
            ->label('Spacing')
            ->options(
                collect(config('atelier.features.spacing.options'))
                    ->mapWithKeys(fn($opt, $key) => [$key => $opt['label']])
            )
            ->default('md')
            ->native(false);
    }
}
