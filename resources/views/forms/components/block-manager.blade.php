@php
    $statePath = $getStatePath();
    $blocks = $getState() ?? [];
    $isAddable = $isAddable();
    $isEditable = $isEditable();
    $isDeletable = $isDeletable();
    $isReorderable = $isReorderable();
    $blockClasses = $getBlockClasses();
    $blockMetadata = $getBlockMetadata();
    $modalKey = 'block-form-modal-' . str_replace('.', '-', $statePath);
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
            blocks: @js($blocks),
            statePath: @js($statePath),
            blockClasses: @js($blockClasses),
            blockMetadata: @js($blockMetadata),
            showTypeSelector: false,
            needsSync: false,

            init() {
                // Store reference to $wire for use in Livewire hooks
                const wire = this.$wire;
                const statePath = this.statePath;
                const getBlocks = () => this.blocks;
                const setNeedsSyncFalse = () => { this.needsSync = false; };

                // Listen for block form saved event
                Livewire.on('block-form-saved', (event) => {
                    if (event.componentStatePath === this.statePath) {
                        this.handleBlockSaved(event);
                    }
                });

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

                // Sync Alpine state to Livewire before ANY commit (Edit/Create/Update)
                // This handles block edits/additions, reordering syncs immediately
                Livewire.hook('commit', ({ component }) => {
                    console.log('⚡ Livewire commit hook fired', { needsSync: this.needsSync, blocksCount: this.blocks.length });
                    if (this.needsSync) {
                        console.log('🔄 Syncing blocks to Livewire state', { statePath, blocks: getBlocks() });
                        wire.set(statePath, getBlocks());
                        setNeedsSyncFalse();
                        console.log('✅ Sync complete, needsSync set to false');
                    }
                });
            },

            handleBlockSaved(event) {
                const { uuid, type, data } = event;

                console.log('🎯 handleBlockSaved called', { uuid, type, data });

                // Find existing block or add new one
                const existingIndex = this.blocks.findIndex(b => b.uuid === uuid);

                if (existingIndex !== -1) {
                    // Update existing block - use splice to trigger Alpine reactivity
                    this.blocks.splice(existingIndex, 1, {
                        ...this.blocks[existingIndex],
                        data: data,
                    });
                    console.log('✏️ Updated existing block at index', existingIndex);
                } else {
                    // Add new block
                    this.blocks.push({
                        uuid: uuid,
                        type: type,
                        data: data,
                        position: this.blocks.length,
                        is_published: true,
                    });
                    console.log('➕ Added new block, total blocks:', this.blocks.length);
                }

                this.reindexBlocks();

                // Mark that we need to sync, but don't do it now (causes re-render)
                // Will sync when form is submitted via Livewire.hook('commit')
                this.needsSync = true;
                console.log('🔄 needsSync set to true, blocks array:', this.blocks);
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
                    data: {}
                });
            },

            openEditBlockModal(uuid) {
                const block = this.blocks.find(b => b.uuid === uuid);

                if (!block) {
                    return;
                }

                // Open the block form modal with existing data - use Livewire 3 named parameters
                Livewire.dispatch('openBlockFormModal', {
                    componentStatePath: this.statePath,
                    blockType: block.type,
                    uuid: block.uuid,
                    data: block.data || {}
                });
            },

            deleteBlock(uuid) {
                if (confirm('Are you sure you want to delete this block?')) {
                    const index = this.blocks.findIndex(b => b.uuid === uuid);
                    if (index !== -1) {
                        // Use splice to trigger Alpine reactivity properly
                        this.blocks.splice(index, 1);
                        this.reindexBlocks();
                        this.needsSync = true;
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
