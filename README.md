# Atelier

[![Latest Version on Packagist](https://img.shields.io/packagist/v/blackpig-creatif/atelier.svg?style=flat-square)](https://packagist.org/packages/blackpig-creatif/atelier)
[![Total Downloads](https://img.shields.io/packagist/dt/blackpig-creatif/atelier.svg?style=flat-square)](https://packagist.org/packages/blackpig-creatif/atelier)

**L'art du contenu** - Artisanal content blocks for FilamentPHP v4

Atelier brings a craftsman's approach to content management in FilamentPHP v4. Unlike rigid page builders, Atelier gives you the flexibility to forge custom content blocks that are precisely shaped to your needs.

Built on a sophisticated polymorphic architecture with first-class translation support, Atelier stores your blocks intelligently - keeping your database lean while maintaining the flexibility to create any block type you can imagine.

## Philosophy

In a master's atelier, every element is placed with intention. Every detail considered. Atelier brings that same philosophy to FilamentPHP - where content blocks are crafted with the care they deserve.

- **Artisanal**: Bespoke blocks, not templates
- **Architectural**: Clean, polymorphic database structure
- **Translatable**: First-class multi-language support
- **Extensible**: Traits and abstracts for rapid customization
- **Performant**: Smart caching and eager loading
- **Live Preview**: Preview blocks before publishing

## Requirements

- PHP 8.2+
- Laravel 11.0+
- FilamentPHP 4.0+

## Installation

Install the package via composer:

```bash
composer require blackpig-creatif/atelier
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="atelier-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="atelier-config"
```

Optionally, publish the views for customization:

```bash
php artisan vendor:publish --tag="atelier-views"
```

## Quick Start

### 1. Add the trait to your model

```php
use BlackpigCreatif\Atelier\Concerns\HasFlexibleBlocks;

class Page extends Model
{
    use HasFlexibleBlocks;
}
```

### 2. Add the BlockManager to your Filament resource

```php
use BlackpigCreatif\Atelier\Forms\Components\BlockManager;
use BlackpigCreatif\Atelier\Blocks\HeroBlock;
use BlackpigCreatif\Atelier\Blocks\TextBlock;
use BlackpigCreatif\Atelier\Blocks\ImageBlock;

public static function form(Form $form): Form
{
    return $form->schema([
        // ... other fields

        BlockManager::make('blocks')
            ->blocks([
                HeroBlock::class,
                TextBlock::class,
                ImageBlock::class,
                // ... more blocks
            ])
            ->livePreview()
            ->collapsed()
            ->collapsible()
            ->reorderable()
            ->cloneable(),
    ]);
}
```

### 3. Render blocks in your view

```blade
<div class="page-content">
    @renderBlocks($page)
</div>

{{-- Or manually --}}
@foreach($page->publishedBlocks as $block)
    {!! $block->instance->render() !!}
@endforeach
```

## Built-in Blocks

Atelier ships with 8 production-ready content blocks:

### **HeroBlock**
Full-width hero section with background image, headline, subheadline, description, and call-to-action button. Perfect for landing pages and feature sections.

### **TextBlock**
Rich text content with optional title. Supports formatting (bold, italic, lists, headings) and configurable text alignment and max-width.

### **TextWithImageBlock**
Text content paired with a single image. Choose image position (left/right) and width percentage for flexible layouts.

### **TextWithTwoImagesBlock**
Rich text with two images in various layout options: side-by-side, stacked, masonry, and more.

### **ImageBlock**
Single image display with optional title and caption. Supports aspect ratios, alignment, and lightbox functionality.

### **VideoBlock**
Embed videos from YouTube, Vimeo, or direct video URLs. Auto-detects platform and converts to proper embed format with autoplay and muted options.

### **GalleryBlock**
Grid-based image gallery with configurable columns, gaps, and lightbox support. Perfect for photo galleries and portfolios.

### **CarouselBlock**
Image carousel/slider with navigation arrows, dots, and autoplay options. Great for featured content and testimonials.

## Display Options

All blocks include powerful display options through the `HasCommonOptions` trait:

### Background Colors
Choose from predefined background colors:
- White
- Light Gray
- Gray
- Primary Color
- Secondary Color

### Spacing
Configure vertical spacing with two modes:

**Balanced Mode**: Equal top and bottom padding
- None, Extra Small (4), Small (8), Medium (16), Large (24), Extra Large (32)

**Individual Mode**: Separate control for top and bottom spacing

### Width Constraints
Control content width:
- Container (responsive container)
- Narrow (max-w-3xl)
- Wide (max-w-7xl)
- Full Width

### Block Dividers
Add decorative SVG dividers between blocks:
- Wave
- Curve Up / Curve Down
- Diagonal (Left to Right / Right to Left)
- Triangle

Each divider can transition to the next section's background color for seamless designs.

## Creating Custom Blocks

Create a new block class:

```php
namespace App\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use BlackpigCreatif\Atelier\Forms\Components\TranslatableContainer;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class QuoteBlock extends BaseBlock
{
    use HasCommonOptions;

    public static function getLabel(): string
    {
        return 'Quote';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function getSchema(): array
    {
        return [
            Section::make('Content')
                ->schema([
                    TranslatableContainer::make()
                        ->translatableFields([
                            Textarea::make('quote')
                                ->label('Quote')
                                ->required()
                                ->rows(3),

                            TextInput::make('author')
                                ->label('Author')
                                ->required(),
                        ])
                        ->columnSpanFull(),
                ])
                ->collapsible(),

            // Include display options
            ...static::getCommonOptionsSchema(),
        ];
    }

    public static function getTranslatableFields(): array
    {
        return ['quote', 'author'];
    }

    public function render(): View
    {
        return view('blocks.quote', $this->getViewData());
    }
}
```

Create the corresponding view in `resources/views/blocks/quote.blade.php`:

```blade
@php
    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        <blockquote class="text-2xl italic font-light text-gray-700 dark:text-gray-300">
            <p>"{{ $block->getTranslated('quote') }}"</p>
            <footer class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                — {{ $block->getTranslated('author') }}
            </footer>
        </blockquote>
    </div>

    {{-- Block Divider --}}
    @if($block->getDividerComponent())
        <x-dynamic-component
            :component="$block->getDividerComponent()"
            :to-background="$block->getDividerToBackground()"
        />
    @endif
</section>
```

## Translation Support

Atelier includes a custom translation system built specifically for FilamentPHP v4:

```php
// Mark fields as translatable
public static function getTranslatableFields(): array
{
    return ['title', 'content', 'description'];
}

// Use TranslatableContainer in your schema
TranslatableContainer::make()
    ->translatableFields([
        TextInput::make('title'),
        Textarea::make('content'),
    ]);

// Retrieve translated content in views
{{ $block->getTranslated('title') }}
```

Configure your locales in `config/atelier.php`:

```php
'locales' => [
    'en' => 'English',
    'fr' => 'Français',
    'es' => 'Español',
],
```

## Live Preview

Enable live preview to see blocks before saving:

```php
BlockManager::make('blocks')
    ->blocks([...])
    ->livePreview() // Enable preview button for each block
    ->collapsed()
    ->collapsible();
```

## Available Traits

### HasCommonOptions

Adds background color, spacing, width, and divider options:

```php
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;

class MyBlock extends BaseBlock
{
    use HasCommonOptions;

    public static function getSchema(): array
    {
        return [
            // Your fields...

            // Add display options section
            ...static::getCommonOptionsSchema(),
        ];
    }
}
```

Access in templates:

```blade
<section class="{{ $block->getWrapperClasses() }}">
    <div class="{{ $block->getContainerClasses() }}">
        {{-- Content --}}
    </div>
</section>
```

### HasFileUpload

Provides file upload URL helpers for Filament FileUpload fields:

```php
use BlackpigCreatif\Atelier\Concerns\HasFileUpload;

// In your block
$url = $block->getFileUploadUrl('image_field');
$urls = $block->getFileUploadUrls('image_field'); // For multiple files
```

### HasFlexibleBlocks

Add to your parent models (Page, Post, etc):

```php
use BlackpigCreatif\Atelier\Concerns\HasFlexibleBlocks;

class Page extends Model
{
    use HasFlexibleBlocks;
}

// Access blocks
$page->blocks;              // All blocks
$page->publishedBlocks;     // Only published blocks
$page->renderBlocks($locale); // Render all blocks
```

## Media Handling

Atelier integrates with [ChambreNoir](https://github.com/blackpig-creatif/chambre-noir) for responsive image handling. Images are automatically processed with configurable conversions (thumb, medium, large) for optimal performance.

```php
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;

class MyBlock extends BaseBlock
{
    use HasCommonOptions, HasRetouchMedia;

    public static function getSchema(): array
    {
        return [
            RetouchMediaUpload::make('image')
                ->label('Image')
                ->preset(YourConversionClass::class)
                ->imageEditor()
                ->maxFiles(1),
        ];
    }
}
```

## Configuration

The `config/atelier.php` file provides extensive customization:

```php
return [
    // Locales
    'locales' => [
        'en' => 'English',
        'fr' => 'Français',
    ],

    // Default blocks
    'blocks' => [
        \BlackpigCreatif\Atelier\Blocks\HeroBlock::class,
        \BlackpigCreatif\Atelier\Blocks\TextBlock::class,
        // ...
    ],

    // Display features
    'features' => [
        'backgrounds' => [...],
        'spacing' => [...],
        'width' => [...],
        'dividers' => [...],
    ],

    // Caching
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
];
```

## Documentation

For detailed guides and advanced usage:

- **[Block Template Guide](docs/BLOCK-TEMPLATE-GUIDE.md)** - Creating and customizing block templates
- **[Migration Guide](docs/MIGRATION.md)** - Upgrading from previous versions
- **[Responsive Images](docs/RESPONSIVE_IMAGES_USAGE.md)** - Working with ChambreNoir and image conversions

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](docs/CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](docs/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Stuart Hallewell](https://github.com/blackpig-creatif)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](docs/LICENSE.md) for more information.
