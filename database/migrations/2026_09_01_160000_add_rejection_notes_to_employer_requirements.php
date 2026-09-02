<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Why one document can now be rejected on its own.
 *
 * PESO Job Vacancy staff, 2026-09-01: the desk reads the folder a paper at a
 * time and decides on each one — approve this, reject that. `rejected_fields`
 * already held which papers were wrong, but the reason lived in a single
 * `remarks` box for the whole folder, so a staff member who rejected two
 * documents for two different reasons had nowhere to say so.
 *
 * `rejection_notes` is that missing half: field name => the reason for that
 * one paper. `remarks` stays as the message the employer reads at the top of
 * the notice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->json('rejection_notes')->nullable()->after('rejected_fields');
        });
    }

    public function down(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropColumn('rejection_notes');
        });
    }
};
