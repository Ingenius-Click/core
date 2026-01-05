<?php

namespace Ingenius\Core\Enums;

enum RecipientType: string
{
    case CUSTOMER = 'customer';
    case ADMIN = 'admin';

    /**
     * Get the string value of the enum
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::CUSTOMER => 'Customer',
            self::ADMIN => 'Admin',
        };
    }

    /**
     * Get all recipient types as array with labels
     */
    public static function toArrayWithLabels(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }
}
