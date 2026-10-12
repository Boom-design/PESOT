<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * One definition of an acceptable Philippine TIN.
 *
 * The field was `required|string|max:50`, which accepted "1", "not a number at
 * all" and twenty-two nines. The TIN is what PESO and DOLE use to tell one
 * establishment from another in the PEIS, so a field that takes anything is a
 * field that can quietly hold the wrong establishment.
 *
 * Digits only, and exactly nine of them — or exactly twelve when a branch code
 * follows. Nothing else: no dashes, no spaces, no letters. PESO, 2026-10-13
 * asked for it to behave like the mobile number field beside it, which is
 * capped at eleven digits and takes nothing but digits, rather than being a
 * free-text box with a note above it.
 *
 * Nothing between nine and twelve is accepted. A ten-digit TIN is a typed-wrong
 * nine, and saying so while the person is still looking at the card is the only
 * time it can be fixed.
 */
class TaxIdentificationNumber implements ValidationRule
{
    /** Digits in the taxpayer's own number. */
    public const BASE_DIGITS = 9;

    /** Digits in a TIN that carries a branch code. */
    public const BRANCH_DIGITS = 12;

    /** Both accepted lengths, for the maxlength and pattern on the inputs. */
    public const LENGTHS = [self::BASE_DIGITS, self::BRANCH_DIGITS];

    public static function hint(): string
    {
        return 'Enter the TIN as digits only — 9 digits (e.g. 123456789), or 12 '
               . 'if it carries a branch code (e.g. 123456789000). No dashes or spaces.';
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return; // `required` / `nullable` decides this, not the format rule.
        }

        if (!is_string($value) || !preg_match('/^[0-9]+$/', $value)) {
            $fail(self::hint());
            return;
        }

        if (!in_array(strlen($value), self::LENGTHS, true)) {
            $fail(self::hint());
        }
    }
}
