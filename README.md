# Atelier

[![Latest Version on Packagist](https://img.shields.io/packagist/v/blackpig-creatif/atelier.svg?style=flat-square)](https://packagist.org/packages/blackpig-creatif/atelier)
[![Total Downloads](https://img.shields.io/packagist/dt/blackpig-creatif/atelier.svg?style=flat-square)](https://packagist.org/packages/blackpig-creatif/atelier)

A polymorphic content block builder for FilamentPHP v4 with first-class translation support, EAV attribute storage, and Schema.org structured data generation.

Atelier stores block data as polymorphic EAV (Entity-Attribute-Value) rows, keeping your application schema lean while supporting arbitrary block structures. Translatable fields are stored per-locale, and the schema is scanned at save time to determine translatability automatically.

## Requirements

- PHP 8.2+
- Laravel 11+
- FilamentPHP 4.0+

## Installation

```bash
composer require blackpig-creatif/atelier
```

Atelier automatically pulls in its companion packages:
- [Chambre Noir](https://github.com/blackpig-creatif/chambre-noir) -- responsive image processing
- [Sceau](https://github.com/blackpig-creatif/sceau) -- SEO and Schema.org structured data

Publish and run migrations:

```bash
php artisan vendor:publish --tag="atelier-migrations"
php artisan migrate
```

Publish the config:

```bash
php artisan vendor:publish --tag="atelier-config"
```

Register the Filament plugin in your `PanelProvider`:

```php
use BlackpigCreatif\Atelier\AtelierPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            AtelierPlugin::make(),
        ]);
}
```

### Optional Publishables

```bash
# Block Blade templates (recommended -- customise frontend rendering)
php artisan vendor:publish --tag="atelier-block-templates"

# Divider SVG components
php artisan vendor:publish --tag="atelier-dividers"
```

---

## Quick Start

### 1. Add the trait to your model

```php
use BlackpigCreatif\Atelier\Concerns\HasAtelierBlocks;

class Page extends Model
{
    use HasAtelierBlocks;
}
```

This gives you:

```php
$page->blocks;            // MorphMany -- all blocks, ordered
$page->publishedBlocks;   // MorphMany -- only is_published = true
$page->renderBlocks();    // string -- rendered HTML of all published blocks
$page->renderBlocks('fr');
```

### 2. Add BlockManager to your Filament resource

```php
use BlackpigCreatif\Atelier\Forms\Components\BlockManager;
use BlackpigCreatif\Atelier\Collections\BasicBlocks;

public static function form(Form $form): Form
{
    return $form->schema([
        BlockManager::make('blocks')
            ->blocks(BasicBlocks::class)
            ->collapsible()
            ->reorderable(),
    ]);
}
```

### 3. Render blocks in your view

```blade
{{-- Blade directive --}}
@renderBlocks($page)

{{-- Or manually --}}
@foreach($page->publishedBlocks as $block)
    {!! $block->render() !!}
@endforeach

{{-- With explicit locale --}}
@foreach($page->publishedBlocks as $block)
    {!! $block->render('fr') !!}
@endforeach
```

---

## Built-in Blocks

| Block | Description |
|-------|-------------|
| **HeroBlock** | Full-width hero with background image, headline, CTAs |
| **TextBlock** | Rich text with optional title, subtitle, column layout |
| **TextWithImageBlock** | Text + single image, configurable position |
| **TextWithTwoImagesBlock** | Rich text + two images, multiple layout modes |
| **ImageBlock** | Single image with caption, aspect ratio, lightbox |
| **VideoBlock** | YouTube/Vimeo/direct URL embed with auto-detection |
| **GalleryBlock** | Grid gallery with configurable columns and lightbox |
| **CarouselBlock** | Image carousel with navigation and autoplay |

---

## Block Collections

Collections group blocks into reusable sets.

### Built-in collections

| Collection | Blocks |
|-----------|--------|
| `BasicBlocks` | Hero, Text, TextWithImage |
| `MediaBlocks` | Image, Video, Gallery, Carousel |
| `AllBlocks` | All eight built-in blocks |

### Usage

```php
// Single collection
->blocks(BasicBlocks::class)

// Multiple collections
->blocks([BasicBlocks::class, MediaBlocks::class])

// Mix collections with individual blocks
->blocks([BasicBlocks::class, CustomBlock::class])

// Closure for dynamic logic
->blocks(fn () => auth()->user()->isAdmin()
    ? AllBlocks::make()
    : BasicBlocks::make()
)
```

### Creating a collection

```bash
php artisan atelier:make-collection Ecommerce
```

Or manually:

```php
namespace App\BlackpigCreatif\Atelier\Collections;

use BlackpigCreatif\Atelier\Abstracts\BaseBlockCollection;
use BlackpigCreatif\Atelier\Blocks\HeroBlock;
use App\BlackpigCreatif\Atelier\Blocks\ProductBlock;

class EcommerceBlocks extends BaseBlockCollection
{
    public function getBlocks(): array
    {
        return [
            HeroBlock::class,
            ProductBlock::class,
        ];
    }

    public static function getLabel(): string
    {
        return 'E-commerce Blocks';
    }
}
```

---

## Block Configuration

Atelier supports two layers of configuration: **field configuration** (tweak individual field properties) and **schema modification** (add, remove, or reorder fields structurally). Both can be applied globally or per-resource.

For the full reference with all helper methods and patterns, see [docs/block-configuration.md](docs/block-configuration.md).

### Global Configuration

Register global defaults in a service provider:

```php
namespace App\Providers;

use BlackpigCreatif\Atelier\Blocks\HeroBlock;
use BlackpigCreatif\Atelier\Blocks\TextBlock;
use BlackpigCreatif\Atelier\Support\BlockFieldConfig;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\ServiceProvider;

class AtelierServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Configure individual fields
        BlockFieldConfig::configure(TextBlock::class, [
            'subtitle' => ['visible' => false],
            'columns'  => ['options' => ['1' => '1 Column', '2' => '2 Columns']],
        ]);

        // Modify schema structure
        BlockFieldConfig::modifySchema(HeroBlock::class, function ($schema) {
            return BlockFieldConfig::removeFields($schema, ['text_color', 'overlay_opacity']);
        });

        // Add fields
        BlockFieldConfig::modifySchema(HeroBlock::class, function ($schema) {
            return [
                ...$schema,
                Toggle::make('featured')->label('Featured')->default(false),
            ];
        });
    }
}
```

Register it in `bootstrap/providers.php`.

### Per-Resource Configuration

Override globals on a specific resource:

```php
BlockManager::make('blocks')
    ->blocks([HeroBlock::class, TextBlock::class])

    // Field config (array form)
    ->configureBlock(HeroBlock::class, [
        'headline' => ['maxLength' => 60],
        'ctas'     => ['maxItems' => 5],
    ])

    // Schema modifier (closure form)
    ->configureBlock(HeroBlock::class, fn ($schema) =>
        BlockFieldConfig::removeFields($schema, 'subtitle')
    )
```

### Configuration Priority

```
Block Default Schema
  -> Global Schema Modifiers
    -> Per-Resource Schema Modifiers
      -> Global Field Configs
        -> Per-Resource Field Configs (wins)
```

Schema modifiers shape the structure first; field configs tweak properties last. Per-resource always overrides global at the same level.

### Fluent API (BlockConfigurator)

For a chainable alternative:

```php
use BlackpigCreatif\Atelier\Support\BlockConfigurator;

BlockConfigurator::for(HeroBlock::class)
    ->hide('overlay_opacity', 'text_color')
    ->remove('height')
    ->configure('ctas', ['maxItems' => 2])
    ->insertAfter('headline', [
        TextInput::make('tagline')->maxLength(100),
    ])
    ->apply();
```

---

## Creating Custom Blocks

### Artisan generator

```bash
php artisan atelier:make-block Quote
```

Creates the block class and Blade template with the correct boilerplate.

### Manual creation

```php
namespace App\BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
            static::getPublishedField(),

            Section::make('Content')
                ->schema([
                    Textarea::make('quote')
                        ->required()
                        ->rows(3)
                        ->translatable(),  // must be last in chain

                    TextInput::make('author')
                        ->required()
                        ->translatable(),  // must be last in chain
                ])
                ->collapsible(),

            ...static::getCommonOptionsSchema(),
        ];
    }

    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }
}
```

Key points:
- Extend `BaseBlock` and implement `getLabel()`, `getSchema()`, `render()`
- Use `HasCommonOptions` for background, spacing, width, and divider controls
- Call `->translatable()` as the **last** method in a field chain
- Call `static::getPublishedField()` at the top of your schema for the publish toggle
- Call `...static::getCommonOptionsSchema()` at the end for display options

### Block template

Templates live at `resources/views/vendor/atelier/blocks/{block-identifier}.blade.php`. See [docs/block-templates.md](docs/block-templates.md) for the full template guide.

```blade
@php
    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        @if($quote = $block->getTranslated('quote'))
            <blockquote class="text-2xl italic">
                <p>"{{ $quote }}"</p>
            </blockquote>
        @endif

        @if($author = $block->getTranslated('author'))
            <footer class="mt-4 font-semibold">-- {{ $author }}</footer>
        @endif
    </div>

    @if($block->getDividerComponent())
        <x-dynamic-component
            :component="$block->getDividerComponent()"
            :to-background="$block->getDividerToBackground()"
        />
    @endif
</section>
```

---

## Translation

Atelier provides inline, per-field translation with a global locale switcher in the block modal.

### Making fields translatable

Append `->translatable()` as the **last** call in the field chain:

```php
TextInput::make('headline')
    ->required()
    ->maxLength(255)
    ->translatable();
```

The macro clones the field for each configured locale, wrapping them in a group that responds to the global locale selector via Alpine.js.

### Schema scanning

Atelier automatically detects translatable fields by scanning the block schema at save time. You do not need to maintain a `getTranslatableFields()` method. If you do define one, it is used as a performance optimisation on the frontend to avoid schema scanning on render.

### Retrieving translated values

```php
// In templates
$block->getTranslated('headline');         // current locale
$block->getTranslated('headline', 'fr');   // explicit locale
```

### Configuration

In `config/atelier.php`:

```php
'locales' => [
    'en' => 'English',
    'fr' => 'Francais',
],

'default_locale' => 'en',
```

Data is stored as one EAV row per locale per field: `headline/en`, `headline/fr`.

---

## Call to Actions (CTAs)

The `HasCallToActions` trait adds a repeater-based CTA system to any block.

### Adding CTAs

```php
use BlackpigCreatif\Atelier\Concerns\HasCallToActions;

class HeroBlock extends BaseBlock
{
    use HasCallToActions;

    public static function getSchema(): array
    {
        return [
            // ... content fields

            Section::make('Call to Action')
                ->schema([
                    static::getCallToActionsField()
                        ->maxItems(3),
                ])
                ->collapsible(),
        ];
    }
}
```

Each CTA item includes: **label** (translatable), **url**, **icon** (Heroicon name), **style** (from config), **new_tab** toggle.

### Rendering

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

### Helper methods

```php
$block->hasCallToActions(): bool
$block->getCallToActions(): array
$block->getCallToActionLabel($cta, ?string $locale): string
$block->getCallToActionStyleClass($cta): string
$block->getCallToActionTarget($cta): string   // '_blank' or '_self'
$block->isExternalUrl(string $url): bool
```

### Button styles

Configured in `config/atelier.php`:

```php
'features' => [
    'button_styles' => [
        'enabled' => true,
        'options' => [
            'primary'   => ['label' => 'Primary',   'class' => 'btn btn-primary'],
            'secondary' => ['label' => 'Secondary', 'class' => 'btn btn-secondary'],
            'alternate' => ['label' => 'Alternate', 'class' => 'btn btn-alternate'],
        ],
    ],
],
```

---

## Display Options

All blocks using `HasCommonOptions` gain a collapsible "Display Options" section with:

| Feature | Description | Config key |
|---------|-------------|-----------|
| **Background** | Predefined background colours (Tailwind classes + admin colour swatch) | `features.backgrounds` |
| **Spacing** | Balanced (equal `py-`) or individual (`pt-` / `pb-`) vertical padding | `features.spacing` |
| **Width** | Container, Narrow, Wide, or Full Width content constraint | `features.width` |
| **Dividers** | Decorative SVG dividers (wave, curve, diagonal, triangle) with colour transition to next section | `features.dividers` |
| **Published** | Toggle to show/hide on frontend (`is_published` column) | -- |

In templates, use `$block->getWrapperClasses()` on the outer `<section>` (background + spacing) and `$block->getContainerClasses()` on the inner `<div>` (width constraint).

---

## Media Handling

Atelier integrates with [Chambre Noir](https://github.com/blackpig-creatif/chambre-noir) for responsive images. Use `RetouchMediaUpload` in your schema and the `HasRetouchMedia` trait on your block:

```php
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;
use BlackpigCreatif\Atelier\Conversions\BlockHeroConversion;

class HeroBlock extends BaseBlock
{
    use HasRetouchMedia;

    public static function getSchema(): array
    {
        return [
            RetouchMediaUpload::make('background_image')
                ->preset(BlockHeroConversion::class)
                ->imageEditor()
                ->maxFiles(1),
        ];
    }
}
```

In templates:

```blade
{!! $block->getPicture('background_image', [
    'alt'           => $block->getTranslated('headline'),
    'class'         => 'w-full h-full object-cover',
    'fetchpriority' => 'high',
]) !!}
```

### Built-in conversion presets

| Preset | Conversions | Use case |
|--------|-------------|----------|
| `BlockHeroConversion` | thumb, medium, large, desktop, mobile + social (og, twitter) | Hero sections, full-width banners |
| `BlockGalleryConversion` | thumb, medium, large | Galleries, content images, carousels |

### Extracting images from blocks

The `HasAtelierMediaExtraction` trait (add to your model) provides convenience methods:

```php
use BlackpigCreatif\Atelier\Concerns\HasAtelierMediaExtraction;

class Page extends Model
{
    use HasAtelierBlocks, HasAtelierMediaExtraction;
}

$page->getHeroImageFromBlocks('large');
$page->getImageFromBlock('image', 'medium', ImageBlock::class);
```

---

## SEO Schema Generation

Atelier integrates with [Sceau](https://github.com/blackpig-creatif/sceau) to generate Schema.org JSON-LD from blocks.

```php
use BlackpigCreatif\Sceau\Services\PageSchemaBuilder;

public function show(Page $page)
{
    PageSchemaBuilder::build($page);
    return view('pages.show', ['page' => $page]);
}
```

Blocks contribute via `InteractsWithSchema` (inherited from `BaseBlock`):

- **Composite contribution** (e.g. TextBlock content feeds into an Article schema):
  ```php
  public function contributesToComposite(): bool { return true; }
  public function getCompositeContribution(): array {
      return ['type' => 'text', 'content' => strip_tags($this->get('content'))];
  }
  ```

- **Standalone schema** (e.g. VideoBlock generates a VideoObject):
  ```php
  public function hasStandaloneSchema(): bool { return true; }
  public function toStandaloneSchema(): ?array {
      return ['@context' => 'https://schema.org', '@type' => 'VideoObject', ...];
  }
  ```

---

## Configuration Reference

The `config/atelier.php` file:

```php
return [
    'locales'        => ['en' => 'English', 'fr' => 'Francais'],
    'default_locale' => 'en',

    'modal' => ['width' => '5xl'],

    'table_prefix' => 'atelier_',

    'blocks' => [
        // Default blocks when ->blocks() is called without arguments
    ],

    'features' => [
        'backgrounds' => ['enabled' => true, 'options' => [...]],
        'spacing'     => ['enabled' => true, 'options' => [...]],
        'width'       => ['enabled' => true, 'options' => [...]],
        'dividers'    => ['enabled' => true, 'options' => [...]],
        'button_styles' => ['enabled' => true, 'options' => [...]],
    ],

    'cache' => [
        'enabled' => true,
        'ttl'     => 3600,
        'prefix'  => 'atelier_block_',
    ],
];
```

---

## Architecture

Atelier uses a polymorphic EAV storage model:

- **`atelier_blocks`** -- polymorphic (`blockable_type`, `blockable_id`), stores block type, position, UUID, published status
- **`atelier_block_attributes`** -- stores each field value as a row with `key`, `value`, `type`, `locale`, `translatable`, `sort_order`, `collection_name`, `collection_index`

Repeater fields (e.g. CTAs) are stored as collection-based EAV rows, grouped by `collection_name` and `collection_index`.

At hydration time, the `AtelierBlock` model reconstructs the block instance, fills its data array, and caches the result per locale.

---

## Documentation

- [Block Configuration](docs/block-configuration.md) -- full field config and schema modification reference
- [Block Templates](docs/block-templates.md) -- template structure, helper methods, best practices

---

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG](docs/CHANGELOG.md).

## Credits

- [Stuart Hallewell](https://github.com/blackpig-creatif)
- [All Contributors](../../contributors)

## License

MIT. See [LICENSE](docs/LICENSE.md).
