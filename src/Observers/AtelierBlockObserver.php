<?php

namespace BlackpigCreatif\Atelier\Observers;

use BlackpigCreatif\Atelier\Models\AtelierBlock;

class AtelierBlockObserver
{
    public function updated(AtelierBlock $block): void
    {
        $block->clearCache();
    }
    
    public function deleted(AtelierBlock $block): void
    {
        $block->clearCache();
    }

    public function deleting(AtelierBlock $block): void
    {
        // Get all media UUIDs from this block's attributes
        $mediaUuids = $block->attributes()
            ->where('type', 'array')
            ->get()
            ->map(function($attr) {
                $value = json_decode($attr->value, true);
                return is_array($value) ? $value : [];
            })
            ->flatten()
            ->filter()
            ->unique()
            ->toArray();
        
        // Delete the media files
        if (!empty($mediaUuids)) {
            \Spatie\MediaLibrary\MediaCollections\Models\Media::whereIn('uuid', $mediaUuids)->each(function($media) {
                $media->delete();
            });
        }
    }
}
