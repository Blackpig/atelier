<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Filament\Schemas\Components\Section;

trait HasCommonOptions
{
    use HasBackground, HasSpacing, HasWidth;
    
    public static function getCommonOptionsSchema(): array
    {
        $fields = [];
        
        if (config('atelier.features.backgrounds.enabled')) {
            $fields[] = static::getBackgroundField();
        }
        
        if (config('atelier.features.spacing.enabled')) {
            $fields[] = static::getSpacingField();
        }
        
        if (config('atelier.features.width.enabled')) {
            $fields[] = static::getWidthField();
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
