{{-- resources/views/blocks/hero-block.blade.php --}}
@php
    /**
     * Hero Block Template
     * 
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\HeroBlock $block - The block instance
     * @var string|null $headline - Translated headline text
     * @var string|null $subheadline - Translated subheadline text
     * @var string|null $description - Translated description text
     * @var string|null $cta_text - Translated button text
     * @var string|null $cta_url - Button URL
     * @var bool $cta_new_tab - Whether to open link in new tab
     * @var string $height - Height class (e.g., 'min-h-[600px]')
     * @var string $text_color - Text color class (e.g., 'text-white')
     * @var string $content_alignment - Content alignment classes
     * @var string $overlay_opacity - Overlay opacity value (0-80)
     * 
     * Helper Methods:
     * @method string $block->getTranslated(string $field) - Get translated value for field
     * @method \Spatie\MediaLibrary\MediaCollections\Models\Media|null $block->getMedia(string $field) - Get media item
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block->getOverlayClass() - Get overlay darkness class
     */
@endphp

<section class="atelier-hero relative {{ $height }} overflow-hidden {{ $block->getWrapperClasses() }}" 
         data-block-type="hero"
         data-block-id="{{ $block->blockId ?? '' }}">
    
    {{-- Background Image Layer --}}
    @if($backgroundImage = $block->getMediaUrl('background_image'))
        <div class="absolute inset-0 z-0">
            <img 
                src="{{ $backgroundImage }}" 
                alt="{{ $block->getTranslated('headline') ?? 'Hero background' }}"
                class="w-full h-full object-cover"
                loading="eager"
            >
            
            {{-- Dark Overlay for Text Readability --}}
            @if($overlayClass = $block->getOverlayClass())
                <div class="absolute inset-0 {{ $overlayClass }}"></div>
            @endif
        </div>
    @endif
    
    {{-- Content Layer --}}
    <div class="relative z-10 h-full {{ $block->getContainerClasses() }}">
        <div class="h-full flex flex-col justify-center {{ $content_alignment ?? 'text-center items-center' }} py-12 md:py-20">
            <div class="max-w-4xl {{ $text_color ?? 'text-white' }}">
                
                {{-- Headline --}}
                @if($headline = $block->getTranslated('headline'))
                    <h1 class="text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold mb-6 leading-tight animate-fade-in">
                        {{ $headline }}
                    </h1>
                @endif
                
                {{-- Subheadline --}}
                @if($subheadline = $block->getTranslated('subheadline'))
                    <p class="text-xl md:text-2xl lg:text-3xl mb-6 opacity-90 animate-fade-in animation-delay-200">
                        {{ $subheadline }}
                    </p>
                @endif
                
                {{-- Description --}}
                @if($description = $block->getTranslated('description'))
                    <div class="text-base md:text-lg lg:text-xl mb-8 opacity-80 max-w-2xl {{ str_contains($content_alignment ?? '', 'center') ? 'mx-auto' : '' }} animate-fade-in animation-delay-400">
                        {{ $description }}
                    </div>
                @endif
                
                {{-- Call to Action Button --}}
                @if(($ctaText = $block->getTranslated('cta_text')) && $cta_url)
                    <div class="mt-8 animate-fade-in animation-delay-600">
                        <a 
                            href="{{ $cta_url }}" 
                            class="inline-block px-8 py-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-4 focus:ring-primary-300"
                            @if($cta_new_tab ?? false) 
                                target="_blank" 
                                rel="noopener noreferrer"
                                aria-label="{{ $ctaText }} (opens in new tab)"
                            @else
                                aria-label="{{ $ctaText }}"
                            @endif
                        >
                            {{ $ctaText }}
                            @if($cta_new_tab ?? false)
                                <svg class="inline-block w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            @endif
                        </a>
                    </div>
                @endif
                
            </div>
        </div>
    </div>
</section>

{{-- Optional: Add smooth animations --}}
@once
    @push('styles')
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
            opacity: 0;
        }
        
        .animation-delay-200 {
            animation-delay: 0.2s;
        }
        
        .animation-delay-400 {
            animation-delay: 0.4s;
        }
        
        .animation-delay-600 {
            animation-delay: 0.6s;
        }
        
        /* Reduce motion for users who prefer it */
        @media (prefers-reduced-motion: reduce) {
            .animate-fade-in {
                animation: none;
                opacity: 1;
                transform: none;
            }
        }
    </style>
    @endpush
@endonce
