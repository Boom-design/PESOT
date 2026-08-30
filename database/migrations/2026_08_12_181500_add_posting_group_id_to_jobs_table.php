<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An employer may now tick more than one schedule type on a single job
     * posting request. Each ticked channel still becomes its own row, because
     * every channel has its own date, venue, approver and applicant list.
     *
     * What the channels must NOT have is their own vacancy count. "Foreman,
     * 3 slots" posted to company interview + in-house + job fair is three ways of
     * filling the same three seats, not nine seats. posting_group_id ties the
     * sibling rows together so the hired count — and therefore the slots-full
     * rule in Job::scopeActive() — is measured across the whole group.
     *
     * It points at the first row of the group (the first row points at itself),
     * so the group key is coalesce(posting_group_id, job_qualifications_id) and
     * rows created before this migration keep working with no backfill.
     */
    public function up(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->unsignedBigInteger('posting_group_id')->nullable()->after('job_qualifications_id');

            // nullOnDelete, not cascade: losing the first row of a group must
            // not delete the other channels of the same position — they simply
            // become standalone postings again.
            $table->foreign('posting_group_id')
                ->references('job_qualifications_id')
                ->on('job_qualifications')
                ->nullOnDelete();

            $table->index('posting_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropForeign(['posting_group_id']);
            $table->dropIndex(['posting_group_id']);
            $table->dropColumn('posting_group_id');
        });
    }
};
