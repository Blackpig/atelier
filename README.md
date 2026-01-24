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

**Optional:** Configure field defaults globally in `AtelierServiceProvider`:

```php
use BlackpigCreatif\Atelier\Support\BlockFieldConfig;

// In AtelierServiceProvider::boot()
BlockFieldConfig::register(HeroBlock::class, 'ctas', ['maxItems' => 3]);
BlockFieldConfig::register(HeroBlock::class, 'headline', ['maxLength' => 120]);
```

Then override per-resource when needed:

```php
// HomePage - allow more CTAs
BlockManager::make('blocks')
    ->blocks([HeroBlock::class])
    ->configureField(HeroBlock::class, 'ctas', ['maxItems' => 5])
```

See [Field Configuration](#3-field-configuration) for details.

### 3. Field Configuration

Atelier provides two levels of field configuration:

#### Global Configuration (Service Provider)

Set default field configurations that apply to **all resources** using the block.

**Create `app/Providers/AtelierServiceProvider.php` if it doesn't exist:**

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AtelierServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Global block field configurations
    }
}
```

**Register in `bootstrap/providers.php`:**

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AtelierServiceProvider::class,  // Add this line
];
```

**Add your global configurations:**

```php
use BlackpigCreatif\Atelier\Support\BlockFieldConfig;
use BlackpigCreatif\Atelier\Blocks\HeroBlock;
use BlackpigCreatif\Atelier\Blocks\TextBlock;

public function boot(): void
{
    // Global defaults for HeroBlock CTAs
    BlockFieldConfig::register(HeroBlock::class, 'ctas', [
        'maxItems' => 3,  // Default: max 3 CTAs everywhere
    ]);

    // Global defaults for HeroBlock headline
    BlockFieldConfig::register(HeroBlock::class, 'headline', [
        'maxLength' => 120,  // Default: 120 char limit everywhere
    ]);

    // Global defaults for TextBlock title
    BlockFieldConfig::register(TextBlock::class, 'title', [
        'maxLength' => 80,
        'required' => true,
    ]);
}
```

#### Per-Resource Configuration (Override)

Override global defaults for specific resources using `configureField()` in your Filament resource:

```php
use BlackpigCreatif\Atelier\Forms\Components\BlockManager;
use BlackpigCreatif\Atelier\Blocks\HeroBlock;

// HomePage - allow more CTAs than default
BlockManager::make('blocks')
    ->blocks([HeroBlock::class])
    ->configureField(HeroBlock::class, 'headline', [
        'maxLength' => 60,  // Override: shorter on homepage
    ])
    ->configureField(HeroBlock::class, 'ctas', [
        'maxItems' => 5,    // Override: more CTAs on homepage
    ])

// SubPages - stricter limits
BlockManager::make('blocks')
    ->blocks([HeroBlock::class])
    ->configureField(HeroBlock::class, 'ctas', [
        'maxItems' => 1,    // Override: only 1 CTA on subpages
    ])
```

#### How It Works

1. **Global configs** apply everywhere by default
2. **Per-resource configs** override global configs for that resource
3. If neither is set, the field uses its original definition

**Priority Order:**
```
Per-Resource Config (highest priority)
    ↓
Global Config
    ↓
Original Field Definition (lowest priority)
```

#### Configurable Properties

This works with **any field type** - not just fields created by traits. You can configure any Filament field method:

- `maxLength`, `minLength` for TextInput fields
- `maxItems`, `minItems` for Repeater fields
- `required`, `disabled` for any field
- `maxFiles` for FileUpload fields
- `columns` for Grid/Group fields
- Any Filament field method that exists on the component

#### Example Use Cases

**Global Defaults with Per-Resource Overrides:**
```php
// In AtelierServiceProvider - set sensible defaults
BlockFieldConfig::register(HeroBlock::class, 'ctas', ['maxItems' => 2]);

// In HomePageResource - allow more
->configureField(HeroBlock::class, 'ctas', ['maxItems' => 4])

// In BlogPostResource - allow fewer
->configureField(HeroBlock::class, 'ctas', ['maxItems' => 1])
```

**Common Scenarios:**
- HomePage allows 4 CTAs, SubPages allow only 1
- Product pages have longer headlines than blog posts
- Different max image counts for different content types
- Enable/disable fields based on resource type
- Set company-wide defaults, override for special cases

