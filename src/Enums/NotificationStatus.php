<?php

namespace Ingenius\Core\Enums;

enum NotificationStatus: string
{
    case QUEUED = 'queued';
    case SENT = 'sent';
    case FAILED = 'failed';

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
            self::QUEUED => 'Queued',
            self::SENT => 'Sent',
            self::FAILED => 'Failed',
        };
    }

    /**
     * Get badge color for UI
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::QUEUED => 'warning',
            self::SENT => 'success',
            self::FAILED => 'danger',
        };
    }

    /**
     * Get all statuses as array with labels
     */
    public static function toArrayWithLabels(): array
    {
        return array_map(
            fn($case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'color' => $case->badgeColor(),
            ],
            self::cases()
        );
    }
}
