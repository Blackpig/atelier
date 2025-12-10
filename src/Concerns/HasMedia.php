<?php

namespace Blackpigcreatif\Atelier\Concerns;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasMedia
{
    public function getMedia(string $key, string $collection = 'blocks'): ?Media
    {
        $mediaId = $this->get($key);
        
        if (!$mediaId) {
            return null;
        }
        
        return Media::find($mediaId);
    }
    
    public function getMediaUrl(string $key, string $conversion = '', string $collection = 'blocks'): ?string
    {
        $media = $this->getMedia($key, $collection);
        
        if (!$media) {
            return null;
        }
        
        return $conversion 
            ? $media->getUrl($conversion)
            : $media->getUrl();
    }
}
