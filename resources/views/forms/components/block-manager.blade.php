@php
    $statePath = $getStatePath();
    $blocks = $getState() ?? [];
    $isAddable = $isAddable();
    $isEditable = $isEditable();
    $isDeletable = $isDeletable();
    $isReorderable = $isReorderable();
    $blockClasses = $getBlockClasses();
    $modalKey = 'block-form-modal-' . str_replace('.', '-', $statePath);
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    {{-- Render modal with stable wire:key to persist across re-renders --}}
    <div wire:key="{{ $modalKey }}" class="hidden">
        @livewire(\BlackpigCreatif\Atelier\Livewire\BlockFormModal::class, [], key($modalKey))
    </div>

    <div
        x-data="{
            blocks: @js($blocks),
            statePath: @js($statePath),
            blockClasses: @js($blockClasses),
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

                // Sync before any Livewire request (like form save)
                Livewire.hook('commit', ({component}) => {
                    // Only sync for this component instance
                    if (this.needsSync && wire.__instance && component.id === wire.__instance.id) {
                        console.log('Syncing before Livewire commit');
                        // Use $wire to update state
                        wire.set(statePath, getBlocks());
                        setNeedsSyncFalse();
                    }
                });
            },

            handleBlockSaved(event) {
                const { uuid, type, data } = event;

                console.log('handleBlockSaved called', { uuid, type, data });

                // Find existing block or add new one
                const existingIndex = this.blocks.findIndex(b => b.uuid === uuid);

                if (existingIndex !== -1) {
                    // Update existing block - use splice to trigger Alpine reactivity
                    this.blocks.splice(existingIndex, 1, {
                        ...this.blocks[existingIndex],
                        data: data,
                    });
                } else {
                    // Add new block
                    this.blocks.push({
                        uuid: uuid,
                        type: type,
                        data: data,
                        position: this.blocks.length,
                        is_published: true,
                    });
                }

                this.reindexBlocks();
                console.log('Block saved, state updated:', this.blocks);

                // Mark that we need to sync, but don't do it now (causes re-render)
                // Will sync when form is submitted via Livewire.hook('commit')
                this.needsSync = true;
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
                console.log('selectBlockType called', { blockType, statePath: this.statePath });

                // Close the type selector modal first
                this.showTypeSelector = false;
                this.$dispatch('close-modal', { id: 'block-type-selector' });

                console.log('Dispatching openBlockFormModal event');

                // Open the block form modal - use Livewire 3 named parameters
                Livewire.dispatch('openBlockFormModal', {
                    componentStatePath: this.statePath,
                    blockType: blockType,
                    uuid: null,
                    data: {}
                });

                console.log('Event dispatched');
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

            reorderBlocks(newOrder) {
                const reordered = newOrder.map(uuid =>
                    this.blocks.find(block => block.uuid === uuid)
                ).filter(Boolean);

                this.blocks = reordered;
                this.reindexBlocks();
                this.needsSync = true;
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
                x-on:sortable-end="reorderBlocks($event.detail.map(el => el.dataset.uuid))"
            @endif
            class="space-y-3"
        >
            <template x-for="(block, index) in blocks" :key="block.uuid">
                <div
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
                            class="relative flex flex-col items-start gap-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 text-left transition hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                            <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-primary-50 dark:bg-primary-900/20">
                                <x-filament::icon
                                    icon="heroicon-o-cube"
                                    class="w-6 h-6 text-primary-600 dark:text-primary-400"
                                />
                            </div>

                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1" x-text="getBlockLabel(blockClass)"></h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2" x-text="getBlockDescription(blockClass)"></p>
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
