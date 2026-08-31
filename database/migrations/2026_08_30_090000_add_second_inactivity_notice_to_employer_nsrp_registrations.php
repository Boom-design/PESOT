<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The inactivity sweep grows a second notice and a hand-off to staff.
 *
 * PESO, 2026-08-30. The office does not disable an employer the moment a
 * deadline passes; it asks twice, waits a week, and then a person decides. The
 * sweep only ever asked once and then disabled the account by itself, which is
 * both harsher and less informative than what the desk actually does.
 *
 * The rule it now follows:
 *
 *   month 1  the employer is emailed  -> inactivity_notified_at
 *   month 2  the employer is emailed again, and the desk that owns the account
 *            is told                  -> inactivity_second_notified_at
 *   +1 week  the desk is told the grace is over and the account is theirs to
 *            switch off               -> inactivity_disable_prompted_at
 *
 * Each column is the "already said this" mark for its own step. Without them
 * the same reminder would go out every morning until the employer answered.
 * Nothing is disabled by the system any more — the last step is a person.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->timestamp('inactivity_second_notified_at')
                ->nullable()
                ->after('inactivity_notified_at');

            $table->timestamp('inactivity_disable_prompted_at')
                ->nullable()
                ->after('inactivity_second_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'inactivity_second_notified_at',
                'inactivity_disable_prompted_at',
            ]);
        });
    }
};
