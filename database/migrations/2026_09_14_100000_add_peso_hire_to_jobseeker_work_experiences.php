<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A work experience row the system writes itself when an employer marks a
 * jobseeker hired.
 *
 * PESO CDO client, 2026-09-14: the hire belongs on the jobseeker's NSRP form
 * under Work Experience, and while they hold that job they do not apply again.
 * job_matching_id marks the row as PESO's and ties it to the hire, so undoing
 * the hire takes the row back off. status_before keeps the employment status
 * the form had before the hire, so an undone hire restores it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->unsignedBigInteger('job_matching_id')->nullable()->after('jobseeker_nsrp_registration_id')->index();
            $table->json('status_before')->nullable()->after('employment_status');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->dropIndex(['job_matching_id']);
            $table->dropColumn(['job_matching_id', 'status_before']);
        });
    }
};
