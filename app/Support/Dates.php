<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Date helpers, ported 1:1 from the Apps Script originals. Everything speaks
 * the same string formats the client already parses, and day arithmetic is
 * done in UTC-day units so a timezone shift can never move a date by one.
 */
class Dates
{
    public const DAY_NAMES = [
        'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday',
    ];

    public static function tz(): string
    {
        return config('crm.timezone');
    }

    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::tz());
    }

    /** Timestamp format used by every `createdAt` / `submittedAt` column. */
    public static function nowStr(): string
    {
        return self::now()->format('Y/m/d H:i:s');
    }

    public static function todayStr(): string
    {
        return self::now()->format('Y/m/d');
    }

    /** Parse a leading YYYY-MM-DD / YYYY/MM/DD into whole days since the epoch. */
    public static function parseYmd(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! preg_match('#^(\d{4})[/-](\d{1,2})[/-](\d{1,2})#', trim((string) $value), $m)) {
            return null;
        }

        return (int) gmmktime(0, 0, 0, (int) $m[2], (int) $m[3], (int) $m[1]);
    }

    public static function todayUtc(): ?int
    {
        return self::parseYmd(self::todayStr());
    }

    /** Whole days from $a to $b; 0 when either side is unparseable. */
    public static function dayDelta(mixed $a, mixed $b): int
    {
        $ta = self::parseYmd($a);
        $tb = self::parseYmd($b);

        return ($ta === null || $tb === null) ? 0 : (int) round(($tb - $ta) / 86400);
    }

    /** Days from today to $value, or null when there is no usable date. */
    public static function dayDiff(mixed $value): ?int
    {
        $d = self::parseYmd($value);
        $today = self::todayUtc();
        if ($d === null || $today === null) {
            return null;
        }

        return (int) round(($d - $today) / 86400);
    }

    public static function within7(mixed $value): bool
    {
        $diff = self::dayDiff($value);

        return $diff !== null && $diff >= 0 && $diff <= 7;
    }

    public static function shiftDate(mixed $dateStr, int $days): string
    {
        $t = self::parseYmd($dateStr);
        if ($t === null) {
            return (string) ($dateStr ?? '');
        }

        return gmdate('Y-m-d', $t + $days * 86400);
    }

    public static function dayNameOf(mixed $dateStr): string
    {
        $t = self::parseYmd($dateStr);

        return $t === null ? '' : self::DAY_NAMES[(int) gmdate('w', $t)];
    }

    /** Nights between two dates; blank when the range is empty or invalid. */
    public static function calcNights(mixed $checkIn, mixed $checkOut): string
    {
        $a = self::parseYmd($checkIn);
        $b = self::parseYmd($checkOut);
        if ($a === null || $b === null) {
            return '';
        }
        $n = (int) round(($b - $a) / 86400);

        return $n > 0 ? (string) $n : '';
    }

    /** A trip cannot be completed before it has happened. */
    public static function isFuture(mixed $dateStr): bool
    {
        $d = self::parseYmd($dateStr);
        $today = self::todayUtc();

        return $d !== null && $today !== null && $d > $today;
    }
}
