<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hang the two imported-report tables on the staff who uploaded them.
 *
 * Both tables were created with `uploaded_by` as a plain unsigned integer
 * (job_fair_imported_reports 2026-08-23, job_vacancy_imported_reports
 * 2026-08-24). The column has always held a `staff.staff_id` and the models
 * have always related it to Staff, but without the constraint the database
 * itself did not know that, so the tables stood alone in the entity-
 * relationship diagram and nothing stopped a row from naming a staff member
 * who does not exist.
 *
 * `nullOnDelete`, not `cascadeOnDelete`: the report belongs to the office, not
 * to the person who happened to upload it. If that staff account is removed,
 * the report stays and simply no longer names an uploader — which is exactly
 * why the column was nullable from the start.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_imported_reports', function (Blueprint $table) {
            $table->foreign('uploaded_by')
                  ->references('staff_id')->on('staff')
                  ->nullOnDelete();
        });

        Schema::table('job_vacancy_imported_reports', function (Blueprint $table) {
            $table->foreign('uploaded_by')
                  ->references('staff_id')->on('staff')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_imported_reports', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });

        Schema::table('job_vacancy_imported_reports', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });
    }
};
