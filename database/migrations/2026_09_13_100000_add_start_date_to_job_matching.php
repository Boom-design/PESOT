<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The day a hired applicant reports for work.
 *
 * PESO CDO client, 2026-09-13: marking someone Hired said nothing about when
 * they begin, so the jobseeker was told "you are hired" with no day to show up
 * and the office had no way to tell a hire that started from one that was
 * only promised. The employer now gives the start date at the moment of
 * hiring, on every channel — company interview, in-house and job fair.
 *
 * Separate from hired_at on purpose: hired_at is when the decision was
 * recorded, start_date is when the work begins. They are often weeks apart.
 *
 * Nullable: every hire recorded before this column existed has no start date,
 * and inventing one would be worse than admitting it was never asked.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('hired_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->dropColumn('start_date');
        });
    }
};
