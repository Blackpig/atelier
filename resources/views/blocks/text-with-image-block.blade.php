{{-- resources/views/blocks/text-with-image-block.blade.php --}}
@php
    /**
     * Text With Image Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\TextWithImageBlock $block - The block instance
     * @var string|null $title - Translated title
     * @var string|null $content - Translated HTML content
     * @var string $image_position - Image position (left/right)
     * @var string $image_width - Image width percentage (30/40/50)
     *
     * Helper Methods:
     * @method string $block->getTranslated(string $field) - Get translated value for field
     * @method string|null $block->getFileUploadUrl(string $field) - Get file URL for field
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block::getBlockIdentifier() - Get block identifier (e.g., 'text-with-image-block')
     * @method string|null $block->getDividerComponent() - Get divider component name
     * @method string|null $block->getDividerToBackground() - Get divider target background class
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
    $imagePosition = $image_position ?? 'right';
    $imageWidthPercent = $image_width ?? '40';
    $textWidthPercent = 100 - (int)$imageWidthPercent;
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        <div class="flex flex-col md:flex-row gap-8 md:gap-12 items-center {{ $imagePosition === 'left' ? 'md:flex-row-reverse' : '' }}">

            {{-- Text Content --}}
            <div class="w-full md:w-[{{ $textWidthPercent }}%]">
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

                {{-- Call to Action Buttons --}}
                @if($block->hasCallToActions())
                    <div class="mt-8 flex flex-wrap gap-4 justify-{{ str_contains($text_alignment ?? '', 'center') ? 'center' : (str_contains($text_alignment ?? '', 'end') ? 'end' : 'start') }} animate-fade-in animation-delay-600">
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

            {{-- Image --}}
            @if($imageUrl = $block->getFileUploadUrl('image'))
                <div class="w-full md:w-[{{ $imageWidthPercent }}%]">
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $block->getTranslated('title') ?? '' }}"
                        loading="lazy"
                        class="w-full h-auto rounded-lg shadow-lg">
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
