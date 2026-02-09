<?php

namespace BlackpigCreatif\Atelier\Support;

class BlockFieldConfig
{
    protected static array $configurations = [];

    protected static array $temporaryConfigurations = [];

    protected static array $schemaModifiers = [];

    /**
     * Register a field configuration for a block
     *
     * @param string $blockClass
     * @param string $fieldName
     * @param array $config
     * @return void
     */
    public static function register(string $blockClass, string $fieldName, array $config): void
    {
        $key = static::makeKey($blockClass, $fieldName);
        static::$configurations[$key] = $config;
    }

    /**
     * Get configuration for a field
     * Temporary configurations (per-resource) override global configurations
     *
     * @param string $blockClass
     * @param string $fieldName
     * @return array
     */
    public static function get(string $blockClass, string $fieldName): array
    {
        $key = static::makeKey($blockClass, $fieldName);

        // Temporary configs have priority
        if (isset(static::$temporaryConfigurations[$key])) {
            return static::$temporaryConfigurations[$key];
        }

        return static::$configurations[$key] ?? [];
    }

    /**
     * Check if a field has configuration
     *
     * @param string $blockClass
     * @param string $fieldName
     * @return bool
     */
    public static function has(string $blockClass, string $fieldName): bool
    {
        $key = static::makeKey($blockClass, $fieldName);
        return isset(static::$configurations[$key]);
    }

    /**
     * Get all global configurations for a specific block class
     * Returns array keyed by field name
     *
     * @param string $blockClass
     * @return array<string, array>
     */
    public static function getAllForBlock(string $blockClass): array
    {
        $blockConfigs = [];
        $prefix = $blockClass . '::';
        $prefixLength = strlen($prefix);

        foreach (static::$configurations as $key => $config) {
            if (str_starts_with($key, $prefix)) {
                $fieldName = substr($key, $prefixLength);
                $blockConfigs[$fieldName] = $config;
            }
        }

        return $blockConfigs;
    }

    /**
     * Register multiple field configurations for a block at once
     * Convenience method for batch configuration
     *
     * @param string $blockClass
     * @param array<string, array> $configurations ['fieldName' => ['config' => 'value']]
     * @return void
     */
    public static function configure(string $blockClass, array $configurations): void
    {
        foreach ($configurations as $fieldName => $config) {
            static::register($blockClass, $fieldName, $config);
        }
    }

    /**
     * Helper to set select/radio options for a field
     * Common use case shorthand
     *
     * @param string $blockClass
     * @param string $fieldName
     * @param array $options
     * @return void
     */
    public static function setOptions(string $blockClass, string $fieldName, array $options): void
    {
        static::register($blockClass, $fieldName, ['options' => $options]);
    }

    /**
     * Register a global schema modifier for a block class
     * Modifier receives the schema array and must return modified schema array
     *
     * @param string $blockClass
     * @param \Closure $modifier
     * @return void
     */
    public static function modifySchema(string $blockClass, \Closure $modifier): void
    {
        if (!isset(static::$schemaModifiers[$blockClass])) {
            static::$schemaModifiers[$blockClass] = [];
        }
        static::$schemaModifiers[$blockClass][] = $modifier;
    }

    /**
     * Get all schema modifiers for a block class
     *
     * @param string $blockClass
     * @return array<\Closure>
     */
    public static function getSchemaModifiers(string $blockClass): array
    {
        return static::$schemaModifiers[$blockClass] ?? [];
    }

    /**
     * Clear all schema modifiers (useful for testing)
     *
     * @return void
     */
    public static function clearSchemaModifiers(): void
    {
        static::$schemaModifiers = [];
    }

    /**
     * Register a temporary field configuration (per-resource override)
     * These take priority over global configurations and are cleared after use
     *
     * @param string $blockClass
     * @param string $fieldName
     * @param array $config
     * @return void
     */
    public static function registerTemporary(string $blockClass, string $fieldName, array $config): void
    {
        $key = static::makeKey($blockClass, $fieldName);
        static::$temporaryConfigurations[$key] = $config;
    }

    /**
     * Clear temporary configurations
     * Called after schema is built to prevent configurations bleeding into other forms
     *
     * @return void
     */
    public static function clearTemporary(): void
    {
        static::$temporaryConfigurations = [];
    }

