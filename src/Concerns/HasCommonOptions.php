<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

trait HasCommonOptions
{
    use HasBackground, HasDivider, HasSpacing, HasWidth;

    /**
     * Get the Published toggle field (appears first in schema)
     */
    public static function getPublishedField(): Toggle
    {
        return Toggle::make('is_published')
            ->label('Published')
            ->helperText('Unpublished blocks will not appear on the frontend')
            ->default(true)
            ->inline(false);
    }

    /**
     * Get common display options (appears last in schema)
     */
    public static function getCommonOptionsSchema(): array
    {
        $displayFields = [];

        if (config('atelier.features.backgrounds.enabled')) {
            $displayFields[] = static::getBackgroundField();
        }

        if (config('atelier.features.spacing.enabled')) {
            $spacingFields = static::getSpacingField();
            // Handle both single field and array of fields
            if (is_array($spacingFields)) {
                $displayFields = array_merge($displayFields, $spacingFields);
            } else {
                $displayFields[] = $spacingFields;
            }
        }

        if (config('atelier.features.width.enabled')) {
            $displayFields[] = static::getWidthField();
        }

        if (config('atelier.features.dividers.enabled')) {
            $dividerFields = static::getDividerField();
            // Handle both single field and array of fields
            if (is_array($dividerFields)) {
                $displayFields = array_merge($displayFields, $dividerFields);
            } else {
                $displayFields[] = $dividerFields;
            }
        }

        if (! empty($displayFields)) {
            return [
                Section::make('Display Options')
                    ->schema($displayFields)
                    ->columns(3)
                    ->collapsed()
                    ->collapsible()
            ];
        }

        return [];
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
