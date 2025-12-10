# Atelier

[![Latest Version on Packagist](https://img.shields.io/packagist/v/blackpig-creatif/atelier.svg?style=flat-square)](https://packagist.org/packages/blackpig-creatif/atelier)
[![Total Downloads](https://img.shields.io/packagist/dt/blackpig-creatif/atelier.svg?style=flat-square)](https://packagist.org/packages/blackpig-creatif/atelier)

**L'art du contenu** - Artisanal content blocks for FilamentPHP v4

Atelier brings a craftsman's approach to content management in FilamentPHP v4. Unlike rigid page builders, Atelier gives you the flexibility to forge custom content blocks that are precisely shaped to your needs.

Built on a sophisticated polymorphic architecture with first-class translation support, Atelier stores your blocks intelligently - keeping your database lean while maintaining the flexibility to create any block type you can imagine.

## Philosophy

In a master's atelier, every element is placed with intention. Every detail considered. Atelier brings that same philosophy to FilamentPHP - where content blocks are crafted with the care they deserve.

- 🎨 **Artisanal**: Bespoke blocks, not templates
- 🏗️ **Architectural**: Clean, polymorphic database structure  
- 🌍 **Translatable**: First-class multi-language support
- 🔧 **Extensible**: Traits and abstracts for rapid customization
- ⚡ **Performant**: Smart caching and eager loading
- 👁️ **Live Preview**: Preview blocks before publishing

## Requirements

- PHP 8.2+
- Laravel 11.0+
- FilamentPHP 4.0+
- Spatie Laravel Media Library 11.0+

## Installation

You can install the package via composer:
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

This is the contents of the published config file:
```php
return [
    'locales' => [
        'en' => 'English',
        'fr' => 'Français',
    ],
    
    'default_locale' => 'en',
    
    // ... more configuration
];
```

Optionally, you can publish the views:
```bash
php artisan vendor:publish --tag="atelier-views"
```

## Quick Start

### 1. Add the trait to your model
```php
use Blackpigcreatif\Atelier\Concerns\HasFlexibleBlocks;

class Page extends Model
{
    use HasFlexibleBlocks;
}
```

### 2. Add the BlockManager to your Filament resource
```php
use Blackpigcreatif\Atelier\Forms\Components\BlockManager;
use Blackpigcreatif\Atelier\Blocks\HeroBlock;
use Blackpigcreatif\Atelier\Blocks\TextWithTwoImagesBlock;

public static function form(Form $form): Form
{
    return $form->schema([
        // ... other fields
        
        BlockManager::make('blocks')
            ->blocks([
                HeroBlock::class,
                TextWithTwoImagesBlock::class,
            ])
            ->livePreview() // Enable preview for each block
            ->blockNumbers(false)
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

## Creating Custom Blocks

Create a new block class:
```php
namespace App\Blocks;

use Blackpigcreatif\Atelier\Abstracts\BaseBlock;
use Blackpigcreatif\Atelier\Concerns\HasCommonOptions;
use Blackpigcreatif\Atelier\Forms\Components\TranslatableContainer;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
<section class="{{ $block->getWrapperClasses() }}">
    <div class="{{ $block->getContainerClasses() }}">
        <blockquote class="text-2xl italic font-light">
            <p>"{{ $block->getTranslated('quote') }}"</p>
            <footer class="mt-4 text-lg font-semibold">
                — {{ $block->getTranslated('author') }}
            </footer>
        </blockquote>
    </div>
</section>
```

## Built-in Blocks

Atelier ships with two example blocks:

- **HeroBlock**: Full-width hero section with background image and CTA
- **TextWithTwoImagesBlock**: Rich text content with two images in various layouts

## Publishing Block Templates

Atelier ships with beautifully crafted, production-ready block templates. To customize them for your design:
```bash
# Publish just the block templates
php artisan vendor:publish --tag="atelier-block-templates"
```

This will copy the templates to `resources/views/vendor/atelier/blocks/` where your designer can modify them.

Each template includes:
- ✅ Comprehensive PHPDoc comments explaining available variables
- ✅ Responsive design with Tailwind CSS
- ✅ Accessibility features (ARIA labels, semantic HTML)
- ✅ Dark mode support
- ✅ Loading states and animations
- ✅ SEO-friendly markup

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

Enable live preview in your Filament resource:
```php
BlockManager::make('blocks')
    ->blocks([...])
    ->livePreview() // Enable preview button for each block
    ->collapsed()
    ->collapsible();
```

This adds a preview button (👁️) to each block that opens a modal showing how the block will render on the frontend.

## Available Traits

### HasCommonOptions

Adds background color, spacing, and width options:
```php
use Blackpigcreatif\Atelier\Concerns\HasCommonOptions;

class MyBlock extends BaseBlock
{
    use HasCommonOptions;
    
    public static function getSchema(): array
    {
        return [
            // Your fields...
            
            ...static::getCommonOptionsSchema(),
        ];
    }
}
```

### HasMedia

Simplifies Spatie Media Library integration:
```php
use Blackpigcreatif\Atelier\Concerns\HasMedia;

// In your block
$image = $block->getMedia('image_field');
$url = $block->getMediaUrl('image_field', 'large');
```

### HasFlexibleBlocks

Add to your parent models (Page, Post, etc):
```php
use Blackpigcreatif\Atelier\Concerns\HasFlexibleBlocks;

class Page extends Model
{
    use HasFlexibleBlocks;
}

// Access blocks
$page->blocks;
$page->publishedBlocks;
$page->renderBlocks($locale);
```

## Testing
```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Stuart Hallewell](https://github.com/blackpig-creatif)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
