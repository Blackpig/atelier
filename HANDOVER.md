# Atelier Package - Development Handover

**Last Updated:** 2025-12-16
**Current Status:** Block Preview & Skeleton Templates Complete

---

## 🎯 Current State

### ✅ Completed Features

#### Block Preview System
- **Preview Modal**: Reuses existing `BlockFormModal` slide-over for preview mode
- **State Management**: Properly toggles between preview/edit modes without conflicts
- **Preview Button**: Eye icon on each block card opens preview in slide-over
- **Read-Only Mode**: Auto-detects `ViewRecord` pages and hides edit/delete/add buttons
- **Block Rendering**: Successfully renders blocks using default Atelier view templates

#### Block Templates (All Created)
- ✅ `hero-block.blade.php` - Full-width hero with background, headline, CTA (already existed)
- ✅ `text-block.blade.php` - Title + rich text content with alignment
- ✅ `image-block.blade.php` - Single image with caption, aspect ratio control
- ✅ `text-with-image-block.blade.php` - Side-by-side text and image layout
- ✅ `gallery-block.blade.php` - Responsive grid of images
- ✅ `carousel-block.blade.php` - Basic horizontal scroll carousel
- ✅ `video-block.blade.php` - YouTube/Vimeo embed with direct URL support
- ✅ `text-with-two-images-block.blade.php` - Complex layout (already existed)

#### UI Improvements
- Custom SVG icons working in block picker and block list
- Improved spacing between action buttons (`gap-3`)
- Better hover states on block picker cards
- Drag-and-drop reordering working smoothly
- Empty states with helpful messages

#### Architecture
- EAV (Entity-Attribute-Value) pattern for flexible block storage
- Translation support via `TranslatableContainer` component
- Common options (background, spacing, container width) via concerns
- Block scaffolding command for rapid development

---

## 🔴 Known Issues & Limitations

### 1. File Uploads Not Working ⚠️ HIGH PRIORITY
**Issue:** File upload fields (images, videos) are not being properly saved/loaded

**Symptoms:**
- Hero block `background_image` field not showing uploaded images
- Preview shows white rectangle instead of background image
- Gallery/Carousel blocks not displaying uploaded images
- Image paths not being correctly stored in EAV attributes

**Affected Blocks:**
- HeroBlock (`background_image`)
- ImageBlock (`image`)
- GalleryBlock (`images`)
- CarouselBlock (`images`)
- TextWithImageBlock (`image`)
- TextWithTwoImagesBlock (`image_left`, `image_right`)

**Suspected Causes:**
- FileUpload component returns array format: `{"uuid": "path/to/file.jpg"}`
- Current EAV save logic may not properly extract file paths
- `isFileUploadValue()` and `extractFilePath()` methods in `BlockManager.php` exist but may need refinement
- Livewire temporary upload handling needs investigation

**Code Locations:**
- `/Users/stuart/Clients/Sites/filament-packages/atelier/src/Forms/Components/BlockManager.php:324-432`
  - `isFileUploadValue()` method
  - `extractFilePath()` method
  - `saveBlockAttributes()` method
- Block schema definitions in `/Users/stuart/Clients/Sites/filament-packages/atelier/src/Blocks/`

### 2. Preview Defaults Working
**Status:** ✅ RESOLVED in this session

The preview now uses sensible defaults for all fields to avoid "undefined variable" errors:
```php
$defaults = [
    'height' => 'min-h-[600px]',
    'text_color' => 'text-white',
    'content_alignment' => 'text-center items-center',
    'overlay_opacity' => '40',
    'cta_new_tab' => false,
    'text_alignment' => 'text-left',
    'max_width' => 'max-w-3xl',
    'alignment' => 'center',
    'aspect_ratio' => 'aspect-auto',
    'lightbox' => true,
    'columns' => '3',
    'gap' => 'gap-4',
    // ... etc
];
```

### 3. Template Limitations (By Design - Not Issues)
- **Gallery/Image lightbox:** Not implemented (noted in templates)
- **Carousel functionality:** Basic CSS scroll only, no JS carousel library integrated
- **Video uploads:** Only embed URLs supported, not direct file uploads
- **Rich text rendering:** Basic prose classes, no custom styling

---

## 📋 Next Priorities

