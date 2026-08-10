<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the staff table primary key from the generic "id" to the descriptive
 * "staff_id" (table name + _id).
 *
 * Two of the inbound constraints use SET NULL on delete and two use CASCADE,
 * so each is recreated with its original rule.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign('announcements_staff_id_foreign');
        });
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropForeign('employer_requirements_reviewed_by_foreign');
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->dropForeign('inhouse_schedules_reviewed_by_foreign');
        });
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropForeign('job_fair_events_created_by_foreign');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->renameColumn('id', 'staff_id');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->foreign('staff_id')->references('staff_id')->on('staff')->onDelete('cascade');
        });
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->foreign('reviewed_by')->references('staff_id')->on('staff')->onDelete('set null');
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->foreign('reviewed_by')->references('staff_id')->on('staff')->onDelete('set null');
        });
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->foreign('created_by')->references('staff_id')->on('staff')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign('announcements_staff_id_foreign');
        });
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropForeign('employer_requirements_reviewed_by_foreign');
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->dropForeign('inhouse_schedules_reviewed_by_foreign');
        });
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropForeign('job_fair_events_created_by_foreign');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->renameColumn('staff_id', 'id');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
        });
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->foreign('reviewed_by')->references('id')->on('staff')->onDelete('set null');
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->foreign('reviewed_by')->references('id')->on('staff')->onDelete('set null');
        });
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('staff')->onDelete('cascade');
        });
    }
};
