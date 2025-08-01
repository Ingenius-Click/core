<?php

namespace Ingenius\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ingenius\Core\Support\Image;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Template extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['name', 'description', 'identifier', 'features', 'active', 'styles_vars'];

    protected $casts = [
        'features' => 'array',
        'styles_vars' => 'array',
    ];

    protected $appends = ['images'];

    protected $hidden = ['media'];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->width(235)
                    ->height(235)
                    ->nonQueued();
                $this->addMediaConversion('rectangle')
                    ->width(365)
                    ->height(190)
                    ->nonQueued();
            });
        $this->addMediaCollection('file')
            ->singleFile();
    }

    protected function getImagesAttribute(): array
    {
        $collection = $this->getMedia('images');

        $images = [];

        foreach ($collection as $media) {

            $images[] = new Image(
                $media->id,
                $media->getUrl(),
                $media->getUrl('thumb'),
                $media->getUrl('rectangle'),
                $media->mime_type,
                $media->size
            );
        }

        return $images;
    }
}
