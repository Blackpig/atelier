# Responsive Images in Atelier Blocks

Complete guide to using responsive images in your Atelier blocks.

## Quick Start

### 1. Use HasRetouchMedia Trait

```php
<?php

namespace App\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;

class MyCustomBlock extends BaseBlock
{
    use HasRetouchMedia;

    // ... rest of your block
}
```

### 2. Use RetouchMediaUpload with Preset

```php
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;

public static function getSchema(): array
{
    return [
        RetouchMediaUpload::make('featured_image')
            ->preset(BlockGalleryConversion::class)
            ->imageEditor()
            ->required(),
    ];
}
```

### 3. Render in Your Blade View

```blade
{{-- resources/views/blocks/my-custom-block.blade.php --}}
<div class="my-block">
    {!! $block->getPicture('featured_image', [
        'alt' => $block->get('title'),
        'class' => 'w-full h-auto'
    ]) !!}
</div>
```

## Built-in Conversion Presets

Atelier includes two ready-to-use conversion presets:

### BlockHeroConversion

For hero sections and large featured images:

```php
use BlackpigCreatif\Atelier\Conversions\BlockHeroConversion;

RetouchMediaUpload::make('hero_image')
    ->preset(BlockHeroConversion::class)
    ->imageEditor();
```

**Conversions:**
- `thumb`: 200x200 (square crop)
- `medium`: 800x600
- `large`: 1920x1080
- `desktop`: 1920x1080
- `mobile`: 768x1024 (portrait)

**Responsive Config:**
- Desktop: 1024px+ width
- Medium: 768px+ width
- Mobile: <767px width

### BlockGalleryConversion

For galleries, carousels, and content images:

```php
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;

RetouchMediaUpload::make('gallery_images')
    ->preset(BlockGalleryConversion::class)
    ->multiple()
    ->imageEditor();
```

**Conversions:**
- `thumb`: 200x200 (square crop)
- `medium`: 800x600
- `large`: 1600x1200

**Responsive Config:**
- Large: 1024px+ width
- Medium: 640px+ width
- Thumb: fallback

## Block Examples

### Hero Block

```php
// src/Blocks/HeroBlock.php
<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Conversions\BlockHeroConversion;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;

class HeroBlock extends BaseBlock
{
    use HasRetouchMedia;

    public static function getSchema(): array
    {
        return [
            TextInput::make('headline')->required(),

            RetouchMediaUpload::make('background_image')
                ->preset(BlockHeroConversion::class)
                ->imageEditor()
                ->required(),
        ];
    }
}
```

```blade
{{-- resources/views/blocks/hero.blade.php --}}
<section class="hero relative min-h-[600px] flex items-center justify-center">
    {{-- Background with picture element --}}
    <div class="absolute inset-0 -z-10">
        {!! $block->getPicture('background_image', [
            'alt' => '',
            'class' => 'w-full h-full object-cover',
            'fetchpriority' => 'high'
        ]) !!}
    </div>

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/40 -z-10"></div>

    {{-- Content --}}
    <div class="container mx-auto px-4 text-center text-white">
        <h1 class="text-5xl font-bold">{{ $block->get('headline') }}</h1>
    </div>
</section>
```

### Image Block

```php
// src/Blocks/ImageBlock.php
<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;

class ImageBlock extends BaseBlock
{
    use HasRetouchMedia;

    public static function getSchema(): array
    {
        return [
            RetouchMediaUpload::make('image')
                ->preset(BlockGalleryConversion::class)
                ->imageEditor()
                ->required(),

            TextInput::make('caption')->maxLength(255),
        ];
    }
}
```

```blade
{{-- resources/views/blocks/image.blade.php --}}
<figure class="my-8">
    {!! $block->getPicture('image', [
        'alt' => $block->get('caption') ?? '',
        'class' => 'w-full h-auto rounded-lg shadow-lg',
        'loading' => 'lazy'
    ]) !!}

    @if($caption = $block->get('caption'))
        <figcaption class="mt-2 text-sm text-gray-600 text-center">
            {{ $caption }}
        </figcaption>
    @endif
</figure>
```

### Gallery Block

```php
// src/Blocks/GalleryBlock.php
<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;

class GalleryBlock extends BaseBlock
{
    use HasRetouchMedia;

    public static function getSchema(): array
    {
        return [
            RetouchMediaUpload::make('images')
                ->preset(BlockGalleryConversion::class)
                ->multiple()
                ->reorderable()
                ->imageEditor()
                ->minFiles(3)
                ->maxFiles(20),
        ];
    }
}
```

```blade
{{-- resources/views/blocks/gallery.blade.php --}}
<div class="gallery grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($block->get('images') as $imageData)
        <div class="gallery-item">
            {!! $block->getPicture('images', [
                'alt' => 'Gallery Image',
                'class' => 'w-full h-full object-cover rounded-lg hover:scale-105 transition-transform',
                'loading' => 'lazy'
            ]) !!}
        </div>
    @endforeach
</div>
```

