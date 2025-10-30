<?php

namespace Ingenius\Core\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Ingenius\Core\Models\UserProfile;

/**
 * Trait HasCustomerProfileTrait
 *
 * Provides default implementation for HasCustomerProfile interface.
 * Retrieves customer data from the polymorphic user_profiles table.
 *
 * This trait allows minimal changes to existing User models while
 * providing required customer profile data (lastname, address, phone).
 *
 * @package Ingenius\Core\Traits
 */
trait HasCustomerProfileTrait
{
    /**
     * Get the user's profile (polymorphic relation).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function profile(): MorphOne
    {
        return $this->morphOne(UserProfile::class, 'userable');
    }

    /**
     * Get the user's first name.
     * Falls back to 'name' attribute if profile doesn't exist.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        // Try profile first
        if ($this->profile && $this->profile->firstname) {
            return $this->profile->firstname;
        }

        // Fallback to 'name' attribute (backward compatibility)
        if (isset($this->attributes['name'])) {
            return $this->attributes['name'];
        }

        return '';
    }

    /**
     * Get the user's last name.
     *
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->profile?->lastname;
    }

    /**
     * Get the user's full name (firstname + lastname).
     * Falls back to 'name' attribute if profile doesn't exist.
     *
     * @return string
     */
    public function getFullName(): string
    {
        // If profile exists and has firstname, use profile data
        if ($this->profile && $this->profile->firstname) {
            return $this->profile->full_name;
        }

        // Fallback to 'name' attribute (backward compatibility)
        if (isset($this->attributes['name'])) {
            return $this->attributes['name'];
        }

        return '';
    }

    /**
     * Get the user's email address.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    /**
     * Get the user's phone number.
     *
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->profile?->phone;
    }

    /**
     * Get the user's address.
     *
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->profile?->address;
    }

    /**
     * Check if the user has a complete profile.
     *
     * @return bool
     */
    public function hasCompleteProfile(): bool
    {
        return $this->profile?->isComplete() ?? false;
    }

    /**
     * Create or update the user's profile.
     *
     * @param array $data
     * @return \Ingenius\Core\Models\UserProfile
     */
    public function updateProfile(array $data): UserProfile
    {
        return $this->profile()->updateOrCreate(
            [
                'userable_id' => $this->id,
                'userable_type' => get_class($this),
            ],
            $data
        );
    }

    /**
     * Eager load profile relationship by default.
     * Add this to your User model's $with property for automatic eager loading.
     *
     * protected $with = ['profile'];
     */
}
