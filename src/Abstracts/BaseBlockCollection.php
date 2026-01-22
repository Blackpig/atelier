<?php

namespace BlackpigCreatif\Atelier\Abstracts;

abstract class BaseBlockCollection
{
    /**
     * Define the blocks included in this collection
     *
     * @return array<int, class-string<BaseBlock>>
     */
    abstract public function getBlocks(): array;

    /**
     * Static helper to get blocks from this collection
     *
     * @return array<int, class-string<BaseBlock>>
     */
    public static function make(): array
    {
        return (new static)->getBlocks();
    }

    /**
     * Get the collection name/label
     * Override in child classes for better identification
     */
    public static function getLabel(): string
    {
        return class_basename(static::class);
    }

    /**
     * Get the collection description
     * Override in child classes for documentation
     */
    public static function getDescription(): ?string
    {
        return null;
    }
}
