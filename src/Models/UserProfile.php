<?php

namespace Ingenius\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * UserProfile Model
 *
 * Stores additional profile information for users via polymorphic relation.
 * This allows minimal changes to existing User models while providing
 * required customer data (lastname, address, phone) for production use.
 *
 * @package Ingenius\Core\Models
 */
class UserProfile extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'userable_id',
        'userable_type',
        'firstname',
        'lastname',
        'phone',
        'address',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the owning userable model (User).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function userable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the full name (firstname + lastname).
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? ''));
    }

    /**
     * Check if the profile is complete (has all required fields).
     *
     * @return bool
     */
    public function isComplete(): bool
    {
        return !empty($this->firstname)
            && !empty($this->lastname)
            && !empty($this->phone)
            && !empty($this->address);
    }

    /**
     * Scope to filter complete profiles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeComplete($query)
    {
        return $query->whereNotNull('firstname')
            ->whereNotNull('lastname')
            ->whereNotNull('phone')
            ->whereNotNull('address');
    }

    /**
     * Scope to filter incomplete profiles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncomplete($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('firstname')
                ->orWhereNull('lastname')
                ->orWhereNull('phone')
                ->orWhereNull('address');
        });
    }
}
