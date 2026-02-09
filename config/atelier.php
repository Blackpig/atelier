<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Define the locales available for content translation in Atelier.
    | The first locale in the array will be treated as the default.
    |
    */
    'locales' => [
        'en' => 'English',
        'fr' => 'Français',
        // 'es' => 'Español',
        // 'de' => 'Deutsch',
    ],
    
    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Block Form Modal
    |--------------------------------------------------------------------------
    |
    | Configure the appearance of the block editing modal.
    |
    */
    'modal' => [
        'width' => '5xl', // Options: xl, 2xl, 3xl, 4xl, 5xl, 6xl, 7xl, screen
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for Atelier's database tables. Useful if you need to avoid
    | naming conflicts with existing tables.
    |
    */
    'table_prefix' => 'atelier_',
    
    /*
    |--------------------------------------------------------------------------
    | Block Registration
    |--------------------------------------------------------------------------
    |
    | Define the default blocks available in BlockManager components.
    | These blocks will be used when ->blocks() is not called or called empty.
    | Override per-resource by calling ->blocks([...]) with specific classes.
    |
    */
    'blocks' => [
       /*  \BlackpigCreatif\Atelier\Blocks\HeroBlock::class,
        \BlackpigCreatif\Atelier\Blocks\TextBlock::class,
        \BlackpigCreatif\Atelier\Blocks\ImageBlock::class,
        \BlackpigCreatif\Atelier\Blocks\TextWithImageBlock::class,
        \BlackpigCreatif\Atelier\Blocks\TextWithTwoImagesBlock::class,
        \BlackpigCreatif\Atelier\Blocks\GalleryBlock::class,
        \BlackpigCreatif\Atelier\Blocks\CarouselBlock::class, */
        \BlackpigCreatif\Atelier\Blocks\VideoBlock::class,
        // Add custom blocks here:
        // \App\Blocks\CustomBlock::class,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Display Features
    |--------------------------------------------------------------------------
    |
    | Configure global display options available to all blocks.
    | These can be extended or modified per your design system.
    |
    | NOTE: Background colors require BOTH 'class' and 'color' properties:
    | - 'class': The Tailwind CSS class applied to the block wrapper
    | - 'color': Hex value used for color swatches in the admin UI
    |
    | The 'color' property is necessary because Tailwind's JIT compiler cannot
    | generate classes from dynamic Alpine.js bindings at runtime. We use inline
    | styles for the admin swatches while the 'class' is applied to the frontend.
    |
    | FUTURE IMPROVEMENT: These colors should ideally be defined once in a
    | Filament admin theme and referenced here to avoid duplication between
    | tailwind.config/app.css and this config file.
    |
    */
    'features' => [
        'backgrounds' => [
            'enabled' => true,
            'options' => [
                'white' => [
                    'label' => 'White',
                    'class' => 'bg-white',
                    'color' => '#FFFFFF'
                ],
                'gray-50' => [
                    'label' => 'Light Gray',
                    'class' => 'bg-gray-50',
                    'color' => '#F9FAFB'
                ],
                'gray-100' => [
                    'label' => 'Gray',
                    'class' => 'bg-gray-100',
                    'color' => '#F3F4F6'
                ],
                'primary' => [
                    'label' => 'Primary',
                    'class' => 'bg-primary-500 text-white',
                    'color' => '#6366F1'
                ],
                'secondary' => [
                    'label' => 'Secondary',
                    'class' => 'bg-secondary-500 text-white',
                    'color' => '#EC4899'
                ],
            ],
        ],
        
        'spacing' => [
            'enabled' => true,
            'options' => [
                'none' => ['label' => 'None', 'value' => 0],
                'xs' => ['label' => 'Extra Small', 'value' => 4],
                'sm' => ['label' => 'Small', 'value' => 8],
                'md' => ['label' => 'Medium', 'value' => 16],
                'lg' => ['label' => 'Large', 'value' => 24],
                'xl' => ['label' => 'Extra Large', 'value' => 32],
            ],
        ],
        
        'width' => [
            'enabled' => true,
            'options' => [
                'container' => ['label' => 'Container', 'class' => 'container mx-auto px-4'],
                'narrow' => ['label' => 'Narrow', 'class' => 'max-w-3xl mx-auto px-4'],
                'wide' => ['label' => 'Wide', 'class' => 'max-w-7xl mx-auto px-4'],
                'full' => ['label' => 'Full Width', 'class' => 'w-full px-4'],
            ],
        ],

        'dividers' => [
            'enabled' => true,
            'options' => [
                'none' => ['label' => 'None', 'class' => '', 'component' => null],
                'diagonal-left-right' => ['label' => 'Diagonal (Bottom Left to Top Right)', 'class' => 'atelier-divider-diagonal-lr', 'component' => 'atelier::dividers.diagonal-lr'],
                'diagonal-right-left' => ['label' => 'Diagonal (Bottom Right to Top Left)', 'class' => 'atelier-divider-diagonal-rl', 'component' => 'atelier::dividers.diagonal-rl'],
                'curve-up' => ['label' => 'Curved Line Up', 'class' => 'atelier-divider-curve-up', 'component' => 'atelier::dividers.curve-up'],
                'curve-down' => ['label' => 'Curved Line Down', 'class' => 'atelier-divider-curve-down', 'component' => 'atelier::dividers.curve-down'],
                'wave' => ['label' => 'Wave', 'class' => 'atelier-divider-wave', 'component' => 'atelier::dividers.wave'],
                'triangle' => ['label' => 'Triangle', 'class' => 'atelier-divider-triangle', 'component' => 'atelier::dividers.triangle'],
            ],
        ],

        'button_styles' => [
            'enabled' => true,
            'options' => [
                'primary' => [
                    'label' => 'Primary',
                    'class' => 'btn btn-primary',
                ],
                'secondary' => [
                    'label' => 'Secondary',
                    'class' => 'btn btn-secondary',
                ],
                'alternate' => [
                    'label' => 'Alternate',
                    'class' => 'btn btn-alternate',
                ],
            ],
        ],
    ],
     
    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Enable caching to improve performance when hydrating blocks.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'atelier_block_',
    ],
];