### 4. Render blocks in your view

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

    /**
     * Optional: Define translatable fields for frontend performance optimization.
     *
     * The admin panel automatically detects translatable fields by scanning the schema,
     * so this method is not required. However, including it can improve frontend
     * rendering performance by avoiding schema scanning on every page load.
     */
    // public static function getTranslatableFields(): array
    // {
    //     return ['quote', 'author'];
    // }

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

## Architecture: Schema Scanning

Atelier uses **schema scanning** as the source of truth for determining which fields are translatable. This provides several benefits:

### How It Works

1. **Admin Panel (Saving)**:
   - Scans your block's schema to detect fields with `->translatable()`
   - Automatically saves translatable fields with locale stamps to the database
   - No manual configuration needed

2. **Frontend (Rendering)**:
   - Prefers `getTranslatableFields()` method if defined (for performance)
   - Falls back to schema scanning if method doesn't exist
   - Ensures blocks work correctly regardless of method presence

3. **Data Structure Detection**:
   - Checks actual data format first (locale-keyed arrays vs plain values)
   - Handles translatable status changes automatically
   - No migration needed when adding/removing `->translatable()`

### Benefits

✅ **No manual tracking** - Add/remove `->translatable()` and it just works
✅ **Automatic CTA handling** - Repeater fields with translatable children work out of the box
✅ **Schema is truth** - What you define in your schema is what gets saved
✅ **Performance optional** - Use `getTranslatableFields()` only when you need the speed
✅ **Migration-friendly** - Change field translatable status without breaking existing data

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

### Declare Translatable Fields (Optional)

**Schema scanning is now the source of truth** - Atelier automatically detects which fields are translatable by scanning your block's schema. The `getTranslatableFields()` method is now **optional** and only needed for frontend performance optimization.

```php
/**
 * Optional: Define translatable fields for frontend performance optimization.
 *
 * The admin panel automatically detects translatable fields by scanning the schema,
 * so this method is not required. However, including it can improve frontend
 * rendering performance by avoiding schema scanning on every page load.
 *
 * Only include this method if:
 * - Your block is used frequently on high-traffic pages
 * - You want to optimize frontend performance
 * - Your translatable fields are stable and won't change often
 */
public static function getTranslatableFields(): array
{
    return ['headline', 'description', 'cta_text'];
}
```

**When you remove `->translatable()` from a field**, the schema scanner automatically detects the change and handles the data correctly - no manual cleanup needed.

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

## Call to Actions (CTAs)

The `HasCallToActions` trait provides a powerful, reusable way to add multiple call-to-action buttons to any block with full translation support.

### Why Use HasCallToActions?

- ✅ **Repeater-based** - Add unlimited CTAs (or set a limit)
- ✅ **Fully translatable** - Button labels support all configured locales
- ✅ **Flexible URLs** - Accepts both full URLs and relative paths
- ✅ **Icon support** - Optional Heroicons for buttons
- ✅ **Style variants** - Config-driven button styles (Primary, Secondary, etc.)
- ✅ **Target control** - Open in new tab option
- ✅ **Automatic EAV storage** - Smart collection-based database structure

### Adding CTAs to Your Block

**1. Add the trait:**

```php
use BlackpigCreatif\Atelier\Concerns\HasCallToActions;

class HeroBlock extends BaseBlock
{
    use HasCallToActions;
    use HasCommonOptions;
    // ...
}
```

**2. Add the field to your schema:**

```php
public static function getSchema(): array
{
    return [
        Section::make('Content')
            ->schema([
                TextInput::make('headline')
                    ->required()
                    ->translatable(),
            ]),

        Section::make('Call to Action')
            ->schema([
                static::getCallToActionsField()
                    ->maxItems(3),  // Optional: limit number of CTAs
            ])
            ->collapsible(),

        ...static::getCommonOptionsSchema(),
    ];
}
```

