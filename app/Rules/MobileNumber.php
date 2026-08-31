<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * One definition of an acceptable Philippine mobile number.
 *
 * Job fair text messages are sent to whatever is stored in these fields, so a
 * number that does not match this shape means the person silently never gets
 * one. Landlines belong in the telephone field, not here.
 */
class MobileNumber implements ValidationRule
{
    public const PATTERN = '/^09[0-9]{9}$/';

    public static function hint(): string
    {
        return 'Enter an 11-digit mobile number starting with 09 (e.g. 09171234567).';
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return; // `required` / `nullable` decides this, not the format rule.
        }

        if (!is_string($value) || !preg_match(self::PATTERN, $value)) {
            $fail(self::hint());
        }
    }
}
