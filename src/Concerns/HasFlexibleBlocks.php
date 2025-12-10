<?php

namespace Blackpigcreatif\Atelier\Concerns;

use Blackpigcreatif\Atelier\Models\AtelierBlock;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFlexibleBlocks
{
    public function blocks(): MorphMany
    {
        return $this->morphMany(AtelierBlock::class, 'blockable')
            ->ordered();
    }
    
    public function publishedBlocks(): MorphMany
    {
        return $this->blocks()->published();
    }
    
    public function renderBlocks(?string $locale = null): string
    {
        return $this->publishedBlocks
            ->map(fn(AtelierBlock $block) => $block->hydrateBlock($locale)->render())
            ->implode('');
    }
}
