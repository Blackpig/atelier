<?php

namespace BlackpigCreatif\Atelier\Concerns;

use BlackpigCreatif\Atelier\Models\AtelierBlock;

trait HasAtelierMediaExtraction
{
    /**
     * Extract image URL from a specific block type and field
     *
     * This method searches through the model's published blocks to find the first
     * block matching the specified type(s), then extracts and resolves the image
     * URL from the specified field using ChambreNoir media conversions.
     *
     * @param string $fieldName Field name containing the image (e.g., 'background_image', 'image', 'thumbnail')
     * @param string $conversion ChambreNoir conversion name (e.g., 'large', 'og', 'shop-panel')
     * @param string|array $blockTypes Block class(es) to match (e.g., HeroBlock::class or [HeroBlock::class, ImageBlock::class])
     * @return string|null Resolved image URL or null if not found
     *
     * @example
     * // Get hero background image
     * $url = $page->getImageFromBlock('background_image', 'large', HeroBlock::class);
     *
     * @example
     * // Get image from multiple possible block types
     * $url = $page->getImageFromBlock('image', 'thumbnail', [ImageBlock::class, GalleryBlock::class]);
     */
    public function getImageFromBlock(
        string $fieldName,
        string $conversion = 'large',
        string|array $blockTypes = []
    ): ?string {
        // Check if model has blocks relationship
        if (! method_exists($this, 'blocks')) {
            return null;
        }

        // Normalize to array for consistent processing
        $blockTypes = is_array($blockTypes) ? $blockTypes : [$blockTypes];

        // Find first matching published block
        $matchingBlock = $this->blocks()
            ->published()
            ->ordered()
            ->get()
            ->first(function (AtelierBlock $block) use ($blockTypes) {
                $instance = $block->hydrateBlock();

                foreach ($blockTypes as $type) {
                    if ($instance instanceof $type) {
                        return true;
                    }
                }

                return false;
            });

        if (! $matchingBlock) {
            return null;
        }

        // Get field value from hydrated block
        $instance = $matchingBlock->hydrateBlock();
        $imageData = $instance->get($fieldName);

        if (! $imageData) {
            return null;
        }

        return $this->resolveBlockMediaUrl($imageData, $conversion);
    }

    /**
     * Convenience method to get hero block background image
     *
     * @param string $conversion ChambreNoir conversion name
     * @return string|null Resolved image URL or null if not found
     */
    public function getHeroImageFromBlocks(string $conversion = 'large'): ?string
    {
        return $this->getImageFromBlock(
            fieldName: 'background_image',
            conversion: $conversion,
            blockTypes: \BlackpigCreatif\Atelier\Blocks\HeroBlock::class
        );
    }

    /**
     * Resolve ChambreNoir media URL from various formats
     *
     * Handles both simple string paths and ChambreNoir's JSON format with conversions.
     *
     * @param mixed $media Media data (string path or ChambreNoir array)
     * @param string $conversion Desired conversion name
     * @return string|null Resolved URL or null
     */
    protected function resolveBlockMediaUrl(mixed $media, string $conversion): ?string
    {
        if (empty($media)) {
            return null;
        }

        // Simple string path (not processed by ChambreNoir)
        if (is_string($media)) {
            return \Storage::disk('public')->url($media);
        }

        // ChambreNoir JSON format with conversions
        if (is_array($media) && isset($media['conversions'][$conversion])) {
            return \Storage::disk('public')->url($media['conversions'][$conversion]);
        }

        // Fall back to original if conversion doesn't exist
        if (is_array($media) && isset($media['original'])) {
            return \Storage::disk('public')->url($media['original']);
        }

        return null;
    }
}
