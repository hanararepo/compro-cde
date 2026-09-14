<?php

namespace App\Enums;

enum JobType: string
{
    case FullTime  = 'full_time';
    case PartTime  = 'part_time';

    /**
     * Human-readable label for the current locale.
     */
    public function label(): string
    {
        return match ($this) {
            self::FullTime => app()->getLocale() === 'id' ? 'Penuh Waktu' : 'Full Time',
            self::PartTime => app()->getLocale() === 'id' ? 'Paruh Waktu' : 'Part Time',
        };
    }

    /**
     * Tailwind badge colour classes.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::FullTime => 'bg-brand-100 text-brand-700 border border-brand-200',
            self::PartTime => 'bg-amber-100 text-amber-700 border border-amber-200',
        };
    }
}
