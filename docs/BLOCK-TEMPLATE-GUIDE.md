# Atelier Block Template Guide

This guide provides comprehensive instructions for creating custom block templates for Atelier.

## Available in All Templates

Every block template has access to the following variables and methods:

- `$block` - The block instance with helper methods
- `$block->getTranslated('field')` - Get translated field value
- `$block->getFileUploadUrl('field')` - Get file URL for uploaded images
- `$block->getWrapperClasses()` - Background/spacing classes
- `$block->getContainerClasses()` - Width constraint classes
- `$block->blockId` - Unique block ID
- `$block::getBlockIdentifier()` - Block identifier (e.g., 'hero-block')
- `$block->getDividerComponent()` - Get divider component name
- `$block->getDividerToBackground()` - Get divider target background class
- All field values are available directly as variables

## Template Structure

Basic template structure with proper documentation:

```blade
@php
    /**
     * Block Template Documentation
     *
     * @var \App\Blocks\CustomBlock $block
     * @var string|null $title
     * @var string|null $content
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        {{-- Your block content here --}}
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

## Responsive Design

Atelier uses Tailwind CSS. Use responsive prefixes for mobile-first design:

| Breakpoint | Min Width | Example |
|------------|-----------|---------|
| `sm:` | 640px | `sm:text-lg` |
| `md:` | 768px | `md:text-xl` |
| `lg:` | 1024px | `lg:text-2xl` |
| `xl:` | 1280px | `xl:text-3xl` |
| `2xl:` | 1536px | `2xl:text-4xl` |

**Example:**
```blade
<h1 class="text-xl md:text-3xl lg:text-5xl">Responsive Heading</h1>
```

## Dark Mode

Add dark mode support using Tailwind's `dark:` prefix:

```blade
<div class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">
    Dark mode compatible content
</div>
```

## Accessibility

Always ensure your templates are accessible:

- ✅ Alt text for images
- ✅ ARIA labels for interactive elements
- ✅ Semantic HTML (header, nav, main, article, section)
- ✅ Keyboard navigation support
- ✅ Focus states (e.g., `focus:ring-4 focus:ring-primary-300`)

**Example:**
```blade
<button
    type="button"
    aria-label="Close modal"
    class="focus:ring-4 focus:ring-primary-300 focus:outline-none">
    <span aria-hidden="true">&times;</span>
</button>
```

## Performance

Optimize your block templates for performance:

- Add `loading="lazy"` to images below the fold
- Use appropriate image sizes
- Avoid inline styles where possible
- Use `@once` for global styles/scripts
- Minimize DOM elements

**Example:**
```blade
<img
    src="{{ $block->getFileUploadUrl('image') }}"
    alt="{{ $block->getTranslated('alt_text') }}"
    loading="lazy"
    class="w-full h-auto">
```

## Translation

Always use `getTranslated()` for translatable content:

```blade
{{-- Get translated text --}}
{{ $block->getTranslated('title') }}

{{-- Get translated rich content --}}
{!! $block->getTranslated('content') !!}

{{-- Check if translation exists --}}
@if($title = $block->getTranslated('title'))
    <h2>{{ $title }}</h2>
@endif
```

## Media Handling

Use the `HasFileUpload` trait methods to work with uploaded files:

```blade
{{-- Single image --}}
@if($imageUrl = $block->getFileUploadUrl('image_field'))
    <img src="{{ $imageUrl }}"
         alt="{{ $block->getTranslated('alt_text') }}"
         loading="lazy">
@endif

{{-- Multiple images --}}
@foreach($block->getFileUploadUrls('gallery_images') as $imageUrl)
    <img src="{{ $imageUrl }}" alt="" loading="lazy">
@endforeach
```

## Conditional Rendering

Always check if content exists before rendering:

```blade
@if($title = $block->getTranslated('title'))
    <h2 class="text-3xl font-bold">{{ $title }}</h2>
@endif

@if($content = $block->getTranslated('content'))
    <div class="prose max-w-none">
        {!! $content !!}
    </div>
@endif
```

## Wrapper vs Container

Understanding the two-layer system:

### `getWrapperClasses()`
- Applied to the `<section>` element
- Contains: background color, spacing (padding), dividers
- Spans full viewport width

### `getContainerClasses()`
- Applied to inner `<div>` element
- Contains: width constraints, horizontal padding
- Centers and constrains content width

**Structure:**
```blade
<section class="{{ $block->getWrapperClasses() }}">
    {{-- Full width with background and spacing --}}

    <div class="{{ $block->getContainerClasses() }}">
        {{-- Constrained width content --}}
    </div>
