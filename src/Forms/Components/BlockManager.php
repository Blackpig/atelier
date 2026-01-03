<?php

namespace BlackpigCreatif\Atelier\Forms\Components;

use BlackpigCreatif\Atelier\Models\AtelierBlock;
use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Support\Str;

class BlockManager extends Field
{
    protected string $view = 'atelier::forms.components.block-manager';

    protected array|Closure $blockClasses = [];

    protected bool|Closure $isAddable = true;

    protected bool|Closure $isEditable = true;

    protected bool|Closure $isDeletable = true;

    protected bool|Closure $isReorderable = true;

    protected bool|Closure $isCollapsible = false;

    protected ?string $addButtonLabel = null;

    /**
     * Set the available block classes
     * If called without arguments or with empty array, will use config('atelier.blocks')
     */
    public function blocks(array|Closure $blockClasses = []): static
    {
        $this->blockClasses = $blockClasses;

        return $this;
    }

    /**
     * Get the registered block classes
     * Falls back to config('atelier.blocks') if not explicitly set
     */
    public function getBlockClasses(): array
    {
        $blocks = $this->evaluate($this->blockClasses);

        // If no blocks explicitly set, use config default
        if (empty($blocks)) {
            $blocks = config('atelier.blocks', []);
        }

        return $blocks;
    }

