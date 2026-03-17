<?php

namespace App\Constants;

class RecordStatus
{
    public const DRAFT = 0;
    public const SUBMITTED = 1;
    public const APPROVED = 2;
    public const REJECTED = 3;
    public const REVIEWED = 4;

    /**
     * Get all status values
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::REVIEWED,
            self::APPROVED,
            self::REJECTED,
        ];
    }

    /**
     * Get status label
     */
    public static function getLabel(int $status): string
    {
        return match ($status) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::REVIEWED => 'Reviewed',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
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
            self::SUBMITTED => self::getLabel(self::SUBMITTED),
            self::REVIEWED => self::getLabel(self::REVIEWED),
            self::APPROVED => self::getLabel(self::APPROVED),
            self::REJECTED => self::getLabel(self::REJECTED),
        ];
    }
}

