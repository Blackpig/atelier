{{-- resources/views/blocks/carousel-block.blade.php --}}
@php
    /**
     * Carousel Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\CarouselBlock $block - The block instance
     * @var string|null $title - Translated title
     * @var string $height - Height class (e.g., 'h-96')
     * @var string $aspect_ratio - Aspect ratio class (e.g., 'aspect-video')
     * @var bool $autoplay - Enable autoplay
     * @var bool $show_dots - Show navigation dots
     * @var bool $show_arrows - Show navigation arrows
     *
     * Helper Methods:
     * @method string $block->getTranslated(string $field) - Get translated value for field
     * @method array $block->getFileUploadUrls(string $field) - Get array of file URLs
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block::getBlockIdentifier() - Get block identifier (e.g., 'carousel-block')
     * @method string|null $block->getDividerComponent() - Get divider component name
     * @method string|null $block->getDividerToBackground() - Get divider target background class
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
    $carouselId = 'carousel-' . ($block->blockId ?? uniqid());
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">

        {{-- Title --}}
        @if($title = $block->getTranslated('title'))
            <h2 class="text-3xl md:text-4xl font-bold mb-8 text-gray-900 dark:text-white text-center">
                {{ $title }}
            </h2>
        @endif

        {{-- Carousel Container --}}
        @if($imageUrls = $block->getFileUploadUrls('images'))
            <div class="relative {{ $height ?? 'h-96' }}"
                 id="{{ $carouselId }}"
                 data-carousel="true"
                 data-autoplay="{{ ($autoplay ?? false) ? 'true' : 'false' }}"
                 data-show-dots="{{ ($show_dots ?? true) ? 'true' : 'false' }}"
                 data-show-arrows="{{ ($show_arrows ?? true) ? 'true' : 'false' }}">

                {{-- Carousel Items --}}
                <div class="carousel-items relative h-full overflow-hidden rounded-lg shadow-lg bg-gray-900">
                    @foreach($imageUrls as $index => $imageUrl)
                        <div class="carousel-item absolute inset-0 transition-opacity duration-500 {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}"
                             data-carousel-item="{{ $index }}">
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $block->getTranslated('title') ?? 'Carousel image' }} {{ $index + 1 }}"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                class="w-full h-full {{ $aspect_ratio ?? 'aspect-auto' }} object-cover"
                            >
                        </div>
                    @endforeach
                </div>

                {{-- Previous Arrow --}}
                @if($show_arrows ?? true)
                    <button
                        type="button"
                        class="carousel-prev absolute left-4 top-1/2 -translate-y-1/2 z-10 bg-white/80 hover:bg-white rounded-full p-3 shadow-lg transition-all hover:scale-110 focus:outline-none focus:ring-4 focus:ring-primary-300"
                        aria-label="Previous slide">
                        <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    {{-- Next Arrow --}}
                    <button
                        type="button"
                        class="carousel-next absolute right-4 top-1/2 -translate-y-1/2 z-10 bg-white/80 hover:bg-white rounded-full p-3 shadow-lg transition-all hover:scale-110 focus:outline-none focus:ring-4 focus:ring-primary-300"
                        aria-label="Next slide">
                        <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                @endif

                {{-- Dots Navigation --}}
                @if($show_dots ?? true)
                    <div class="carousel-dots absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex gap-2">
                        @foreach($imageUrls as $index => $imageUrl)
                            <button
                                type="button"
                                class="carousel-dot w-3 h-3 rounded-full transition-all {{ $index === 0 ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/75' }}"
                                data-carousel-dot="{{ $index }}"
                                aria-label="Go to slide {{ $index + 1 }}">
                            </button>
                        @endforeach
                    </div>
                @endif

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

{{-- Carousel functionality placeholder - to be implemented later --}}
@once
    @push('scripts')
    <script>
        // TODO: Implement carousel functionality
        // Use data-carousel, data-autoplay, data-show-dots, and data-show-arrows attributes
        // Implement slide navigation, autoplay, and keyboard controls
        // Recommended libraries: Swiper, Splide, or custom implementation
    </script>
    @endpush
@endonce
