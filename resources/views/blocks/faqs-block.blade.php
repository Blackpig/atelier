{{-- resources/views/blocks/faqs-block.blade.php --}}
@php
    /**
     * FAQs Block Template
     *
     * Available Variables:
     * @var \BlackpigCreatif\Atelier\Blocks\FaqsBlock $block - The block instance
     * @var array $faqs - Array of FAQ pairs with 'question' and 'answer' keys
     *
     * Helper Methods:
     * @method string $block->getWrapperClasses() - Get wrapper classes (background, spacing)
     * @method string $block->getContainerClasses() - Get container classes (width)
     * @method string $block::getBlockIdentifier() - Get block identifier (e.g., 'faqs-block')
     * @method string|null $block->getDividerComponent() - Get divider component name
     */

    $blockIdentifier = 'atelier-' . $block::getBlockIdentifier();
    $faqPairs = array_filter($block->get('faqs', []), fn ($item) => ! empty($item['question']) && ! empty($item['answer']));
@endphp

<section class="{{ $blockIdentifier }} {{ $block->getWrapperClasses() }}"
         data-block-type="{{ $block::getBlockIdentifier() }}"
         data-block-id="{{ $block->blockId ?? '' }}">

    <div class="{{ $block->getContainerClasses() }}">
        <div class="max-w-3xl mx-auto">

            @if (! empty($faqPairs))
                <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($faqPairs as $pair)
                        <div x-data="{ open: false }" class="py-4">
                            <dt>
                                <button
                                    type="button"
                                    class="flex w-full items-start justify-between text-left"
                                    @click="open = !open"
                                    :aria-expanded="open"
                                >
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">
                                        {{ $pair['question'] }}
                                    </span>
                                    <span class="ml-6 flex h-7 items-center">
                                        <svg x-show="! open" class="size-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                        </svg>
                                        <svg x-show="open" class="size-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                                        </svg>
                                    </span>
                                </button>
                            </dt>
                            <dd x-show="open" x-cloak class="mt-2 pr-12">
                                <p class="text-base text-gray-600 dark:text-gray-300">
                                    {!! nl2br(e($pair['answer'])) !!}
                                </p>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            @endif

        </div>
    </div>

    {{-- Block Divider --}}
    @if ($block->getDividerComponent())
        <x-dynamic-component
            :component="$block->getDividerComponent()"
            :to-background="$block->getDividerToBackground()"
        />
    @endif
</section>
