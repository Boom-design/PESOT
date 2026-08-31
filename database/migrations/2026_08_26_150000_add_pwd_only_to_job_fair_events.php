<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A job fair can be held for PWD applicants.
 *
 * PESO Job Fair staff, 2026-08-26: an event may be run for a particular group,
 * and the clearest example is a fair for persons with disability. The vacancies
 * that belong on that fair are the ones whose employer already said they accept
 * PWD applicants — the posting has carried that answer since it was written, it
 * was simply never used to decide anything.
 *
 * Off by default, so every event that already exists keeps taking any vacancy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->boolean('pwd_only')->default(false)->after('target_industries');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropColumn('pwd_only');
        });
    }
};
