@php
    // Variables passed from modal: $blockClasses, $componentName
@endphp

<div
    x-data="{
        selectBlockType(blockType) {
            // Close the modal
            close();

            // Open the block form modal
            $wire.dispatchFormEvent('open-block-form-modal', '{{ $componentName }}', blockType, null);
        }
    }"
    class="p-6"
>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($blockClasses as $blockClass)
            @php
                if (!class_exists($blockClass)) {
                    continue;
                }

                $label = $blockClass::getLabel();
                $description = $blockClass::getDescription();
                $icon = $blockClass::getIcon();
                $identifier = $blockClass::getBlockIdentifier();
            @endphp

            <button
                type="button"
                x-on:click="selectBlockType('{{ $blockClass }}')"
                class="relative flex flex-col items-start gap-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 text-left transition hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
            >
                {{-- Icon --}}
                <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-primary-50 dark:bg-primary-900/20">
                    <x-filament::icon
                        :icon="$icon"
                        class="w-6 h-6 text-primary-600 dark:text-primary-400"
                    />
                </div>

                {{-- Content --}}
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                        {{ $label }}
                    </h3>

                    @if($description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                            {{ $description }}
                        </p>
                    @endif
                </div>

                {{-- Arrow Icon --}}
                <div class="absolute top-6 right-6">
                    <x-filament::icon
                        icon="heroicon-m-arrow-right"
                        class="w-5 h-5 text-gray-400"
                    />
                </div>
            </button>
        @endforeach
    </div>

    @if(empty($blockClasses))
        <div class="flex flex-col items-center justify-center p-12 text-center">
            <x-filament::icon
                icon="heroicon-o-cube-transparent"
                class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-4"
            />
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-1">
                No block types available
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Please register block types in your BlockManager configuration.
            </p>
        </div>
    @endif
</div>
