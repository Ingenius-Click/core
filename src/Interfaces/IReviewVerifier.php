<?php

namespace Ingenius\Core\Interfaces;

interface IReviewVerifier
{
    /**
     * Check if a reviewer can review a specific reviewable entity
     *
     * @param mixed $reviewer The user/entity attempting to review
     * @param mixed $reviewable The entity being reviewed
     * @return bool
     */
    public function canReview($reviewer, $reviewable): bool;

    /**
     * Get verification metadata (e.g., purchase date, order number)
     *
     * @param mixed $reviewer The user/entity attempting to review
     * @param mixed $reviewable The entity being reviewed
     * @return array
     */
    public function getVerificationMetadata($reviewer, $reviewable): array;

    public function getFailedMessage(): string;
}
