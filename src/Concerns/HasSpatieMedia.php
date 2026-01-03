<?php

namespace BlackpigCreatif\Atelier\Concerns;

use Illuminate\Support\Collection;

/**
 * Handles Spatie Media Library fields
 * Media is attached to AtelierBlockAttribute models with collection names
 */
trait HasSpatieMedia
{
    /**
     * Get Spatie media URL for a field
     */
    public function getSpatieMediaUrl(string $key, string $conversion = 'large'): ?string
    {
        $media = $this->getSpatieMedia($key);

        if ($media->isEmpty()) {
            return null;
        }

        return $media->first()->getUrl($conversion);
    }

    /**
     * Get all Spatie media items for a field
     */
    public function getSpatieMedia(string $key): Collection
    {
        // Get the attribute ID from metadata (only available for saved blocks)
        $attributeId = $this->data["_{$key}_attribute_id"] ?? null;
        $collectionName = $this->data["_{$key}_collection"] ?? null;

        // During preview (unsaved blocks), we don't have attribute ID
        // Return empty collection - media will be available after save
        if (!$attributeId || !$collectionName) {
            return collect();
        }

        // Load the attribute model and get its media
        $attribute = \BlackpigCreatif\Atelier\Models\AtelierBlockAttribute::find($attributeId);

        if (!$attribute) {
            return collect();
        }

        return $attribute->getMedia($collectionName);
    }

    /**
     * Get all Spatie media URLs for a field (multiple file uploads)
     */
    public function getSpatieMediaUrls(string $key, string $conversion = 'large'): array
    {
        $media = $this->getSpatieMedia($key);

        return $media->map(fn($item) => $item->getUrl($conversion))->toArray();
    }
}