### 🔥 HIGH PRIORITY: File Upload Support
**Estimated Effort:** Medium (2-3 hours)
**Why It Matters:** Blocks without images are not very useful for content editors

**Tasks:**
1. **Investigate FileUpload format:**
   - Determine exact format returned by Filament FileUpload component
   - Test with single vs multiple file uploads
   - Check if format differs between Livewire temporary uploads and saved files

2. **Fix `extractFilePath()` method:**
   - Ensure it correctly extracts paths from Livewire upload format
   - Handle both single files (string or `{"uuid": "path"}`) and multiple files (array)
   - Preserve file arrays for Gallery/Carousel blocks

3. **Update `saveBlockAttributes()` method:**
   - Ensure file paths are saved correctly to EAV attributes
   - Test with translatable file upload fields (if any)
   - Verify saved format matches what templates expect

4. **Update block templates:**
   - Ensure templates correctly handle file path retrieval
   - Verify `asset('storage/' . $path)` works for all blocks
   - Add fallback handling for missing images

5. **Test all affected blocks:**
   - HeroBlock background image
   - ImageBlock single image
   - Gallery & Carousel multiple images
   - TextWithImage layouts

**Testing Checklist:**
- [ ] Upload image to HeroBlock, save, reload - image displays
- [ ] Upload image to ImageBlock, preview works
- [ ] Upload multiple images to Gallery, all display
- [ ] Upload multiple images to Carousel, slider works
- [ ] Edit existing block with image - image persists
- [ ] Delete image from block - removes correctly

### 🟡 MEDIUM PRIORITY

#### 1. Page-Level Language Switcher
**Estimated Effort:** Medium-High (3-4 hours)
**Dependencies:** Spatie Translatable integration

**Context:**
- Currently using `TranslatableContainer` for per-field translation UI
- Need page-level switcher to change entire form language at once
- Should integrate with existing Spatie translatable setup
- Reference: `/Users/stuart/Clients/Sites/filament-packages/atelier/src/Forms/Components/TranslatableContainer.php`

**Considerations:**
- Where to place language switcher (header action? page action?)
- State management - switching language should reload all translatable fields
- Should it persist across page reloads?
- Default locale from config: `config('atelier.default_locale', 'en')`
- Available locales from config: `config('atelier.locales', ['en' => 'English'])`

#### 2. Complex Field Support (Repeaters, JSON)
**Estimated Effort:** High (4-6 hours)

**Current Limitation:**
- EAV system handles simple types: string, integer, float, boolean, array (as JSON)
- Repeater fields might need special handling
- JSON-based fields (like RichEditor TipTap format) should work but untested

**Investigation Needed:**
- Test RichEditor content saving/loading
- Test nested Repeater fields
- Determine if current JSON encoding/decoding is sufficient

#### 3. Spatie Media Library Support
**Estimated Effort:** High (5-7 hours)

**Context:**
- Some blocks reference `$block->getMedia()` method (see HeroBlock template line 33)
- `HasMedia` concern exists in `/Users/stuart/Clients/Sites/filament-packages/atelier/src/Concerns/HasMedia.php`
- Would enable advanced image management (conversions, responsive images, etc.)

**Decision Needed:**
- Should we fully implement Spatie Media Library?
- Or stick with simple FileUpload + storage paths?
- Media Library adds complexity but provides better image handling

#### 4. Remove TranslatableContainer Dependency
**Estimated Effort:** Medium-High (4-5 hours)

**Context:**
- Current translation UI uses custom `TranslatableContainer` wrapper
- Works but adds extra nesting in form schema
- More elegant solution: Custom form field that handles translation internally
- Would require extending Filament's base field components

**User Quote:**
> "More elegant would be to have the component be able to deal with the locale keyed form data - and not need to be wrappered in the translatable container"

---

## 🏗️ Architecture Overview

### Block Storage (EAV Pattern)

**Tables:**
- `atelier_blocks` - Core block records
  - `id`, `uuid`, `blockable_type`, `blockable_id`, `block_type`, `position`, `is_published`
- `atelier_block_attributes` - Block field values
  - `id`, `block_id`, `key`, `value`, `type`, `locale`, `translatable`, `sort_order`

**Key Points:**
- Each block field becomes one or more attributes
- Translatable fields create one attribute per locale
- Non-translatable fields create single attribute with `locale = null`
- JSON/array values stored as JSON strings with `type = 'array'`

