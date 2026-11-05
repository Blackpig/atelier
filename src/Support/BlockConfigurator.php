<?php

namespace BlackpigCreatif\Atelier\Support;

use Closure;

class BlockConfigurator
{
    protected string $blockClass;

    protected array $fieldConfigurations = [];

    protected ?Closure $schemaModifier = null;

    /**
     * Create a new configurator for a block class
     */
    public static function for(string $blockClass): static
    {
        $instance = new static;
        $instance->blockClass = $blockClass;

        return $instance;
    }

    /**
     * Configure a field using actual component method names
     * This is the primary API - calls real Filament component methods
     *
     * @param  array  $config  ['methodName' => $value] - e.g., ['options' => [...], 'default' => '1']
     *
     * @example
     * ->configure('columns', [
     *     'options' => ['1' => '1 Column', '2' => '2 Columns'],
     *     'default' => '1',
     *     'required' => true,
     * ])
     * // This calls: $component->options(...)->default(...)->required(...)
     */
    public function configure(string $fieldName, array $config): static
    {
        if (isset($this->fieldConfigurations[$fieldName])) {
            // Merge with existing config
            $this->fieldConfigurations[$fieldName] = array_merge(
                $this->fieldConfigurations[$fieldName],
                $config
            );
        } else {
            $this->fieldConfigurations[$fieldName] = $config;
        }

        return $this;
    }

    /**
     * Hide one or more fields (sets visible to false)
     * Convenience method for better readability
     */
    public function hide(string ...$fieldNames): static
    {
        foreach ($fieldNames as $fieldName) {
            $this->configure($fieldName, ['visible' => false]);
        }

        return $this;
    }

    /**
     * Show one or more fields (sets visible to true)
     * Convenience method for better readability
     */
    public function show(string ...$fieldNames): static
    {
        foreach ($fieldNames as $fieldName) {
            $this->configure($fieldName, ['visible' => true]);
        }

        return $this;
    }

    /**
     * Remove one or more fields entirely from the block schema.
     * This is different from hide() - it completely removes fields from the schema.
     */
    public function remove(string ...$fieldNames): static
    {
        $fieldsToRemove = $fieldNames;

        $this->modifySchema(function ($schema) use ($fieldsToRemove) {
            return BlockFieldConfig::removeFields($schema, $fieldsToRemove);
        });

        return $this;
    }

    /**
     * Remove one or more container sections by their heading label.
     */
    public function removeSection(string ...$headings): static
    {
        $headingsToRemove = $headings;

        $this->modifySchema(function ($schema) use ($headingsToRemove) {
            return BlockFieldConfig::removeSections($schema, $headingsToRemove);
        });

        return $this;
    }

    /**
     * Make one or more fields required
     * Convenience method for better readability
     */
    public function require(string ...$fieldNames): static
    {
        foreach ($fieldNames as $fieldName) {
            $this->configure($fieldName, ['required' => true]);
        }

        return $this;
    }

    /**
     * Make one or more fields optional
     * Convenience method for better readability
     */
    public function optional(string ...$fieldNames): static
    {
        foreach ($fieldNames as $fieldName) {
            $this->configure($fieldName, ['required' => false]);
        }

        return $this;
    }

    /**
     * Add a custom schema modifier closure
     * This allows full control over the schema array
     *
     * @param  Closure  $modifier  fn($schema) => $modifiedSchema
     */
    public function modifySchema(Closure $modifier): static
    {
        $existingModifier = $this->schemaModifier;

        if ($existingModifier) {
            // Chain modifiers if one already exists
            $this->schemaModifier = function ($schema) use ($existingModifier, $modifier) {
                $schema = $existingModifier($schema);

                return $modifier($schema);
            };
        } else {
            $this->schemaModifier = $modifier;
        }

        return $this;
    }

    /**
     * Insert fields before a specific field
     */
    public function insertBefore(string $beforeFieldName, array $fields): static
    {
        return $this->modifySchema(function ($schema) use ($beforeFieldName, $fields) {
            return BlockFieldConfig::insertBefore($schema, $beforeFieldName, $fields);
        });
    }

    /**
     * Insert fields after a specific field
     */
    public function insertAfter(string $afterFieldName, array $fields): static
    {
        return $this->modifySchema(function ($schema) use ($afterFieldName, $fields) {
            return BlockFieldConfig::insertAfter($schema, $afterFieldName, $fields);
        });
    }

    /**
     * Add fields to the end of the schema
     */
    public function addFields(array $fields): static
    {
        return $this->modifySchema(function ($schema) use ($fields) {
            return BlockFieldConfig::addFields($schema, $fields);
        });
    }

    /**
     * Add fields to the beginning of the schema
     */
    public function prependFields(array $fields): static
    {
        return $this->modifySchema(function ($schema) use ($fields) {
            return BlockFieldConfig::prependFields($schema, $fields);
        });
    }

    /**
     * Apply all configurations to BlockFieldConfig registry
     * Must be called explicitly to commit the configurations
     */
    public function apply(): void
    {
        // Register field configurations
        foreach ($this->fieldConfigurations as $fieldName => $config) {
            BlockFieldConfig::register($this->blockClass, $fieldName, $config);
        }

        // Register schema modifier if set
        if ($this->schemaModifier) {
            BlockFieldConfig::modifySchema($this->blockClass, $this->schemaModifier);
        }

        // Clear local state after applying
        $this->fieldConfigurations = [];
        $this->schemaModifier = null;
    }

    /**
     * Static helper to configure and apply in one call
     * Useful when you want immediate application
     */
    public static function configureUsing(string $blockClass, Closure $callback): void
    {
        $configurator = static::for($blockClass);
        $callback($configurator);
        $configurator->apply();
    }
}
