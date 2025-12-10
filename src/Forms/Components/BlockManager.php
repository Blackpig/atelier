<?php

namespace Blackpigcreatif\Atelier\Forms\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Str;

class BlockManager extends Builder
{
    protected array $blockClasses = [];
    
    public function blocks(array $blockClasses): static
    {
        $this->blockClasses = $blockClasses;
        
        // Convert block classes to Filament Builder blocks
        $builderBlocks = collect($blockClasses)
            ->map(fn($blockClass) => $this->convertToBuilderBlock($blockClass))
            ->toArray();
        
        // Call parent blocks() method
        parent::blocks($builderBlocks);
        
        return $this;
    }
    
    protected function convertToBuilderBlock(string $blockClass): Block
    {
        return Block::make($blockClass::getBlockIdentifier())
            ->label($blockClass::getLabel())
            ->icon($blockClass::getIcon())
            ->schema($blockClass::getSchema())
            ->columns(2)
            ->when(
                $blockClass::getDescription(),
                fn(Block $block, string $description) => $block->hint($description)
            );
    }
    
    /**
     * Enable live preview for blocks
     */
    public function livePreview(bool $condition = true): static
    {
        if ($condition) {
            $this->extraItemActions([
                Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Block Preview')
                    ->modalContent(fn (array $arguments, $component) => 
                        view('atelier::preview.modal', [
                            'block' => $component->getItemState($arguments['item']),
                        ])
                    )
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->slideOver(),
            ]);
        }
        
        return $this;
    }
    
    /**
     * Override the default state handling to work with our database structure
     */
    public function saveRelationshipsUsing(?Closure $callback): static
    {
        $this->saveRelationshipsUsing = $callback ?? function ($component, $state) {
            if (!$state) {
                return;
            }
            
            $record = $component->getRecord();
            
            // Get existing blocks
            $existingBlocks = $record->blocks()->pluck('uuid')->toArray();
            $processedBlocks = [];
            
            foreach ($state as $position => $blockData) {
                $blockType = $blockData['type'];
                $blockClass = $this->getBlockClassFromIdentifier($blockType);
                
                if (!$blockClass) {
                    continue;
                }
                
                // Find or create the block
                $uuid = $blockData['uuid'] ?? null;
                $block = $uuid 
                    ? $record->blocks()->where('uuid', $uuid)->first()
                    : null;
                
                if (!$block) {
                    $block = $record->blocks()->create([
                        'block_type' => $blockClass,
                        'position' => $position,
                        'uuid' => $uuid ?? (string) Str::uuid(),
                    ]);
                } else {
                    $block->update(['position' => $position]);
                }
                
                $processedBlocks[] = $block->uuid;
                
                // Save attributes
                $this->saveBlockAttributes($block, $blockClass, $blockData['data']);
            }
            
            // Delete removed blocks
            $blocksToDelete = array_diff($existingBlocks, $processedBlocks);
            $record->blocks()->whereIn('uuid', $blocksToDelete)->delete();
        };
        
        return $this;
    }
    
    protected function saveBlockAttributes($block, string $blockClass, array $data): void
    {
        $translatableFields = $blockClass::getTranslatableFields();
        $locales = array_keys(config('atelier.locales', ['en' => 'English']));
        
        // Clear existing attributes for this block
        $block->attributes()->delete();
        
        $sortOrder = 0;
        
        foreach ($data as $key => $value) {
            $isTranslatable = in_array($key, $translatableFields);
            
            if ($isTranslatable && is_array($value)) {
                // Save translation for each locale
                foreach ($value as $locale => $translatedValue) {
                    if ($translatedValue !== null && $translatedValue !== '') {
                        $block->attributes()->create([
                            'key' => $key,
                            'value' => $this->castValueForStorage($translatedValue),
                            'type' => $this->getValueType($translatedValue),
                            'locale' => $locale,
                            'translatable' => true,
                            'sort_order' => $sortOrder,
                        ]);
                    }
                }
            } else {
                // Non-translatable field - save once without locale
                $block->attributes()->create([
                    'key' => $key,
                    'value' => $this->castValueForStorage($value),
                    'type' => $this->getValueType($value),
                    'locale' => null,
                    'translatable' => false,
                    'sort_order' => $sortOrder,
                ]);
            }
            
            $sortOrder++;
        }
        
        // Clear cache for this block
        $block->clearCache();
    }
    
    protected function castValueForStorage(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }
        
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        
        return (string) $value;
    }
    
    protected function getValueType(mixed $value): string
    {
        return match(true) {
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_bool($value) => 'boolean',
            is_array($value) => 'array',
            default => 'string',
        };
    }
    
    protected function getBlockClassFromIdentifier(string $identifier): ?string
    {
        foreach ($this->blockClasses as $class) {
            if ($class::getBlockIdentifier() === $identifier) {
                return $class;
            }
        }
        
        return null;
    }
    
    /**
     * Load state from database
     */
    public function dehydrateStateUsing(?Closure $callback): static
    {
        $this->dehydrateStateUsing = $callback ?? function ($component, $state) {
            $record = $component->getRecord();
            
            if (!$record || !$record->exists) {
                return [];
            }
            
            return $record->blocks()
                ->ordered()
                ->get()
                ->map(function ($block) {
                    $blockClass = $block->block_type;
                    $translatableFields = $blockClass::getTranslatableFields();
                    
                    // Load all attributes for this block
                    $attributes = $block->attributes()->get();
                    
                    $data = [];
                    
                    // Group by key
                    foreach ($attributes->groupBy('key') as $key => $attributeGroup) {
                        if (in_array($key, $translatableFields)) {
                            // Build translation array: field_name => ['en' => 'value', 'fr' => 'value']
                            $translations = [];
                            foreach ($attributeGroup as $attr) {
                                if ($attr->locale) {
                                    $translations[$attr->locale] = $attr->getCastedValue();
                                }
                            }
                            $data[$key] = $translations;
                        } else {
                            // Non-translatable - just the value
                            $data[$key] = $attributeGroup->first()->getCastedValue();
                        }
                    }
                    
                    return [
                        'type' => $blockClass::getBlockIdentifier(),
                        'uuid' => $block->uuid,
                        'data' => $data,
                    ];
                })
                ->toArray();
        };
        
        return $this;
    }
}
