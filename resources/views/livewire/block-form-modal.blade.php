<x-filament::modal
    id="block-form-modal"
    :heading="$isPreview ? ($blockType ? $blockType::getLabel() . ' Preview' : 'Block Preview') : ($blockType ? $blockType::getLabel() : 'Block Form')"
    :width="$this->getModalWidth()"
    slide-over
    :close-by-clicking-away="false"
>
    @if($isOpen && $blockType)
        @if($isPreview)
            {{-- Preview Mode --}}
            <div class="prose dark:prose-invert max-w-none">
                {!! $previewHtml !!}
            </div>

            <x-slot name="footer">
                <div class="flex justify-end">
                    <x-filament::button
                        type="button"
                        color="gray"
                        wire:click="close"
                    >
                        Close
                    </x-filament::button>
                </div>
            </x-slot>
        @else
            {{-- Form Mode --}}
            {{ $this->form }}

            <x-slot name="footer">
                <div class="flex justify-end gap-3">
                    <x-filament::button
                        type="button"
                        color="gray"
                        wire:click="close"
                    >
                        Cancel
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                    >
                        {{ $uuid ? 'Save Changes' : 'Add Block' }}
                    </x-filament::button>
                </div>
            </x-slot>
        @endif
    @else
        <div class="p-6 text-center text-gray-500">
            Loading...
        </div>
    @endif
</x-filament::modal>
