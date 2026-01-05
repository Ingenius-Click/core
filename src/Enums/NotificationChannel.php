<?php

namespace Ingenius\Core\Enums;

enum NotificationChannel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';

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
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
        };
    }

    /**
     * Get all channels as array with labels
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
