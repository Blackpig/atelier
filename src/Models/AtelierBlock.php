<?php

namespace BlackpigCreatif\Atelier\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class AtelierBlock extends Model
{
    protected $fillable = [
        'blockable_type',
        'blockable_id',
        'block_type',
        'position',
        'uuid',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'position' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('atelier.table_prefix', 'atelier_').'blocks';
    }

    protected static function booted(): void
    {
        static::creating(function (AtelierBlock $block) {
            if (! $block->uuid) {
                $block->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(AtelierBlockAttribute::class, 'block_id')
            ->orderBy('sort_order');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('id');
    }

    // Get hydrated block instance
    protected function instance(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hydrateBlock()
        );
    }

    /**
     * Get translatable fields for a block class
     * Tries schema scanning first (source of truth), falls back to getTranslatableFields() method
     *
     * @param string $class
     * @return array
     */
    protected function getTranslatableFieldsForBlock(string $class): array
    {
        // For frontend performance, prefer the legacy method if it exists
        // It's faster than schema scanning and is sufficient for rendering
        if (method_exists($class, 'getTranslatableFields')) {
            return $class::getTranslatableFields();
        }

        // Fallback: scan schema if getTranslatableFields() doesn't exist
        // This ensures blocks without the method still work correctly
        return $this->scanSchemaForTranslatableFields($class);
    }

    /**
     * Scan block schema to find translatable fields
     * Note: This is primarily for admin/saving. Frontend uses getTranslatableFields() for performance.
     *
     * @param string $blockClass
     * @return array
     */
    protected function scanSchemaForTranslatableFields(string $blockClass): array
    {
        if (! class_exists($blockClass) || ! method_exists($blockClass, 'getSchema')) {
            return [];
        }

        try {
            $schema = $blockClass::getSchema();
            $translatableFields = [];

            $this->recursiveScanForTranslatable($schema, $translatableFields);

            return $translatableFields;
        } catch (\Exception $e) {
            // If schema scanning fails, return empty array
            return [];
        }
    }

    /**
     * Recursively scan schema components to find translatable fields
     *
     * @param array $components
     * @param array &$translatableFields
     * @return void
     */
    protected function recursiveScanForTranslatable(array $components, array &$translatableFields): void
    {
        foreach ($components as $component) {
            if (! is_object($component)) {
                continue;
            }

            // Try to get statePath property (for Form fields with ->translatable())
            $name = null;

            if (property_exists($component, 'statePath')) {
                try {
                    $reflection = new \ReflectionProperty($component, 'statePath');
                    $reflection->setAccessible(true);
                    $name = $reflection->getValue($component);
                } catch (\Exception $e) {
                    // Continue
                }
            }

            // Check if name matches translatable pattern: fieldname.locale (e.g. "headline.en")
            if ($name && preg_match('/^(.+)\.([a-z]{2})(_[A-Z]{2})?$/', $name, $matches)) {
                $baseFieldName = $matches[1];

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
                                $this->recursiveScanForTranslatable($children, $translatableFields);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Continue
                }
            }
        }
    }

    public function hydrateBlock(?string $locale = null): mixed
    {
        $locale = $locale ?? app()->getLocale();

        // Check cache first
        $cacheKey = $this->getCacheKey($locale);

        if (config('atelier.cache.enabled')) {
            $cached = cache()->get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        // Instantiate block class
        $class = $this->block_type;

        if (! class_exists($class)) {
            throw new \Exception("Block class {$class} not found");
        }

        $instance = new $class;

        // Get translatable fields for this block
        // Try to use schema scanning if available, fallback to getTranslatableFields()
        $translatableFields = $this->getTranslatableFieldsForBlock($class);

        // Load all attributes
        $attributes = $this->attributes()->get();

        $data = [];

        // Separate collection and non-collection attributes
        $collectionAttributes = $attributes->whereNotNull('collection_name');
        $nonCollectionAttributes = $attributes->whereNull('collection_name');

        // Process non-collection attributes
        foreach ($nonCollectionAttributes->groupBy('key') as $key => $attributeGroup) {
            if (in_array($key, $translatableFields)) {
                // Build translation array
                $translations = [];
                foreach ($attributeGroup as $attr) {
                    if ($attr->locale) {
                        $translations[$attr->locale] = $attr->getCastedValue();
                    }
                }
                $data[$key] = $translations;
            } else {
                // Non-translatable - just the value
                $data[$key] = $attributeGroup->first()->getCastedValue();
            }
        }

        // Process collection attributes (e.g., repeaters like CTAs)
        foreach ($collectionAttributes->groupBy('collection_name') as $collectionName => $collectionGroup) {
            $collection = [];

            // Group by collection_index to build each item in the collection
            foreach ($collectionGroup->groupBy('collection_index') as $index => $itemAttributes) {
                $item = [];

                // Group by key within this collection item
                foreach ($itemAttributes->groupBy('key') as $key => $keyGroup) {
                    if (in_array($key, $translatableFields)) {
                        // Build translation array
                        $translations = [];
                        foreach ($keyGroup as $attr) {
                            if ($attr->locale) {
                                $translations[$attr->locale] = $attr->getCastedValue();
                            }
                        }
                        $item[$key] = $translations;
                    } else {
                        // Non-translatable - just the value
                        $item[$key] = $keyGroup->first()->getCastedValue();
                    }
                }

                $collection[$index] = $item;
            }

            // Sort by index and reindex
            ksort($collection);
            $data[$collectionName] = array_values($collection);
        }

        // Fill instance with data
        $instance->fill($data)
            ->setBlockId($this->id)
            ->setLocale($locale);

        // Cache the instance
        if (config('atelier.cache.enabled')) {
            cache()->put($cacheKey, $instance, config('atelier.cache.ttl'));
        }

        return $instance;
    }

    private function getCacheKey(string $locale): string
    {
        return config('atelier.cache.prefix')."{$this->id}_{$locale}";
    }

    public function clearCache(): void
    {
        $locales = array_keys(config('app.locales', ['en' => 'English']));

        foreach ($locales as $locale) {
            cache()->forget($this->getCacheKey($locale));
        }
    }

    /**
     * Render the block with automatic hydration
     *
     * @param  string|null  $locale  Optional locale override
     */
    public function render(?string $locale = null): \Illuminate\Contracts\View\View
    {
        $instance = $this->hydrateBlock($locale);

        return $instance->render();
    }
}
