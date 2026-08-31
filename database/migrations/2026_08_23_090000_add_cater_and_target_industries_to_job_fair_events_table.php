<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A job fair no longer has a maximum, and its invitation is aimed at an
 * industry.
 *
 * PESO Job Fair staff, interviewed 2026-08-23:
 *
 *   1. "Walay maximum sa job fair event kay depende na sa sponsor sa job fair."
 *      How many employers fit is the sponsor's call, not PESO's. The headcount
 *      the staff typed in was being enforced as a hard limit — an employer who
 *      confirmed one seat too late was refused. It stays as a target the staff
 *      can look at, and stops being a wall.
 *
 *   2. The invitation goes to the employers the fair is actually looking for,
 *      not to everybody who happens to be approved.
 *
 * Two new columns:
 *
 *   `cater`             which employer types this fair wants: local, overseas,
 *                       or both. Until now this was inferred from
 *                       `local_capacity > 0`, which only worked because the
 *                       number was mandatory. Once the number is optional a
 *                       fair that wants overseas employers but has no target
 *                       for them would read as not wanting them at all, so the
 *                       answer is stored instead of guessed.
 *
 *   `target_industries` the PSIC industry groups the fair is looking for.
 *                       NULL means all of them, which is exactly how every
 *                       event behaved before today.
 *
 * The three capacity columns become nullable but are kept and still written —
 * nothing that reads them breaks. No row is dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->json('cater')->nullable()->after('venue');
            $table->json('target_industries')->nullable()->after('cater');
        });

        // Carry every existing event's employer types across before the number
        // they were inferred from stops being reliable.
        foreach (DB::table('job_fair_events')->get() as $event) {
            $cater = [];
            if ((int) $event->local_capacity    > 0) $cater[] = 'local';
            if ((int) $event->overseas_capacity > 0) $cater[] = 'overseas';

            // An older event with no capacity at all was open to everyone.
            if (!$cater) $cater = ['local', 'overseas'];

            DB::table('job_fair_events')
                ->where('job_fair_events_id', $event->job_fair_events_id)
                ->update(['cater' => json_encode($cater)]);
        }

        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->unsignedInteger('employer_capacity')->nullable()->change();
            $table->unsignedInteger('local_capacity')->nullable()->change();
            $table->unsignedInteger('overseas_capacity')->nullable()->change();
        });
    }

    public function down(): void
    {
        // A NOT NULL column cannot take the nulls this migration allowed, so
        // they are filled with 0 first — the same value the columns held for a
        // type that was not being catered to.
        DB::table('job_fair_events')->whereNull('employer_capacity')->update(['employer_capacity' => 0]);
        DB::table('job_fair_events')->whereNull('local_capacity')->update(['local_capacity' => 0]);
        DB::table('job_fair_events')->whereNull('overseas_capacity')->update(['overseas_capacity' => 0]);

        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->unsignedInteger('employer_capacity')->nullable(false)->change();
            $table->unsignedInteger('local_capacity')->nullable(false)->change();
            $table->unsignedInteger('overseas_capacity')->nullable(false)->change();
            $table->dropColumn(['cater', 'target_industries']);
        });
    }
};
