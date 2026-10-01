<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

final class LocalTime
{
    public static function format(?DateTimeInterface $date, string $format = 'd M Y, H:i T'): string
    {
        if ($date === null) {
            return '—';
        }

        // Convert a copy so rendering cannot change the model's stored UTC timestamp.
        return CarbonImmutable::instance($date)
            ->setTimezone(config('app.display_timezone', 'Asia/Jakarta'))
            ->format($format);
    }
}
