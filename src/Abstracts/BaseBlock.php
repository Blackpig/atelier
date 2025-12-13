<?php

namespace BlackpigCreatif\Atelier\Abstracts;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

abstract class BaseBlock
{
    protected array $data = [];
    protected ?int $blockId = null;
    protected ?string $locale = null;
    
    // Abstract methods that must be implemented
    abstract public static function getSchema(): array;
    abstract public static function getLabel(): string;
    abstract public function render(): ViewContract;
    
    // Optional methods to override
    public static function getDescription(): ?string
    {
        return null;
    }
    
    public static function getIcon(): string
    {
        return 'heroicon-o-square-3-stack-3d';
    }
    
    public static function getBlockIdentifier(): string
    {
        return Str::kebab(class_basename(static::class));
    }
    
    public static function getViewPath(): string
    {
        return 'atelier::blocks.' . static::getBlockIdentifier();
    }
    
    // Override to specify translatable fields
    public static function getTranslatableFields(): array
    {
        return [];
    }
    
    // Data management
    public function fill(array $data): static
    {
        $this->data = $data;
        return $this;
    }
    
    public function setBlockId(int $id): static
    {
        $this->blockId = $id;
        return $this;
    }
    
    public function setLocale(string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }
    
    // Get translated value for current locale
    public function getTranslated(string $key, ?string $locale = null): mixed
    {
        $locale = $locale ?? $this->locale ?? app()->getLocale();
        
        // Check if this field is translatable
        if (in_array($key, static::getTranslatableFields())) {
            return $this->get("{$key}.{$locale}") ?? $this->get($key);
        }
        
        return $this->get($key);
    }
    
    // Get all translations for a field
    public function getTranslations(string $key): array
    {
        $translations = [];
        $locales = array_keys(config('atelier.locales', ['en' => 'English']));
        
        foreach ($locales as $locale) {
            $value = $this->get("{$key}.{$locale}");
            if ($value !== null) {
                $translations[$locale] = $value;
            }
        }
        
        return $translations;
    }
    
    // View data helper
    public function getViewData(): array
    {
        return array_merge(
            $this->data,
            [
                'block' => $this,
                'blockId' => $this->blockId,
                'locale' => $this->locale,
            ]
        );
    }
    
    // Validation
    public function validate(): bool
    {
        // Override for custom validation
        return true;
    }
}
