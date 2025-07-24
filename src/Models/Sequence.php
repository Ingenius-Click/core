<?php

namespace Ingenius\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sequence extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'prefix',
        'suffix',
        'start_number',
        'current_number',
        'random',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_number' => 'integer',
        'current_number' => 'integer',
        'random' => 'boolean',
    ];

    /**
     * Get the next number in the sequence and increment the current number.
     *
     * @return string
     */
    public function getNextNumber(): string
    {
        // Use a database transaction to prevent race conditions
        return DB::transaction(function () {
            // If this is the first number and current_number is 0, use start_number
            if ($this->current_number === 0) {
                $nextNumber = $this->start_number;
            } else {
                $nextNumber = $this->current_number + 1;
            }

            // Update the current number
            $this->update(['current_number' => $nextNumber]);

            // Format the number with prefix and suffix
            return $this->formatNumber($nextNumber);
        });
    }

    /**
     * Format a number with the prefix and suffix.
     *
     * @param int $number
     * @return string
     */
    public function formatNumber(int $number): string
    {
        // If random is true, append a random string
        $formattedNumber = $number;
        if ($this->random) {
            $randomPart = substr(md5(uniqid()), 0, 8);
            $formattedNumber = $number . '-' . $randomPart;
        }

        // Add prefix and suffix if they exist
        return ($this->prefix ? $this->prefix : '') .
            $formattedNumber .
            ($this->suffix ? $this->suffix : '');
    }
}
