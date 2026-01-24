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
     * @method string|null $block->getFileUploadUrl(string $field) - Get file URL for field
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block->getOverlayClass() - Get overlay darkness class
     * @method string $block::getBlockIdentifier() - Get block identifier (e.g., 'hero-block')
     * @method string|null $block->getDividerComponent() - Get divider component name
     * @method string|null $block->getDividerToBackground() - Get divider target background class
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }} {{ $height }} overflow-hidden"
         data-block-type="{{ $block::getBlockIdentifier() }}"
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
