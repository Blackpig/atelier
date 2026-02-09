<?php

namespace BlackpigCreatif\Atelier\Conversions;

use BlackpigCreatif\ChambreNoir\Conversions\BaseConversion;

class BlockGalleryConversion extends BaseConversion
{
    /**
     * Default quality for gallery images
     * Balanced quality for multiple images
     */
    protected int $defaultQuality = 85;

    /**
     * Define gallery image conversions
     * Optimized for image galleries, carousels, and image blocks
     */
    protected function define(): array
    {
        return [
            // Small thumbnail for admin preview and grid views
            'thumb' => [
                'width' => 200,
                'height' => 200,
                'fit' => 'crop',
            ],

            // Medium size for gallery grids
            'medium' => [
                'width' => 800,
                'height' => 600,
                'fit' => 'contain',
            ],

            // Large size for lightbox/modal view
            'large' => [
                'width' => 1600,
                'height' => 1200,
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
