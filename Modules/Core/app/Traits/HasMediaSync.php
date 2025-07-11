<?php

namespace Modules\Core\Traits;

trait HasMediaSync
{
    /**
     * Sync media files with the given model.
     *
     * @param  mixed  $model  The model to associate media with.
     * @param  array|\Illuminate\Http\UploadedFile  $images  Single or multiple uploaded image files.
     * @param  string  $collection  The media collection name (default is 'default').
     * @param  bool  $replace  Whether to replace the existing media in the collection.
     * @return void
     */
    public function syncMedia($model, $images, string $collection = 'default', bool $replace = true): void
    {
        // If no images are provided, exit early
        if (empty($images)) {
            return;
        }

        // Ensure $images is an array
        $images = is_array($images) ? $images : [$images];

        // Optionally clear the existing media collection before adding new ones
        if ($replace) {
            $model->clearMediaCollection($collection);
        }

        // Loop through each image and add it to the media collection
        foreach ($images as $image) {
            // Only handle valid uploaded files
            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $model->addMedia($image)
                    // Customize the file name to remove unwanted characters
                    ->sanitizingFileName(function ($fileName) {
                        return strtolower(
                            str_replace(['#', '/', '\\', ' ', '%', '?'], '-', pathinfo($fileName, PATHINFO_FILENAME))
                        ) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
                    })
                    ->toMediaCollection($collection); // Save the file to the specified media collection
            }
        }
    }
}