    /**
     * Clear all configurations (global, temporary, and schema modifiers)
     *
     * @return void
     */
    public static function clear(): void
    {
        static::$configurations = [];
        static::$temporaryConfigurations = [];
        static::$schemaModifiers = [];
    }

    /**
     * Make a unique key for a block field
     *
     * @param string $blockClass
     * @param string $fieldName
     * @return string
     */
    protected static function makeKey(string $blockClass, string $fieldName): string
    {
        return $blockClass . '::' . $fieldName;
    }

    // ========================================
    // Schema Manipulation Helpers
    // ========================================

    /**
     * Remove field(s) from schema by name (recursive - searches nested components)
     * Modifies components in-place to avoid cloning issues
     *
     * @param array $schema
     * @param array|string $fieldNames
     * @return array
     */
    public static function removeFields(array $schema, array|string $fieldNames): array
    {
        $fieldsToRemove = is_array($fieldNames) ? $fieldNames : [$fieldNames];

        // First pass: recursively filter nested children in container components
        foreach ($schema as $component) {
            if (property_exists($component, 'childComponents')) {
                try {
                    $reflection = new \ReflectionProperty($component, 'childComponents');
                    $reflection->setAccessible(true);
                    $childComponents = $reflection->getValue($component);

                    if (is_array($childComponents) && !empty($childComponents)) {
                        foreach ($childComponents as $key => $children) {
                            if (is_array($children)) {
                                // Recursively filter children
                                $childComponents[$key] = static::removeFields($children, $fieldsToRemove);
                            }
                        }
                        // Update the component's children in-place
                        $reflection->setValue($component, $childComponents);
                    }
                } catch (\Exception $e) {
                    // Continue if we can't access the property
                }
            }
        }

        // Second pass: filter top-level components
        return array_values(array_filter($schema, function ($component) use ($fieldsToRemove) {
            if (!method_exists($component, 'getName')) {
                return true;
            }
            return !in_array($component->getName(), $fieldsToRemove);
        }));
    }

    /**
     * Add field(s) to the end of schema
     *
     * @param array $schema
     * @param array $fields
     * @return array
     */
    public static function addFields(array $schema, array $fields): array
    {
        return [...$schema, ...$fields];
    }

    /**
     * Insert field(s) at the beginning of schema
     *
     * @param array $schema
     * @param array $fields
     * @return array
     */
    public static function prependFields(array $schema, array $fields): array
    {
        return [...$fields, ...$schema];
    }

    /**
     * Insert field(s) before a specific field
     *
     * @param array $schema
     * @param string $beforeFieldName
     * @param array $fields
     * @return array
     */
    public static function insertBefore(array $schema, string $beforeFieldName, array $fields): array
    {
        $index = static::findFieldIndex($schema, $beforeFieldName);

        if ($index === null) {
            return $schema;
        }

        return [
            ...array_slice($schema, 0, $index),
            ...$fields,
            ...array_slice($schema, $index),
        ];
    }

    /**
     * Insert field(s) after a specific field
     *
     * @param array $schema
     * @param string $afterFieldName
     * @param array $fields
     * @return array
     */
    public static function insertAfter(array $schema, string $afterFieldName, array $fields): array
    {
        $index = static::findFieldIndex($schema, $afterFieldName);

        if ($index === null) {
            return $schema;
        }

        return [
            ...array_slice($schema, 0, $index + 1),
            ...$fields,
            ...array_slice($schema, $index + 1),
        ];
    }

    /**
     * Move a field to a different position
     *
     * @param array $schema
     * @param string $fieldName
     * @param int $newPosition
     * @return array
     */
    public static function moveField(array $schema, string $fieldName, int $newPosition): array
    {
        $index = static::findFieldIndex($schema, $fieldName);

        if ($index === null) {
            return $schema;
        }

        $field = $schema[$index];
        unset($schema[$index]);
        $schema = array_values($schema);

        return [
            ...array_slice($schema, 0, $newPosition),
            $field,
            ...array_slice($schema, $newPosition),
        ];
    }

    /**
     * Find the index of a field by name
     *
     * @param array $schema
     * @param string $fieldName
     * @return int|null
     */
    protected static function findFieldIndex(array $schema, string $fieldName): ?int
    {
        foreach ($schema as $index => $component) {
            if (method_exists($component, 'getName') && $component->getName() === $fieldName) {
                return $index;
            }
        }

        return null;
    }
}
