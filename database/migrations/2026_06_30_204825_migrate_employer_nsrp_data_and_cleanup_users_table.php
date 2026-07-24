<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Copy existing data — company users ra
        $companies = DB::table('users')->where('role', 'company')->get();

        foreach ($companies as $user) {
            DB::table('employer_nsrp_registrations')->insert([
                'user_id'       => $user->id,
                'employer_type' => $user->employer_type,
                'is_overseas'   => $user->is_overseas,
                'created_at'    => $user->created_at,
                'updated_at'    => $user->updated_at,
            ]);
        }

        // Drop columns sa users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employer_type', 'is_overseas']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employer_type')->nullable();
            $table->boolean('is_overseas')->default(false);
        });
    }
};