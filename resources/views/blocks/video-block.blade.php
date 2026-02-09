{{-- resources/views/blocks/video-block.blade.php --}}
@php
    /**
     * Video Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\VideoBlock $block - The block instance
     * @var string|null $title - Translated title
     * @var string|null $description - Translated description
     * @var string $video_url - Video URL
     * @var string $aspect_ratio - Aspect ratio class (e.g., 'aspect-video')
     * @var string $max_width - Max width class (e.g., 'max-w-4xl')
     * @var bool $autoplay - Enable autoplay
     * @var bool $muted - Start muted
     *
     * Helper Methods:
     * @method string $block->getTranslated(string $field) - Get translated value for field
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block::getBlockIdentifier() - Get block identifier (e.g., 'video-block')
     * @method string|null $block->getDividerComponent() - Get divider component name
     * @method string|null $block->getDividerToBackground() - Get divider target background class
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();

    // Convert video URL to embed URL
    $embedUrl = $video_url ?? '';
    $videoType = 'unknown';

    if (str_contains($embedUrl, 'youtube.com') || str_contains($embedUrl, 'youtu.be')) {
        // Extract YouTube video ID
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $embedUrl, $matches);
        $videoId = $matches[1] ?? '';
        if ($videoId) {
            $embedUrl = "https://www.youtube.com/embed/{$videoId}";
            $embedUrl .= '?' . http_build_query([
                'autoplay' => $autoplay ?? false ? 1 : 0,
                'mute' => $muted ?? false ? 1 : 0,
            ]);
            $videoType = 'youtube';
        }
    } elseif (str_contains($embedUrl, 'vimeo.com')) {
        // Extract Vimeo video ID
        preg_match('/vimeo\.com\/(\d+)/', $embedUrl, $matches);
        $videoId = $matches[1] ?? '';
        if ($videoId) {
            $embedUrl = "https://player.vimeo.com/video/{$videoId}";
            $embedUrl .= '?' . http_build_query([
                'autoplay' => $autoplay ?? false ? 1 : 0,
                'muted' => $muted ?? false ? 1 : 0,
            ]);
            $videoType = 'vimeo';
        }
    }
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        <div class="{{ $max_width ?? 'max-w-4xl' }} mx-auto">

            {{-- Title --}}
            @if($title = $block->getTranslated('title'))
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900 dark:text-white">
                    {{ $title }}
                </h2>
            @endif

            {{-- Description --}}
            @if($description = $block->getTranslated('description'))
                <p class="mb-6 text-lg text-gray-600 dark:text-gray-400">
                    {{ $description }}
                </p>
            @endif

            {{-- Video Embed --}}
            @if($embedUrl)
                <div class="{{ $aspect_ratio ?? 'aspect-video' }} rounded-lg overflow-hidden shadow-lg bg-gray-900">
                    @if(in_array($videoType, ['youtube', 'vimeo']))
                        <iframe
                            src="{{ $embedUrl }}"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                    @else
                        {{-- Direct video URL or unsupported platform --}}
                        <video
                            src="{{ $embedUrl }}"
                            class="w-full h-full"
                            controls
                            @if($autoplay ?? false) autoplay @endif
                            @if($muted ?? false) muted @endif
                            preload="metadata"
                        >
                            Your browser does not support the video tag.
                        </video>
                    @endif
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
