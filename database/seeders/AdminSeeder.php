<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * The three administrators the PESO office actually has.
     *
     * PESO, 2026-10-14: there are three of them and they had been sharing one
     * login. Everything any of them did was recorded as the work of "PESO
     * Admin", so a staff account switched off on Tuesday could not be traced
     * to the person who did it — not to blame anyone, but because the other
     * two need to know it happened and who to ask about it.
     *
     * One account each, and an Activity Logs page they share instead.
     *
     * The first keeps the original address so nothing that already uses it
     * breaks — it is the account the system was set up with.
     *
     * updateOrCreate on the email, so re-running this never overwrites a
     * password an administrator has since set for themselves. The starter
     * password only ever applies to an account that does not exist yet.
     */
    private const ADMINS = [
        ['admin@peso.gov.ph',   'PESO Admin I'],
        ['admin2@peso.gov.ph',  'PESO Admin II'],
        ['admin3@peso.gov.ph',  'PESO Admin III'],
    ];

    public function run(): void
    {
        foreach (self::ADMINS as [$email, $name]) {
            $existing = User::where('email', $email)->first();

            if ($existing) {
                // Only the role and status are enforced on an account that is
                // already there. The name is theirs to change — the Activity
                // Logs page is written in it — and so is the password.
                $existing->update(['role' => 'admin', 'status' => 'approved']);
                continue;
            }

            $password = $this->starterPassword();

            User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($password),
                // Whatever the starter is, it is a starter. EnsurePasswordChanged
                // holds the account on the change screen until a real password
                // replaces it under the full PasswordPolicy — so the value below
                // is worth nothing to anyone who reads it later.
                'must_change_password' => true,
                'role'     => 'admin',
                'status'   => 'approved',
                'phone'    => null,
            ]);

            $this->command?->warn(sprintf(
                'Created %s — starter password: %s   (must be changed at first sign-in)',
                $email, $password
            ));
        }
    }

    /**
     * The password a brand-new administrator account starts on.
     *
     * ── NOT A LITERAL IN THIS FILE ──
     *
     * It used to be a literal, written here, in a repository anyone can
     * read. The live site was set up by running this seeder, so that string
     * was the administrator password of a public website until somebody
     * thought to change it — and nothing in the system said whether anybody
     * had. It is not repeated here; naming it in the comment that removes it
     * would leave it exactly as published as it was before.
     *
     * ADMIN_STARTER_PASSWORD from the environment when it is set, so a
     * deployment can choose. Otherwise a fresh random one, printed once to
     * whoever ran the seeder and stored nowhere. Either way the account is
     * created with must_change_password, so the starter survives exactly one
     * sign-in.
     *
     * An existing account is never touched by any of this; see run().
     */
    private function starterPassword(): string
    {
        $fromEnv = trim((string) env('ADMIN_STARTER_PASSWORD', ''));

        if ($fromEnv !== '') {
            return $fromEnv;
        }

        // Mixed case, digits and symbols, so it passes PasswordPolicy on the
        // way in and cannot be guessed on the way out.
        return Str::password(16, letters: true, numbers: true, symbols: true);
    }
}
