<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the employer_nsrp_registrations table primary key from the generic
 * "id" to the descriptive "employer_nsrp_registrations_id".
 *
 * The constraint on job_qualifications.company_id still carries its pre-rename
 * name "jobs_company_id_foreign", so it is dropped by explicit name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign('announcements_employer_id_foreign');
        });
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropForeign('employer_requirements_user_id_foreign');
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->dropForeign('inhouse_schedules_employer_id_foreign');
        });
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->dropForeign('job_fair_employment_requests_employer_id_foreign');
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dropForeign('job_fair_participants_employer_id_foreign');
        });
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropForeign('jobs_company_id_foreign');
        });

        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->renameColumn('id', 'employer_nsrp_registrations_id');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->foreign('employer_id')->references('employer_nsrp_registrations_id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->foreign('user_id')->references('employer_nsrp_registrations_id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->foreign('employer_id')->references('employer_nsrp_registrations_id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->foreign('employer_id')->references('employer_nsrp_registrations_id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->foreign('employer_id')->references('employer_nsrp_registrations_id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->foreign('company_id')->references('employer_nsrp_registrations_id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign('announcements_employer_id_foreign');
        });
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropForeign('employer_requirements_user_id_foreign');
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->dropForeign('inhouse_schedules_employer_id_foreign');
        });
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->dropForeign('job_fair_employment_requests_employer_id_foreign');
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dropForeign('job_fair_participants_employer_id_foreign');
        });
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropForeign('job_qualifications_company_id_foreign');
        });

        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->renameColumn('employer_nsrp_registrations_id', 'id');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->foreign('employer_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->foreign('employer_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->foreign('employer_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->foreign('employer_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
    }
};
