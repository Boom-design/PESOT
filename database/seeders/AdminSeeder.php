<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

            User::create([
                'name'     => $name,
                'email'    => $email,
                // Meets PasswordPolicy: 8+ characters, mixed case, a digit and
                // a symbol. The old 'admin123' did not, so the account it
                // created could not have been set through any of the app's own
                // forms.
                'password' => Hash::make('Admin123#'),
                'role'     => 'admin',
                'status'   => 'approved',
                'phone'    => null,
            ]);
        }
    }
}
