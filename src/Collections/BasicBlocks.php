<?php

namespace BlackpigCreatif\Atelier\Collections;

use BlackpigCreatif\Atelier\Abstracts\BaseBlockCollection;
use BlackpigCreatif\Atelier\Blocks\HeroBlock;
use BlackpigCreatif\Atelier\Blocks\TextBlock;
use BlackpigCreatif\Atelier\Blocks\TextWithImageBlock;

class BasicBlocks extends BaseBlockCollection
{
    public function getBlocks(): array
    {
        return [
            HeroBlock::class,
            TextBlock::class,
            TextWithImageBlock::class,
        ];
    }

    public static function getLabel(): string
    {
        return 'Basic Content Blocks';
    }

    public static function getDescription(): ?string
    {
        return 'Essential blocks for standard content pages: hero sections, text, and text with images.';
    }
}
