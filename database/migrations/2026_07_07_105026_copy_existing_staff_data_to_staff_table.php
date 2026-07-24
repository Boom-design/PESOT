<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $staffUsers = DB::table('users')->where('role', 'staff')->get();

        foreach ($staffUsers as $user) {
            DB::table('staff')->insert([
                'user_id'     => $user->id,
                'staff_role'  => $user->staff_role,
                'first_name'  => $user->first_name,
                'last_name'   => $user->last_name,
                'middle_name' => $user->middle_name,
                'phone'       => $user->phone,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('staff')->truncate();
    }
};