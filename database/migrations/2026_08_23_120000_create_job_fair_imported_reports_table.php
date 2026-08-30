<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Job Fair staff's own report, kept beside the system's.
 *
 * PESO Job Fair staff, 2026-08-23: they keep a report by hand that the system
 * does not produce, and they need it inside the system. Their words:
 * "ilahi ang report nga gikan sa system ug ang excel report nga gi import" —
 * the two are separate and stay separate. Nothing here is ever added into a
 * system figure; it is stored, shown, and can be downloaded again.
 *
 * `headers` and `rows` are whatever the uploaded file contained. No column
 * layout is imposed, because the whole point is that this report is a
 * different report — a fixed schema would reject the very file the feature
 * exists to accept.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_fair_imported_reports', function (Blueprint $table) {
            $table->id('job_fair_imported_reports_id');

            $table->foreignId('job_fair_id')
                  ->constrained('job_fair_events', 'job_fair_events_id')
                  ->cascadeOnDelete();

            // Nullable: ang rekord magpabilin bisan mawala na ang staff nga
            // nag-upload — ang report gipangayo sa opisina, dili sa tawo.
            $table->unsignedBigInteger('uploaded_by')->nullable();

            $table->string('title', 120);
            $table->string('original_filename');

            $table->json('headers');
            $table->json('rows');

            // Gitipigan aron ang listahan dili mag-decode sa tibuok json para
            // ra makaingon kung pila ka laray ang sulod.
            $table->unsignedInteger('row_count')->default(0);

            $table->timestamps();

            $table->index(['job_fair_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_fair_imported_reports');
    }
};
