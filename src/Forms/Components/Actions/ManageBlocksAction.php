<?php

namespace BlackpigCreatif\Atelier\Forms\Components\Actions;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Str;

class ManageBlocksAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manageBlocks';
    }

    /**
     * Configure the action for adding a new block
     */
    public static function makeAddAction(): static
    {
        return static::make('addBlock')
            ->label('Add Content Block')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->modalHeading('Select Block Type')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalWidth('3xl')
            ->modalContent(fn($livewire, $action) => view('atelier::forms.components.block-type-selector', [
                'blockClasses' => $action->getBlockClasses(),
                'componentName' => $action->getComponent()->getStatePath(),
            ]))
            ->action(function (array $arguments, $livewire, Component $component) {
                // This is called from Alpine when a block type is selected
                $blockType = $arguments['blockType'] ?? null;

                if (!$blockType) {
                    return;
                }

                // Store the selected type in a temporary property
                $livewire->dispatch('open-block-form', [
                    'blockType' => $blockType,
                    'componentName' => $component->getStatePath(),
                    'uuid' => null,
                ]);
            });
    }

    /**
     * Configure the action for editing an existing block
     */
    public static function makeEditAction(): static
    {
        return static::make('editBlock')
            ->label('Edit Block')
            ->icon('heroicon-o-pencil')
            ->color('gray')
            ->modalHeading(fn($arguments) => 'Edit Block')
            ->modalWidth('5xl')
            ->fillForm(function ($arguments, Get $get, Component $component): array {
                $uuid = $arguments['uuid'] ?? null;
                $blocks = $get($component->getStatePath()) ?? [];

                foreach ($blocks as $block) {
                    if (($block['uuid'] ?? null) === $uuid) {
                        return $block['data'] ?? [];
                    }
                }

                return [];
            })
            ->form(function ($arguments, Component $component): array {
                $uuid = $arguments['uuid'] ?? null;
                $blocks = $component->getState() ?? [];

                // Find the block
                $blockData = null;
                foreach ($blocks as $block) {
                    if (($block['uuid'] ?? null) === $uuid) {
                        $blockData = $block;
                        break;
                    }
                }

                if (!$blockData) {
                    return [];
                }

                $blockClass = $blockData['type'] ?? null;

                if (!$blockClass || !class_exists($blockClass)) {
                    return [];
                }

                return $blockClass::getSchema();
            })
            ->action(function (array $data, array $arguments, Component $component, Get $get) {
                $uuid = $arguments['uuid'] ?? null;
                $blocks = $get($component->getStatePath()) ?? [];

                // Update the block data
                foreach ($blocks as $index => $block) {
                    if (($block['uuid'] ?? null) === $uuid) {
                        $blocks[$index]['data'] = $data;
                        break;
                    }
                }

                // Update component state
                $component->state($blocks);
            });
    }

    /**
     * Configure the action for deleting a block
     */
    public static function makeDeleteAction(): static
    {
        return static::make('deleteBlock')
            ->label('Delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Delete Block')
            ->modalDescription('Are you sure you want to delete this block? This action cannot be undone.')
            ->modalSubmitActionLabel('Delete')
            ->action(function (array $arguments, Component $component, Get $get) {
                $uuid = $arguments['uuid'] ?? null;
                $blocks = $get($component->getStatePath()) ?? [];

                // Filter out the block
                $blocks = array_values(array_filter($blocks, function ($block) use ($uuid) {
                    return ($block['uuid'] ?? null) !== $uuid;
                }));

                // Re-index positions
                foreach ($blocks as $index => $block) {
                    $blocks[$index]['position'] = $index;
                }

                // Update component state
                $component->state($blocks);
            });
    }

    /**
     * Get block classes from component
     */
    protected function getBlockClasses(): array
    {
        $component = $this->getComponent();

        if (method_exists($component, 'getBlockClasses')) {
            return $component->getBlockClasses();
        }

        return [];
    }
}