**Note:** You do NOT need to add CTA fields to `getTranslatableFields()` - the schema scanner automatically detects translatable fields within repeaters. In fact, `getTranslatableFields()` is now entirely optional for all blocks (see [Schema Scanning Architecture](#architecture-schema-scanning)).

### CTA Fields

Each CTA in the repeater includes:

| Field | Type | Description |
|-------|------|-------------|
| **Label** | TextInput (translatable) | Button text - supports all locales |
| **URL** | TextInput | Full URL (`https://...`) or relative path (`/page`) |
| **Icon** | TextInput (optional) | Heroicon name (e.g., `heroicon-o-arrow-right`) |
| **Style** | Select | Button style from config (Primary, Secondary, Alternate) |
| **New Tab** | Toggle | Whether to open link in new window |

### Rendering CTAs in Templates

**Option 1: Using the Blade Component (Recommended)**

```blade
@if($block->hasCallToActions())
    <div class="flex gap-4">
        @foreach($block->getCallToActions() as $index => $cta)
            <x-atelier::call-to-action
                :cta="$cta"
                :block="$block"
                :index="$index"
            />
        @endforeach
    </div>
@endif
```

**Option 2: Manual Rendering**

```blade
@if($block->hasCallToActions())
    <div class="flex gap-4">
        @foreach($block->getCallToActions() as $cta)
            <a
                href="{{ $cta['url'] }}"
                target="{{ $block->getCallToActionTarget($cta) }}"
                class="{{ $block->getCallToActionStyleClass($cta) }}"
                @if($block->isExternalUrl($cta['url'])) rel="noopener noreferrer" @endif
            >
                @if(!empty($cta['icon']))
                    <x-filament::icon :icon="$cta['icon']" class="w-5 h-5" />
                @endif

                {{ $block->getCallToActionLabel($cta) }}
            </a>
        @endforeach
    </div>
@endif
```

### Helper Methods

```php
// Check if block has CTAs
$block->hasCallToActions(): bool

// Get all CTAs
$block->getCallToActions(): array

// Get translated label (automatically uses current locale)
$block->getCallToActionLabel($cta): string
$block->getCallToActionLabel($cta, 'fr'): string  // Specific locale

// Get CSS class from config
$block->getCallToActionStyleClass($cta): string
$block->getCallToActionStyleClass('primary'): string  // Or pass style key directly

// Get target attribute
$block->getCallToActionTarget($cta): string  // Returns '_blank' or '_self'

// Check if URL is external
$block->isExternalUrl($cta['url']): bool
```

### CTA Data Structure

When you loop through `getCallToActions()`, each `$cta` is an array:

```php
[
    'label' => ['en' => 'Get Started', 'fr' => 'Commencer'],  // Translatable
    'url' => '/signup',
    'icon' => 'heroicon-o-arrow-right',
    'style' => 'primary',
    'new_tab' => false,
]
```

### Customizing Button Styles

Configure button styles in `config/atelier.php`:

```php
'features' => [
    'button_styles' => [
        'enabled' => true,
        'options' => [
            'primary' => [
                'label' => 'Primary',
                'class' => 'btn btn-primary',  // Your CSS classes
            ],
            'secondary' => [
                'label' => 'Secondary',
                'class' => 'btn btn-secondary',
            ],
            'alternate' => [
                'label' => 'Alternate',
                'class' => 'btn btn-alternate',
            ],
            // Add your own styles
            'ghost' => [
                'label' => 'Ghost',
                'class' => 'btn btn-ghost',
            ],
        ],
    ],
],
```

### Advanced Usage

**Limit CTAs per block:**

```php
static::getCallToActionsField()
    ->maxItems(2)  // Only allow 2 CTAs
```

**Require at least one CTA:**

```php
static::getCallToActionsField()
    ->minItems(1)
    ->defaultItems(1)  // Start with 1 CTA pre-added
```

**Customize the repeater:**

```php
static::getCallToActionsField()
    ->maxItems(3)
    ->label('Action Buttons')
    ->addActionLabel('Add Button')
    ->collapsible(false)
    ->reorderable(false)
```

**Conditional display:**

```blade
@if($block->hasCallToActions())
    <div class="hero-actions {{ $block->get('content_alignment') === 'text-center' ? 'justify-center' : 'justify-start' }}">
        @foreach($block->getCallToActions() as $cta)
            {{-- Custom rendering per alignment --}}
        @endforeach
    </div>
@endif
```

### How It Works (Technical)

The `HasCallToActions` trait leverages Atelier's collection-based EAV (Entity-Attribute-Value) system:

1. **Form Level**: Repeater creates array of items with translatable labels
2. **Save**: BlockManager detects repeater structure and saves to database:
   - Each CTA stored with `collection_name='ctas'` and `collection_index=0,1,2...`
   - Translatable labels stored as separate rows per locale
   - Non-translatable fields (url, icon, style) stored once per item
3. **Hydrate**: BlockManager reconstructs the array from EAV rows automatically
4. **Render**: Helper methods handle locale resolution and attribute generation

This architecture means:
- ✅ No database migrations needed when adding new repeater fields
- ✅ Works with any number of locales
- ✅ Efficient querying and caching
- ✅ Reusable pattern for other repeater-based features

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
