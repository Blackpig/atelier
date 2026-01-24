<?php

namespace BlackpigCreatif\Atelier\Forms\Components;

use BlackpigCreatif\Atelier\Models\AtelierBlock;
use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Support\Str;

class BlockManager extends Field
{
    protected string $view = 'atelier::forms.components.block-manager';

    protected array|string|Closure $blockClasses = [];

    protected bool|Closure $isAddable = true;

    protected bool|Closure $isEditable = true;

    protected bool|Closure $isDeletable = true;

    protected bool|Closure $isReorderable = true;

    protected bool|Closure $isCollapsible = false;

    protected ?string $addButtonLabel = null;

    protected array $fieldConfigurations = [];

    /**
     * Set the available block classes
     *
     * Accepts:
     * - Array of block classes: [HeroBlock::class, TextBlock::class]
     * - Array of BlockCollection classes: [BasicBlocks::class, EcommerceBlocks::class]
     * - Mixed array: [BasicBlocks::class, CustomBlock::class]
     * - Single BlockCollection class string: BasicBlocks::class
     * - Closure returning array: fn() => [...]
     * - Empty/not set: Falls back to config('atelier.blocks')
     */
    public function blocks(array|string|Closure $blockClasses = []): static
    {
        $this->blockClasses = $blockClasses;

        return $this;
    }

    /**
     * Get the registered block classes
     * Falls back to config('atelier.blocks') if not explicitly set
     * Resolves BlockCollection classes to their block arrays
     */
    public function getBlockClasses(): array
    {
        $blocks = $this->evaluate($this->blockClasses);

        // If no blocks explicitly set, use config default
        if (empty($blocks)) {
            $blocks = config('atelier.blocks', []);
        }

        // If it's a single BlockCollection class string, convert to array
        if (is_string($blocks)) {
            $blocks = [$blocks];
        }

        // Resolve BlockCollections to their block classes
        return $this->resolveBlockCollections($blocks);
    }

    /**
     * Resolve BlockCollection classes to their block arrays
     *
     * @return array<int, class-string>
     */
    protected function resolveBlockCollections(array $blocks): array
    {
        $resolved = [];

        foreach ($blocks as $block) {
            // Check if this is a BlockCollection class
            if (is_string($block) && class_exists($block)) {
                $reflection = new \ReflectionClass($block);

                // If it's a BlockCollection, get its blocks
                if ($reflection->isSubclassOf(\BlackpigCreatif\Atelier\Abstracts\BaseBlockCollection::class)) {
                    $collectionBlocks = $block::make();
                    $resolved = array_merge($resolved, $collectionBlocks);

                    continue;
                }
            }

            // Otherwise, it's a regular block class
            $resolved[] = $block;
        }

        // Remove duplicates and reindex
        return array_values(array_unique($resolved));
    }

    /**
     * Configure a specific field in a block
     *
     * @param string $blockClass The block class to configure
     * @param string $fieldName The field name to modify
     * @param array $config Configuration array (e.g., ['maxItems' => 1])
     * @return static
     */
    public function configureField(string $blockClass, string $fieldName, array $config): static
    {
        $key = $blockClass . '::' . $fieldName;
        $this->fieldConfigurations[$key] = $config;

        return $this;
    }

