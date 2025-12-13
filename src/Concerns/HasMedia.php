<?php

namespace BlackpigCreatif\Atelier\Concerns;

trait HasMedia
{
    public function getMediaUrl(string $key): ?string
    {
        $path = $this->get($key);
        
        if (!$path) {
            return null;
        }
        
        // If it's an array (multiple files), get first
        if (is_array($path)) {
            $path = $path[0] ?? null;
        }
        
        if (!$path) {
            return null;
        }
        
        return \Storage::url($path);
    }
    
    public function getMedia(string $key): ?string
    {
        return $this->getMediaUrl($key);
    }
}
