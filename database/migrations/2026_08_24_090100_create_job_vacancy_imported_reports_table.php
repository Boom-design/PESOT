<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Job Vacancy staff's own report, kept beside the system's.
 *
 * The same arrangement the Job Fair staff already have
 * (job_fair_imported_reports, 2026-08-23), for the same reason: the office
 * keeps a report by hand that the system does not produce, and the two are
 * separate and stay separate. Nothing here is ever added into a system figure;
 * it is stored, shown, and can be downloaded again.
 *
 * `headers` and `rows` are whatever the uploaded file contained. No column
 * layout is imposed, because the whole point is that this report is a
 * different report — a fixed schema would reject the very file the feature
 * exists to accept.
 *
 * There is no event to hang these on the way the job fair ones hang on an
 * event, so `period` carries the month the sheet covers, and it is optional:
 * a sheet that spans a year has no month to give.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancy_imported_reports', function (Blueprint $table) {
            $table->id('job_vacancy_imported_reports_id');

            // Nullable: ang rekord magpabilin bisan mawala na ang staff nga
            // nag-upload — ang report gipangayo sa opisina, dili sa tawo.
            $table->unsignedBigInteger('uploaded_by')->nullable();

            $table->string('title', 120);
            $table->string('period', 7)->nullable();   // YYYY-MM
            $table->string('original_filename');

            $table->json('headers');
            $table->json('rows');

            // Gitipigan aron ang listahan dili mag-decode sa tibuok json para
            // ra makaingon kung pila ka laray ang sulod.
            $table->unsignedInteger('row_count')->default(0);

            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancy_imported_reports');
    }
};