**Example - Hero Block with Translation:**
```php
// Block record
uuid: '123-456-789'
block_type: 'BlackpigCreatif\Atelier\Blocks\HeroBlock'
position: 0

// Attributes
key='headline', value='Welcome', locale='en', translatable=true
key='headline', value='Bienvenue', locale='fr', translatable=true
key='height', value='min-h-[600px]', locale=null, translatable=false
key='overlay_opacity', value='40', locale=null, translatable=false
```

### Block Lifecycle

**1. Loading (Hydration):**
```
BlockManager::afterStateHydrated()
  → Load blocks from DB with attributes
  → extractBlockAttributes() - converts DB attributes to array
  → Handles translatable vs non-translatable fields
  → Sets component state
```

**2. Editing:**
```
Alpine JS manages block state in browser
  → User clicks Edit
  → Livewire.dispatch('openBlockFormModal')
  → BlockFormModal opens with block data
  → Form fills from block.data
```

**3. Saving:**
```
BlockFormModal::save()
  → Gets form state
  → dispatch('block-form-saved') with uuid, type, data
  → Alpine updates blocks array
  → Mark needsSync = true
  → On Livewire commit: sync to $wire.set(statePath, blocks)
```

**4. Persisting:**
```
BlockManager::saveRelationshipsUsing()
  → Called when parent form saves
  → Loops through block state
  → updateOrCreate blocks by UUID
  → saveBlockAttributes() - converts array to EAV
  → Handles translatable fields (one attr per locale)
  → Handles file uploads (extractFilePath)
```

### Preview Rendering

**Flow:**
```
User clicks eye icon
  → Alpine openBlockPreview(uuid)
  → Livewire.dispatch('openBlockPreview', {blockType, data})
  → BlockFormModal::openPreview()
  → generatePreview() creates block instance
  → Merges defaults with data
  → block.fill(mergedData)
  → Renders view with block.getViewData()
```

**Key Files:**
- `/Users/stuart/Clients/Sites/filament-packages/atelier/src/Livewire/BlockFormModal.php:143-235`
- Preview defaults: lines 175-209
- View rendering: lines 225-228

---

## 📁 Key File Reference

### Core Components
| File | Purpose | Lines of Interest |
|------|---------|-------------------|
| `src/Forms/Components/BlockManager.php` | Main form field component | 184-252 (hydration), 300-472 (save) |
| `src/Livewire/BlockFormModal.php` | Modal for editing/preview | 62-86 (open), 88-125 (save), 143-235 (preview) |
| `resources/views/forms/components/block-manager.blade.php` | Alpine JS integration | 31-194 (Alpine data/methods) |
| `resources/views/forms/components/block-card.blade.php` | Individual block UI | Full file |
| `resources/views/livewire/block-form-modal.blade.php` | Modal template | 8-49 (conditional preview/form) |

### Block Classes
All in `/Users/stuart/Clients/Sites/filament-packages/atelier/src/Blocks/`
- `HeroBlock.php` - Full implementation with file upload
- `TextBlock.php` - Simple text content
- `ImageBlock.php` - Single image with file upload
- `GalleryBlock.php` - Multiple images
- `CarouselBlock.php` - Image slider
- `VideoBlock.php` - Video embed
- `TextWithImageBlock.php` - Layout block
- `TextWithTwoImagesBlock.php` - Complex layout

### Block Templates
All in `/Users/stuart/Clients/Sites/FilamentPHP/resources/views/vendor/atelier/blocks/`
- `hero-block.blade.php` (147 lines)
- `text-block.blade.php` (35 lines)
- `image-block.blade.php` (62 lines)
- `text-with-image-block.blade.php` (72 lines)
- `gallery-block.blade.php` (69 lines)
- `carousel-block.blade.php` (92 lines)
- `video-block.blade.php` (88 lines)
- `text-with-two-images-block.blade.php` (203 lines)

### Concerns & Abstracts
| File | Purpose |
|------|---------|
| `src/Abstracts/BaseBlock.php` | Base class all blocks extend |
| `src/Concerns/HasCommonOptions.php` | Background, spacing, container options |
| `src/Concerns/HasMedia.php` | Spatie Media Library integration (partial) |

---

## 🔧 Development Commands