### Text + Image Block

```php
// src/Blocks/TextWithImageBlock.php
<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;

class TextWithImageBlock extends BaseBlock
{
    use HasRetouchMedia;

    public static function getSchema(): array
    {
        return [
            RichEditor::make('content')->required(),

            RetouchMediaUpload::make('image')
                ->preset(BlockGalleryConversion::class)
                ->imageEditor()
                ->required(),

            Select::make('image_position')
                ->options(['left' => 'Left', 'right' => 'Right'])
                ->default('right'),
        ];
    }
}
```

```blade
{{-- resources/views/blocks/text-with-image.blade.php --}}
<div class="flex flex-col {{ $block->get('image_position') === 'left' ? 'md:flex-row-reverse' : 'md:flex-row' }} gap-8 items-center">
    <div class="md:w-1/2 prose">
        {!! $block->get('content') !!}
    </div>

    <div class="md:w-1/2">
        {!! $block->getPicture('image', [
            'alt' => '',
            'class' => 'w-full h-auto rounded-lg',
            'loading' => 'lazy'
        ]) !!}
    </div>
</div>
```

## Advanced Techniques

### Conditional Loading Strategies

```blade
{{-- Load hero with high priority, others lazy --}}
@if($loop->first)
    {!! $block->getPicture('image', ['fetchpriority' => 'high']) !!}
@else
    {!! $block->getPicture('image', ['loading' => 'lazy']) !!}
@endif
```

### Art Direction with Multiple Images

```blade
{{-- Different images for mobile vs desktop --}}
@if($block->has('mobile_image') && $block->has('desktop_image'))
    <picture>
        <source
            srcset="{{ $block->getMediaUrl('desktop_image', 'large') }}"
            media="(min-width: 1024px)">
        <source
            srcset="{{ $block->getMediaUrl('mobile_image', 'medium') }}"
            media="(max-width: 1023px)">
        <img
            src="{{ $block->getMediaUrl('desktop_image', 'large') }}"
            alt="{{ $block->get('title') }}"
            class="w-full">
    </picture>
@endif
```

### Lightbox Integration

```blade
<div class="gallery grid grid-cols-3 gap-4">
    @foreach($block->get('images') as $imageData)
        <a
            href="{{ $block->getMediaUrl('images', 'large') }}"
            data-lightbox="gallery">
            <img
                {!! $block->getSrcset('images') !!}
                alt="Gallery"
                class="rounded-lg cursor-pointer hover:opacity-90 transition">
        </a>
    @endforeach
</div>
```

### Background Images

```blade
{{-- Use getMediaUrl for background-image CSS --}}
<div
    class="hero bg-cover bg-center"
    style="background-image: url('{{ $block->getMediaUrl('background', 'large') }}')">
    {{-- Content --}}
</div>
```

### Preloading Critical Images

```blade
{{-- In layout head for LCP optimization --}}
@push('head')
    @if($firstBlock = $blocks->first())
        @if($firstBlock->has('hero_image'))
            <link
                rel="preload"
                as="image"
                href="{{ $firstBlock->getMediaUrl('hero_image', 'large') }}">
        @endif
    @endif
@endpush
```

## Performance Checklist

✅ Use `fetchpriority="high"` for above-the-fold images
✅ Use `loading="lazy"` for below-the-fold images
✅ Preload critical hero images
✅ Use appropriate conversions based on context
✅ Add `decoding="async"` for better rendering
✅ Use `getPicture()` for art direction needs
✅ Use `getSrcset()` for simple resolution switching
✅ Add meaningful alt text for accessibility

## Troubleshooting

### Images Not Displaying

**Check:**
1. Trait is added: `use HasRetouchMedia;`
2. Preset is set: `->preset(BlockGalleryConversion::class)`
3. Storage link exists: `php artisan storage:link`
4. Image data is present: `@dump($block->get('image'))`

### Wrong Image Sizes Loading

**Check:**
1. Responsive config in conversion preset
2. Browser viewport width
3. Device pixel ratio
4. Media queries in generated HTML

### Srcset Not Working

**Check:**
1. Preset stored with image: `$block->get('image')['preset']`
2. Conversions exist: `$block->get('image')['conversions']`
3. Browser supports srcset (all modern browsers do)

## Next Steps

- [ChambreNoir Responsive Images Guide](../chambre-noir/RESPONSIVE_IMAGES.md) - Deep dive into responsive images
- [Creating Custom Presets](../chambre-noir/CUSTOM_PRESETS.md) - Build your own conversion presets
- [Migration Guide](MIGRATION.md) - Update existing blocks to use responsive images
