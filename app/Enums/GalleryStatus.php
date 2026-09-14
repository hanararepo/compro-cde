<?php

namespace App\Enums;

enum GalleryStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';

    /**
     * Human-readable label for each status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Pending Approval',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
        };
    }

    /**
     * Tailwind CSS badge color classes for each status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-700',
            self::Pending => 'bg-yellow-100 text-yellow-800',
            self::Published => 'bg-green-100 text-green-800',
            self::Rejected => 'bg-red-100 text-red-800',
        };
    }

    /**
     * Returns true when the gallery item is visible on the public frontend.
     */
    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}
