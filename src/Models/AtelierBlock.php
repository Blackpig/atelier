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
        
        $this->table = config('atelier.table_prefix', 'atelier_') . 'blocks';
    }
    
    protected static function booted(): void
    {
        static::creating(function (AtelierBlock $block) {
            if (!$block->uuid) {
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
            get: fn() => $this->hydrateBlock()
        );
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
        
        if (!class_exists($class)) {
            throw new \Exception("Block class {$class} not found");
        }
        
        $instance = new $class();
        
        // Get translatable fields for this block
        $translatableFields = $class::getTranslatableFields();
        
        // Load all attributes
        $attributes = $this->attributes()->get();
        
        $data = [];
        
        // Group by key
        foreach ($attributes->groupBy('key') as $key => $attributeGroup) {
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
        return config('atelier.cache.prefix') . "{$this->id}_{$locale}";
    }
    
    public function clearCache(): void
    {
        $locales = array_keys(config('atelier.locales', ['en' => 'English']));
        
        foreach ($locales as $locale) {
            cache()->forget($this->getCacheKey($locale));
        }
    }
}
