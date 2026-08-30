<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            // Which job fair the employer is ASKING to join.
            //
            // Not the same thing as being in it. A job_fair_employment_requests
            // row means the posting is on the fair, and every report reads it
            // that way; it is written when the office accepts the posting, not
            // when the employer offers it. This column holds the offer in the
            // meantime, and is null for an employer who posted before any fair
            // existed to name.
            $table->unsignedBigInteger('requested_job_fair_id')->nullable()->after('schedule_type');
        });
    }

    public function down(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropColumn('requested_job_fair_id');
        });
    }
};
