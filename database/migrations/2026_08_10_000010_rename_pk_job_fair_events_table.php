<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the job_fair_events table primary key from the generic "id" to the
 * descriptive "job_fair_events_id".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->dropForeign('job_fair_employment_requests_job_fair_id_foreign');
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dropForeign('job_fair_participants_job_fair_id_foreign');
        });
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->dropForeign('job_fair_registrations_job_fair_id_foreign');
        });

        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->renameColumn('id', 'job_fair_events_id');
        });

        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->foreign('job_fair_id')->references('job_fair_events_id')->on('job_fair_events')->onDelete('cascade');
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->foreign('job_fair_id')->references('job_fair_events_id')->on('job_fair_events')->onDelete('cascade');
        });
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->foreign('job_fair_id')->references('job_fair_events_id')->on('job_fair_events')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->dropForeign('job_fair_employment_requests_job_fair_id_foreign');
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dropForeign('job_fair_participants_job_fair_id_foreign');
        });
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->dropForeign('job_fair_registrations_job_fair_id_foreign');
        });

        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->renameColumn('job_fair_events_id', 'id');
        });

        Schema::table('job_fair_employment_requests', function (Blueprint $table) {
            $table->foreign('job_fair_id')->references('id')->on('job_fair_events')->onDelete('cascade');
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->foreign('job_fair_id')->references('id')->on('job_fair_events')->onDelete('cascade');
        });
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->foreign('job_fair_id')->references('id')->on('job_fair_events')->onDelete('cascade');
        });
    }
};
