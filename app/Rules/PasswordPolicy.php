<?php

namespace App\Rules;

use Illuminate\Validation\Rules\Password;

/**
 * One definition of what counts as an acceptable password.
 *
 * Every place that sets or changes a password pulls the rule from here, so the
 * requirement cannot drift between the registration form, the admin "add staff"
 * form and the four change-password screens.
 *
 * Existing accounts are untouched — this only runs when a password is being
 * written, never when one is merely checked at login.
 */
class PasswordPolicy
{
    /**
     * Minimum length, mixed case, at least one digit, at least one symbol.
     */
    public static function rule(): Password
    {
        return Password::min(8)->mixedCase()->numbers()->symbols();
    }

    /**
     * Rule set for a required password field paired with a *_confirmation input.
     */
    public static function required(): array
    {
        return ['required', 'string', 'confirmed', self::rule()];
    }

    /**
     * For forms that have no *_confirmation input — the admin "add staff" form
     * sets someone else's password and relies on its show/hide toggle instead.
     */
    public static function requiredNoConfirm(): array
    {
        return ['required', 'string', self::rule()];
    }

    /**
     * Same, but for forms where leaving the field blank means "keep the current
     * password" — the admin's edit-user form works this way.
     */
    public static function optional(): array
    {
        return ['nullable', 'string', self::rule()];
    }

    /**
     * Shown next to the input so the requirement is visible before submitting,
     * not only after a validation error.
     */
    public static function hint(): string
    {
        return 'Must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character.';
    }
}
