<?php

namespace BlackpigCreatif\Atelier\Conversions;

use BlackpigCreatif\ChambreNoir\Conversions\BaseConversion;

class BlockTwoImageConversion extends BaseConversion
{
    /**
     * Default quality for single image
     */
    protected int $defaultQuality = 85;

    /**
     * Define single image conversions
     * Optimized for single image blocks
     */
    protected function define(): array
    {
        return [
            'thumb' => [
                'width' => 200,
                'height' => 200,
                'fit' => 'crop',
            ],

            'medium_1' => [
                'width' => 800,
                'height' => 500,
                'fit' => 'contain',
            ],

            'medium_2' => [
                'width' => 400,
                'height' => 500,
                'fit' => 'contain',
            ],

            'large_1' => [
                'width' => 1600,
                'height' => 1000,
                'fit' => 'max',
            ],

            'large_2' => [
                'width' => 800,
                'height' => 1000,
                'fit' => 'max',
            ],
        ];
    }

    /**
     * Override responsive configuration for gallery images
     * Defines media queries for picture element and srcset behavior
     */
    public function getResponsiveConfig(): array
    {
        return [
            // Default fallback image
            'default' => 'medium',

            // Srcset configuration (all conversions included)
            'srcset' => [
                'thumb' => true,
                'medium' => true,
                'large' => true,
            ],

            // Picture element configuration with media queries
            'picture' => [
                'large' => '(min-width: 1024px)',
                'medium' => '(min-width: 640px)',
                'thumb' => null, // Fallback img
            ],

            // Auto-generated sizes attribute (can be overridden)
            'sizes' => '(min-width: 1024px) 1600px, (min-width: 640px) 800px, 200px',
        ];
    }
}
