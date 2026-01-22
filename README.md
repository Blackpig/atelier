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
- **SEO-Ready**: Automatic Schema.org structured data generation

## Requirements

- PHP 8.2+
- Laravel 11.0+
- FilamentPHP 4.0+

## Installation

Install the package via composer:

```bash
composer require blackpig-creatif/atelier
```

**Note:** Atelier automatically installs its companion packages:
- **[Chambre Noir](https://github.com/blackpig-creatif/chambre-noir)** - Image processing and conversions
- **[Sceau](https://github.com/blackpig-creatif/sceau)** - SEO and Schema.org structured data

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="atelier-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="atelier-config"
```

Optionally, publish the block templates for customization:

```bash
# Publish block templates (recommended for designers)
php artisan vendor:publish --tag="atelier-block-templates"

# Publish divider components (if you want to customize dividers)
php artisan vendor:publish --tag="atelier-dividers"

# Publish ALL views (⚠️ not recommended - may conflict with package updates)
php artisan vendor:publish --tag="atelier-views-all"
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
use BlackpigCreatif\Atelier\Collections\BasicBlocks;

public static function form(Form $form): Form
{
    return $form->schema([
        // ... other fields

        BlockManager::make('blocks')
            ->blocks(BasicBlocks::class)  // Use a Block Collection
            ->collapsed()
            ->collapsible()
            ->reorderable()
            ->cloneable(),
    ]);
}
```

**Alternative:** Specify individual blocks:

```php
use BlackpigCreatif\Atelier\Blocks\HeroBlock;
use BlackpigCreatif\Atelier\Blocks\TextBlock;
use BlackpigCreatif\Atelier\Blocks\ImageBlock;

BlockManager::make('blocks')
    ->blocks([
        HeroBlock::class,
        TextBlock::class,
        ImageBlock::class,
    ])
```

### 3. Render blocks in your view

```blade
{{-- Using the Blade directive (recommended) --}}
<div class="page-content">
    @renderBlocks($page)
</div>

{{-- Or manually loop through blocks --}}
<div class="page-content">
    @foreach($page->publishedBlocks as $block)
        {!! $block->render() !!}
    @endforeach
</div>

{{-- With custom locale --}}
<div class="page-content">
    @foreach($page->publishedBlocks as $block)
        {!! $block->render('fr') !!}
    @endforeach
</div>
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

## Block Collections

Organize blocks into reusable collections for cleaner code and better organization. Block Collections are similar to ChambreNoir's Conversions pattern.

### Using Built-in Collections

Atelier ships with three pre-configured collections:

```php
use BlackpigCreatif\Atelier\Collections\BasicBlocks;
use BlackpigCreatif\Atelier\Collections\MediaBlocks;
use BlackpigCreatif\Atelier\Collections\AllBlocks;

// Use a single collection
BlockManager::make('blocks')
    ->blocks(BasicBlocks::class)

// Combine multiple collections
BlockManager::make('blocks')
    ->blocks([BasicBlocks::class, MediaBlocks::class])

// Mix collections with individual blocks
BlockManager::make('blocks')
    ->blocks([
        BasicBlocks::class,
        CustomBlock::class,
    ])
```

**Built-in Collections:**
- **`BasicBlocks`**: Hero, Text, Text with Image
- **`MediaBlocks`**: Image, Video, Gallery, Carousel
- **`AllBlocks`**: All available Atelier blocks

### Creating Custom Collections

Use the Artisan command to quickly generate a new collection:

```bash
php artisan make:atelier-collection Ecommerce
```

This creates a collection class at `app/Filament/Atelier/Collections/EcommerceBlocks.php`.

Or create manually by extending `BaseBlockCollection`:

```php
namespace App\Filament\Atelier\Collections;

use BlackpigCreatif\Atelier\Abstracts\BaseBlockCollection;
use BlackpigCreatif\Atelier\Blocks\HeroBlock;
use App\Filament\Atelier\Blocks\ProductBlock;
use App\Filament\Atelier\Blocks\TestimonialBlock;

class EcommerceBlocks extends BaseBlockCollection
{
    public function getBlocks(): array
    {
        return [
            HeroBlock::class,
            ProductBlock::class,
            TestimonialBlock::class,
        ];
    }

    public static function getLabel(): string
    {
        return 'E-commerce Blocks';
    }

    public static function getDescription(): ?string
    {
        return 'Blocks for product pages and e-commerce content.';
    }
}
```

Then use it in your resources:

```php
BlockManager::make('blocks')
    ->blocks(EcommerceBlocks::class)
```

### All Supported Formats

The `blocks()` method accepts multiple formats:

```php
// Single collection class
->blocks(BasicBlocks::class)

// Array of collections
->blocks([BasicBlocks::class, MediaBlocks::class])

// Array of block classes (traditional)
->blocks([HeroBlock::class, TextBlock::class])

// Mixed: collections + individual blocks
->blocks([BasicBlocks::class, CustomBlock::class])

// Closure (for dynamic logic)
->blocks(fn() => auth()->user()->isAdmin()
    ? AllBlocks::make()
    : BasicBlocks::make()
)

// Empty (falls back to config)
->blocks()
```

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

### Publication Status
Every block includes a publication toggle:
- **Published**: Block appears on the frontend (default)
- **Unpublished**: Block is saved but hidden from public view

Use unpublished blocks for:
- Draft content that's not ready to go live
- Seasonal content scheduled for later
- A/B testing different block variations
- Content review workflows

```php
// Only published blocks are rendered
$page->publishedBlocks; // Returns only is_published = true

// Access all blocks regardless of status
$page->blocks; // Returns all blocks
```

## Creating Custom Blocks

### Using the Artisan Command

The fastest way to create a new block is using the built-in Artisan command:

```bash
php artisan make:atelier-block Quote
```

This will:
- Create a block class at `app/Filament/Atelier/Blocks/QuoteBlock.php`
- Create a blade template at `resources/views/vendor/atelier/blocks/quote-block.blade.php`
- Set up basic structure with all required methods
- Include translatable fields, common options, and divider support

The command will prompt you for the block name if not provided as an argument.

### Manual Block Creation

Alternatively, create a new block class manually:

```php
namespace App\Filament\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
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

    public static function getIcon(): string | IconSize | Htmlable | null
    {
        // Option 1: Use a Heroicon name (string)
        return 'heroicon-o-chat-bubble-left-right';

        // Option 2: Use custom SVG via HtmlString
        // return new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="..."/></svg>');

        // Option 3: Return null for no icon
        // return null;
    }

    public static function getSchema(): array
    {
        return [
            Section::make('Content')
                ->schema([
                    Textarea::make('quote')
                        ->label('Quote')
                        ->required()
                        ->rows(3)
                        ->translatable(),  // Must be last

                    TextInput::make('author')
                        ->label('Author')
                        ->required()
                        ->translatable(),  // Must be last
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
        return view(static::getViewPath(), $this->getViewData());
    }
}
```

Create the corresponding view in `resources/views/vendor/atelier/blocks/quote-block.blade.php`:

```blade
{{-- resources/views/vendor/atelier/blocks/quote-block.blade.php --}}
@php
    /**
     * Quote Block Template
     *
     * @var \App\Filament\Atelier\Blocks\QuoteBlock $block
     * @var string|null $quote - Translated quote text
     * @var string|null $author - Translated author name
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        <blockquote class="text-2xl italic font-light text-gray-700 dark:text-gray-300">
            @if($quote = $block->getTranslated('quote'))
                <p>"{{ $quote }}"</p>
            @endif

            @if($author = $block->getTranslated('author'))
                <footer class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                    — {{ $author }}
                </footer>
            @endif
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

Then register your block in a BlockManager:

```php
BlockManager::make('blocks')
    ->blocks([
        \App\Filament\Atelier\Blocks\QuoteBlock::class,
        // ... other blocks
    ]);
```

## Block Icons

Atelier supports flexible icon formats for your custom blocks. The `getIcon()` method can return:

### Heroicon String (Recommended)
Use any Heroicon name:

```php
public static function getIcon(): string | IconSize | Htmlable | null
{
    return 'heroicon-o-chat-bubble-left-right';
}
```

### Custom SVG
Use `HtmlString` for custom SVG icons:

```php
use Illuminate\Support\HtmlString;

public static function getIcon(): string | IconSize | Htmlable | null
{
    return new HtmlString('
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
        </svg>
    ');
}
```

**Tip:** Use `fill="currentColor"` in your SVG paths to ensure icons match the UI theme.

### No Icon
Return `null` if you don't want an icon:

```php
public static function getIcon(): string | IconSize | Htmlable | null
{
    return null;
}
```

## Translation Support

Atelier includes an elegant inline translation system with global locale switching:

### Making Fields Translatable

Add `.translatable()` to any Filament field. **Important:** `.translatable()` must be the **last method** in the chain:

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

public static function getSchema(): array
{
    return [
        Section::make('Content')
            ->schema([
                TextInput::make('headline')
                    ->label('Headline')
                    ->required()
                    ->maxLength(255)
                    ->translatable(),  // ← Must be LAST

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->translatable(),  // ← Must be LAST
            ]),

        Section::make('Settings')
            ->schema([
                TextInput::make('url')  // ← Not translatable
                    ->url(),
            ]),
    ];
}
```

### How It Works

- Global locale switcher appears at the top of the block modal
- Click to switch between languages instantly
- Each translatable field shows a globe icon and locale badge `[EN]` or `[FR]`
- Fields are shown/hidden based on the selected locale
- Data is stored as: `['headline' => ['en' => 'Hello', 'fr' => 'Bonjour']]`

### Configuration

Configure your locales in `config/atelier.php`:

```php
'locales' => [
    'en' => 'English',
    'fr' => 'Français',
    'es' => 'Español',
],

'default_locale' => 'en',
```

### Declare Translatable Fields

For proper data handling, declare which fields are translatable:

```php
public static function getTranslatableFields(): array
{
    return ['headline', 'description', 'cta_text'];
}
```

### Retrieve Translated Content

In your block templates:

```blade
{{-- Get translated value for current locale --}}
{{ $block->getTranslated('headline') }}

{{-- With specific locale --}}
{{ $block->getTranslated('headline', 'fr') }}
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

## SEO Schema Generation

Atelier integrates with **[Sceau](https://github.com/blackpig-creatif/sceau)** to automatically generate Schema.org structured data (JSON-LD) from your blocks. This helps search engines understand your content and can result in rich snippets in search results.

### How It Works

Every Atelier block inherits schema contribution capabilities through the `InteractsWithSchema` trait. Blocks can either:

1. **Contribute to composite schemas** (like Article) - Multiple blocks combine into one schema
2. **Generate standalone schemas** (like VideoObject) - Each block creates its own schema

### Built-in Schema Support

**TextBlock** - Contributes content to Article schemas
**ImageBlock** - Contributes images to Article schemas
**VideoBlock** - Generates standalone VideoObject schemas

### Automatic Schema Generation

Use `PageSchemaBuilder` in your controller to automatically generate schemas from all page blocks:

```php
use BlackpigCreatif\Sceau\Services\PageSchemaBuilder;

public function show(Page $page)
{
    // Automatically generates:
    // - Article schema (from TextBlocks and ImageBlocks)
    // - VideoObject schemas (from VideoBlocks)
    PageSchemaBuilder::build($page);

    return view('pages.show', ['page' => $page]);
}
```

### Custom Block Schemas

Override schema methods in your custom blocks:

**Contributing to Article Schema:**

```php
class QuoteBlock extends BaseBlock
{
    public function contributesToComposite(): bool
    {
        return true;
    }

    public function getCompositeContribution(): array
    {
        return [
            'type' => 'text',
            'content' => $this->get('quote'),
        ];
    }
}
```

**Generating Standalone Schema:**

```php
class FaqBlock extends BaseBlock
{
    public function hasStandaloneSchema(): bool
    {
        return !empty($this->get('pairs'));
    }

    public function toStandaloneSchema(): ?array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($this->get('pairs'))->map(fn($pair) => [
                '@type' => 'Question',
                'name' => $pair['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $pair['answer'],
                ],
            ])->toArray(),
        ];
    }
}
```

### Example Output

For a page with 3 TextBlocks, 1 ImageBlock, and 1 VideoBlock, the generated JSON-LD will include:

```json
[
  {
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "Page Title",
    "articleBody": "Combined text from all TextBlocks...",
    "image": ["image-url.jpg"],
    "author": {...},
    "datePublished": "2025-01-22T18:00:00Z"
  },
  {
    "@context": "https://schema.org",
    "@type": "VideoObject",
    "name": "Video Title",
    "contentUrl": "https://youtube.com/watch?v=...",
    "embedUrl": "https://youtube.com/embed/..."
  }
]
```

For more details, see the **[Sceau documentation](https://github.com/blackpig-creatif/sceau)**.

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
