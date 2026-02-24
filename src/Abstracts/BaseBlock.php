<?php

namespace BlackpigCreatif\Atelier\Abstracts;

use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use BlackpigCreatif\Atelier\Contracts\HasCompositeSchema;
use BlackpigCreatif\Atelier\Contracts\HasSchemaContribution;
use BlackpigCreatif\Atelier\Contracts\HasStandaloneSchema;
use Filament\Support\Enums\IconSize;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

abstract class BaseBlock implements HasCompositeSchema, HasSchemaContribution, HasStandaloneSchema
{
    use HasCommonOptions;

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

    public static function getIcon(): string|IconSize|Htmlable|null
    {
        return 'heroicon-o-square-3-stack-3d';
    }

    public static function getBlockIdentifier(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    public static function getViewPath(): string
    {
        return 'atelier::blocks.'.static::getBlockIdentifier();
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

    public function set(string $key, mixed $value): static
    {
        data_set($this->data, $key, $value);

        return $this;
    }

    // Get translated value for current locale
    public function getTranslated(string $key, ?string $locale = null): mixed
    {
        $locale = $locale ?? $this->locale ?? app()->getLocale();

        // Get the raw value first
        $rawValue = $this->get($key);

        // Check if the value is already in locale-keyed format (e.g., {"en":"value", "fr":"value"})
        // This is more reliable than checking getTranslatableFields()
        if (is_array($rawValue) && $this->isLocaleKeyedArray($rawValue)) {
            return $rawValue[$locale] ?? $rawValue[array_key_first($rawValue)] ?? null;
        }

        // Fallback: Check getTranslatableFields() for performance optimization
        if (method_exists(static::class, 'getTranslatableFields') && in_array($key, static::getTranslatableFields())) {
            $value = $this->get("{$key}.{$locale}") ?? $this->get($key);

            // If the value is still an array (legacy multi-locale data), try to extract the locale
            if (is_array($value)) {
                return $value[$locale] ?? $value[array_key_first($value)] ?? null;
            }

            return $value;
        }

        return $this->get($key);
    }

    // Get all translations for a field
    public function getTranslations(string $key): array
    {
        $translations = [];
        $locales = array_keys(config('app.locales', ['en' => 'English']));

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

    // HasCompositeSchema defaults — override in blocks that contribute to Article schemas

    public function contributesToComposite(): bool
    {
        return false;
    }

    public function getCompositeContribution(): ?array
    {
        return null;
    }

    // HasStandaloneSchema defaults — override in blocks that hand-craft their own schema

    public function hasStandaloneSchema(): bool
    {
        return false;
    }

    public function toStandaloneSchema(): ?array
    {
        return null;
    }

    // HasSchemaContribution defaults — override in blocks that use the driver pattern

    public function getSchemaType(): ?\BackedEnum
    {
        return null;
    }

    /** @return array<string, mixed> */
    public function getSchemaData(): array
    {
        return [];
    }

    /**
     * Check if an array is locale-keyed (e.g., {"en": "value", "fr": "value"})
     */
    protected function isLocaleKeyedArray($value): bool
    {
        if (! is_array($value) || empty($value)) {
            return false;
        }

        $availableLocales = array_keys(config('app.locales', ['en' => 'English']));

        // Check if all keys are valid locale codes
        foreach (array_keys($value) as $key) {
            if (! in_array($key, $availableLocales)) {
                return false;
            }
        }

        return true;
    }
}
