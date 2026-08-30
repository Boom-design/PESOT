<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks an employer that has stopped giving vacancies.
 *
 * PESO, 2026-08-24: a company that closes down simply stops posting. Nobody
 * tells the office, because logging in to say so is a waste of the employer's
 * time — so the office is left holding accounts that will never post again and
 * cannot tell them apart from a company that is merely between hires.
 *
 * The sweep: a month with no new vacancy sends an email asking for their
 * status; a week of silence after that turns the account dormant. A dormant
 * employer can still log in — they have to, to give their reason — and staff
 * switch the account back on once they have read it.
 *
 * The columns sit on the employer record rather than in a history table: the
 * office only ever acts on the current state, and clearing them is what
 * "switched back on" means. `inactivity_notified_at` doubles as the
 * already-warned flag, the same way `*_expiry_notified_at` does for the
 * requirement warnings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->timestamp('inactivity_notified_at')->nullable()->after('is_walk_in');
            $table->timestamp('inactivity_responded_at')->nullable()->after('inactivity_notified_at');
            // still_hiring | paused | closed — kung unsa ang gipili sa employer
            $table->string('inactivity_status', 20)->nullable()->after('inactivity_responded_at');
            $table->text('inactivity_response')->nullable()->after('inactivity_status');
            $table->timestamp('dormant_at')->nullable()->after('inactivity_response');
        });

        Schema::table('job_qualifications', function (Blueprint $table) {
            // Ang posting nga gisirado sa sweep, dili sa employer ug dili sa
            // deadline. Kini ra ang buksan pag-balik pag-enable sa account —
            // ang posting nga gisirado sa employer mismo magpabiling sirado.
            $table->timestamp('dormant_closed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'inactivity_notified_at',
                'inactivity_responded_at',
                'inactivity_status',
                'inactivity_response',
                'dormant_at',
            ]);
        });

        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropColumn('dormant_closed_at');
        });
    }
};
