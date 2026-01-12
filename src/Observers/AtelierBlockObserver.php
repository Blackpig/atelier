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
        // Get all file paths from this block's attributes
        $filePaths = $block->attributes()
            ->get()
            ->map(function($attr) {
                $value = json_decode($attr->value, true);

                // Handle array of paths (multiple file uploads)
                if (is_array($value)) {
                    return array_values($value);
                }

                // Handle single path
                if (is_string($value) && !empty($value)) {
                    return [$value];
                }

                return [];
            })
            ->flatten()
            ->filter(function($path) {
                // Only include actual file paths (not livewire temp paths, not absolute paths)
                return is_string($path)
                    && !str_contains($path, 'livewire-tmp')
                    && !str_starts_with($path, '/');
            })
            ->unique()
            ->toArray();

        // Delete the files from storage
        if (!empty($filePaths)) {
            \Storage::delete($filePaths);
        }
    }
}
