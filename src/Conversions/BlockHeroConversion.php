<?php

namespace BlackpigCreatif\Atelier\Conversions;

use BlackpigCreatif\ChambreNoir\Conversions\BaseConversion;

class BlockHeroConversion extends BaseConversion
{
    /**
     * Default quality for hero images
     * Higher quality for hero sections since they're prominent
     */
    protected int $defaultQuality = 90;

    /**
     * Define hero image conversions
     * Optimized for full-width hero sections in blocks
     */
    protected function define(): array
    {
        return [
            // Small thumbnail for admin preview and mobile
            'thumb' => [
                'width' => 200,
                'height' => 200,
                'fit' => 'crop',
            ],

            // Medium size for tablets and smaller desktops
            'medium' => [
                'width' => 800,
                'height' => 600,
                'fit' => 'contain',
            ],

            // Large size for desktop displays
            'large' => [
                'width' => 1920,
                'height' => 1080,
                'fit' => 'max',
            ],

            // Desktop-specific (same as large, for backward compatibility)
            'desktop' => [
                'width' => 1920,
                'height' => 1080,
                'fit' => 'max',
            ],

            // Mobile portrait orientation
            'mobile' => [
                'width' => 768,
                'height' => 1024,
                'fit' => 'max',
            ],
        ];
    }
}
