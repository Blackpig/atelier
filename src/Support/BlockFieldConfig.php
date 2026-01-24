<?php

namespace BlackpigCreatif\Atelier\Support;

class BlockFieldConfig
{
    protected static array $configurations = [];

    protected static array $temporaryConfigurations = [];

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
     * Clear all configurations (both global and temporary)
     *
     * @return void
     */
    public static function clear(): void
    {
        static::$configurations = [];
        static::$temporaryConfigurations = [];
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
}
