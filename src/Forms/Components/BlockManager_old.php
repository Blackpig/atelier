<?php

namespace BlackpigCreatif\Atelier\Forms\Components;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;

class BlockManager extends Builder
{
   /*  protected array $blockClasses = [];
    
    public function blocks($blocks): static
    {
        // Store the classes
        $this->blockClasses = is_array($blocks) ? $blocks : $this->evaluate($blocks);
        
        // Convert to Builder blocks
        $builderBlocks = collect($this->blockClasses)
            ->map(function($blockClass) {
                return Block::make($blockClass::getBlockIdentifier())
                    ->label($blockClass::getLabel())
                    ->icon($blockClass::getIcon())
                    ->schema(function() use ($blockClass) {
                        return $blockClass::getSchema();
                    })
                    ->columns(1);
            })
            ->toArray();
        
        // Call parent
        return parent::blocks($builderBlocks);
    } */
}
