<?php

namespace BlackpigCreatif\Atelier\Concerns;

use BlackpigCreatif\ChambreNoir\Services\ConversionManager;

/**
 * Handles simple FileUpload fields (stores paths as strings/arrays)
 * Also supports ChambreNoir RetouchFileUpload format
 */
trait HasFileUpload
{
    public function getFileUploadUrl(string $key, string $conversion = 'large'): ?string
    {
        $path = $this->get($key);

        if (! $path) {
            return null;
        }

        // Handle ChambreNoir JSON format: {"original": "path", "conversions": {...}}
        if (is_array($path) && isset($path['original'])) {
            $manager = app(ConversionManager::class);

            return $manager->getUrl($path, $conversion, 'public');
        }

        // Handle array formats from FileUpload (UUID-keyed arrays)
        if (is_array($path)) {
            // Extract just the path values
            $paths = array_values($path);
            $path = $paths[0] ?? null;
        }

        if (! $path || ! is_string($path)) {
            return null;
        }

        // Handle Livewire temporary upload paths (preview mode)
        // These are absolute paths that aren't web-accessible yet
        // Skip showing images in preview for temp uploads
        if (str_contains($path, 'livewire-tmp') || str_starts_with($path, '/')) {
            return null; // Preview won't show image, but that's OK
        }

        // Regular stored file paths (relative paths)
        return \Storage::url($path);
    }

    /**
     * Get all file upload URLs for a field (for multiple file uploads)
     */
    public function getFileUploadUrls(string $key, string $conversion = 'large'): array
    {
        $paths = $this->get($key);

        if (! $paths) {
            return [];
        }

        // Handle ChambreNoir JSON format: {"original": "path", "conversions": {...}}
        if (is_array($paths) && isset($paths['original'])) {
            // Single image in ChambreNoir format
            $manager = app(ConversionManager::class);
            $url = $manager->getUrl($paths, $conversion, 'public');

            return $url ? [$url] : [];
        }

        // If it's not an array, make it one
        if (! is_array($paths)) {
            $paths = [$paths];
        }

        // Extract path values (handles both numeric and UUID keys)
        $pathValues = array_values($paths);

        // Convert all paths to URLs
        return array_map(
            fn ($path) => $path ? \Storage::url($path) : null,
            array_filter($pathValues)
        );
    }
}
