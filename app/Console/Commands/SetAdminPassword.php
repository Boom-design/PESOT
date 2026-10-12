<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Rules\PasswordPolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Sets the administrator password from the console.
 *
 * The login screen no longer resets the admin password to a known value, so
 * this is the supported way to set or recover it. Running it in a terminal
 * means the password is never sent over HTTP or stored in the codebase.
 */
class SetAdminPassword extends Command
{
    protected $signature = 'peso:admin-password
                            {--email=admin@peso.gov.ph : Which admin account to update}';

    protected $description = 'Set the PESO administrator password (prompts securely, never echoes input)';

    public function handle(): int
    {
        $email = $this->option('email');
        $admin = User::where('email', $email)->first();

        if (!$admin) {
            $this->error("No account found for {$email}.");
            return self::FAILURE;
        }

        if ($admin->role !== 'admin') {
            $this->error("{$email} is not an admin account (role: {$admin->role}).");
            return self::FAILURE;
        }

        $password = $this->secret('New password');
        $confirm  = $this->secret('Confirm password');

        if ($password !== $confirm) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        // Same policy the web forms enforce, so the console cannot be used as a
        // way around the requirement.
        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', PasswordPolicy::rule()]]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }
            return self::FAILURE;
        }

        $admin->password = Hash::make($password);
        $admin->status   = 'approved';
        $admin->save();

        $this->info("Password updated for {$email}.");

        return self::SUCCESS;
    }
}
