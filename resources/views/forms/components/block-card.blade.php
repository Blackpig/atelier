@php
    // This template is included in block-manager.blade.php inside an x-for loop
    // Variables available: block (from x-for), isEditable, isDeletable, isReorderable
@endphp

<div
    class="flex items-start gap-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-4 shadow-sm transition hover:shadow-md"
    x-bind:class="{ 'cursor-move': {{ $isReorderable ? 'true' : 'false' }} }"
>
    {{-- Drag Handle --}}
    @if($isReorderable)
        <div class="flex-shrink-0 pt-1 cursor-move">
            <x-filament::icon
                icon="heroicon-o-bars-3"
                class="w-5 h-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            />
        </div>
    @endif

    {{-- Block Icon --}}
    <div class="flex-shrink-0">
        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-900/20">
            <x-filament::icon
                icon="heroicon-o-cube"
                class="w-5 h-5 text-primary-600 dark:text-primary-400"
            />
        </div>
    </div>

    {{-- Block Content --}}
    <div class="flex-1 min-w-0">
        {{-- Block Type Label --}}
        <div class="flex items-center gap-2 mb-1">
            <h4
                class="text-sm font-medium text-gray-900 dark:text-white"
                x-text="getBlockLabel(block.type)"
            ></h4>
            <span
                x-show="!block.is_published"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
            >
                Draft
            </span>
        </div>

        {{-- Block Preview --}}
        <p
            class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2"
            x-text="getBlockPreview(block)"
        ></p>

        {{-- Position Badge --}}
        <div class="mt-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                Position: <span x-text="index + 1"></span>
            </span>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex-shrink-0 flex items-center gap-1">
        @if($isEditable)
            <x-filament::icon-button
                icon="heroicon-o-pencil"
                x-on:click="openEditBlockModal(block.uuid)"
                label="Edit"
                color="gray"
                size="sm"
            />
        @endif

        @if($isDeletable)
            <x-filament::icon-button
                icon="heroicon-o-trash"
                x-on:click="deleteBlock(block.uuid)"
                label="Delete"
                color="danger"
                size="sm"
            />
        @endif
    </div>
</div>

@pushOnce('scripts')
<script>
    // Helper functions for block rendering
    window.getBlockLabel = function(blockType) {
        // Map block types to labels
        const labels = {
            'BlackpigCreatif\\Atelier\\Blocks\\HeroBlock': 'Hero Section',
            'BlackpigCreatif\\Atelier\\Blocks\\TextWithTwoImagesBlock': 'Text with Two Images',
        };

        return labels[blockType] || blockType.split('\\').pop().replace('Block', '');
    };

    window.getBlockPreview = function(block, currentLocale = 'en') {
        const data = block.data || {};

        // Try common field names for preview
        const previewFields = ['headline', 'title', 'content', 'description', 'text'];

        for (const field of previewFields) {
            if (data[field]) {
                let value = data[field];

                // Handle translatable fields (objects with locale keys like {en: "value", fr: "valeur"})
                if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                    // Try to get value for current locale, fallback to first available
                    value = value[currentLocale] || Object.values(value)[0];
                }

                // Handle rich text content (TipTap format)
                if (typeof value === 'object' && value !== null && value.type === 'doc') {
                    // Extract text from TipTap JSON
                    value = extractTextFromTipTap(value);
                }

                // Strip HTML tags and truncate
                if (typeof value === 'string' && value.trim() !== '') {
                    const stripped = value.replace(/<[^>]*>/g, '');
                    return stripped.length > 100
                        ? stripped.substring(0, 100) + '...'
                        : stripped;
                }
            }
        }

        return 'No preview available';
    };

    function extractTextFromTipTap(doc) {
        if (!doc || !doc.content) return '';

        let text = '';
        for (const node of doc.content) {
            if (node.content) {
                for (const child of node.content) {
                    if (child.text) {
                        text += child.text + ' ';
                    }
                }
            }
        }
        return text.trim();
    }
</script>
@endPushOnce
