<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What changed on a posting after it went live, and when.
 *
 * PESO, 2026-08-28: an employer can widen a posting after people have already
 * applied — start accepting PWD applicants, raise the number of slots, move the
 * deadline — and can report hires they made outside PESO. Until now none of
 * that left a trace. An applicant who was screened out under the old wording,
 * and a desk asked why the slots no longer add up, had nothing to read.
 *
 * The row is written for the person looking at the posting, so it stores the
 * sentence as well as the raw before/after. `changes` keeps the detail for
 * anyone who needs to see the exact values later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_activity_logs', function (Blueprint $table) {
            $table->id('job_activity_logs_id');

            $table->unsignedBigInteger('job_id');
            $table->foreign('job_id')
                ->references('job_qualifications_id')->on('job_qualifications')
                ->cascadeOnDelete();

            // Null when the system wrote it rather than a person.
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('actor_name')->nullable();

            $table->string('action', 40);
            $table->string('summary', 500);
            $table->json('changes')->nullable();

            $table->timestamps();
            $table->index(['job_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_activity_logs');
    }
};
