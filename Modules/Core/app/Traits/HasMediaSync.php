<?php

namespace Modules\Core\Traits;

trait HasMediaSync
{
    public function syncMedia($model, $images, string $collection = 'default', bool $replace = true): void
    {
        if (empty($images)) {
            return;
        }

        $images = is_array($images) ? $images : [$images];

        if ($replace) {
            $model->clearMediaCollection($collection);
        }

        foreach ($images as $image) {
            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $model->addMedia($image)
                    ->sanitizingFileName(function ($fileName) {
                        return strtolower(
                            str_replace(['#', '/', '\\', ' ', '%', '?'], '-', pathinfo($fileName, PATHINFO_FILENAME))
                        ) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
                    })
                    ->toMediaCollection($collection);
            }
        }
    }
}
