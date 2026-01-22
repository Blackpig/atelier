<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

trait HasCommonOptions
{
    use HasBackground, HasDivider, HasSpacing, HasWidth;

    public static function getCommonOptionsSchema(): array
    {
        $sections = [];
        $fields = [];

        if (config('atelier.features.backgrounds.enabled')) {
            $fields[] = static::getBackgroundField();
        }

        if (config('atelier.features.spacing.enabled')) {
            $spacingFields = static::getSpacingField();
            // Handle both single field and array of fields
            if (is_array($spacingFields)) {
                $fields = array_merge($fields, $spacingFields);
            } else {
                $fields[] = $spacingFields;
            }
        }

        if (config('atelier.features.width.enabled')) {
            $fields[] = static::getWidthField();
        }

        if (config('atelier.features.dividers.enabled')) {
            $dividerFields = static::getDividerField();
            // Handle both single field and array of fields
            if (is_array($dividerFields)) {
                $fields = array_merge($fields, $dividerFields);
            } else {
                $fields[] = $dividerFields;
            }
        }

        if (! empty($fields)) {
            $sections[] = Section::make('Display Options')
                ->schema($fields)
                ->columns(3)
                ->collapsed()
                ->collapsible();
        }

        // Publication status - always included
        $sections[] = Section::make('Publication')
            ->schema([
                Toggle::make('is_published')
                    ->label('Published')
                    ->helperText('Unpublished blocks will not appear on the frontend')
                    ->default(true)
                    ->inline(false),
            ])
            ->collapsed()
            ->collapsible();

        return $sections;
    }

    public function getWrapperClasses(): string
    {
        $classes = [];

        // Add relative positioning if block has a divider
        if (method_exists($this, 'hasDivider') && $this->hasDivider()) {
            $classes[] = 'relative';
        }

        if (method_exists($this, 'getBackgroundClass')) {
            $classes[] = $this->getBackgroundClass();
        }

        if (method_exists($this, 'getSpacingClass')) {
            $classes[] = $this->getSpacingClass();
        }

        return implode(' ', array_filter($classes));
    }

    public function getContainerClasses(): string
    {
        if (method_exists($this, 'getWidthClass')) {
            return $this->getWidthClass();
        }

        return '';
    }
}
