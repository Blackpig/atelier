<?php

namespace BlackpigCreatif\Atelier\Collections;

use BlackpigCreatif\Atelier\Abstracts\BaseBlockCollection;
use BlackpigCreatif\Atelier\Blocks\CarouselBlock;
use BlackpigCreatif\Atelier\Blocks\GalleryBlock;
use BlackpigCreatif\Atelier\Blocks\ImageBlock;
use BlackpigCreatif\Atelier\Blocks\VideoBlock;

class MediaBlocks extends BaseBlockCollection
{
    public function getBlocks(): array
    {
        return [
            ImageBlock::class,
            VideoBlock::class,
            GalleryBlock::class,
            CarouselBlock::class,
        ];
    }

    public static function getLabel(): string
    {
        return 'Media Blocks';
    }

    public static function getDescription(): ?string
    {
        return 'Visual content blocks for images, videos, galleries, and carousels.';
    }
}
