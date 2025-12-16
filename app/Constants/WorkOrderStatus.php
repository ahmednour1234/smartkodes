<?php

namespace App\Constants;

class WorkOrderStatus
{
    public const DRAFT = 0;
    public const ASSIGNED = 1;
    public const IN_PROGRESS = 2;
    public const COMPLETED = 3;

    /**
     * Get all status values
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::ASSIGNED,
            self::IN_PROGRESS,
            self::COMPLETED,
        ];
    }

    /**
     * Get status label
     */
    public static function getLabel(int $status): string
    {
        return match ($status) {
            self::DRAFT => 'Draft',
            self::ASSIGNED => 'Assigned',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            default => 'Unknown',
        };
    }

    /**
     * Get all statuses with labels
     */
    public static function allWithLabels(): array
    {
        return [
            self::DRAFT => self::getLabel(self::DRAFT),
            self::ASSIGNED => self::getLabel(self::ASSIGNED),
            self::IN_PROGRESS => self::getLabel(self::IN_PROGRESS),
            self::COMPLETED => self::getLabel(self::COMPLETED),
        ];
    }
}