    /**
     * Get field configurations formatted for frontend
     *
     * Returns configurations nested by block class:
     * [
     *   'App\Blocks\HeroBlock' => ['ctas' => ['maxItems' => 2]],
     *   'App\Blocks\TextBlock' => ['heading' => ['maxLength' => 100]]
     * ]
     *
     * @return array
     */
    public function getFieldConfigurations(): array
    {
        $nested = [];

        foreach ($this->fieldConfigurations as $key => $config) {
            // Key format: "BlockClass::fieldName"
            if (str_contains($key, '::')) {
                [$blockClass, $fieldName] = explode('::', $key, 2);

                if (! isset($nested[$blockClass])) {
                    $nested[$blockClass] = [];
                }

                $nested[$blockClass][$fieldName] = $config;
            }
        }

        return $nested;
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
    protected function getIconSvg(mixed $icon): ?string
    {
        // Handle HtmlString (custom SVG)
        if ($icon instanceof \Illuminate\Contracts\Support\Htmlable) {
            return $icon->toHtml();
        }

        // Handle string icons
        if (is_string($icon)) {
            // Check if it's an Atelier custom icon
            if (str_starts_with($icon, 'atelier.icons.')) {
                $iconName = str_replace('atelier.icons.', '', $icon);
                $iconPath = __DIR__.'/../../../resources/views/components/icons/'.$iconName.'.blade.php';

                if (file_exists($iconPath)) {
                    return file_get_contents($iconPath);
                }
            }

            // For heroicons, render them using Filament's icon system
            if (str_starts_with($icon, 'heroicon-')) {
                return $this->renderHeroicon($icon);
            }
        }

        // For other cases, return null
        return null;
    }

    /**
     * Render a Heroicon to SVG string
     */
    protected function renderHeroicon(string $icon): ?string
    {
        try {
            // Use Filament's icon rendering
            $iconHtml = \Filament\Support\Facades\FilamentIcon::resolve($icon);

            if ($iconHtml) {
                return $iconHtml;
            }

            // Fallback: Try to render using Blade
            return \Illuminate\Support\Facades\Blade::render(
                '<x-filament::icon :icon="$icon" class="w-6 h-6" />',
                ['icon' => $icon]
            );
        } catch (\Exception $e) {
            \Log::warning('Failed to render heroicon', [
                'icon' => $icon,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
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

                // Include is_published in the data for form hydration
                $extractedData['is_published'] = $block->is_published;

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
                    'is_published_value' => $blockData['is_published'] ?? 'NOT SET',
                    'full_block_data' => $blockData,
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

        // Scan actual schema to determine which fields are currently translatable
        // This ensures schema is source of truth, not hardcoded getTranslatableFields()
        $schemaTranslatableFields = $this->getSchemaTranslatableFields($blockClass);

        \Log::info('🔍 Schema-based translatable fields', [
            'block_class' => $blockClass,
            'schema_translatable' => $schemaTranslatableFields,
            'legacy_method' => $blockClass::getTranslatableFields(),
        ]);

        $attributes = $block->attributes()->get();
        $data = [];

        // Separate collection-based attributes from simple attributes
        $collectionAttributes = $attributes->whereNotNull('collection_name');
        $simpleAttributes = $attributes->whereNull('collection_name');

        // Process simple (non-collection) attributes first
        foreach ($simpleAttributes->groupBy('key') as $key => $attributeGroup) {
            // Check if field is CURRENTLY marked as translatable in schema
            $isCurrentlyTranslatable = in_array($key, $schemaTranslatableFields);

            // Check if data HAS locale stamps (was translatable at some point)
            $hasLocaleStamps = $attributeGroup->whereNotNull('locale')->isNotEmpty();

            if ($isCurrentlyTranslatable && $hasLocaleStamps) {
                // Case 1: Field IS translatable AND has locale data → return as locale-keyed array
                $translations = [];

                foreach ($attributeGroup as $attr) {
                    if ($attr->locale) {
                        $translations[$attr->locale] = $attr->getCastedValue();
                    }
                }

                $data[$key] = $translations;
            } elseif ($isCurrentlyTranslatable && ! $hasLocaleStamps) {
                // Case 4: Field IS translatable NOW but has NO locale stamps (was non-translatable)
                // Wrap plain value in current locale array
                $firstAttr = $attributeGroup->first();
                $currentLocale = app()->getLocale();

                $data[$key] = $firstAttr ? [$currentLocale => $firstAttr->getCastedValue()] : [];

                \Log::info('📝 Case 4: Wrapping non-locale data in current locale', [
                    'key' => $key,
                    'original_value' => $firstAttr?->getCastedValue(),
                    'wrapped_value' => $data[$key],
                ]);
            } elseif (! $isCurrentlyTranslatable && $hasLocaleStamps) {
                // Case 2: Field is NOT CURRENTLY translatable but DB has locale stamps
                // Extract only current locale's value as plain string
                $currentLocale = app()->getLocale();
                $defaultLocale = config('atelier.default_locale', 'en');

                // Try current locale first, then default locale, then first available
                $currentLocaleAttr = $attributeGroup->where('locale', $currentLocale)->first();
                $defaultLocaleAttr = $attributeGroup->where('locale', $defaultLocale)->first();
                $firstAttr = $attributeGroup->first();

                $data[$key] = ($currentLocaleAttr ?? $defaultLocaleAttr ?? $firstAttr)?->getCastedValue();

                \Log::info('📝 Case 2: Extracting locale value as plain string', [
                    'key' => $key,
                    'extracted_value' => $data[$key],
                    'current_locale' => $currentLocale,
                    'had_current_locale' => $currentLocaleAttr !== null,
                ]);
            } else {
                // Case 3: Non-translatable field with no locale stamps - just the value
                $firstAttr = $attributeGroup->first();
                $data[$key] = $firstAttr ? $firstAttr->getCastedValue() : null;
            }
        }

        // Process collection-based attributes (Repeater fields)
        // Group by collection_name to find all repeater fields
        $collectionNames = $collectionAttributes->pluck('collection_name')->unique();

        foreach ($collectionNames as $collectionName) {
            $collectionAttrs = $collectionAttributes->where('collection_name', $collectionName);
            if ($collectionAttrs->isNotEmpty()) {
                $data[$collectionName] = $this->extractRepeaterCollection($collectionAttrs);
            }
        }

        return $data;
    }

    /**
     * Scan block schema to find which fields are currently marked as translatable
     * This makes the schema the source of truth, not getTranslatableFields()
     *
     * @param string $blockClass
     * @return array<int, string>
     */
    protected function getSchemaTranslatableFields(string $blockClass): array
    {
        if (! class_exists($blockClass) || ! method_exists($blockClass, 'getSchema')) {
            return [];
        }

        $schema = $blockClass::getSchema();
        $translatableFields = [];

        $this->scanSchemaForTranslatableFields($schema, $translatableFields);

        return $translatableFields;
    }

    /**
     * Recursively scan schema components to find translatable fields
     *
     * Fields created with ->translatable() macro are Groups containing locale-specific fields
     * like "headline.en", "headline.fr". We detect these patterns to find translatable fields.
     *
     * @param array $components
     * @param array &$translatableFields
     * @return void
     */
    protected function scanSchemaForTranslatableFields(array $components, array &$translatableFields): void
    {
        foreach ($components as $component) {
            if (! is_object($component)) {
                continue;
            }

            // Try to get name/statePath using reflection to avoid container initialization
            $name = null;

            // Try to get statePath property (for Form fields)
            if (property_exists($component, 'statePath')) {
                try {
                    $reflection = new \ReflectionProperty($component, 'statePath');
                    $reflection->setAccessible(true);
                    $name = $reflection->getValue($component);
                } catch (\Exception $e) {
                    // Continue
                }
            }

            // Try to get name property (for Schema components)
            if (! $name && property_exists($component, 'name')) {
                try {
                    $reflection = new \ReflectionProperty($component, 'name');
                    $reflection->setAccessible(true);
                    $name = $reflection->getValue($component);
                } catch (\Exception $e) {
                    // Continue
                }
            }

            // Check if name matches translatable pattern: fieldname.locale (e.g. "headline.en")
            if ($name && preg_match('/^(.+)\.([a-z]{2})(_[A-Z]{2})?$/', $name, $matches)) {
                $baseFieldName = $matches[1];

                // Add base field name if not already added
                if (! in_array($baseFieldName, $translatableFields)) {
                    $translatableFields[] = $baseFieldName;
                }
            }

            // Recursively scan child components
            if (property_exists($component, 'childComponents')) {
                try {
                    $reflection = new \ReflectionProperty($component, 'childComponents');
                    $reflection->setAccessible(true);
                    $childComponents = $reflection->getValue($component);

                    if (is_array($childComponents)) {
                        foreach ($childComponents as $children) {
                            if (is_array($children)) {
                                $this->scanSchemaForTranslatableFields($children, $translatableFields);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Continue if we can't access the property
                }
            }
        }
    }

    /**
     * Detect if a data key is a repeater field by analyzing structure
     *
     * @param mixed $value
     * @return bool
     */
    protected function isRepeaterData($value): bool
    {
        // Must be an array
        if (!is_array($value) || empty($value)) {
            return false;
        }

        // Check if it's a numeric-indexed array of associative arrays
        // This is the signature of repeater data
        $isNumericKeys = array_keys($value) === range(0, count($value) - 1);

        if (!$isNumericKeys) {
            return false;
        }

        // Check first item - should be an associative array
        $firstItem = reset($value);

        return is_array($firstItem) && !empty($firstItem) && array_keys($firstItem) !== range(0, count($firstItem) - 1);
    }

    /**
     * Detect which fields within repeater items are translatable
     *
     * @param array $items Repeater items
     * @return array<int, string>
     */
    protected function getRepeaterTranslatableFieldsFromData(array $items): array
    {
        $translatableFields = [];

        if (empty($items)) {
            return $translatableFields;
        }

        // Analyze first item to find translatable fields
        $firstItem = reset($items);

        foreach ($firstItem as $key => $value) {
            // If value is a locale-keyed array, it's translatable
            if ($this->isLocaleKeyedArray($value)) {
                $translatableFields[] = $key;
            }
        }

        return $translatableFields;
    }

    /**
     * Extract and reconstruct repeater collection from EAV rows
     *
     * @param \Illuminate\Support\Collection $attributes
     * @return array
     */
    protected function extractRepeaterCollection($attributes): array
    {
        $items = [];

        // Group by collection_index (each represents one repeater item)
        $groupedByIndex = $attributes->groupBy('collection_index');

        foreach ($groupedByIndex as $index => $itemAttrs) {
            $item = [];

            // Group attributes within this item by key
            foreach ($itemAttrs->groupBy('key') as $key => $keyAttrs) {
                // Check if this field has multiple locales (translatable)
                $hasMultipleLocales = $keyAttrs->whereNotNull('locale')->count() > 1 ||
                                      ($keyAttrs->whereNotNull('locale')->count() === 1 && $keyAttrs->first()->translatable);

                if ($hasMultipleLocales || $keyAttrs->first()->translatable) {
                    // Build locale array for translatable fields
                    $item[$key] = [];
                    foreach ($keyAttrs as $attr) {
                        if ($attr->locale) {
                            $item[$key][$attr->locale] = $attr->getCastedValue();
                        }
                    }
                } else {
                    // Single value for non-translatable fields
                    $item[$key] = $keyAttrs->first()->getCastedValue();
                }
            }

            $items[$index] = $item;
        }

        // Sort by index and reindex array
        ksort($items);
        return array_values($items);
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

        // Use schema scanning to determine translatable fields (source of truth)
        // Falls back to getTranslatableFields() if schema scanning fails
        $schemaTranslatableFields = $this->getSchemaTranslatableFields($blockType);
        $legacyTranslatableFields = method_exists($blockType, 'getTranslatableFields')
            ? $blockType::getTranslatableFields()
            : [];

        // Prefer schema-based, fallback to legacy method
        $translatableFields = ! empty($schemaTranslatableFields)
            ? $schemaTranslatableFields
            : $legacyTranslatableFields;

        \Log::info('BlockManager: Translatable fields for saving', [
            'schema_based' => $schemaTranslatableFields,
            'legacy_method' => $legacyTranslatableFields,
            'using' => $translatableFields,
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
            // Skip is_published - it's a model field, not an attribute
            if ($key === 'is_published') {
                continue;
            }

            // Skip if value is null or empty string
            if ($value === null || $value === '') {
                \Log::debug('BlockManager: Skipping null/empty value', [
                    'key' => $key,
                ]);

                continue;
            }

            // Check if this is a Repeater field by data structure
            if ($this->isRepeaterData($value)) {
                \Log::debug('BlockManager: Processing repeater field', [
                    'key' => $key,
                    'items_count' => is_array($value) ? count($value) : 0,
                ]);

                $count = $this->saveRepeaterCollection($block, $key, $value, $sortOrder);
                $createdAttributesCount += $count;
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
     * Save repeater collection as EAV rows
     *
     * @param AtelierBlock $block
     * @param string $collectionName
     * @param array $items
     * @param int &$sortOrder
     * @return int Number of attributes created
     */
    protected function saveRepeaterCollection(
        AtelierBlock $block,
        string $collectionName,
        array $items,
        int &$sortOrder
    ): int {
        $translatableFields = $this->getRepeaterTranslatableFieldsFromData($items);
        $createdCount = 0;

        \Log::info('BlockManager: saveRepeaterCollection started', [
            'collection_name' => $collectionName,
            'items_count' => count($items),
            'translatable_fields' => $translatableFields,
        ]);

        foreach ($items as $index => $item) {
            foreach ($item as $fieldKey => $fieldValue) {
                // Skip null/empty values
                if ($fieldValue === null || $fieldValue === '') {
                    continue;
                }

                if (in_array($fieldKey, $translatableFields)) {
                    // Translatable field - create one attribute per locale
                    if (is_array($fieldValue)) {
                        foreach ($fieldValue as $locale => $localeValue) {
                            if ($localeValue !== null && $localeValue !== '') {
                                $this->createAttribute(
                                    block: $block,
                                    key: $fieldKey,
                                    value: $localeValue,
                                    locale: $locale,
                                    translatable: true,
                                    sortOrder: $sortOrder++,
                                    collectionName: $collectionName,
                                    collectionIndex: $index
                                );
                                $createdCount++;
                            }
                        }
                    }
                } else {
                    // Non-translatable field
                    $this->createAttribute(
                        block: $block,
                        key: $fieldKey,
                        value: $fieldValue,
                        locale: null,
                        translatable: false,
                        sortOrder: $sortOrder++,
                        collectionName: $collectionName,
                        collectionIndex: $index
                    );
                    $createdCount++;
                }
            }
        }

        \Log::info('BlockManager: saveRepeaterCollection completed', [
            'collection_name' => $collectionName,
            'created_attributes' => $createdCount,
        ]);

        return $createdCount;
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
        int $sortOrder,
        ?string $collectionName = null,
        ?int $collectionIndex = null
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
                'collection_name' => $collectionName,
                'collection_index' => $collectionIndex,
            ]);

            \Log::debug('BlockManager: Attribute created', [
                'attribute_id' => $attribute->id,
                'key' => $key,
                'type' => $type,
                'locale' => $locale,
                'translatable' => $translatable,
                'collection_name' => $collectionName,
                'collection_index' => $collectionIndex,
                'value_length' => strlen($value),
            ]);

        } catch (\Exception $e) {
            \Log::error('BlockManager: Failed to create attribute', [
                'block_id' => $block->id,
                'key' => $key,
                'type' => $type,
                'locale' => $locale,
                'collection_name' => $collectionName,
                'collection_index' => $collectionIndex,
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
