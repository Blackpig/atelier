<?php

namespace Blackpigcreatif\Atelier\Observers;

use Blackpigcreatif\Atelier\Models\AtelierBlock;

class AtelierBlockObserver
{
    public function updated(AtelierBlock $block): void
    {
        $block->clearCache();
    }
    
    public function deleted(AtelierBlock $block): void
    {
        $block->clearCache();
    }
}
