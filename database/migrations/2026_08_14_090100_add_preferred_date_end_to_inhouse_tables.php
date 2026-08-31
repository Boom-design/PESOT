<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An in-house request becomes a window, not a single day.
     *
     * The employer says "we are free 13–17 August"; LRA/SRA then confirm one
     * actual day inside that window. `preferred_date` stays as the first day of
     * the window so nothing that already reads it breaks, and the new column
     * holds the last day.
     *
     * Rows written before this migration are single-day requests: their end is
     * backfilled to equal their start, which is exactly what they meant.
     */
    public function up(): void
    {
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->date('preferred_date_end')->nullable()->after('preferred_date');
        });

        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->date('preferred_date_end')->nullable()->after('preferred_date');
            // The one day LRA/SRA picked inside the window. Null until the
            // posting is approved; `inhouse_schedules` already had its own
            // `confirmed_date` for the schedule-only path.
            $table->date('confirmed_date')->nullable()->after('preferred_date_end');
        });

        DB::table('inhouse_schedules')->whereNotNull('preferred_date')
            ->update(['preferred_date_end' => DB::raw('preferred_date')]);

        DB::table('job_qualifications')->whereNotNull('preferred_date')
            ->where('schedule_type', 'inhouse')
            ->update(['preferred_date_end' => DB::raw('preferred_date')]);
    }

    public function down(): void
    {
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->dropColumn('preferred_date_end');
        });

        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropColumn(['preferred_date_end', 'confirmed_date']);
        });
    }
};