</section>
```

## Common Tailwind Classes

Quick reference for commonly used classes:

### Spacing
- **Vertical:** `py-4`, `py-8`, `py-16`, `py-24`
- **Horizontal:** `px-4`, `px-6`, `px-8`

### Typography
- **Sizes:** `text-xl`, `text-2xl`, `text-3xl`, `text-4xl`
- **Weight:** `font-bold`, `font-semibold`, `font-medium`

### Colors
- **Background:** `bg-gray-50`, `bg-primary-500`, `bg-white`
- **Text:** `text-gray-900`, `text-white`, `text-primary-600`

### Layout
- **Flex:** `flex`, `flex-col`, `items-center`, `justify-between`
- **Grid:** `grid`, `grid-cols-2`, `gap-4`, `gap-6`

### Sizing
- **Width:** `w-full`, `max-w-4xl`, `container`, `mx-auto`

### Effects
- **Shadows:** `shadow-sm`, `shadow-md`, `shadow-lg`
- **Rounded:** `rounded`, `rounded-lg`, `rounded-xl`

## Block Dividers

Atelier supports decorative dividers between blocks:

```blade
{{-- Add divider at end of block --}}
@if($block->getDividerComponent())
    <x-dynamic-component
        :component="$block->getDividerComponent()"
        :to-background="$block->getDividerToBackground()"
    />
@endif
```

Available divider types:
- Wave
- Curve Up
- Curve Down
- Diagonal (Left to Right)
- Diagonal (Right to Left)
- Triangle

## Custom Styles

Use `@once` to include styles only once per page:

```blade
@once
    @push('styles')
    <style>
        .my-custom-class {
            /* Your custom styles */
        }

        @media (min-width: 768px) {
            .my-custom-class {
                /* Responsive styles */
            }
        }
    </style>
    @endpush
@endonce
```

## Custom Scripts

Use `@once` to include scripts only once per page:

```blade
@once
    @push('scripts')
    <script>
        // Your JavaScript code
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize your block functionality
        });
    </script>
    @endpush
@endonce
```

## Complete Example

Here's a complete, production-ready block template:

```blade
@php
    /**
     * Custom Content Block
     *
     * @var \App\Blocks\CustomBlock $block
     * @var string|null $title - Translated title
     * @var string|null $content - Translated HTML content
     * @var string|null $image - Image field name
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">

        {{-- Title --}}
        @if($title = $block->getTranslated('title'))
            <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900 dark:text-white">
                {{ $title }}
            </h2>
        @endif

        {{-- Image --}}
        @if($imageUrl = $block->getFileUploadUrl('image'))
            <div class="mb-8">
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $block->getTranslated('image_alt') ?? $title ?? '' }}"
                    loading="lazy"
                    class="w-full h-auto rounded-lg shadow-lg">
            </div>
        @endif

        {{-- Content --}}
        @if($content = $block->getTranslated('content'))
            <div class="prose prose-lg max-w-none dark:prose-invert">
                {!! $content !!}
            </div>
        @endif

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

## Best Practices

1. ✅ Always document available variables with `@php` comments
2. ✅ Use semantic HTML5 elements
3. ✅ Include data attributes for JavaScript hooks
4. ✅ Test with and without content in all translatable fields
5. ✅ Test responsive behavior at all breakpoints
6. ✅ Validate HTML output
7. ✅ Check accessibility with screen readers
8. ✅ Test dark mode if applicable
9. ✅ Optimize images and lazy load when appropriate
10. ✅ Keep templates simple and focused

## Debugging

### View All Block Data

```blade
<pre>{{ print_r($block->getViewData(), true) }}</pre>
```

### Check Current Locale

```blade
<p>Current locale: {{ app()->getLocale() }}</p>
```

### Inspect Block Properties

```blade
<div class="debug">
    <p>Block ID: {{ $block->blockId }}</p>
    <p>Block Type: {{ $block::getBlockIdentifier() }}</p>
    <p>Locale: {{ $block->locale ?? 'default' }}</p>
</div>
```

## Helpful Blade Directives

| Directive | Description |
|-----------|-------------|
| `@once` | Include content only once per page |
| `@push` / `@stack` | Push content to named stacks |
| `@isset` / `@empty` | Check if variable exists/is empty |
| `@auth` / `@guest` | Check authentication status |
| `@production` | Only in production environment |
| `@env('local')` | Only in specific environment |

## Additional Resources

- [Tailwind CSS Documentation](https://tailwindcss.com)
- [Laravel Blade Documentation](https://laravel.com/docs/blade)
- [FilamentPHP Documentation](https://filamentphp.com/docs)
- [Web Accessibility Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

---

**Need Help?** Check the [MIGRATION.md](./MIGRATION.md) guide for upgrading existing blocks, or [CONTRIBUTING.md](./CONTRIBUTING.md) for contribution guidelines.
