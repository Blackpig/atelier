@php
    // This template is included in block-manager.blade.php inside an x-for loop
    // Variables available: block (from x-for), isEditable, isDeletable, isReorderable
@endphp

<div
    class="group flex items-center gap-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 shadow-sm transition-all hover:shadow-md hover:border-primary-500 dark:hover:border-primary-500"
>
    {{-- Drag Handle --}}
    @if($isReorderable)
        <div x-sortable-handle class="flex-shrink-0 cursor-move opacity-50 group-hover:opacity-100 transition-opacity">
            <x-filament::icon
                icon="heroicon-o-bars-3"
                class="w-4 h-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            />
        </div>
    @endif

    {{-- Block Icon --}}
    <div class="flex-shrink-0">
        <div class="flex items-center justify-center w-10 h-10 rounded-md bg-primary-50 dark:bg-primary-900/20">
            <div x-html="blockMetadata[block.type]?.iconSvg || ''" class="w-5 h-5 text-primary-600 dark:text-primary-400"></div>
        </div>
    </div>

    {{-- Block Content --}}
    <div class="flex-1 min-w-0">
        {{-- Block Type Label & Preview in one line --}}
        <div class="flex items-center gap-2">
            <h4
                class="text-sm font-semibold text-gray-900 dark:text-white"
                x-text="blockMetadata[block.type]?.label || block.type.split('\\\\').pop().replace('Block', '')"
            ></h4>
            <span
                x-show="!block.is_published"
                class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400"
            >
                Draft
            </span>

            {{-- Color Flow Indicators --}}
            <template x-if="block.data?.background">
                <div class="flex items-center gap-1.5">
                    <span class="text-gray-400 dark:text-gray-600">•</span>
                    {{-- Background Color Swatch --}}
                    <div
                        class="w-3 h-3 rounded-sm border border-gray-300 dark:border-gray-600"
                        :style="getBackgroundSwatchStyle(block.data.background)"
                        :title="'Background: ' + (backgroundOptions[block.data.background]?.label || 'Unknown')"
                    ></div>
                    {{-- Divider Arrow & To Background (only if divider exists) --}}
                    <template x-if="block.data?.divider && block.data.divider !== 'none' && block.data.divider !== '' && block.data?.divider_to_background">
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <div
                                class="w-3 h-3 rounded-sm border border-gray-300 dark:border-gray-600"
                                :style="getBackgroundSwatchStyle(block.data.divider_to_background)"
                                :title="'Divider To: ' + (backgroundOptions[block.data.divider_to_background]?.label || 'Unknown')"
                            ></div>
                        </div>
                    </template>
                </div>
            </template>

            <span class="text-gray-400 dark:text-gray-600">•</span>
            <p
                class="flex-1 text-xs text-gray-500 dark:text-gray-400 truncate"
                x-text="getBlockPreview(block)"
            ></p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex-shrink-0 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
        {{-- Quick Edit Background/Divider Button --}}
        <x-filament::icon-button
            icon="heroicon-o-adjustments-horizontal"
            x-on:click="openQuickEditModal(block.uuid)"
            label="Quick Edit Colors"
            color="gray"
        />

        {{-- Publish Button (only shown when unpublished) --}}
        <x-filament::icon-button
            icon="heroicon-o-eye"
            x-show="!block.is_published"
            x-on:click="publishBlock(block.uuid)"
            label="Publish"
            color="success"
        />

        {{-- Unpublish Button (only shown when published) --}}
        <x-filament::icon-button
            icon="heroicon-o-eye-slash"
            x-show="block.is_published"
            x-on:click="unpublishBlock(block.uuid)"
            label="Unpublish"
            color="danger"
        />

        @if($isEditable)
            <x-filament::icon-button
                icon="heroicon-o-pencil"
                x-on:click="openEditBlockModal(block.uuid)"
                label="Edit"
                color="gray"
            />
        @endif

        @if($isDeletable)
            <x-filament::icon-button
                icon="heroicon-o-trash"
                x-on:click="deleteBlock(block.uuid)"
                label="Delete"
                color="danger"
            />
        @endif
    </div>
</div>

@pushOnce('scripts')
<script>
    // Helper functions for block rendering
    // Note: blockMetadata is passed from the parent Alpine component
    window.getBlockLabel = function(blockType) {
        // Try to get label from parent Alpine blockMetadata
        const parentElement = document.querySelector('[x-data]');
        if (parentElement && parentElement._x_dataStack) {
            const alpineData = parentElement._x_dataStack[0];
            if (alpineData.blockMetadata && alpineData.blockMetadata[blockType]) {
                return alpineData.blockMetadata[blockType].label;
            }
        }

        // Fallback to extracting from class name
        return blockType.split('\\').pop().replace('Block', '');
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
