<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Local and overseas employers are now counted separately, so an event can
     * ask for e.g. 20 local and 10 overseas rather than 30 of any type.
     *
     * `employer_capacity` stays as the total so existing reads keep working.
     */
    public function up(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->unsignedInteger('local_capacity')->nullable()->after('employer_capacity');
            $table->unsignedInteger('overseas_capacity')->nullable()->after('local_capacity');
        });

        // Existing events only ever had one combined number, and their invites
        // were not split by type — put the whole figure on local so the totals
        // still add up rather than inventing an overseas quota.
        DB::table('job_fair_events')
            ->whereNotNull('employer_capacity')
            ->update([
                'local_capacity'    => DB::raw('employer_capacity'),
                'overseas_capacity' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropColumn(['local_capacity', 'overseas_capacity']);
        });
    }
};
