{{-- resources/views/blocks/text-block.blade.php --}}
@php
    /**
     * Text Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\TextBlock $block - The block instance
     * @var string|null $title - Translated title
     * @var string|null $content - Translated HTML content
     * @var string $text_alignment - Text alignment class (e.g., 'text-left')
     * @var string $max_width - Max width class (e.g., 'max-w-3xl')
     *
     * Helper Methods:
     * @method string $block->getTranslated(string $field) - Get translated value for field
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block::getBlockIdentifier() - Get block identifier (e.g., 'text-block')
     * @method string|null $block->getDividerComponent() - Get divider component name
     * @method string|null $block->getDividerToBackground() - Get divider target background class
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        <div class="{{ $max_width ?? 'max-w-3xl' }} {{ $text_alignment ?? 'text-left' }} mx-auto">

            {{-- Title --}}
            @if($title = $block->getTranslated('title'))
                <h2 class="text-3xl md:text-4xl font-bold mb-6 text-gray-900 dark:text-white">
                    {{ $title }}
                </h2>
            @endif

            {{-- Content --}}
            @if($content = $block->getTranslated('content'))
                <div class="prose prose-lg max-w-none dark:prose-invert prose-headings:font-bold prose-a:text-primary-600 hover:prose-a:text-primary-700">
                    {!! $content !!}
                </div>
            @endif

        </div>
    </div>

    {{-- Block Divider --}}
    @if($block->getDividerComponent())
        <x-dynamic-component
            :component="$block->getDividerComponent()"
            :to-background="$block->getDividerToBackground()"
        />
    @endif
</section>