### Clear Caches
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan filament:cache-components
```

### Create New Block
```bash
php artisan atelier:make-block MyCustomBlock
# Creates: src/Blocks/MyCustomBlock.php
# Then manually create: resources/views/vendor/atelier/blocks/my-custom-block.blade.php
```

### Testing
```bash
# Run specific tests
php artisan test --filter=BlockManager

# Test file uploads specifically
php artisan test tests/Feature/BlockFileUploadTest.php
```

---

## 🐛 Debugging Tips

### Preview Issues
1. Check Laravel logs: `storage/logs/laravel.log`
2. Look for "BlockFormModal preview error" entries
3. Debug logging at `BlockFormModal.php:220-223` shows viewData

### File Upload Issues
1. Check uploaded files: `storage/app/public/blocks/`
2. Verify symbolic link: `php artisan storage:link`
3. Check EAV attributes: `SELECT * FROM atelier_block_attributes WHERE key LIKE '%image%'`
4. Inspect form state in browser: Alpine devtools or `console.log(this.blocks)`

### State Management Issues
1. Add logging to Alpine methods in `block-manager.blade.php`
2. Check Livewire network requests in browser devtools
3. Verify `needsSync` flag behavior (line 102)
4. Check Livewire hook execution (lines 67-72)

### Translation Issues
1. Check if field is in `getTranslatableFields()` array
2. Verify locale config: `config('atelier.locales')`
3. Check EAV attributes have correct `locale` and `translatable` flags
4. Use `$block->getTranslated('field')` in templates, not `$field`

---

## 💡 Code Patterns

### Creating a New Block

1. **Generate block class:**
```bash
php artisan atelier:make-block FeatureListBlock
```

2. **Define schema:**
```php
public static function getSchema(): array
{
    return [
        Section::make('Content')
            ->schema([
                TranslatableContainer::make()
                    ->translatableFields([
                        TextInput::make('title')->required(),
                        RichEditor::make('description'),
                    ])
                    ->columnSpanFull(),
            ]),

        ...static::getCommonOptionsSchema(),
    ];
}
```

3. **Create template:**
```blade
{{-- resources/views/vendor/atelier/blocks/feature-list-block.blade.php --}}
<section class="atelier-feature-list {{ $block->getWrapperClasses() }}">
    <div class="{{ $block->getContainerClasses() }}">
        @if($title = $block->getTranslated('title'))
            <h2>{{ $title }}</h2>
        @endif
    </div>
</section>
```

4. **Register in config:**
```php
// config/atelier.php
'blocks' => [
    \BlackpigCreatif\Atelier\Blocks\FeatureListBlock::class,
],
```

### Adding File Upload to Block

```php
FileUpload::make('hero_image')
    ->label('Hero Image')
    ->image()
    ->imageEditor()
    ->maxFiles(1)
    ->disk('public')
    ->directory('blocks/features')
    ->visibility('public')
    ->required(),
```

### Rendering File Upload in Template

```blade
@php
    $imagePath = is_array($hero_image ?? null) ? ($hero_image[0] ?? null) : ($hero_image ?? null);
    $imageUrl = $imagePath ? asset('storage/' . $imagePath) : null;
@endphp

@if($imageUrl)
    <img src="{{ $imageUrl }}" alt="{{ $title }}">
@endif
```

---

## 🎨 UI/UX Decisions Made

1. **Preview in Slide-Over:** Reuse existing modal instead of creating new one (reduces complexity)
2. **Auto-Hide on View Pages:** Automatically detect view mode and hide editing controls
3. **Preview Always Available:** Even in read-only mode, preview button remains visible
4. **Hover-to-Show Actions:** Action buttons fade in on card hover (cleaner UI)
5. **Icon Spacing:** `gap-3` between action icons for better click targets
6. **Empty States:** Helpful messages when no blocks exist or no images uploaded
7. **Drag Handle:** Only shows when reorderable, indicates draggable blocks

---

## 📝 Configuration

### Package Config
Location: `config/atelier.php` (in user's app after publishing)

```php
return [
    'default_locale' => 'en',

    'locales' => [
        'en' => 'English',
        'fr' => 'French',
    ],

    'blocks' => [
        \BlackpigCreatif\Atelier\Blocks\HeroBlock::class,
        \BlackpigCreatif\Atelier\Blocks\TextBlock::class,
        // ... all available blocks
    ],

    'modal' => [
        'width' => '5xl', // Used for block form modal
    ],
];
```

### Usage in Resource

```php
use BlackpigCreatif\Atelier\Forms\Components\BlockManager;

