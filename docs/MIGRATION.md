# Atelier Migration Guide

Guide to upgrading Atelier blocks to use responsive images.

## Overview

This guide covers:
- Migrating from FileUpload to RetouchMediaUpload
- Updating blocks to use HasRetouchMedia
- Converting to BlockGalleryConversion/BlockHeroConversion presets
- Updating Blade views for responsive rendering

## Block Migration

### Before (Standard FileUpload)

```php
<?php

namespace App\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasMedia;
use Filament\Forms\Components\FileUpload;

class MyBlock extends BaseBlock
{
    use HasMedia;

    public static function getSchema(): array
    {
        return [
            FileUpload::make('image')
                ->image()
                ->disk('public')
                ->directory('blocks/my-block')
                ->required(),
        ];
    }
}
```

### After (RetouchMediaUpload)

```php
<?php

namespace App\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;
use BlackpigCreatif\ChambreNoir\Forms\Components\RetouchMediaUpload;

class MyBlock extends BaseBlock
{
    use HasRetouchMedia;

    public static function getSchema(): array
    {
        return [
            RetouchMediaUpload::make('image')
                ->preset(BlockGalleryConversion::class)
                ->imageEditor()
                ->disk('public')
                ->directory('blocks/my-block')
                ->required(),
        ];
    }
}
```

