<?php

namespace BlackpigCreatif\Atelier\Forms\Components;

use BlackpigCreatif\Atelier\Models\AtelierBlock;
use BlackpigCreatif\Atelier\Models\AtelierBlockAttribute;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Str;

class BlockManager extends Field
{
    protected string $view = 'atelier::forms.components.block-manager';

    protected array | Closure $blockClasses = [];

    protected bool | Closure $isAddable = true;
    protected bool | Closure $isEditable = true;
    protected bool | Closure $isDeletable = true;
    protected bool | Closure $isReorderable = true;
    protected bool | Closure $isCollapsible = false;

    protected ?string $addButtonLabel = null;

    /**
     * Set the available block classes
     */
    public function blocks(array | Closure $blockClasses): static
    {
        $this->blockClasses = $blockClasses;
        return $this;
    }

    /**
     * Get the registered block classes
     */
    public function getBlockClasses(): array
    {
        return $this->evaluate($this->blockClasses);
    }

    /**
     * Get block metadata for all registered block classes
     */
    public function getBlockMetadata(): array
    {
        $blockClasses = $this->getBlockClasses();
        $metadata = [];

        foreach ($blockClasses as $blockClass) {
            if (!class_exists($blockClass)) {
                continue;
            }

            $icon = $blockClass::getIcon();
            $iconSvg = $this->getIconSvg($icon);

            $metadata[$blockClass] = [
                'label' => $blockClass::getLabel(),
                'description' => $blockClass::getDescription(),
                'icon' => $icon,
                'iconSvg' => $iconSvg,
            ];
        }

        return $metadata;
    }

    /**
     * Get SVG content for an icon
     */
    protected function getIconSvg(string $icon): ?string
    {
        // Check if it's an Atelier icon
        if (str_starts_with($icon, 'atelier.icons.')) {
            $iconName = str_replace('atelier.icons.', '', $icon);
            $iconPath = __DIR__ . '/../../../resources/views/components/icons/' . $iconName . '.blade.php';

            if (file_exists($iconPath)) {
                return file_get_contents($iconPath);
            }
        }

        // For heroicons or other icons, return null (will use Filament's icon component)
        return null;
    }

    /**
     * Configure addability
     */
    public function addable(bool | Closure $condition = true): static
    {
        $this->isAddable = $condition;
        return $this;
    }

    public function isAddable(): bool
    {
        return $this->evaluate($this->isAddable);
    }

    /**
     * Configure editability
     */
    public function editable(bool | Closure $condition = true): static
    {
        $this->isEditable = $condition;
        return $this;
    }

    public function isEditable(): bool
    {
        return $this->evaluate($this->isEditable);
    }

    /**
     * Configure deletability
     */
    public function deletable(bool | Closure $condition = true): static
    {
        $this->isDeletable = $condition;
        return $this;
    }

    public function isDeletable(): bool
    {
        return $this->evaluate($this->isDeletable);
    }

    /**
     * Configure reorderability
     */
    public function reorderable(bool | Closure $condition = true): static
    {
        $this->isReorderable = $condition;
        return $this;
    }

    public function isReorderable(): bool
    {
        return $this->evaluate($this->isReorderable);
    }

    /**
     * Configure collapsibility
     */
    public function collapsible(bool | Closure $condition = true): static
    {
        $this->isCollapsible = $condition;
        return $this;
    }

    public function isCollapsible(): bool
    {
        return $this->evaluate($this->isCollapsible);
    }

    /**
     * Set add button label
     */
    public function addButtonLabel(?string $label): static
    {
        $this->addButtonLabel = $label;
        return $this;
    }

    public function getAddButtonLabel(): string
    {
        return $this->addButtonLabel ?? __('Add Content Block');
    }

    /**
     * Setup the component
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set default state to empty array
        $this->default([]);

        // Hydrate blocks from database
        $this->afterStateHydrated(function (BlockManager $component, $state, $record) {
            if (!$record || !method_exists($record, 'blocks')) {
                return;
            }

            $blocks = $record->blocks()
                ->ordered()
                ->with('attributes')
                ->get();

            if ($blocks->isEmpty()) {
                $component->state([]);
                return;
            }

            $blockData = [];

            foreach ($blocks as $block) {
                $extractedData = $component->extractBlockAttributes($block);

                $blockData[] = [
                    'uuid' => $block->uuid,
                    'type' => $block->block_type,
                    'data' => $extractedData,
                    'position' => $block->position,
                    'is_published' => $block->is_published,
                ];
            }

            $component->state($blockData);
        });

        // Save blocks to database
        $this->saveRelationshipsUsing(function ($component, $state) {
            $record = $component->getRecord();

            if (!$record || !method_exists($record, 'blocks')) {
                return;
            }

            // Get current block UUIDs from state
            $currentUuids = collect($state ?? [])->pluck('uuid')->filter()->toArray();

            // Delete blocks that are no longer in state
            $record->blocks()
                ->whereNotIn('uuid', $currentUuids)
                ->get()
                ->each(function ($block) {
                    // Delete all attributes first
                    $block->attributes()->delete();
                    // Delete the block
                    $block->delete();
                });

            // Create or update blocks
            foreach ($state ?? [] as $index => $blockData) {
                $block = $record->blocks()->updateOrCreate(
                    ['uuid' => $blockData['uuid']],
                    [
                        'block_type' => $blockData['type'],
                        'position' => $index,
                        'is_published' => $blockData['is_published'] ?? true,
                    ]
                );

                // Save block attributes
                $component->saveBlockAttributes($block, $blockData['data'] ?? [], $blockData['type']);
            }
        });

        // Dehydrate - ensure state is always set before saving
        $this->dehydrated();
    }

    /**
     * Extract attributes from a block model
     */
    protected function extractBlockAttributes(AtelierBlock $block): array
    {
        $blockClass = $block->block_type;

        if (!class_exists($blockClass)) {
            return [];
        }

        $translatableFields = $blockClass::getTranslatableFields();
        $attributes = $block->attributes()->get();
        $data = [];

        // Group attributes by key
        foreach ($attributes->groupBy('key') as $key => $attributeGroup) {
            if (in_array($key, $translatableFields)) {
                // Build translation array - translatable fields ALWAYS return as locale-keyed array
                $translations = [];

                foreach ($attributeGroup as $attr) {
                    if ($attr->locale) {
                        $translations[$attr->locale] = $attr->getCastedValue();
                    }
                }

                // Translatable fields always return as array (even if empty or single locale)
                $data[$key] = $translations;
            } else {
                // Non-translatable - just the value
                $firstAttr = $attributeGroup->first();
                $data[$key] = $firstAttr ? $firstAttr->getCastedValue() : null;
            }
        }

        return $data;
    }

