<?php

namespace Ingenius\Core\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

trait HandleImages
{
    /**
     * @param array<UploadedFile> $images
     * @param HasMedia $model
     * @param string|null $collection
     * @return bool
     */
    protected function saveImages(array $images, HasMedia $model, ?string $collection = null): bool
    {
        $count = 0;

        foreach ($images as $image) {
            if ($this->saveImage($image, $model, $collection)) {

                $count++;
            }
        }

        return $count > 0;
    }

    /**
     * @param UploadedFile $image
     * @param HasMedia $model
     * @param string|null $collection
     * @return Media|null
     */
    protected function saveImage(UploadedFile $image, HasMedia $model, ?string $collection = null): ?Media
    {
        try {

            return $model->addMedia($image)
                ->toMediaCollection($this->getCollectionName($collection));
        } catch (Throwable $e) {

            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param UploadedFile $image
     * @param HasMedia $model
     * @param string|null $collection
     * @return Media|null
     */
    protected function saveImageToCollection(UploadedFile $image, HasMedia $model, ?string $collection = null): ?Media
    {
        try {

            return $model->addMedia($image)
                ->toMediaCollection($this->getCollectionName($collection));
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param array $images
     * @param HasMedia $model
     * @param string|null $collection
     * @return void
     */
    protected function removeImages(array $images, HasMedia $model, ?string $collection = null): void
    {
        foreach ($images as $image) {

            if ($media = $model->media()->find($image)) {

                $media->delete();
            }
        }
    }

    /**
     * @param array $images
     * @param HasMedia $model
     * @param string|null $collection
     * @return void
     */
    protected function removeImagesById($images, HasMedia $model, ?string $collection = null): void
    {
        foreach ($images as $image) {

            if ($media = $model->media()->find($image['id'], ['id'])) {
                $media->delete();
            }
        }
    }

    /**
     * @param string|null $collection
     * @return string
     */
    protected function getCollectionName(?string $collection = null): string
    {
        if ($collection !== null) {
            return $collection;
        }

        return $this->collectionName ?? 'images';
    }
}
