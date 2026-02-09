# Block Templates

Guide to creating and customising Blade templates for Atelier blocks.

---

## Template Location

Published templates: `resources/views/vendor/atelier/blocks/{block-identifier}.blade.php`

The block identifier is the kebab-case class name, e.g. `HeroBlock` becomes `hero-block`.

Publish the built-in templates for customisation:

```bash
php artisan vendor:publish --tag="atelier-block-templates"
```

---

## Template Structure

Every template receives a `$block` instance and all field data as view variables.

```blade
@php
    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        {{-- Block content --}}
    </div>

    @if($block->getDividerComponent())
        <x-dynamic-component
            :component="$block->getDividerComponent()"
            :to-background="$block->getDividerToBackground()"
        />
    @endif
</section>
```

### Wrapper vs Container

The two-layer structure separates full-width concerns from content constraints:

- **`getWrapperClasses()`** -- applied to the `<section>`. Contains background colour and vertical spacing. Spans the full viewport width.
- **`getContainerClasses()`** -- applied to the inner `<div>`. Contains width constraints (container, narrow, wide, full) and horizontal padding. Centers content.

---

## Available Methods

### Data Access

```php
$block->get('field_name')                  // Raw field value
$block->get('field_name', 'default')       // With fallback
$block->getTranslated('field_name')        // Locale-resolved value
$block->getTranslated('field_name', 'fr')  // Explicit locale
```

### Display Options

```php
$block->getWrapperClasses()     // Background + spacing classes
$block->getContainerClasses()   // Width constraint classes
$block->getDividerComponent()   // Divider Blade component name, or null
$block->getDividerToBackground() // Divider transition colour class
$block->hasDivider()            // bool
```

### Media (requires HasRetouchMedia or HasFileUpload)

```php
// HasFileUpload
$block->getFileUploadUrl('image')     // Single file URL
$block->getFileUploadUrls('gallery')  // Array of URLs

// HasRetouchMedia (ChambreNoir)
$block->getPicture('image', ['alt' => '...', 'class' => '...'])
$block->getSrcset('image')
$block->getMediaUrl('image', 'large')
```

### CTAs (requires HasCallToActions)

```php
$block->hasCallToActions()                     // bool
$block->getCallToActions()                     // array of CTA items
$block->getCallToActionLabel($cta)             // Translated label
$block->getCallToActionStyleClass($cta)        // CSS class from config
$block->getCallToActionTarget($cta)            // '_blank' or '_self'
$block->isExternalUrl($cta['url'])             // bool
```

### Identity

```php
$block->blockId                  // int -- database ID
$block::getBlockIdentifier()     // string -- e.g. 'hero-block'
$block::getLabel()               // string -- e.g. 'Hero Section'
```

---

## Translation in Templates

Always use `getTranslated()` for translatable content:

```blade
@if($title = $block->getTranslated('title'))
    <h2>{{ $title }}</h2>
@endif

@if($content = $block->getTranslated('content'))
    <div class="prose">{!! $content !!}</div>
@endif
```

---

## Media in Templates

### Standard file uploads (HasFileUpload)

```blade
@if($url = $block->getFileUploadUrl('image'))
    <img src="{{ $url }}" alt="" loading="lazy">
@endif
```

### Responsive images (HasRetouchMedia / ChambreNoir)

```blade
{{-- Picture element with responsive sources --}}
{!! $block->getPicture('image', [
    'alt'   => $block->getTranslated('title'),
    'class' => 'w-full h-auto',
    'loading' => 'lazy',
]) !!}

{{-- Above-the-fold hero images --}}
{!! $block->getPicture('background_image', [
    'alt'           => '',
    'class'         => 'w-full h-full object-cover',
    'fetchpriority' => 'high',
]) !!}
```

---

## CTAs in Templates

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

Or render manually:

```blade
@if($block->hasCallToActions())
    <div class="flex gap-4">
        @foreach($block->getCallToActions() as $cta)
            <a href="{{ $cta['url'] }}"
               target="{{ $block->getCallToActionTarget($cta) }}"
               class="{{ $block->getCallToActionStyleClass($cta) }}"
               @if($block->isExternalUrl($cta['url'])) rel="noopener noreferrer" @endif>
                @if(!empty($cta['icon']))
                    <x-filament::icon :icon="$cta['icon']" class="w-5 h-5" />
                @endif
                {{ $block->getCallToActionLabel($cta) }}
            </a>
        @endforeach
    </div>
@endif
```

---

## Complete Example

```blade
@php
    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">

        @if($title = $block->getTranslated('title'))
            <h2 class="text-3xl md:text-4xl font-bold mb-6">{{ $title }}</h2>
        @endif

        @if($imageUrl = $block->getFileUploadUrl('image'))
            <div class="mb-8">
                <img src="{{ $imageUrl }}"
                     alt="{{ $block->getTranslated('image_alt') ?? $title ?? '' }}"
                     loading="lazy"
                     class="w-full h-auto rounded-lg">
            </div>
        @endif

        @if($content = $block->getTranslated('content'))
            <div class="prose prose-lg max-w-none">{!! $content !!}</div>
        @endif

        @if($block->hasCallToActions())
            <div class="mt-8 flex gap-4">
                @foreach($block->getCallToActions() as $index => $cta)
                    <x-atelier::call-to-action :cta="$cta" :block="$block" :index="$index" />
                @endforeach
            </div>
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

## Debugging

```blade
{{-- Dump all block data --}}
<pre>{{ print_r($block->getViewData(), true) }}</pre>

{{-- Check block identity --}}
<p>ID: {{ $block->blockId }}, Type: {{ $block::getBlockIdentifier() }}</p>
```
