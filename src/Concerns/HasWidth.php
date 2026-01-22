<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Forms\Components\Select;

trait HasWidth
{
    public function getWidthClass(): string
    {
        $width = $this->get('width', 'container');

        return config("atelier.features.width.options.{$width}.class", '');
    }

    public static function getWidthField(): Select
    {
        return Select::make('width')
            ->label('Width')
            ->options(
                collect(config('atelier.features.width.options'))
                    ->mapWithKeys(fn ($opt, $key) => [$key => $opt['label']])
            )
            ->default('container')
            ->native(false);
    }
}
