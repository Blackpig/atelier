{{-- resources/views/blocks/text-with-two-images-block.blade.php --}}
@php
    /**
     * Text with Two Images Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\TextWithTwoImagesBlock $block - The block instance
     * @var string|null $title - Translated title
     * @var string|null $content - Translated HTML content
     * @var string|null $image_1_caption - Translated caption for first image
     * @var string|null $image_2_caption - Translated caption for second image
     * @var string $layout - Layout style (images-left, images-right, etc.)
     * @var string $image_aspect - Image aspect ratio class
     * @var string $image_size - Image size (small, medium, large)
     *
     * Layout Options:
     * - images-left: Images stacked vertically on left, text on right
     * - images-right: Images stacked vertically on right, text on left
     * - images-stacked-left: Images side-by-side on left, text on right
     * - images-stacked-right: Images side-by-side on right, text on left
     * - images-top: Images side-by-side above text
     * - images-bottom: Images side-by-side below text
     * - masonry: Mixed grid layout
     *
     * Helper Methods:
     * @method string $block->getImageSizeClass() - Get responsive width class for images
     * @method string|null $block->getImage1() - Get URL for first image
     * @method string|null $block->getImage2() - Get URL for second image
     */
@endphp

<section class="atelier-text-with-two-images {{ $block->getWrapperClasses() }}" 
         data-block-type="text-with-two-images"
         data-block-id="{{ $block->blockId ?? '' }}">
    <div class="{{ $block->getContainerClasses() }}">
        
        {{-- Optional Title --}}
        @if($title = $block->getTranslated('title'))
            <h2 class="text-3xl md:text-4xl font-bold mb-8 md:mb-12">
                {{ $title }}
            </h2>
        @endif
        
        @php
            $layout = $layout ?? 'images-left';
            $imageAspect = $image_aspect ?? 'aspect-video';
            $imageSizeClass = $block->getImageSizeClass();
            $image1 = $block->getImage1();
            $image2 = $block->getImage2();
            $imageCaption1 = $block->getTranslated('image_1_caption');
            $imageCaption2 = $block->getTranslated('image_2_caption');
            $contentHtml = $block->getTranslated('content');
        @endphp
        
        {{-- Layout: Images Left, Text Right --}}
        @if($layout === 'images-left')
            <div class="flex flex-col md:flex-row gap-6 lg:gap-8 items-start">
                <div class="w-full {{ $imageSizeClass }} space-y-4 lg:space-y-6">
                    @if($image1)
                        <figure class="group">
                            <img 
                                src="{{ $image1) }}" 
                                alt="{{ $imageCaption1 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption1)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption1 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                    
                    @if($image2)
                        <figure class="group">
                            <img 
                                src="{{ $image2 }}" 
                                alt="{{ $imageCaption2 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption2)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption2 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                </div>
                
                <div class="flex-1 prose prose-lg dark:prose-invert max-w-none">
                    {!! $contentHtml !!}
                </div>
            </div>
        @endif
        
        {{-- Layout: Images Right, Text Left --}}
        @if($layout === 'images-right')
            <div class="flex flex-col md:flex-row-reverse gap-6 lg:gap-8 items-start">
                <div class="w-full {{ $imageSizeClass }} space-y-4 lg:space-y-6">
                    @if($image1)
                        <figure class="group">
                            <img 
                                src="{{ $image1->getUrl('large') }}" 
                                alt="{{ $imageCaption1 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption1)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption1 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                    
                    @if($image2)
                        <figure class="group">
                            <img 
                                src="{{ $image2->getUrl('large') }}" 
                                alt="{{ $imageCaption2 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption2)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption2 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                </div>
                
                <div class="flex-1 prose prose-lg dark:prose-invert max-w-none">
                    {!! $contentHtml !!}
                </div>
            </div>
        @endif
        
        {{-- Layout: Images Stacked Left (side-by-side) --}}
        @if($layout === 'images-stacked-left')
            <div class="flex flex-col md:flex-row gap-6 lg:gap-8 items-start">
                <div class="w-full {{ $imageSizeClass }} grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if($image1)
                        <figure class="group">
                            <img 
                                src="{{ $image1->getUrl('large') }}" 
                                alt="{{ $imageCaption1 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption1)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption1 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                    
                    @if($image2)
                        <figure class="group">
                            <img 
                                src="{{ $image2->getUrl('large') }}" 
                                alt="{{ $imageCaption2 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption2)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption2 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                </div>
                
                <div class="flex-1 prose prose-lg dark:prose-invert max-w-none">
                    {!! $contentHtml !!}
                </div>
            </div>
        @endif
        
        {{-- Layout: Images Stacked Right (side-by-side) --}}
        @if($layout === 'images-stacked-right')
            <div class="flex flex-col md:flex-row-reverse gap-6 lg:gap-8 items-start">
                <div class="w-full {{ $imageSizeClass }} grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if($image1)
                        <figure class="group">
                            <img 
                                src="{{ $image1->getUrl('large') }}" 
                                alt="{{ $imageCaption1 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption1)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption1 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                    
                    @if($image2)
                        <figure class="group">
                            <img 
                                src="{{ $image2->getUrl('large') }}" 
                                alt="{{ $imageCaption2 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption2)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption2 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                </div>
                
                <div class="flex-1 prose prose-lg dark:prose-invert max-w-none">
                    {!! $contentHtml !!}
                </div>
            </div>
        @endif
        
        {{-- Layout: Images Top --}}
        @if($layout === 'images-top')
            <div class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
                    @if($image1)
                        <figure class="group">
                            <img 
                                src="{{ $image1->getUrl('large') }}" 
                                alt="{{ $imageCaption1 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption1)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption1 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                    
                    @if($image2)
                        <figure class="group">
                            <img 
                                src="{{ $image2->getUrl('large') }}" 
                                alt="{{ $imageCaption2 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption2)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption2 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                </div>
                
                <div class="prose prose-lg dark:prose-invert max-w-none">
                    {!! $contentHtml !!}
                </div>
            </div>
        @endif
        
        {{-- Layout: Images Bottom --}}
        @if($layout === 'images-bottom')
            <div class="space-y-8">
                <div class="prose prose-lg dark:prose-invert max-w-none">
                    {!! $contentHtml !!}
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
                    @if($image1)
                        <figure class="group">
                            <img 
                                src="{{ $image1->getUrl('large') }}" 
                                alt="{{ $imageCaption1 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption1)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption1 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                    
                    @if($image2)
                        <figure class="group">
                            <img 
                                src="{{ $image2->getUrl('large') }}" 
                                alt="{{ $imageCaption2 ?? $title ?? 'Image' }}"
                                class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                                loading="lazy"
                            >
                            @if($imageCaption2)
                                <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                    {{ $imageCaption2 }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif
                </div>
            </div>
        @endif
        
        {{-- Layout: Masonry Grid --}}
        @if($layout === 'masonry')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 auto-rows-auto">
                <div class="prose prose-lg dark:prose-invert lg:col-span-2">
                    {!! $contentHtml !!}
                </div>
                
                @if($image1)
                    <figure class="group lg:row-span-2">
                        <img 
                            src="{{ $image1->getUrl('large') }}" 
                            alt="{{ $imageCaption1 ?? $title ?? 'Image' }}"
                            class="w-full h-full object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                            loading="lazy"
                        >
                        @if($imageCaption1)
                            <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                {{ $imageCaption1 }}
                            </figcaption>
                        @endif
                    </figure>
                @endif
                
                @if($image2)
                    <figure class="group lg:col-span-2">
                        <img 
                            src="{{ $image2->getUrl('large') }}" 
                            alt="{{ $imageCaption2 ?? $title ?? 'Image' }}"
                            class="w-full {{ $imageAspect }} object-cover rounded-lg shadow-md group-hover:shadow-xl transition-shadow duration-300"
                            loading="lazy"
                        >
                        @if($imageCaption2)
                            <figcaption class="text-sm text-gray-600 dark:text-gray-400 mt-2 italic">
                                {{ $imageCaption2 }}
                            </figcaption>
                        @endif
                    </figure>
                @endif
            </div>
        @endif
        
    </div>
</section>