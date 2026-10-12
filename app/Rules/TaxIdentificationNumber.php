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
 * BIR issues nine digits to a taxpayer. A branch adds a branch code after
 * them — three digits on the older cards and five on the newer ones, so both
 * are accepted. Nothing in between: a ten-digit TIN is a typed-wrong nine, and
 * saying so while the person is still looking at the card is the only time it
 * can be fixed.
 *
 * Separators are the paper's business, not ours. Dashes and spaces are ignored
 * when counting, and whatever the employer typed is what gets stored — the card
 * reads 123-456-789-000 and so should the record.
 */
class TaxIdentificationNumber implements ValidationRule
{
    /** Digits in the taxpayer's own number. */
    public const BASE_DIGITS = 9;

    /** Digits a branch code may add. Three on older cards, five on newer. */
    public const BRANCH_DIGITS = [3, 5];

    public static function hint(): string
    {
        return 'Enter the 9-digit TIN (e.g. 123-456-789). A branch adds its branch '
               . 'code after it — 3 or 5 more digits, as printed on the card '
               . '(e.g. 123-456-789-000).';
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return; // `required` / `nullable` decides this, not the format rule.
        }

        if (!is_string($value)) {
            $fail(self::hint());
            return;
        }

        // Only dashes and spaces may separate the groups. Letters and other
        // punctuation are not a TIN written differently; they are not a TIN.
        if (preg_match('/[^0-9\s-]/', $value)) {
            $fail(self::hint());
            return;
        }

        $digits = strlen(preg_replace('/\D/', '', $value));

        $allowed = array_merge(
            [self::BASE_DIGITS],
            array_map(fn(int $branch) => self::BASE_DIGITS + $branch, self::BRANCH_DIGITS)
        );

        if (!in_array($digits, $allowed, true)) {
            $fail(self::hint());
        }
    }
}