    /**
     * Get block metadata for all registered block classes
     */
    public function getBlockMetadata(): array
    {
        $blockClasses = $this->getBlockClasses();
        $metadata = [];

        foreach ($blockClasses as $blockClass) {
            if (! class_exists($blockClass)) {
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
            $iconPath = __DIR__.'/../../../resources/views/components/icons/'.$iconName.'.blade.php';

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
    public function addable(bool|Closure $condition = true): static
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
    public function editable(bool|Closure $condition = true): static
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
    public function deletable(bool|Closure $condition = true): static
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
    public function reorderable(bool|Closure $condition = true): static
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
    public function collapsible(bool|Closure $condition = true): static
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
            \Log::info('BlockManager: afterStateHydrated started', [
                'has_record' => (bool) $record,
                'record_id' => $record?->id,
            ]);

            // Auto-detect if we're on a ViewRecord page and disable editing
            try {
                $livewire = $component->getLivewire();
                if ($livewire instanceof \Filament\Resources\Pages\ViewRecord) {
                    $component->editable(false);
                    $component->deletable(false);
                    $component->addable(false);
                    $component->reorderable(false);
                }
            } catch (\Exception $e) {
                // If we can't detect, continue with default behavior
            }

            if (! $record || ! method_exists($record, 'blocks')) {
                \Log::warning('BlockManager: Hydration stopped - no record or blocks method');
                return;
            }

            $blocks = $record->blocks()
                ->ordered()
                ->with('attributes')
                ->get();

            \Log::info('BlockManager: Loaded blocks from database', [
                'blocks_count' => $blocks->count(),
            ]);

            if ($blocks->isEmpty()) {
                $component->state([]);
                \Log::info('BlockManager: No blocks found, setting empty state');
                return;
            }

            $blockData = [];

            foreach ($blocks as $block) {
                $extractedData = $component->extractBlockAttributes($block);

                \Log::info('BlockManager: Extracted block data', [
                    'block_id' => $block->id,
                    'uuid' => $block->uuid,
                    'type' => $block->block_type,
                    'extracted_data_keys' => array_keys($extractedData),
                    'extracted_data' => $extractedData,
                ]);

                $blockData[] = [
                    'uuid' => $block->uuid,
                    'type' => $block->block_type,
                    'data' => $extractedData,
                    'position' => $block->position,
                    'is_published' => $block->is_published,
                ];
            }

            \Log::info('BlockManager: Setting hydrated state', [
                'total_blocks' => count($blockData),
            ]);

            $component->state($blockData);
        });

        // Save blocks to database
        $this->saveRelationshipsUsing(function ($component, $state) {
            \Log::info('BlockManager: saveRelationshipsUsing started', [
                'state_count' => count($state ?? []),
                'state' => $state,
            ]);

            $record = $component->getRecord();

            if (! $record || ! method_exists($record, 'blocks')) {
                \Log::warning('BlockManager: Record missing or no blocks() method', [
                    'has_record' => (bool) $record,
                    'record_class' => $record ? get_class($record) : null,
                ]);
                return;
            }

            // Get current block UUIDs from state
            $currentUuids = collect($state ?? [])->pluck('uuid')->filter()->toArray();

            \Log::info('BlockManager: Current UUIDs', [
                'current_uuids' => $currentUuids,
            ]);

            // Delete blocks that are no longer in state
            $blocksToDelete = $record->blocks()->whereNotIn('uuid', $currentUuids)->get();

            \Log::info('BlockManager: Deleting orphaned blocks', [
                'count' => $blocksToDelete->count(),
                'uuids' => $blocksToDelete->pluck('uuid')->toArray(),
            ]);

            $blocksToDelete->each(function ($block) {
                // Delete all attributes first
                $block->attributes()->delete();
                // Delete the block
                $block->delete();
            });

            // Create or update blocks
            foreach ($state ?? [] as $index => $blockData) {
                \Log::info('BlockManager: Processing block', [
                    'index' => $index,
                    'uuid' => $blockData['uuid'] ?? null,
                    'type' => $blockData['type'] ?? null,
                    'data_keys' => array_keys($blockData['data'] ?? []),
                ]);

                try {
                    $block = $record->blocks()->updateOrCreate(
                        ['uuid' => $blockData['uuid']],
                        [
                            'block_type' => $blockData['type'],
                            'position' => $index,
                            'is_published' => $blockData['is_published'] ?? true,
                        ]
                    );

                    \Log::info('BlockManager: Block saved', [
                        'block_id' => $block->id,
                        'uuid' => $block->uuid,
                        'was_recently_created' => $block->wasRecentlyCreated,
                    ]);

                    // Save block attributes
                    $component->saveBlockAttributes($block, $blockData['data'] ?? [], $blockData['type']);

                    \Log::info('BlockManager: Attributes saved for block', [
                        'block_id' => $block->id,
                        'attribute_count' => $block->attributes()->count(),
                    ]);

                } catch (\Exception $e) {
                    \Log::error('BlockManager: Error saving block', [
                        'uuid' => $blockData['uuid'] ?? null,
                        'type' => $blockData['type'] ?? null,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw $e;
                }
            }

            \Log::info('BlockManager: saveRelationshipsUsing completed successfully');
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

        if (! class_exists($blockClass)) {
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
        \Log::info('BlockManager: saveBlockAttributes started', [
            'block_id' => $block->id,
            'block_type' => $blockType,
            'data_keys' => array_keys($data),
            'data' => $data,
        ]);

        if (! class_exists($blockType)) {
            \Log::error('BlockManager: Block type class not found', [
                'block_type' => $blockType,
            ]);
            return;
        }

        $translatableFields = $blockType::getTranslatableFields();

        \Log::info('BlockManager: Translatable fields', [
            'translatable_fields' => $translatableFields,
        ]);

        // Delete existing attributes
        $deletedCount = $block->attributes()->count();
        $block->attributes()->delete();

        \Log::info('BlockManager: Deleted existing attributes', [
            'count' => $deletedCount,
        ]);

        $sortOrder = 0;
        $defaultLocale = config('atelier.default_locale', 'en');
        $createdAttributesCount = 0;

        foreach ($data as $key => $value) {
            // Skip if value is null or empty string
            if ($value === null || $value === '') {
                \Log::debug('BlockManager: Skipping null/empty value', [
                    'key' => $key,
                ]);
                continue;
            }

            if (in_array($key, $translatableFields)) {
                // Translatable field - MUST be saved with locale keys
                \Log::debug('BlockManager: Processing translatable field', [
                    'key' => $key,
                    'is_array' => is_array($value),
                ]);

                // If value is not an array, wrap it in default locale
                if (! is_array($value)) {
                    $value = [$defaultLocale => $value];
                } elseif (! empty($value) && ! $this->isLocaleKeyedArray($value)) {
                    // It's an array but not locale-keyed, wrap in default locale
                    $value = [$defaultLocale => $value];
                }

                // Create one attribute per locale
                foreach ($value as $locale => $localeValue) {
                    if ($localeValue !== null && $localeValue !== '') {
                        $this->createAttribute($block, $key, $localeValue, $locale, true, $sortOrder++);
                        $createdAttributesCount++;
                    }
                }
            } else {
                // Non-translatable field - single attribute without locale
                \Log::debug('BlockManager: Processing non-translatable field', [
                    'key' => $key,
                    'value_type' => gettype($value),
                ]);

                $this->createAttribute($block, $key, $value, null, false, $sortOrder++);
                $createdAttributesCount++;
            }
        }

        \Log::info('BlockManager: saveBlockAttributes completed', [
            'block_id' => $block->id,
            'created_attributes' => $createdAttributesCount,
            'final_attribute_count' => $block->attributes()->count(),
        ]);

        // Clear block cache
        $block->clearCache();
    }

    /**
     * Check if an array is locale-keyed (has locale codes as keys)
     */
    protected function isLocaleKeyedArray($value): bool
    {
        if (! is_array($value) || empty($value)) {
            return false;
        }

        $availableLocales = array_keys(config('atelier.locales', ['en' => 'English']));

        // Check if all keys are valid locale codes
        foreach (array_keys($value) as $key) {
            if (! in_array($key, $availableLocales)) {
                return false;
            }
        }

        return true;
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
        $type = match (true) {
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_bool($value) => 'boolean',
            is_array($value) => 'array',
            default => 'string',
        };

        // Convert arrays to JSON
        $originalValue = $value;
        if (is_array($value)) {
            $value = json_encode($value);
        }

        // Convert booleans to string
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        try {
            $attribute = $block->attributes()->create([
                'key' => $key,
                'value' => (string) $value,
                'type' => $type,
                'locale' => $locale,
                'translatable' => $translatable,
                'sort_order' => $sortOrder,
            ]);

            \Log::debug('BlockManager: Attribute created', [
                'attribute_id' => $attribute->id,
                'key' => $key,
                'type' => $type,
                'locale' => $locale,
                'translatable' => $translatable,
                'value_length' => strlen($value),
            ]);

        } catch (\Exception $e) {
            \Log::error('BlockManager: Failed to create attribute', [
                'block_id' => $block->id,
                'key' => $key,
                'type' => $type,
                'locale' => $locale,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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