    /**
     * Static wrapper for saving block attributes (for use in Resource pages)
     */
    public static function saveBlockAttributesStatic(AtelierBlock $block, array $data, string $blockType): void
    {
        $instance = new static('dummy');
        $instance->saveBlockAttributes($block, $data, $blockType);
    }

    /**
     * Save block attributes to database
     */
    protected function saveBlockAttributes(AtelierBlock $block, array $data, string $blockType): void
    {
        if (!class_exists($blockType)) {
            return;
        }

        $translatableFields = $blockType::getTranslatableFields();

        // Delete existing attributes
        $block->attributes()->delete();

        $sortOrder = 0;
        $defaultLocale = config('atelier.default_locale', 'en');

        foreach ($data as $key => $value) {
            // Extract file paths if this is a file upload field
            if ($this->isFileUploadValue($value)) {
                $value = $this->extractFilePath($value);
            }

            if (in_array($key, $translatableFields)) {
                // Translatable field - MUST be saved with locale keys

                // If value is not an array, wrap it in default locale
                if (!is_array($value)) {
                    $value = [$defaultLocale => $value];
                } elseif (!empty($value) && !$this->isLocaleKeyedArray($value)) {
                    // It's an array but not locale-keyed (might be file upload array)
                    // Keep as is if it's a file upload, otherwise wrap in default locale
                    if (!$this->isFileUploadValue($value)) {
                        $value = [$defaultLocale => $value];
                    }
                }

                // Create one attribute per locale
                foreach ($value as $locale => $localeValue) {
                    if ($localeValue !== null && $localeValue !== '') {
                        // Handle file uploads in translatable fields
                        if ($this->isFileUploadValue($localeValue)) {
                            $localeValue = $this->extractFilePath($localeValue);
                        }

                        $this->createAttribute($block, $key, $localeValue, $locale, true, $sortOrder++);
                    }
                }
            } else {
                // Non-translatable field - single attribute without locale
                if ($value !== null && $value !== '') {
                    $this->createAttribute($block, $key, $value, null, false, $sortOrder++);
                }
            }
        }

        // Clear block cache
        $block->clearCache();
    }

    /**
     * Check if an array is locale-keyed (has locale codes as keys)
     */
    protected function isLocaleKeyedArray($value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        $availableLocales = array_keys(config('atelier.locales', ['en' => 'English']));

        // Check if all keys are valid locale codes
        foreach (array_keys($value) as $key) {
            if (!in_array($key, $availableLocales)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a value looks like a file upload value
     */
    protected function isFileUploadValue($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        // Check if it's the {"uuid":"path"} format from file uploads
        foreach ($value as $key => $val) {
            // File upload format has UUID keys and path values
            if (is_string($key) && is_string($val) &&
                preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $key) &&
                str_contains($val, '/')) {
                return true;
            }
            // Also check for simple array of paths ["path1", "path2"]
            if (is_numeric($key) && is_string($val) && str_contains($val, '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract actual file paths from Livewire temporary upload format
     * Always returns array format for FileUpload compatibility
     */
    protected function extractFilePath($value)
    {
        if (!is_array($value)) {
            return is_string($value) && $value !== '' ? [$value] : null;
        }

        // Handle {"uuid":"path"} format - extract the paths
        $paths = [];
        foreach ($value as $key => $val) {
            if (is_string($val) && str_contains($val, '/')) {
                $paths[] = $val;
            }
        }

        return !empty($paths) ? $paths : null;
    }

    /**
     * Create a single attribute
     */
    protected function createAttribute(
        AtelierBlock $block,
        string $key,
        mixed $value,
        ?string $locale,
        bool $translatable,
        int $sortOrder
    ): void {
        // Determine type
        $type = match(true) {
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_bool($value) => 'boolean',
            is_array($value) => 'array',
            default => 'string',
        };

        // Convert arrays to JSON
        if (is_array($value)) {
            $value = json_encode($value);
        }

        // Convert booleans to string
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        $block->attributes()->create([
            'key' => $key,
            'value' => (string) $value,
            'type' => $type,
            'locale' => $locale,
            'translatable' => $translatable,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Get block class by type string
     */
    public function getBlockClass(string $type): ?string
    {
        $blockClasses = $this->getBlockClasses();

        foreach ($blockClasses as $class) {
            if ($class === $type || $class::getBlockIdentifier() === $type) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Generate a new UUID
     */
    public function generateUuid(): string
    {
        return (string) Str::uuid();
    }
}
