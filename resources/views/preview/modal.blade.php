{{-- resources/views/preview/modal.blade.php --}}
<div class="atelier-preview">
    @if(isset($block['type']))
        @php
            // Get the block class from identifier
            $blockClasses = config('atelier.blocks', []);
            $blockClass = null;
            
            foreach ($blockClasses as $class) {
                if ($class::getBlockIdentifier() === $block['type']) {
                    $blockClass = $class;
                    break;
                }
            }
            
            // If we couldn't find in config, try the shipped blocks
            if (!$blockClass) {
                $shippedBlocks = [
                    \Blackpigcreatif\Atelier\Blocks\HeroBlock::class,
                    \Blackpigcreatif\Atelier\Blocks\TextWithTwoImagesBlock::class,
                ];
                
                foreach ($shippedBlocks as $class) {
                    if ($class::getBlockIdentifier() === $block['type']) {
                        $blockClass = $class;
                        break;
                    }
                }
            }
            
            if ($blockClass) {
                $instance = new $blockClass();
                $instance->fill($block['data'] ?? []);
                $instance->setLocale(app()->getLocale());
            }
        @endphp
        
        @if(isset($instance))
            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                    {!! $instance->render() !!}
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>{{ __('atelier::atelier.preview.title') }}:</strong> 
                    {{ __('atelier::atelier.preview.note', ['locale' => app()->getLocale()]) }}
                </p>
            </div>
        @else
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    {{ __('atelier::atelier.preview.not_available', ['type' => $block['type']]) }}
                </p>
            </div>
        @endif
    @else
        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('atelier::atelier.preview.no_data') }}
            </p>
        </div>
    @endif
</div>

<style>
    .atelier-preview {
        min-height: 400px;
    }
    
    /* Ensure preview content is contained */
    .atelier-preview img {
        max-width: 100%;
        height: auto;
    }
    
    .atelier-preview * {
        box-sizing: border-box;
    }
</style>
