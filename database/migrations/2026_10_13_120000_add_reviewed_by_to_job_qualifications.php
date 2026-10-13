<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who approved or rejected a posting.
 *
 * Every other decision in the office already said who made it —
 * inhouse_schedules.reviewed_by, employer_requirements.reviewed_by,
 * job_fair_participants.sra_decided_by. A posting was the one that did not: it
 * moved from pending to approved and the record said only that it had.
 *
 * PESO, 2026-10-13: a desk can be two people. Two SRAs, two LRAs — the admin's
 * Add Staff form has counted staff per role since 2026-08-26 for exactly that
 * reason. With two on one seat, "approved" names the desk and neither of them
 * can tell whether the other has already answered it.
 *
 * Nullable, and nothing is backfilled: the postings decided before this column
 * existed have no honest answer, and inventing one is worse than the blank the
 * screens read as "not recorded".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('posting_status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');

            // Set null, not cascade: a staff member leaving must not erase the
            // record of what they decided, and must not take the posting with
            // them either.
            $table->foreign('reviewed_by')
                  ->references('staff_id')->on('staff')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at']);
        });
    }
};
