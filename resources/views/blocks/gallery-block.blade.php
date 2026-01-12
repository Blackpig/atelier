{{-- resources/views/blocks/gallery-block.blade.php --}}
@php
    /**
     * Gallery Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\GalleryBlock $block - The block instance
     * @var string|null $title - Translated title
     * @var string $columns - Number of columns (2/3/4)
     * @var string $gap - Gap class (e.g., 'gap-4')
     * @var bool $lightbox - Enable lightbox functionality
     * @var bool $auto_rows - Enable auto row height
     *
     * Helper Methods:
     * @method string $block->getTranslated(string $field) - Get translated value for field
     * @method array $block->getFileUploadUrls(string $field) - Get array of file URLs
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block::getBlockIdentifier() - Get block identifier (e.g., 'gallery-block')
     * @method string|null $block->getDividerComponent() - Get divider component name
     * @method string|null $block->getDividerToBackground() - Get divider target background class
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
    $columnClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-' . ($columns ?? '3');
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

        {{-- Gallery Grid --}}
        @if($imageUrls = $block->getFileUploadUrls('images'))
            <div class="grid {{ $columnClass }} {{ $gap ?? 'gap-4' }}"
                 data-gallery="{{ ($lightbox ?? true) ? 'true' : 'false' }}">

                @foreach($imageUrls as $index => $imageUrl)
                    <div class="relative overflow-hidden rounded-lg shadow-lg group {{ ($auto_rows ?? false) ? '' : 'aspect-square' }}">
                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $block->getTranslated('title') ?? 'Gallery image' }} {{ $index + 1 }}"
                            loading="lazy"
                            class="w-full h-full object-cover {{ ($lightbox ?? true) ? 'cursor-pointer hover:scale-105 transition-transform duration-300' : '' }}"
                            @if($lightbox ?? true)
                                data-lightbox="gallery"
                                data-lightbox-src="{{ $imageUrl }}"
                                data-lightbox-index="{{ $index }}"
                            @endif
                        >

                        {{-- Hover Overlay --}}
                        @if($lightbox ?? true)
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center">
                                <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                </svg>
                            </div>
                        @endif
                    </div>
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
            // TODO: Implement lightbox gallery functionality
            // Use data-lightbox, data-lightbox-src, and data-lightbox-index attributes
            // Recommended libraries: GLightbox, PhotoSwipe, or custom implementation
        </script>
        @endpush
    @endonce
@endif
