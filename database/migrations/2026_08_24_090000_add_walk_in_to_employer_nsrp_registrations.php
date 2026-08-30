<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks an employer that Job Vacancy staff encoded at the counter.
 *
 * PESO, 2026-08-24: a local employer who walks in used to be sent home to
 * register online. Staff now register the company, attach its documents and
 * post its vacancy in one sitting.
 *
 * Unlike the jobseeker walk-in, the employer still gets a real account — the
 * office wants the company to show up in the Approved Employers tab straight
 * away, and that tab is built on `users` rows. So this is only a marker: it
 * says who did the typing, nothing more. The staff-issued password is
 * temporary and `users.must_change_password` forces the employer to replace it
 * the first time they log in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->boolean('is_walk_in')->default(false)->after('is_overseas');
        });
    }

    public function down(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->dropColumn('is_walk_in');
        });
    }
};
