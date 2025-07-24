<?php

namespace Ingenius\Core\Services;

use Ingenius\Core\Models\Sequence;
use Illuminate\Support\Facades\Config;

class SequenceGeneratorService
{
    /**
     * Generate a number for the given type.
     *
     * @param string $type
     * @return string
     */
    public function generateNumber(string $type): string
    {
        // Get or create the sequence
        $sequence = $this->getSequence($type);

        // Get the next number
        return $sequence->getNextNumber();
    }

    /**
     * Get a sequence by type. If it doesn't exist, create it with default settings.
     *
     * @param string $type
     * @return Sequence
     */
    public function getSequence(string $type): Sequence
    {
        $sequence = Sequence::where('type', $type)->first();

        if (!$sequence) {
            // Get default settings from config
            $config = Config::get('sequences.' . $type, []);

            // Create a new sequence with default or config settings
            $sequence = $this->createSequence(
                $type,
                $config['prefix'] ?? null,
                $config['suffix'] ?? null,
                $config['start_number'] ?? 1,
                $config['random'] ?? false
            );
        }

        return $sequence;
    }

    /**
     * Create a new sequence.
     *
     * @param string $type
     * @param string|null $prefix
     * @param string|null $suffix
     * @param int $startNumber
     * @param bool $random
     * @return Sequence
     */
    public function createSequence(
        string $type,
        ?string $prefix = null,
        ?string $suffix = null,
        int $startNumber = 1,
        bool $random = false
    ): Sequence {
        return Sequence::create([
            'type' => $type,
            'prefix' => $prefix,
            'suffix' => $suffix,
            'start_number' => $startNumber,
            'current_number' => 0, // Will be updated when getNextNumber is called
            'random' => $random,
        ]);
    }
}
