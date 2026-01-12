<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Schemas\Components\Section;

trait HasCommonOptions
{
    use HasBackground, HasSpacing, HasWidth, HasDivider;
    
    public static function getCommonOptionsSchema(): array
    {
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

        if (empty($fields)) {
            return [];
        }

        return [
            Section::make('Display Options')
                ->schema($fields)
                ->columns(3)
                ->collapsed()
                ->collapsible(),
        ];
    }
    
    public function getWrapperClasses(): string
    {
        $classes = [];
        
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
