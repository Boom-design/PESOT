<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the jobseeker_registrations table primary key from the generic "id"
 * to the descriptive "jobseeker_registrations_id".
 *
 * The constraint on job_matching.jobseeker_id still carries its pre-rename name
 * "applications_jobseeker_id_foreign", so it is dropped by explicit name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign('announcements_jobseeker_id_foreign');
        });
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->dropForeign('inhouse_participants_jobseeker_id_foreign');
        });
        Schema::table('jobseeker_nsrp_registrations', function (Blueprint $table) {
            $table->dropForeign('jobseeker_nsrp_registrations_jobseeker_registration_id_foreign');
        });
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->dropForeign('job_fair_registrations_user_id_foreign');
        });
        Schema::table('job_matching', function (Blueprint $table) {
            $table->dropForeign('applications_jobseeker_id_foreign');
        });

        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->renameColumn('id', 'jobseeker_registrations_id');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('jobseeker_registrations_id')->on('jobseeker_registrations')->onDelete('cascade');
        });
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('jobseeker_registrations_id')->on('jobseeker_registrations')->onDelete('cascade');
        });
        Schema::table('jobseeker_nsrp_registrations', function (Blueprint $table) {
            $table->foreign('jobseeker_registration_id', 'jobseeker_nsrp_registrations_jobseeker_registration_id_foreign')
                ->references('jobseeker_registrations_id')->on('jobseeker_registrations')->onDelete('cascade');
        });
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->foreign('user_id')->references('jobseeker_registrations_id')->on('jobseeker_registrations')->onDelete('cascade');
        });
        Schema::table('job_matching', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('jobseeker_registrations_id')->on('jobseeker_registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign('announcements_jobseeker_id_foreign');
        });
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->dropForeign('inhouse_participants_jobseeker_id_foreign');
        });
        Schema::table('jobseeker_nsrp_registrations', function (Blueprint $table) {
            $table->dropForeign('jobseeker_nsrp_registrations_jobseeker_registration_id_foreign');
        });
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->dropForeign('job_fair_registrations_user_id_foreign');
        });
        Schema::table('job_matching', function (Blueprint $table) {
            $table->dropForeign('job_matching_jobseeker_id_foreign');
        });

        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->renameColumn('jobseeker_registrations_id', 'id');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('id')->on('jobseeker_registrations')->onDelete('cascade');
        });
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('id')->on('jobseeker_registrations')->onDelete('cascade');
        });
        Schema::table('jobseeker_nsrp_registrations', function (Blueprint $table) {
            $table->foreign('jobseeker_registration_id', 'jobseeker_nsrp_registrations_jobseeker_registration_id_foreign')
                ->references('id')->on('jobseeker_registrations')->onDelete('cascade');
        });
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('jobseeker_registrations')->onDelete('cascade');
        });
        Schema::table('job_matching', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('id')->on('jobseeker_registrations')->onDelete('cascade');
        });
    }
};
