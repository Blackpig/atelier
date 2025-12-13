<?php

namespace BlackpigCreatif\Atelier\Forms\Components;

use Closure;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class TranslatableContainer extends Tabs
{
    protected array | Closure $translatableFields = [];
    
    public function translatableFields(array | Closure $fields): static
    {
        $this->translatableFields = $fields;
        return $this;
    }
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->contained(false);
        $this->persistTabInQueryString();
        
        // Build tabs dynamically
        $this->tabs(function (TranslatableContainer $component): array {
            $locales = config('atelier.locales', ['en' => 'English']);
            $fields = $component->evaluate($component->translatableFields);
            $defaultLocale = config('atelier.default_locale', 'en');
            
            $tabs = [];
            
            foreach ($locales as $locale => $label) {
                $localizedFields = [];
                
                foreach ($fields as $field) {
                    $clonedField = clone $field;
                    $originalName = $field->getName();
                    
                    // Change field name to include locale: field_name.en, field_name.fr
                    $clonedField->statePath("{$originalName}.{$locale}");
                    
                    // Only require for default locale
                    if ($locale !== $defaultLocale && $field->isRequired()) {
                        $clonedField->required(false);
                    }
                    
                    $localizedFields[] = $clonedField;
                }
                
                $tab = Tab::make($locale)
                    ->label($label)
                    ->badge(strtoupper($locale))
                    ->schema($localizedFields);
                
                // Mark default locale
                if ($locale === $defaultLocale) {
                    $tab->badge('Default')->badgeColor('success');
                }
                
                $tabs[] = $tab;
            }
            
            return $tabs;
        });
    }
}
