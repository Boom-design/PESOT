<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_recovers_missing_admin_account(): void
    {
        User::where('email', 'admin@peso.gov.ph')->delete();

        $response = $this->post('/login', [
            'email' => 'admin@peso.gov.ph',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/admin/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'admin@peso.gov.ph',
            'role' => 'admin',
            'status' => 'approved',
        ]);
    }
}
