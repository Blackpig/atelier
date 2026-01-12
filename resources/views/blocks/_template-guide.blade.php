{{--
╔══════════════════════════════════════════════════════════════════════════════╗
║                     ATELIER BLOCK TEMPLATE GUIDE                              ║
╚══════════════════════════════════════════════════════════════════════════════╝

This is a guide for creating custom block templates for Atelier.

AVAILABLE IN ALL TEMPLATES:
---------------------------
- $block: The block instance with helper methods
- $block->getTranslated('field'): Get translated field value
- $block->getFileUploadUrl('field'): Get file URL for uploaded images
- $block->getWrapperClasses(): Background/spacing classes
- $block->getContainerClasses(): Width constraint classes
- $block->blockId: Unique block ID
- All field values directly as variables

TEMPLATE STRUCTURE:
------------------
@php
    // Document available variables at the top
    // Use type hints for IDE support
@endphp

<section class="{{ $block->getWrapperClasses() }}" 
         data-block-type="your-block-identifier"
         data-block-id="{{ $block->blockId ?? '' }}">
    
    <div class="{{ $block->getContainerClasses() }}">
        {{-- Your block content here --}}
    </div>
</section>

RESPONSIVE DESIGN:
-----------------
Use Tailwind's responsive prefixes:
- sm: @media (min-width: 640px)
- md: @media (min-width: 768px)
- lg: @media (min-width: 1024px)
- xl: @media (min-width: 1280px)
- 2xl: @media (min-width: 1536px)

Example: class="text-xl md:text-3xl lg:text-5xl"

DARK MODE:
---------
Add dark: prefix for dark mode styles:
- text-gray-900 dark:text-gray-100
- bg-white dark:bg-gray-900

ACCESSIBILITY:
-------------
Always include:
- Alt text for images
- ARIA labels for interactive elements
- Semantic HTML (header, nav, main, article, section)
- Keyboard navigation support
- Focus states (focus:ring-4 focus:ring-primary-300)

PERFORMANCE:
-----------
- Add loading="lazy" to images below the fold
- Use appropriate image conversions (thumb, medium, large)
- Avoid inline styles where possible
- Use @once for global styles/scripts

EXAMPLE BLOCK:
-------------
@php
    /**
     * @var \App\Blocks\CustomBlock $block
     * @var string|null $title
     * @var string|null $content
     */
@endphp

<section class="my-custom-block {{ $block->getWrapperClasses() }}" 
         data-block-type="custom-block">
    <div class="{{ $block->getContainerClasses() }}">
        @if($title = $block->getTranslated('title'))
            <h2 class="text-3xl font-bold mb-6">{{ $title }}</h2>
        @endif
        
        @if($content = $block->getTranslated('content'))
            <div class="prose max-w-none">
                {!! $content !!}
            </div>
        @endif
    </div>
</section>

COMMON CLASSES:
--------------
Spacing: py-8, py-16, py-24 (vertical), px-4, px-6 (horizontal)
Text: text-xl, text-2xl, text-3xl, font-bold, font-semibold
Colors: bg-gray-50, bg-primary-500, text-white
Layout: flex, grid, grid-cols-2, gap-4, gap-6
Sizing: w-full, max-w-4xl, container, mx-auto
Shadows: shadow-sm, shadow-md, shadow-lg
Rounded: rounded, rounded-lg, rounded-xl

TRANSLATION:
-----------
Always use $block->getTranslated('field_name') for translatable content:
- {{ $block->getTranslated('title') }}
- {{ $block->getTranslated('description') }}
- {!! $block->getTranslated('content') !!}

MEDIA:
------
Get file URLs for uploaded images:
- $url = $block->getFileUploadUrl('image_field')
- $urls = $block->getFileUploadUrls('image_field') // for multiple files

Display images:
@if($imageUrl = $block->getFileUploadUrl('image_field'))
    <img src="{{ $imageUrl }}"
         alt="{{ $block->getTranslated('alt_text') }}"
         loading="lazy">
@endif

CONDITIONAL RENDERING:
---------------------
Always check if content exists before rendering:
@if($title = $block->getTranslated('title'))
    <h2>{{ $title }}</h2>
@endif

WRAPPER VS CONTAINER:
--------------------
- getWrapperClasses(): Background color, spacing (padding)
- getContainerClasses(): Width constraints, horizontal padding

Example structure:
<section class="{{ $block->getWrapperClasses() }}">  {{-- Full width with background --}}
    <div class="{{ $block->getContainerClasses() }}">  {{-- Constrained width --}}
        {{-- Content here --}}
    </div>
</section>

CUSTOM STYLES:
-------------
Use @once to include styles only once per page:
@once
    @push('styles')
    <style>
        .my-custom-class {
            /* Your styles */
        }
    </style>
    @endpush
@endonce

CUSTOM SCRIPTS:
--------------
Use @once to include scripts only once per page:
@once
    @push('scripts')
    <script>
        // Your JavaScript
    </script>
    @endpush
@endonce

BEST PRACTICES:
--------------
1. Always document available variables with @php comments
2. Use semantic HTML5 elements
3. Include data attributes for JavaScript hooks
4. Test with and without content in all translatable fields
5. Test responsive behavior at all breakpoints
6. Validate HTML output
7. Check accessibility with screen readers
8. Test dark mode if applicable
9. Optimize images and lazy load when appropriate
10. Keep templates simple and focused

DEBUGGING:
---------
To see all available data in a block:
<pre>{{ print_r($block->getViewData(), true) }}</pre>

To check current locale:
<p>Current locale: {{ app()->getLocale() }}</p>

HELPFUL BLADE DIRECTIVES:
-------------------------
@renderBlocks($model) - Render all blocks for a model
@once - Include content only once per page
@push / @stack - Push content to named stacks
@isset / @empty - Check if variable exists/is empty
@auth / @guest - Check authentication status

--}}
