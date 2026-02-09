{{-- resources/views/blocks/image-block.blade.php --}}
@php
    /**
     * Image Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\ImageBlock $block - The block instance
     * @var string|null $title - Translated title
     * @var string|null $subtitle - Translated subtitle
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
                        @if($subtitle = $block->getTranslated('subtitle'))
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ $subtitle }}
                            </p>
                        @endif
                    </figcaption>
                @elseif($subtitle = $block->getTranslated('subtitle'))
                    <figcaption class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
                        {{ $subtitle }}
                    </figcaption>
                @endif

            </figure>
        @endif

        {{-- Call to Action Buttons --}}
        @if($block->hasCallToActions())
            <div class="mt-8 flex flex-wrap gap-4 justify-{{ str_contains($content_alignment ?? '', 'center') ? 'center' : (str_contains($content_alignment ?? '', 'end') ? 'end' : 'start') }} animate-fade-in animation-delay-600">
                @foreach($block->getCallToActions() as $index => $cta)
                    <x-atelier::call-to-action
                        :cta="$cta"
                        :block="$block"
                        :index="$index"
                    />
                @endforeach
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
