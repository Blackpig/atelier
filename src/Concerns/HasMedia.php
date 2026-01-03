<?php

namespace BlackpigCreatif\Atelier\Concerns;

/**
 * Unified media interface supporting:
 * - Regular FileUpload (string paths or arrays)
 * - ChambreNoir RetouchFileUpload (JSON with conversions)
 */
trait HasMedia
{
    use HasFileUpload;

    /**
     * Get media URL - delegates to FileUpload handler
     * Supports both regular FileUpload and ChambreNoir formats
     */
    public function getMediaUrl(string $key, string $conversion = 'large'): ?string
    {
        return $this->getFileUploadUrl($key, $conversion);
    }

    /**
     * Get all media URLs
     */
    public function getMediaUrls(string $key, string $conversion = 'large'): array
    {
        return $this->getFileUploadUrls($key, $conversion);
    }
}
