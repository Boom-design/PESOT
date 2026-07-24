<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $companies = DB::table('users')->where('role', 'company')->get();

        foreach ($companies as $user) {
            DB::table('employer_nsrp_registrations')
                ->where('user_id', $user->id)
                ->update([
                    'company_name'   => $user->company_name,
                    'contact_person' => $user->contact_person,
                    'position_title' => $user->position_title,
                    'mobile_number'  => $user->mobile_number,
                ]);
        }
    }

    public function down(): void {}
};