@php
    $statePath = $getStatePath();
    $blocks = $getState() ?? [];
    $isAddable = $isAddable();
    $isEditable = $isEditable();
    $isDeletable = $isDeletable();
    $isReorderable = $isReorderable();
    $blockClasses = $getBlockClasses();
    $blockMetadata = $getBlockMetadata();
    $fieldConfigurations = $getFieldConfigurations();
    $modalKey = 'block-form-modal-' . str_replace('.', '-', $statePath);

    // Get background and divider options for quick edit
    $backgroundOptions = collect(config('atelier.features.backgrounds.options', []))->map(fn($opt) => $opt)->toArray();
    $dividerOptions = collect(config('atelier.features.dividers.options', []))->map(fn($opt) => $opt)->toArray();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    {{-- Render modal - wire:ignore prevents re-render during parent updates --}}
    <div wire:ignore>
        @livewire(\BlackpigCreatif\Atelier\Livewire\BlockFormModal::class, [], key($modalKey))
    </div>

    <div
        x-data="{
            statePath: @js($statePath),
            blockClasses: @js($blockClasses),
            blockMetadata: @js($blockMetadata),
            fieldConfigurations: @js($fieldConfigurations),
            backgroundOptions: @js($backgroundOptions),
            dividerOptions: @js($dividerOptions),
            showTypeSelector: false,

            // Use shared store for modal state
            get showQuickEditModal() {
                return Alpine.store('quickEditModal_' + this.statePath)?.show || false;
            },
            set showQuickEditModal(value) {
                if (!Alpine.store('quickEditModal_' + this.statePath)) {
                    Alpine.store('quickEditModal_' + this.statePath, { show: false, uuid: null, data: {} });
                }
                Alpine.store('quickEditModal_' + this.statePath).show = value;
            },
            get quickEditBlockUuid() {
                return Alpine.store('quickEditModal_' + this.statePath)?.uuid || null;
            },
            set quickEditBlockUuid(value) {
                if (!Alpine.store('quickEditModal_' + this.statePath)) {
                    Alpine.store('quickEditModal_' + this.statePath, { show: false, uuid: null, data: {} });
                }
                Alpine.store('quickEditModal_' + this.statePath).uuid = value;
            },
            get quickEditData() {
                return Alpine.store('quickEditModal_' + this.statePath)?.data || {};
            },
            set quickEditData(value) {
                if (!Alpine.store('quickEditModal_' + this.statePath)) {
                    Alpine.store('quickEditModal_' + this.statePath, { show: false, uuid: null, data: {} });
                }
                Alpine.store('quickEditModal_' + this.statePath).data = value;
            },

            // Use getter/setter to access shared store
            get blocks() {
                const storeKey = 'blocks_' + this.statePath.replace(/\./g, '_');
                if (!Alpine.store(storeKey)) {
                    Alpine.store(storeKey, @js($blocks));
                }
                return Alpine.store(storeKey);
            },
            set blocks(value) {
                const storeKey = 'blocks_' + this.statePath.replace(/\./g, '_');
                Alpine.store(storeKey, value);
            },

            init() {
                // Prevent multiple instances - check if already initialized
                const instanceId = 'block-manager-' + this.statePath;
                if (window[instanceId]) {
                    return;
                }
                window[instanceId] = true;

                // Initialize _revision for all blocks loaded from server
                this.blocks.forEach(block => {
                    if (block._revision === undefined) {
                        block._revision = 0;
                    }
                });

                // Listen for block form saved event - store cleanup function
                const cleanupListener = Livewire.on('block-form-saved', (event) => {
                    if (event.componentStatePath === this.statePath) {
                        this.handleBlockSaved(event);
                    }
                });

                // Clean up on component destroy (if Alpine supports it)
                if (this.$cleanup) {
                    this.$cleanup(() => {
                        cleanupListener();
                        delete window[instanceId];
                    });
                }

                // Hook into SortableJS to update Alpine state on drag end
                setTimeout(() => {
                    const container = this.$refs.sortableContainer;
                    if (container && container.sortable) {
                        container.sortable.options.onEnd = (event) => {
                            const { oldIndex, newIndex } = event;

                            // No change, do nothing
                            if (oldIndex === newIndex) return;

                            // Get new order from current DOM state (after SortableJS moved elements)
                            const items = Array.from(container.querySelectorAll('[x-sortable-item]'));
                            const newOrder = items.map(item => item.dataset.uuid);

                            // Update Alpine state and sync to Livewire
                            this.reorderBlocks(newOrder);
                        };
                    }
                }, 100);
            },

            handleBlockSaved(event) {
                const { uuid, type, data } = event;

                // Extract is_published from data
                const isPublished = data.is_published ?? true;

                // Keep is_published in data for form hydration consistency
                // It will be filtered out when saving attributes to database
                const blockData = { ...data };

                // Find existing block or add new one
                const existingIndex = this.blocks.findIndex(b => b.uuid === uuid);

                if (existingIndex !== -1) {
                    const oldBlock = this.blocks[existingIndex];

                    // Build new block object explicitly (don't spread old block)
                    // Increment revision to force Alpine x-for re-render
                    const updatedBlock = {
                        uuid: oldBlock.uuid,
                        type: oldBlock.type,
                        position: oldBlock.position,
                        data: blockData,
                        is_published: isPublished,  // Explicit override
                        _revision: (oldBlock._revision || 0) + 1,
                    };

                    // Update existing block - use splice to trigger Alpine reactivity
                    this.blocks.splice(existingIndex, 1, updatedBlock);
                } else {
                    // Add new block with initial revision
                    this.blocks.push({
                        uuid: uuid,
                        type: type,
                        data: blockData,
                        position: this.blocks.length,
                        is_published: isPublished,
                        _revision: 0,
                    });
                }

                this.reindexBlocks();

                // Sync IMMEDIATELY to Livewire (like reorderBlocks does)
                // This ensures the updated is_published value is synced before parent form save
                this.$wire.set(this.statePath, this.blocks);
            },

            openAddBlockModal() {
                this.showTypeSelector = true;
                this.$dispatch('open-modal', { id: 'block-type-selector' });
            },

            closeTypeSelector() {
                this.showTypeSelector = false;
                this.$dispatch('close-modal', { id: 'block-type-selector' });
            },

            selectBlockType(blockType) {
                // Close the type selector modal first
                this.showTypeSelector = false;
                this.$dispatch('close-modal', { id: 'block-type-selector' });

                // Open the block form modal - use Livewire 3 named parameters
                Livewire.dispatch('openBlockFormModal', {
                    componentStatePath: this.statePath,
                    blockType: blockType,
                    uuid: null,
                    data: {},
                    fieldConfigurations: this.fieldConfigurations[blockType] || {}
                });
            },

            openEditBlockModal(uuid) {
                const block = this.blocks.find(b => b.uuid === uuid);

                if (!block) {
                    return;
                }

                // is_published is already in block.data from hydration
                // But ensure it's synced with block level value
                const data = {
                    ...(block.data || {}),
                    is_published: block.data?.is_published ?? block.is_published ?? true
                };

                // Open the block form modal with existing data - use Livewire 3 named parameters
                Livewire.dispatch('openBlockFormModal', {
                    componentStatePath: this.statePath,
                    blockType: block.type,
                    uuid: block.uuid,
                    data: data,
                    fieldConfigurations: this.fieldConfigurations[block.type] || {}
                });
            },

            publishBlock(uuid) {
                const index = this.blocks.findIndex(b => b.uuid === uuid);
                if (index !== -1) {
                    const block = this.blocks[index];

                    // Create new block object (same as handleBlockSaved approach)
                    const updatedBlock = {
                        uuid: block.uuid,
                        type: block.type,
                        position: block.position,
                        data: {
                            ...block.data,
                            is_published: true
                        },
                        is_published: true,
                        _revision: (block._revision || 0) + 1,
                    };

                    // Use splice to trigger Alpine reactivity
                    this.blocks.splice(index, 1, updatedBlock);

                    // Sync to Livewire in nextTick to avoid race conditions
                    this.$nextTick(() => {
                        this.$wire.set(this.statePath, this.blocks);
                    });
                }
            },

            unpublishBlock(uuid) {
                const index = this.blocks.findIndex(b => b.uuid === uuid);
                if (index !== -1) {
                    const block = this.blocks[index];

                    // Create new block object (same as handleBlockSaved approach)
                    const updatedBlock = {
                        uuid: block.uuid,
                        type: block.type,
                        position: block.position,
                        data: {
                            ...block.data,
                            is_published: false
                        },
                        is_published: false,
                        _revision: (block._revision || 0) + 1,
                    };

                    // Use splice to trigger Alpine reactivity
                    this.blocks.splice(index, 1, updatedBlock);

                    // Sync to Livewire in nextTick to avoid race conditions
                    this.$nextTick(() => {
                        this.$wire.set(this.statePath, this.blocks);
                    });
                }
            },

            deleteBlock(uuid) {
                if (confirm('Are you sure you want to delete this block?')) {
                    const index = this.blocks.findIndex(b => b.uuid === uuid);
                    if (index !== -1) {
                        // Use splice to trigger Alpine reactivity properly
                        this.blocks.splice(index, 1);
                        this.reindexBlocks();

                        // Sync IMMEDIATELY to Livewire
                        this.$wire.set(this.statePath, this.blocks);
                    }
                }
            },

            openBlockPreview(uuid) {
                const block = this.blocks.find(b => b.uuid === uuid);

                if (!block) {
                    return;
                }

                // Dispatch to BlockFormModal in preview mode
                Livewire.dispatch('openBlockPreview', {
                    blockType: block.type,
                    data: block.data || {}
                });
            },

            reorderBlocks(newOrder) {
                // Reorder blocks - keep original Proxy objects
                const reordered = newOrder.map(uuid =>
                    this.blocks.find(block => block.uuid === uuid)
                ).filter(Boolean);

                // Update positions
                reordered.forEach((block, index) => {
                    block.position = index;
                });

                // Replace array to trigger Alpine reactivity
                this.blocks = reordered;

                // Sync IMMEDIATELY to Livewire (not deferred)
                this.$wire.set(this.statePath, this.blocks);
            },

            reindexBlocks() {
                // Update positions in place to maintain Alpine reactivity
                this.blocks.forEach((block, index) => {
                    block.position = index;
                });
            },

            openQuickEditModal(uuid) {
                const block = this.blocks.find(b => b.uuid === uuid);
                if (!block) return;

                this.quickEditBlockUuid = uuid;
                this.quickEditData = {
                    background: block.data?.background || 'white',
                    divider: block.data?.divider || 'none',
                    divider_to_background: block.data?.divider_to_background || 'white'
                };
                this.showQuickEditModal = true;
                this.$dispatch('open-modal', { id: 'quick-edit-colors' });
            },

            saveQuickEdit() {
                const index = this.blocks.findIndex(b => b.uuid === this.quickEditBlockUuid);
                if (index === -1) return;

                const block = this.blocks[index];

                // Create new block object (same as handleBlockSaved approach)
                const updatedBlock = {
                    uuid: block.uuid,
                    type: block.type,
                    position: block.position,
                    data: {
                        ...block.data,
                        background: this.quickEditData.background,
                        divider: this.quickEditData.divider,
                        divider_to_background: this.quickEditData.divider_to_background
                    },
                    is_published: block.is_published,
                    _revision: (block._revision || 0) + 1,
                };

                // Use splice to trigger Alpine reactivity
                this.blocks.splice(index, 1, updatedBlock);

                // Sync to Livewire in nextTick to avoid race conditions
                this.$nextTick(() => {
                    this.$wire.set(this.statePath, this.blocks);
                });

                this.closeQuickEditModal();
            },

            closeQuickEditModal() {
                this.showQuickEditModal = false;
                this.quickEditBlockUuid = null;
                this.quickEditData = {};
                this.$dispatch('close-modal', { id: 'quick-edit-colors' });
            },

            getBackgroundSwatchStyle(backgroundKey) {
                const option = this.backgroundOptions[backgroundKey];
                const color = option?.color || '#e5e7eb'; // fallback to gray-200
                return `background-color: ${color}`;
            }
        }"
        class="space-y-4"
    >
        {{-- Blocks List --}}
        <div
            wire:ignore
            @if($isReorderable)
                x-sortable
                x-ref="sortableContainer"
            @endif
            class="space-y-1.5"
        >
            <template x-for="(block, index) in blocks" :key="block.uuid + '-' + block.position">
                <div
                    x-sortable-item
                    :data-uuid="block.uuid"
                    class="relative"
                >
                    @include('atelier::forms.components.block-card', [
                        'isEditable' => $isEditable,
                        'isDeletable' => $isDeletable,
                        'isReorderable' => $isReorderable,
                        'blockClasses' => $blockClasses,
                    ])
                </div>
            </template>
        </div>

        {{-- Empty State --}}
        <div
            x-show="blocks.length === 0"
            class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-12 text-center"
        >
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                <x-filament::icon
                    icon="heroicon-o-cube"
                    class="w-6 h-6 text-gray-400 dark:text-gray-500"
                />
            </div>
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                No blocks yet
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Get started by adding your first content block
            </p>
        </div>

        {{-- Add Block Button --}}
        @if($isAddable)
            <div class="flex justify-center">
                <x-filament::button
                    type="button"
                    color="gray"
                    x-on:click="openAddBlockModal"
                    icon="heroicon-o-plus-circle"
                >
                    {{ $getAddButtonLabel() }}
                </x-filament::button>
            </div>
        @endif

        {{-- Block Type Selector Modal --}}
        <x-filament::modal
            id="block-type-selector"
            width="3xl"
            x-bind:visible="showTypeSelector"
            x-on:close="closeTypeSelector"
        >
            <x-slot name="heading">
                Select Block Type
            </x-slot>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="blockClass in blockClasses" :key="blockClass">
                        <button
                            type="button"
                            x-on:click="selectBlockType(blockClass)"
                            class="relative flex items-start gap-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 text-left transition-all duration-200 hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-md hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                            <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-gray-200 dark:border-gray-700">
                                <div x-html="blockMetadata[blockClass]?.iconSvg || ''" class="w-6 h-6 text-primary-600 dark:text-primary-400"></div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1" x-text="blockMetadata[blockClass]?.label || blockClass"></h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2" x-text="blockMetadata[blockClass]?.description || ''"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </x-filament::modal>

        {{-- Quick Edit Colors Modal --}}
        <x-filament::modal
            id="quick-edit-colors"
            width="md"
            x-bind:visible="showQuickEditModal"
            x-on:close="closeQuickEditModal"
        >
            <x-slot name="heading">
                Quick Edit Colors
            </x-slot>

            <div class="p-6 space-y-4">
                {{-- Background Color --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Background Color
                    </label>
                    <select
                        x-model="quickEditData.background"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <template x-for="(option, key) in backgroundOptions" :key="key">
                            <option :value="key" x-text="option.label"></option>
                        </template>
                    </select>
                </div>

                {{-- Divider Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Divider
                    </label>
                    <select
                        x-model="quickEditData.divider"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <template x-for="(option, key) in dividerOptions" :key="key">
                            <option :value="key" x-text="option.label"></option>
                        </template>
                    </select>
                </div>

                {{-- Divider To Background (only shown if divider is not 'none') --}}
                <div x-show="quickEditData.divider && quickEditData.divider !== 'none'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Divider To Background
                    </label>
                    <select
                        x-model="quickEditData.divider_to_background"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <template x-for="(option, key) in backgroundOptions" :key="key">
                            <option :value="key" x-text="option.label"></option>
                        </template>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button
                        color="gray"
                        x-on:click="closeQuickEditModal"
                    >
                        Cancel
                    </x-filament::button>
                    <x-filament::button
                        x-on:click="saveQuickEdit"
                    >
                        Save
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>
    </div>
</x-dynamic-component>

@pushOnce('scripts')
<script>
    // Helper function to get block description
    window.getBlockDescription = function(blockClass) {
        // Placeholder - you'd need to pass descriptions from backend
        return 'Configure this block type';
    };
</script>
@endPushOnce