public static function form(Form $form): Form
{
    return $form->schema([
        BlockManager::make('blocks')
            ->blocks([
                \BlackpigCreatif\Atelier\Blocks\HeroBlock::class,
                \BlackpigCreatif\Atelier\Blocks\TextBlock::class,
                \BlackpigCreatif\Atelier\Blocks\ImageBlock::class,
            ])
            ->addButtonLabel('Add Content Block')
            ->collapsible(true)
            ->columnSpanFull(),
    ]);
}
```

---

## ✅ Session Completion Summary

### What We Built Tonight
1. ✅ Block preview functionality with slide-over modal
2. ✅ Seven skeleton block templates (text, image, gallery, carousel, video, layouts)
3. ✅ Auto-detection of view/edit mode for read-only pages
4. ✅ Preview defaults to avoid undefined variable errors
5. ✅ Improved action button spacing and sizing
6. ✅ State management between preview and form modes

### What Works Well
- Drag-and-drop reordering is smooth
- Preview opens instantly and renders correctly (minus images)
- Custom SVG icons look great
- Translation handling is solid
- EAV storage is flexible and working

### What Needs Work
- **File uploads** - This is the main blocker for full functionality
- Complex field types testing (repeaters, etc.)
- More elegant translation UI (remove container wrapper)
- Spatie Media Library integration decision

---

## 🚀 Starting Next Session

### Quick Start Commands
```bash
cd /Users/stuart/Clients/Sites/FilamentPHP
composer run dev  # Or: php artisan serve

# In another terminal
cd /Users/stuart/Clients/Sites/FilamentPHP
npm run dev
```

### First Steps for File Upload Fix

1. **Create a test block with image:**
   ```bash
   # Open in browser
   # Navigate to Pages resource
   # Edit a page
   # Add HeroBlock with background image
   # Save and check logs
   ```

2. **Debug the save process:**
   - Add `dd($data)` in `BlockManager::saveBlockAttributes()` at line 323
   - Upload an image and save
   - Inspect the structure of `$data['background_image']`

3. **Check database:**
   ```sql
   SELECT * FROM atelier_block_attributes
   WHERE key = 'background_image'
   ORDER BY id DESC LIMIT 5;
   ```

4. **Verify storage:**
   ```bash
   ls -la storage/app/public/blocks/hero/
   # Check if files exist and have correct names
   ```

5. **Test extraction:**
   - Add logging to `extractFilePath()` method
   - Verify it correctly identifies file upload format
   - Check what format is being saved to database

---

## 📞 Questions for Next Session

- [ ] Should we fully implement Spatie Media Library or stick with simple file storage?
- [ ] Do we want lightbox functionality for galleries? (Would need JS library)
- [ ] Should carousel use a JS library (Swiper, Glide) or keep native scroll?
- [ ] Page-level language switcher - header action or page action placement?
- [ ] Any new block types needed beyond the current 8?

---

## 🎯 Success Criteria for Next Session

**File Upload Working Means:**
- ✅ Upload image to HeroBlock, save, see image in preview
- ✅ Upload image to ImageBlock, displays correctly
- ✅ Upload multiple images to Gallery, all show in grid
- ✅ Edit existing block, images persist
- ✅ Delete image from block, properly removes
- ✅ Image URLs correctly generated with `asset('storage/...')`

**Nice to Have:**
- Preview shows proper images instead of placeholders
- File deletion cleans up storage files
- Image validation working (size, type, dimensions)

---

## 📚 Additional Resources

### Filament Documentation
- Forms: https://filamentphp.com/docs/4.x/forms/fields
- File Upload: https://filamentphp.com/docs/4.x/forms/fields/file-upload
- Resources: https://filamentphp.com/docs/4.x/panels/resources

### Livewire File Uploads
- https://livewire.laravel.com/docs/uploads
- Temporary upload format
- Storage configuration

### Laravel Storage
- https://laravel.com/docs/12.x/filesystem
- Public disk configuration
- Symbolic links

---

**End of Handover Document**

*Ready to tackle file uploads when you are! 🚀*
