<?php

namespace App\Models\Traits;

use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\FileAdder;

trait HasHashedMediaTrait
{
    use InteractsWithMedia {
        InteractsWithMedia::addMedia as parentAddMedia;
    }

    public function addMedia($file): FileAdder
    {
        // Handle both UploadedFile objects and string paths
        if (is_string($file)) {
            // If it's a string path, use the original addMedia method
            return $this->parentAddMedia($file);
        } else {
            // If it's an UploadedFile object, use hashName()
            return $this->parentAddMedia($file)->usingFileName($file->hashName());
        }
    }
}
