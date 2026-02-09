<?php

namespace BlackpigCreatif\Atelier\Collections;

use BlackpigCreatif\Atelier\Abstracts\BaseBlockCollection;
use BlackpigCreatif\Atelier\Blocks\CarouselBlock;
use BlackpigCreatif\Atelier\Blocks\GalleryBlock;
use BlackpigCreatif\Atelier\Blocks\HeroBlock;
use BlackpigCreatif\Atelier\Blocks\ImageBlock;
use BlackpigCreatif\Atelier\Blocks\TextBlock;
use BlackpigCreatif\Atelier\Blocks\TextWithImageBlock;
use BlackpigCreatif\Atelier\Blocks\TextWithTwoImagesBlock;
use BlackpigCreatif\Atelier\Blocks\VideoBlock;

class AllBlocks extends BaseBlockCollection
{
    public function getBlocks(): array
    {
        return [
            HeroBlock::class,
            TextBlock::class,
            TextWithImageBlock::class,
            TextWithTwoImagesBlock::class,
            ImageBlock::class,
            VideoBlock::class,
            GalleryBlock::class,
            CarouselBlock::class,
        ];
    }

    public static function getLabel(): string
    {
        return 'All Blocks';
    }

    public static function getDescription(): ?string
    {
        return 'All available Atelier blocks in one collection.';
    }
}
