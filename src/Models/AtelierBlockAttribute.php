<?php

namespace Blackpigcreatif\Atelier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AtelierBlockAttribute extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    protected $fillable = [
        'block_id',
        'key',
        'value',
        'type',
        'locale',
        'translatable',
        'sort_order',
    ];
    
    protected $casts = [
        'translatable' => 'boolean',
        'sort_order' => 'integer',
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->table = config('atelier.table_prefix', 'atelier_') . 'block_attributes';
    }
    
    public function block(): BelongsTo
    {
        return $this->belongsTo(AtelierBlock::class, 'block_id');
    }
    
    // Cast value based on type
    public function getCastedValue(): mixed
    {
        return match($this->type) {
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'boolean' => (bool) $this->value,
            'array', 'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
