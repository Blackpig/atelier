<?php

namespace BlackpigCreatif\Atelier\Forms\Components;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Illuminate\Support\Str;

class BlockManager extends Builder
{
    protected array $blockClasses = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Hydrate blocks from database BEFORE component renders
       /*  $this->afterStateHydrated(function ($component, $state) {
            $record = $component->getRecord();
            
            if (!$record || !$record->exists) {
                return;
            }
            
            $blocks = $record->blocks()
                ->ordered()
                ->get()
                ->map(function ($block) {
                    $blockClass = $block->block_type;
                    
                    if (!class_exists($blockClass)) {
                        return null;
                    }
                    
                    $translatableFields = $blockClass::getTranslatableFields();
                    $attributes = $block->attributes()->get();
                    $data = [];
                    
                    foreach ($attributes->groupBy('key') as $key => $attributeGroup) {
                        if (in_array($key, $translatableFields)) {
                            $translations = [];
                            foreach ($attributeGroup as $attr) {
                                if ($attr->locale) {
                                    $value = $attr->getCastedValue();
                                    if (is_array($value) && empty($value)) {
                                        $value = null;
                                    }
                                    $translations[$attr->locale] = $value;
                                }
                            }
                            $data[$key] = $translations;
                        } else {
                            $value = $attributeGroup->first()->getCastedValue();
                            if (is_array($value) && empty($value)) {
                                $value = null;
                            }
                            $data[$key] = $value;
                        }
                    }
                    
                    return [
                        'type' => $blockClass::getBlockIdentifier(),
                        'uuid' => $block->uuid,
                        'data' => $data,
                    ];
                })
                ->filter()
                ->toArray();
            
                $component->state($blocks);
                $component->getLivewire()->dispatch('refresh-file-uploads');
        });
        
        // Save blocks to database
        $this->saveRelationshipsUsing(function ($component, $state) {
            if (!$state) {
                return;
            }
            
            $record = $component->getRecord();
            $existingBlocks = $record->blocks()->pluck('uuid')->toArray();
            $processedBlocks = [];
            
            $position = 0;
            foreach ($state as $blockData) {
                $blockType = $blockData['type'];
                $blockClass = $this->getBlockClassFromIdentifier($blockType);
                
                if (!$blockClass) {
                    continue;
                }
                
                $uuid = $blockData['uuid'] ?? null;
                $block = $uuid ? $record->blocks()->where('uuid', $uuid)->first() : null;
                
                if (!$block) {
                    $block = $record->blocks()->create([
                        'block_type' => $blockClass,
                        'uuid' => $uuid ?? (string) Str::uuid(),
                        'position' => $position,
                    ]);
                } else {
                    $block->update(['position' => $position]);
                }
                
                $processedBlocks[] = $block->uuid;
                $this->saveBlockAttributes($block, $blockClass, $blockData['data']);
                $position++;
            }
            
            $blocksToDelete = array_diff($existingBlocks, $processedBlocks);
            $record->blocks()->whereIn('uuid', $blocksToDelete)->delete();
        }); */
    }

    public function blocks(Closure|array $blockClasses): static
    {
        // If it's a closure, evaluate it
        if ($blockClasses instanceof Closure) {
            $blockClasses = $this->evaluate($blockClasses);
        }
        
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
            ->schema(fn() => $blockClass::getSchema())
            ->columns(1);
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
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->slideOver(),
            ]);
        }
        
        return $this;
    }
    
    protected function saveBlockAttributes($block, string $blockClass, array $data): void
    {
        $translatableFields = $blockClass::getTranslatableFields();
        
        // Get existing attributes keyed by a composite key
        $existingAttributes = $block->attributes()->get()->keyBy(function($attr) {
            return $attr->locale ? "{$attr->key}|{$attr->locale}" : $attr->key;
        });
        
        $processedKeys = [];
        $sortOrder = 0;
        
        foreach ($data as $key => $value) {
            $isTranslatable = in_array($key, $translatableFields);
            
            // Only extract file paths for fields that look like file uploads
            if ($this->isFileUploadValue($value)) {
                $value = $this->extractFilePath($value);
            }
            
            if ($isTranslatable && is_array($value)) {
                // Save translation for each locale
                foreach ($value as $locale => $translatedValue) {
                    if ($translatedValue !== null && $translatedValue !== '') {
                        $compositeKey = "{$key}|{$locale}";
                        $processedKeys[] = $compositeKey;
                        
                        // Extract file path for translatable values too
                        if ($this->isFileUploadValue($translatedValue)) {
                            $translatedValue = $this->extractFilePath($translatedValue);
                        }
                        
                        if ($existingAttributes->has($compositeKey)) {
                            $existingAttributes->get($compositeKey)->update([
                                'value' => $this->castValueForStorage($translatedValue),
                                'type' => $this->getValueType($translatedValue),
                                'sort_order' => $sortOrder,
                            ]);
                        } else {
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
                }
            } else {
                // Non-translatable field
                $compositeKey = $key;
                $processedKeys[] = $compositeKey;
                
                if ($existingAttributes->has($compositeKey)) {
                    $existingAttributes->get($compositeKey)->update([
                        'value' => $this->castValueForStorage($value),
                        'type' => $this->getValueType($value),
                        'sort_order' => $sortOrder,
                    ]);
                } else {
                    $block->attributes()->create([
                        'key' => $key,
                        'value' => $this->castValueForStorage($value),
                        'type' => $this->getValueType($value),
                        'locale' => null,
                        'translatable' => false,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }
            
            $sortOrder++;
        }
        
        // Delete attributes that no longer exist
        $attributesToDelete = $existingAttributes->filter(function($attr) use ($processedKeys) {
            $attrKey = $attr->locale ? "{$attr->key}|{$attr->locale}" : $attr->key;
            return !in_array($attrKey, $processedKeys);
        });
        
        foreach ($attributesToDelete as $attr) {
            $attr->delete();
        }
        
        $block->clearCache();
    }
    
    /**
     * Check if a value looks like a file upload value
     */
    protected function isFileUploadValue($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        
        // Check if it's the {"uuid":"path"} format from file uploads
        foreach ($value as $key => $val) {
            // File upload format has UUID keys and path values
            if (is_string($key) && is_string($val) && 
                preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $key) &&
                str_contains($val, '/')) {
                return true;
            }
            // Also check for simple array of paths ["path1", "path2"]
            if (is_numeric($key) && is_string($val) && str_contains($val, '/')) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Extract actual file paths from Livewire temporary upload format
     * Always returns array format for FileUpload compatibility
     */
    protected function extractFilePath($value)
    {
        if (!is_array($value)) {
            return is_string($value) && $value !== '' ? [$value] : null;
        }
        
        // Handle {"uuid":"path"} format - extract the paths
        $paths = [];
        foreach ($value as $key => $val) {
            if (is_string($val) && str_contains($val, '/')) {
                $paths[] = $val;
            }
        }
        
        return !empty($paths) ? $paths : null;
    }
    
    protected function castValueForStorage(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }
        
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        
        if ($value === null) {
            return '';
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

}
