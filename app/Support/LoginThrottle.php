<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Brute-force guard for the login forms.
 *
 * Every login entry point routes through here so the limit is identical across
 * the unified web login, the company login and the API login.
 *
 * Backed by the cache store (CACHE_STORE=database), so the counter survives
 * across requests and is shared by all workers.
 */
class LoginThrottle
{
    /** Failed attempts allowed before the account/IP pair is locked. */
    public const MAX_ATTEMPTS = 3;

    /** How long the lock lasts, in seconds. */
    public const LOCK_SECONDS = 60;

    /**
     * Keyed on email *and* IP on purpose. Keying on the email alone would let
     * anyone lock a known account out simply by failing logins against it.
     */
    public static function key(Request $request): string
    {
        return 'login:' . Str::lower((string) $request->input('email')) . '|' . $request->ip();
    }

    public static function tooManyAttempts(Request $request): bool
    {
        return RateLimiter::tooManyAttempts(self::key($request), self::MAX_ATTEMPTS);
    }

    public static function secondsRemaining(Request $request): int
    {
        return RateLimiter::availableIn(self::key($request));
    }

    /** Call on every rejected login, whatever the reason. */
    public static function recordFailure(Request $request): void
    {
        RateLimiter::hit(self::key($request), self::LOCK_SECONDS);
    }

    /** Call on a successful login so the counter does not linger. */
    public static function clear(Request $request): void
    {
        RateLimiter::clear(self::key($request));
    }

    public static function message(int $seconds): string
    {
        return 'Too many failed login attempts. Please try again in '
            . $seconds . ' second' . ($seconds === 1 ? '' : 's') . '.';
    }
}