**Changes:**
1. ✅ Import `HasRetouchMedia` from ChambreNoir (not Atelier's `HasMedia`)
2. ✅ Import `RetouchMediaUpload` instead of `FileUpload`
3. ✅ Import conversion preset (e.g., `BlockGalleryConversion`)
4. ✅ Use `->preset()` instead of manual config
5. ✅ Remove `->image()` (automatic in RetouchMediaUpload)
6. ✅ Optional: Add `->imageEditor()` for built-in editing

## Trait Migration

### HasMedia Trait Removed

The `HasMedia` trait has been removed from Atelier.

**Before:**
```php
use BlackpigCreatif\Atelier\Concerns\HasMedia;

class MyBlock extends BaseBlock
{
    use HasCommonOptions, HasMedia;
}
```

**After:**
```php
use BlackpigCreatif\ChambreNoir\Concerns\HasRetouchMedia;

class MyBlock extends BaseBlock
{
    use HasCommonOptions, HasRetouchMedia;
}
```

**Method compatibility:**
- ✅ `getMediaUrl()` - Still works
- ✅ `getMediaUrls()` - Still works
- ✅ `getPicture()` - NEW responsive method
- ✅ `getSrcset()` - NEW responsive method

## View Migration

### Before (Simple Image Tag)

```blade
{{-- resources/views/blocks/my-block.blade.php --}}
<div class="block">
    @if($image = $block->getMediaUrl('image'))
        <img src="{{ $image }}" alt="{{ $block->get('title') }}" class="w-full">
    @endif
</div>
```

### After (Responsive Picture Element)

```blade
{{-- resources/views/blocks/my-block.blade.php --}}
<div class="block">
    {!! $block->getPicture('image', [
        'alt' => $block->get('title'),
        'class' => 'w-full h-auto',
        'loading' => 'lazy'
    ]) !!}
</div>
```

**Or with srcset:**

```blade
<div class="block">
    <img {!! $block->getSrcset('image') !!}
        alt="{{ $block->get('title') }}"
        class="w-full h-auto"
        loading="lazy">
</div>
```

## Built-in Block Migration

All built-in Atelier image blocks have been updated:

### HeroBlock
- ✅ Uses `HasRetouchMedia`
- ✅ Uses `BlockHeroConversion`
- ✅ Desktop/mobile conversions

### ImageBlock
- ✅ Uses `HasRetouchMedia`
- ✅ Uses `BlockGalleryConversion`
- ✅ Thumb/medium/large conversions

### GalleryBlock
- ✅ Uses `HasRetouchMedia`
- ✅ Uses `BlockGalleryConversion`
- ✅ Multiple images support

### CarouselBlock
- ✅ Uses `HasRetouchMedia`
- ✅ Uses `BlockGalleryConversion`
- ✅ Slider-optimized conversions

### TextWithImageBlock
- ✅ Uses `HasRetouchMedia`
- ✅ Uses `BlockGalleryConversion`

### TextWithTwoImagesBlock
- ✅ Uses `HasRetouchMedia`
- ✅ Uses `BlockGalleryConversion`

**No action needed** if you're using built-in blocks.

## Choosing the Right Preset

### Use BlockHeroConversion for:
- Hero sections
- Full-width banners
- Large featured images
- Images needing mobile/desktop variants

### Use BlockGalleryConversion for:
- Galleries
- Carousels
- Content images
- Standard image blocks
- Thumbnails

### Create Custom Presets for:
- Product images
- Avatars
- Specific aspect ratios
- Special use cases

See [ChambreNoir Custom Presets Guide](../chambre-noir/CUSTOM_PRESETS.md).

## Block-by-Block Migration Examples

### Hero Block

**Before:**
```php
FileUpload::make('background_image')
    ->image()
    ->disk('public')
    ->directory('blocks/hero');
```

**After:**
```php
use BlackpigCreatif\Atelier\Conversions\BlockHeroConversion;

RetouchMediaUpload::make('background_image')
    ->preset(BlockHeroConversion::class)
    ->imageEditor()
    ->disk('public')
    ->directory('blocks/hero');
```

**View Before:**
```blade
<div style="background-image: url('{{ $block->getMediaUrl('background_image') }}')">
```

**View After:**
```blade
{!! $block->getPicture('background_image', [
    'alt' => $block->get('headline'),
    'class' => 'w-full h-full object-cover',
    'fetchpriority' => 'high'
]) !!}
```

### Gallery Block

**Before:**
```php
FileUpload::make('images')
    ->image()
    ->multiple()
    ->disk('public')
    ->directory('blocks/gallery');
```

**After:**
```php
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;

RetouchMediaUpload::make('images')
    ->preset(BlockGalleryConversion::class)
    ->multiple()
    ->reorderable()
    ->imageEditor()
    ->disk('public')
    ->directory('blocks/gallery');
```

**View Before:**
```blade
@foreach($block->getMediaUrls('images') as $url)
    <img src="{{ $url }}" alt="Gallery">
@endforeach
```

**View After:**
```blade
@foreach($block->get('images') as $imageData)
    {!! $block->getPicture('images', [
        'alt' => 'Gallery Image',
        'class' => 'gallery-item',
        'loading' => 'lazy'
    ]) !!}
@endforeach
```

### Content Image Block

**Before:**
```php
FileUpload::make('image')
    ->image()
    ->disk('public')
    ->directory('blocks/images');

TextInput::make('caption');
```

**After:**
```php
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;

RetouchMediaUpload::make('image')
    ->preset(BlockGalleryConversion::class)
    ->imageEditor()
    ->disk('public')
    ->directory('blocks/images');

TextInput::make('caption');
```

**View Before:**
```blade
<figure>
    <img src="{{ $block->getMediaUrl('image') }}" alt="{{ $block->get('caption') }}">
    <figcaption>{{ $block->get('caption') }}</figcaption>
</figure>
```

**View After:**
```blade
<figure>
    {!! $block->getPicture('image', [
        'alt' => $block->get('caption'),
        'class' => 'w-full h-auto',
        'loading' => 'lazy'
    ]) !!}
    @if($caption = $block->get('caption'))
        <figcaption class="text-sm text-gray-600">{{ $caption }}</figcaption>
    @endif
</figure>
```

## Data Migration

### Existing Block Content

Blocks created before migration will still work but won't have:
- Preset references stored
- Responsive image markup
- Automatic conversion generation

**Options:**

#### Option 1: Content Re-save (Recommended)
Ask content editors to:
1. Edit the page in admin
2. Re-upload or keep the image
3. Save the page

This will trigger conversion and store preset references.

#### Option 2: Programmatic Migration

```php
use Illuminate\Support\Facades\Artisan;

// In a migration or command
Artisan::call('atelier:migrate-images', [
    '--block' => 'HeroBlock',
    '--preset' => \BlackpigCreatif\Atelier\Conversions\BlockHeroConversion::class,
]);
```

**Custom migration script:**

```php
use App\Models\Page;
use BlackpigCreatif\Atelier\Conversions\BlockGalleryConversion;

Page::query()->each(function ($page) {
    $blocks = $page->blocks;

    foreach ($blocks as &$block) {
        if ($block['type'] === 'image-block' && isset($block['data']['image'])) {
            $imageData = $block['data']['image'];

            // Add preset if missing
            if (is_array($imageData) && !isset($imageData['preset'])) {
                $imageData['preset'] = BlockGalleryConversion::class;
                $block['data']['image'] = $imageData;
            }
        }
    }

    $page->blocks = $blocks;
    $page->save();
});
```

## Testing Your Migration

### Manual Testing

1. **Create new block** with RetouchMediaUpload
2. **Upload image** - check conversions are created
3. **View page** - inspect HTML for responsive markup
4. **Test responsive behavior** - resize browser window
5. **Check performance** - verify lazy loading works

### Automated Testing

```php
use Tests\TestCase;
use App\Models\Page;

class BlockMigrationTest extends TestCase
{
    public function test_blocks_have_preset_references(): void
    {
        $page = Page::factory()->create();

        // Assuming page has blocks
        foreach ($page->blocks as $block) {
            if (isset($block['data']['image'])) {
                $this->assertArrayHasKey('preset', $block['data']['image']);
            }
        }
    }

    public function test_responsive_methods_work(): void
    {
        $page = Page::factory()->create();
        $block = $page->blocks->first();

        $picture = $block->getPicture('image', ['alt' => 'Test']);

        $this->assertStringContains('<picture>', $picture);
        $this->assertStringContains('<source', $picture);
        $this->assertStringContains('<img', $picture);
    }
}
```

## Rollback Plan

If you need to rollback:

1. **Revert block classes** to use `FileUpload` and `HasMedia`
2. **Revert Blade views** to use `getMediaUrl()`
3. **Keep existing image data** - it will still work

No data loss occurs during rollback.

## Breaking Changes

### Removed Traits

- ❌ `BlackpigCreatif\Atelier\Concerns\HasMedia` - Use `HasRetouchMedia` from ChambreNoir
- ❌ `BlackpigCreatif\Atelier\Concerns\HasSpatieMedia` - Not supported

### Changed Behavior

- `HasFileUpload` no longer supports ChambreNoir conversions
- `->image()` is automatic in RetouchMediaUpload (no need to call)

### Method Changes

No method signatures changed in `HasRetouchMedia` - fully backward compatible with old `HasMedia` methods.

## Migration Checklist

- [ ] Update all custom blocks to use `RetouchMediaUpload`
- [ ] Replace `HasMedia` with `HasRetouchMedia` in all blocks
- [ ] Add appropriate preset to each `RetouchMediaUpload` field
- [ ] Update Blade views to use `getPicture()` or `getSrcset()`
- [ ] Test block creation in admin
- [ ] Test responsive rendering on frontend
- [ ] Optional: Migrate existing block data
- [ ] Optional: Add lazy loading and fetch priority attributes
- [ ] Train content editors on new image editor features

## Timeline Recommendation

**Week 1:** Update 2-3 blocks, test thoroughly
**Week 2:** Update remaining blocks
**Week 3:** Update views and test responsive behavior
**Week 4:** Deploy to staging
**Week 5:** Train content editors
**Week 6:** Deploy to production

## Next Steps

- [Responsive Images Usage](RESPONSIVE_IMAGES_USAGE.md) - Using responsive images in blocks
- [ChambreNoir Migration Guide](../chambre-noir/MIGRATION.md) - Package-level migration details
- [ChambreNoir Custom Presets](../chambre-noir/CUSTOM_PRESETS.md) - Creating custom conversion presets

## Support

For issues or questions:
- Check the [Responsive Images Usage guide](RESPONSIVE_IMAGES_USAGE.md)
- Review built-in block implementations for examples
- Report issues on GitHub
