<?php

namespace Ingenius\Core\Interfaces;

use Ingenius\Core\Models\UserProfile;

/**
 * Interface HasCustomerProfile
 *
 * Enforces that user models provide customer profile data.
 * This ensures that lastname, address, and phone information
 * is available for orders and other business operations.
 *
 * @package Ingenius\Core\Interfaces
 */
interface HasCustomerProfile
{
    /**
     * Get the user's profile (polymorphic relation).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function profile();

    /**
     * Get the user's first name.
     *
     * @return string
     */
    public function getFirstName(): string;

    /**
     * Get the user's last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string;

    /**
     * Get the user's full name (firstname + lastname).
     *
     * @return string
     */
    public function getFullName(): string;

    /**
     * Get the user's email address.
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Get the user's phone number.
     *
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * Get the user's address.
     *
     * @return string|null
     */
    public function getAddress(): ?string;

    /**
     * Check if the user has a complete profile.
     *
     * @return bool
     */
    public function hasCompleteProfile(): bool;

    public function updateProfile(array $data): UserProfile;
}
