<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The points an employer gives each qualification on their own posting.
 *
 * PESO CDO, 2026-09-17: the match score weighed every qualification with the
 * same fixed numbers in code (preferred occupation 25, education 15, …), and
 * the defense panel asked where those numbers came from. The employer is the
 * one who knows what matters for their vacancy, so the employer sets them.
 *
 * A posting with nothing stored here scores with the standard points in
 * Job::MATCH_POINTS, so every posting made before this still scores the same.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->json('match_points')->nullable()->after('preferred_residence');
        });
    }

    public function down(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropColumn('match_points');
        });
    }
};
