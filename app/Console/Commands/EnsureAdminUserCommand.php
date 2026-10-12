<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureAdminUserCommand extends Command
{
    protected $signature = 'app:ensure-admin-user';

    protected $description = 'Create or repair the default admin account.';

    public function handle()
    {
        $admin = User::where('email', 'admin@peso.gov.ph')->first();

        $payload = [
            'name' => 'PESO Admin',
            'email' => 'admin@peso.gov.ph',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'approved',
            'phone' => null,
        ];

        if ($admin) {
            $admin->fill($payload);
            $admin->save();
        } else {
            User::create($payload);
        }

        $this->info('Admin account ensured: admin@peso.gov.ph / admin123');

        return self::SUCCESS;
    }
}
