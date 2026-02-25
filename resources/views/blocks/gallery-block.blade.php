{{-- resources/views/blocks/gallery-block.blade.php --}}
@php
    /**
     * Gallery Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\GalleryBlock $block
     * @var array<int, array{thumb: string, medium: string, large: string}> $imagesData
     * @var string|null $title
     * @var string $columns  - '2', '3', '4'
     * @var string $gap      - 'gap-2', 'gap-4', 'gap-6', 'gap-8'
     * @var string $per_page - '5', '10', '15', '20'
     * @var bool   $lightbox
     * @var bool   $auto_rows
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();

    $columnClass = match ((string) ($columns ?? '3')) {
        '2'     => 'grid-cols-1 sm:grid-cols-2',
        '4'     => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    };

    $imageAspectClass = ($auto_rows ?? false) ? '' : 'aspect-square';
    $imagesJson = \Illuminate\Support\Js::from($imagesData);
    $perPageInt  = (int) ($per_page ?? 5);
@endphp

@once
<script>
function galleryBlock(images, perPage) {
    return {
        images,
        perPage,
        currentPage: 0,
        lightboxIndex: null,

        get pagedImages() {
            const start = this.currentPage * this.perPage;
            return this.images.slice(start, start + this.perPage);
        },
        get pageStartIndex() {
            return this.currentPage * this.perPage;
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.images.length / this.perPage));
        },
        get hasPrev() {
            return this.currentPage > 0;
        },
        get hasNext() {
            return this.currentPage < this.totalPages - 1;
        },
        get lightboxImage() {
            return this.lightboxIndex !== null ? this.images[this.lightboxIndex] : null;
        },

        openLightbox(globalIndex) {
            this.lightboxIndex = globalIndex;
        },
        closeLightbox() {
            this.lightboxIndex = null;
        },
        prevImage() {
            this.lightboxIndex = (this.lightboxIndex - 1 + this.images.length) % this.images.length;
        },
        nextImage() {
            this.lightboxIndex = (this.lightboxIndex + 1) % this.images.length;
        },
        prevPage() {
            if (this.hasPrev) { this.currentPage--; }
        },
        nextPage() {
            if (this.hasNext) { this.currentPage++; }
        },
        goToPage(page) {
            if (page >= 0 && page < this.totalPages) { this.currentPage = page; }
        },
    };
}
</script>
@endonce

<section
    class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
    data-block-type="{{ $block::getBlockIdentifier() }}"
    data-block-id="{{ $block->blockId ?? '' }}"
    x-data="galleryBlock({{ $imagesJson }}, {{ $perPageInt }})"
>
    <div class="{{ $block->getContainerClasses() }}">

        {{-- Title --}}
        @if($title = $block->getTranslated('title'))
            <h2 class="text-3xl md:text-4xl font-bold mb-8 text-gray-900 text-center">
                {{ $title }}
            </h2>
        @endif

        {{-- Gallery Grid --}}
        <div x-show="images.length > 0">
            <div class="grid {{ $columnClass }} {{ $gap ?? 'gap-4' }}">
                <template x-for="(image, localIndex) in pagedImages" :key="pageStartIndex + localIndex">
                    <div
                        class="relative overflow-hidden rounded-lg shadow-md group {{ $imageAspectClass }} cursor-pointer"
                        @click="openLightbox(pageStartIndex + localIndex)"
                    >
                        <img
                            :src="image.display"
                            :alt="`Gallery image ${pageStartIndex + localIndex + 1}`"
                            loading="lazy"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                        >
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Pagination --}}
            <template x-if="totalPages > 1">
                <div class="flex items-center justify-center gap-4 mt-8">

                    {{-- Prev --}}
                    <button
                        type="button"
                        @click="prevPage"
                        :disabled="!hasPrev"
                        :class="hasPrev ? 'opacity-100 hover:bg-gray-100' : 'opacity-30 cursor-not-allowed'"
                        class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center transition-colors"
                        aria-label="Previous page"
                    >
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    {{-- Dot indicators --}}
                    <div class="flex items-center gap-2">
                        <template x-for="page in totalPages" :key="page">
                            <button
                                type="button"
                                @click="goToPage(page - 1)"
                                :class="currentPage === page - 1
                                    ? 'w-3 h-3 bg-gray-800'
                                    : 'w-2 h-2 bg-gray-300 hover:bg-gray-500'"
                                class="rounded-full transition-all duration-200"
                                :aria-label="`Page ${page}`"
                            ></button>
                        </template>
                    </div>

                    {{-- Next --}}
                    <button
                        type="button"
                        @click="nextPage"
                        :disabled="!hasNext"
                        :class="hasNext ? 'opacity-100 hover:bg-gray-100' : 'opacity-30 cursor-not-allowed'"
                        class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center transition-colors"
                        aria-label="Next page"
                    >
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    {{-- Page counter --}}
                    <span
                        class="text-sm text-gray-500"
                        x-text="`Page ${currentPage + 1} of ${totalPages}`"
                    ></span>

                </div>
            </template>
        </div>

    </div>

    {{-- Lightbox --}}
    <div
        x-show="lightboxIndex !== null"
        x-cloak
        @keydown.escape.window="if (lightboxIndex !== null) closeLightbox()"
        @keydown.arrow-left.window="if (lightboxIndex !== null) prevImage()"
        @keydown.arrow-right.window="if (lightboxIndex !== null) nextImage()"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90"
        @click.self="closeLightbox()"
    >
        {{-- Close --}}
        <button
            type="button"
            @click="closeLightbox()"
            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-colors"
            aria-label="Close lightbox"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Previous image --}}
        <button
            type="button"
            @click="prevImage()"
            class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-colors"
            aria-label="Previous image"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        {{-- Image --}}
        <img
            :src="lightboxImage?.lightbox"
            class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg shadow-2xl"
            :alt="`Image ${lightboxIndex !== null ? lightboxIndex + 1 : ''} of ${images.length}`"
        >

        {{-- Next image --}}
        <button
            type="button"
            @click="nextImage()"
            class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/25 flex items-center justify-center text-white transition-colors"
            aria-label="Next image"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        {{-- Counter --}}
        <div
            class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/70 text-sm tabular-nums"
            x-text="lightboxIndex !== null ? `${lightboxIndex + 1} / ${images.length}` : ''"
        ></div>
    </div>

    {{-- Block Divider --}}
    @if($block->getDividerComponent())
        <x-dynamic-component
            :component="$block->getDividerComponent()"
            :to-background="$block->getDividerToBackground()"
        />
    @endif
</section>
