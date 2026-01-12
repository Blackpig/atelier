{{-- resources/views/blocks/image-block.blade.php --}}
@php
    /**
     * Image Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\ImageBlock $block - The block instance
     * @var string|null $title - Translated title
     * @var string|null $caption - Translated caption
     * @var string $alignment - Image alignment (left/center/right)
     * @var string $max_width - Max width class (e.g., 'max-w-4xl')
     * @var string $aspect_ratio - Aspect ratio class (e.g., 'aspect-video')
     * @var bool $lightbox - Enable lightbox functionality
     *
     * Helper Methods:
     * @method string $block->getTranslated(string $field) - Get translated value for field
     * @method string|null $block->getFileUploadUrl(string $field) - Get file URL for field
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block::getBlockIdentifier() - Get block identifier (e.g., 'image-block')
     * @method string|null $block->getDividerComponent() - Get divider component name
     * @method string|null $block->getDividerToBackground() - Get divider target background class
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
    $alignmentClass = match($alignment ?? 'center') {
        'left' => 'mr-auto',
        'right' => 'ml-auto',
        default => 'mx-auto',
    };
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        @if($imageUrl = $block->getFileUploadUrl('image'))
            <figure class="{{ $max_width ?? 'max-w-4xl' }} {{ $alignmentClass }}">

                {{-- Image --}}
                <div class="{{ $aspect_ratio ?? 'aspect-auto' }} overflow-hidden rounded-lg shadow-lg">
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $block->getTranslated('title') ?? $block->getTranslated('caption') ?? '' }}"
                        loading="lazy"
                        class="w-full h-full object-cover {{ ($lightbox ?? true) ? 'cursor-pointer hover:opacity-90 transition-opacity' : '' }}"
                        @if($lightbox ?? true)
                            data-lightbox="true"
                            data-lightbox-src="{{ $imageUrl }}"
                        @endif
                    >
                </div>

                {{-- Caption/Title --}}
                @if($title = $block->getTranslated('title'))
                    <figcaption class="mt-4 text-center">
                        <strong class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $title }}
                        </strong>
                        @if($caption = $block->getTranslated('caption'))
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ $caption }}
                            </p>
                        @endif
                    </figcaption>
                @elseif($caption = $block->getTranslated('caption'))
                    <figcaption class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                        {{ $caption }}
                    </figcaption>
                @endif

            </figure>
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

{{-- Lightbox functionality placeholder - to be implemented later --}}
@if($lightbox ?? true)
    @once
        @push('scripts')
        <script>
            // TODO: Implement lightbox functionality
            // Use data-lightbox and data-lightbox-src attributes to enable lightbox
        </script>
        @endpush
    @endonce
@endif
