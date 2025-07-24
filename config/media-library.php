<?php

use Ingenius\Core\Support\TenantAwareUrlGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\TinyPlaceholderGenerator\Blurred;
use Spatie\MediaLibrary\ResponsiveImages\WidthCalculator\FileSizeOptimizedWidthCalculator;

return [
    /*
     * When urls to files get generated, this class will be called. Use the default
     * if your files are stored locally above the site root or on s3.
     */
    'url_generator' => TenantAwareUrlGenerator::class,

    /*
     * The fully qualified class name of the media model.
     */
    'media_model' => Media::class,

    /*
     * The responsive images configuration
     */
    'responsive_images' => [
        'width_calculator' => FileSizeOptimizedWidthCalculator::class,
        'use_tiny_placeholders' => true,
        'tiny_placeholder_generator' => Blurred::class,
    ],
];
