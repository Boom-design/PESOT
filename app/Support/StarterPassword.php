<?php

namespace App\Support;

/**
 * The password an office desk hands to somebody else.
 *
 * PESO, 2026-08-28: the admin used to have to invent — or read out — a
 * twelve-character string with an uppercase letter, a digit and a symbol in it,
 * for an account the person is forced to re-secure the moment they sign in
 * anyway. Over the phone that is slow and error-prone, and during the demo it
 * is pure friction. The starter password is now the person's first name in
 * small letters: easy to say, easy to type, and impossible to mishear.
 *
 * This is safe only because of what happens next. Every caller here also sets
 * `users.must_change_password`, and `EnsurePasswordChanged` will not let the
 * account reach any page until a real password replaces this one — and that
 * replacement is held to the full `PasswordPolicy`: eight characters, mixed
 * case, a number and a symbol.
 *
 * Never use this for a password the account gets to keep.
 */
class StarterPassword
{
    /**
     * First word of the name, lowercase, letters only.
     *
     * Anything that is not an ASCII letter is dropped, so accents and hyphens
     * do not turn into a password nobody can retype over the phone ("José"
     * becomes "jos"). A name that leaves nothing behind falls back to "peso"
     * rather than to an empty string.
     */
    public static function fromName(?string $name): string
    {
        $words = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
        $first = strtolower($words[0] ?? '');
        $first = preg_replace('/[^a-z]/', '', $first);

        return $first !== '' ? $first : 'peso';
    }
}
