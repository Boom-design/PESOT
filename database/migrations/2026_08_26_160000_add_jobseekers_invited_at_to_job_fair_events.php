<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the jobseekers were told this fair is happening.
 *
 * PESO, 2026-08-26: the moment the Job Fair desk posts the first vacancy onto a
 * fair, the jobseekers are told the fair is coming. That announcement is about
 * the event, not the vacancy, so it goes out once — posting nine more vacancies
 * must not send nine more copies of the same notice. This column is what makes
 * it once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->timestamp('jobseekers_invited_at')->nullable()->after('pwd_only');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropColumn('jobseekers_invited_at');
        });
    }
};
