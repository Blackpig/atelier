<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Atelier English Language Lines
    |--------------------------------------------------------------------------
    */

    'blocks' => [
        'hero' => [
            'label' => 'Hero Section',
            'description' => 'Full-width hero section with background image, headline, and call-to-action.',
        ],
        'text_with_two_images' => [
            'label' => 'Text with Two Images',
            'description' => 'Rich text content with two accompanying images in various layout configurations.',
        ],
    ],

    'fields' => [
        'headline' => 'Headline',
        'subheadline' => 'Subheadline',
        'description' => 'Description',
        'content' => 'Content',
        'title' => 'Title',
        'cta_text' => 'Button Text',
        'cta_url' => 'Button URL',
        'cta_new_tab' => 'Open in new tab',
        'background_image' => 'Background Image',
        'image' => 'Image',
        'image_caption' => 'Image Caption',
        'overlay_opacity' => 'Overlay Opacity',
        'text_color' => 'Text Color',
        'height' => 'Section Height',
        'content_alignment' => 'Content Alignment',
        'layout' => 'Layout Style',
        'image_aspect' => 'Image Aspect Ratio',
        'image_size' => 'Image Size',
    ],

    'options' => [
        'overlay' => [
            'none' => 'None',
            'light' => 'Light (20%)',
            'medium' => 'Medium (40%)',
            'dark' => 'Dark (60%)',
            'very_dark' => 'Very Dark (80%)',
        ],
        'text_color' => [
            'white' => 'White',
            'dark' => 'Dark Gray',
            'primary' => 'Primary Color',
        ],
        'height' => [
            'small' => 'Small (400px)',
            'medium' => 'Medium (600px)',
            'large' => 'Large (800px)',
            'full_screen' => 'Full Screen',
        ],
        'alignment' => [
            'left' => 'Left',
            'center' => 'Center',
            'right' => 'Right',
        ],
        'layout' => [
            'images_left' => 'Images Left, Text Right',
            'images_right' => 'Images Right, Text Left',
            'images_stacked_left' => 'Images Stacked Left, Text Right',
            'images_stacked_right' => 'Images Stacked Right, Text Left',
            'images_top' => 'Images Side-by-Side Above Text',
            'images_bottom' => 'Images Side-by-Side Below Text',
            'masonry' => 'Masonry Grid (Text + Images Mixed)',
        ],
        'aspect' => [
            'square' => 'Square (1:1)',
            'video' => 'Video (16:9)',
            'standard' => 'Standard (4:3)',
            'portrait' => 'Portrait (3:4)',
            'auto' => 'Auto (Natural)',
        ],
        'size' => [
            'small' => 'Small (30% width)',
            'medium' => 'Medium (40% width)',
            'large' => 'Large (50% width)',
        ],
    ],

    'sections' => [
        'content' => 'Content',
        'settings' => 'Settings',
        'display_options' => 'Display Options',
        'images' => 'Images',
        'layout' => 'Layout',
    ],

    'hints' => [
        'background_image' => 'Recommended: 1920x1080px or larger',
        'overlay_opacity' => 'Darkens the background image for better text readability',
    ],

    'preview' => [
        'title' => 'Block Preview',
        'note' => 'This is a preview using the current locale (:locale). Styles may vary depending on your frontend implementation.',
        'not_available' => 'Block preview not available. Block type: :type',
        'no_data' => 'No block data available for preview.',
    ],
];
