<?php

namespace App\Constants;

class FormStatus
{
    public const DRAFT = 0;
    public const LIVE = 1;
    public const ARCHIVED = 2;

    /**
     * Get all status values
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::LIVE,
            self::ARCHIVED,
        ];
    }

    /**
     * Get status label
     */
    public static function getLabel(int $status): string
    {
        return match ($status) {
            self::DRAFT => 'Draft',
            self::LIVE => 'Live',
            self::ARCHIVED => 'Archived',
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
            self::LIVE => self::getLabel(self::LIVE),
            self::ARCHIVED => self::getLabel(self::ARCHIVED),
        ];
    }
}

