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

    /**
     * Override responsive configuration for hero images
     * Defines media queries for picture element and srcset behavior
     */
    public function getResponsiveConfig(): array
    {
        return [
            // Default fallback image
            'default' => 'large',

            // Srcset configuration (all conversions included)
            'srcset' => [
                'thumb' => true,
                'medium' => true,
                'large' => true,
                'desktop' => true,
                'mobile' => false, // Don't include mobile in srcset (used in picture)
            ],

            // Picture element configuration with media queries
            'picture' => [
                'desktop' => '(min-width: 1024px)',
                'medium' => '(min-width: 768px)',
                'mobile' => '(max-width: 767px)',
                'thumb' => null, // Fallback img
            ],

            // Auto-generated sizes attribute (can be overridden)
            'sizes' => '(min-width: 1024px) 1920px, (min-width: 768px) 800px, 768px',
        ];
    }
}
