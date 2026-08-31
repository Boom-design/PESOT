<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The day the applicant was actually hired.
 *
 * Until now the hire date was read off updated_at, which is the day the row was
 * last touched by anything at all — a later note, a correction, a status moved
 * and moved back. The jobseeker's history says "Employed since ..." from it,
 * and the office wants to count hires per month from it. Neither can rest on a
 * column that moves for unrelated reasons.
 *
 * Backfilled from updated_at for rows already marked hired: it is the best
 * record that exists for them, and leaving them blank would read as "never
 * hired" rather than "hired, date unknown".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->timestamp('hired_at')->nullable()->after('status');
        });

        DB::table('job_matching')
            ->where('status', 'hired')
            ->update(['hired_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->dropColumn('hired_at');
        });
    }
};
