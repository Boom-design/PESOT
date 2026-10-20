<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A jobseeker can pull out of a job fair, and the row stays.
 *
 * PESO, 2026-10-20: something comes up and the person cannot go. Until now
 * the only honest thing they could do was not turn up, and the desk counted
 * them as expected all the same.
 *
 * Deleting the row was not an option. The slip number is handed out as
 * count() + 1, so a hole in the table gives the next person a slip somebody
 * else is already carrying. The row stays and the slip stays with it; this
 * column is what takes it out of the lists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('attendance_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->dropColumn('cancelled_at');
        });
    }
};
