<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the job_qualifications table primary key from the generic "id" to
 * the descriptive "job_qualifications_id" (table name + _id).
 *
 * The inbound constraint from job_matching still carries its pre-rename name
 * "applications_job_id_foreign", so it is dropped by explicit name rather than
 * by column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->dropForeign('applications_job_id_foreign');
        });
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->dropForeign('job_fair_employment_requests_job_id_foreign');
        });

        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->renameColumn('id', 'job_qualifications_id');
        });

        Schema::table('job_matching', function (Blueprint $table) {
            $table->foreign('job_id')->references('job_qualifications_id')->on('job_qualifications')->onDelete('cascade');
        });
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->foreign('job_id')->references('job_qualifications_id')->on('job_qualifications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->dropForeign('job_matching_job_id_foreign');
        });
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->dropForeign('job_fair_employment_requests_job_id_foreign');
        });

        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->renameColumn('job_qualifications_id', 'id');
        });

        Schema::table('job_matching', function (Blueprint $table) {
            $table->foreign('job_id')->references('id')->on('job_qualifications')->onDelete('cascade');
        });
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->foreign('job_id')->references('id')->on('job_qualifications')->onDelete('cascade');
        });
    }
};
