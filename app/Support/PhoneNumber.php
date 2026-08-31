<?php

namespace App\Support;

/**
 * Turns whatever a user typed into the format PhilSMS expects.
 *
 * Numbers are read straight out of the account each person registered with, so
 * they arrive in whatever shape the form allowed: 09171234567, +63 917 123 4567,
 * 0917-123-4567, or a landline that was entered in the wrong box.
 */
class PhoneNumber
{
    /**
     * Normalise a Philippine mobile number to PhilSMS form: 639XXXXXXXXX.
     *
     * Returns null for anything that is not recognisably a mobile number —
     * landlines, short numbers, junk. Guessing is not an option here: a wrong
     * number costs the office a message and delivers it to a stranger.
     */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '' || $digits === null) {
            return null;
        }

        // 09171234567 — the format every form in the system asks for.
        if (strlen($digits) === 11 && str_starts_with($digits, '09')) {
            return '63' . substr($digits, 1);
        }

        // 9171234567 — typed without the leading zero.
        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            return '63' . $digits;
        }

        // 639171234567 — already carries the country code, with or without '+'.
        if (strlen($digits) === 12 && str_starts_with($digits, '639')) {
            return $digits;
        }

        // 0063917... — international prefix instead of '+'.
        if (strlen($digits) === 14 && str_starts_with($digits, '00639')) {
            return substr($digits, 2);
        }

        return null;
    }

    /**
     * Whether a stored value can actually receive an SMS.
     */
    public static function isMobile(?string $raw): bool
    {
        return self::normalize($raw) !== null;
    }

    /**
     * Display form for the UI — 0917 123 4567 — so staff can eyeball a list
     * without mentally converting country codes.
     */
    public static function forDisplay(?string $raw): ?string
    {
        $normalized = self::normalize($raw);

        if ($normalized === null) {
            return null;
        }

        $local = '0' . substr($normalized, 2);

        return substr($local, 0, 4) . ' ' . substr($local, 4, 3) . ' ' . substr($local, 7);
    }
}
