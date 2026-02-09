<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Forms\Components\Select;

trait HasBackground
{
    public function getBackgroundClass(): string
    {
        $background = $this->get('background', 'white');

        return config("atelier.features.backgrounds.options.{$background}.class", '');
    }

    public static function getBackgroundField(): Select
    {
        return Select::make('background')
            ->label('Background Color')
            ->options(
                collect(config('atelier.features.backgrounds.options'))
                    ->mapWithKeys(fn ($opt, $key) => [$key => $opt['label']])
            )
            ->default('white')
            ->native(false);
    }
}
