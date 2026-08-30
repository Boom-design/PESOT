<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@peso.gov.ph'],
            [
                'name'     => 'PESO Admin',
                // Meets PasswordPolicy: 8+ characters, mixed case, a digit and a
                // symbol. The old 'admin123' did not, so the account it created
                // could not have been set through any of the app's own forms.
                'password' => Hash::make('Admin123#'),
                'role'     => 'admin',
                'status'   => 'approved',
                'phone'    => null,
            ]
        );
    }
}