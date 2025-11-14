<?php

namespace Ingenius\Core\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface for models that can be reviewed
 *
 * Any model implementing this interface can receive reviews and have review statistics
 */
interface IReviewable
{
    /**
     * Get all reviews for this reviewable model
     */
    public function reviews(): MorphMany;

    /**
     * Get the reviewable identifier (typically id)
     */
    public function getReviewableId();

    /**
     * Get the reviewable type (model class name)
     */
    public function getReviewableType(): string;
}
