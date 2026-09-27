<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the jobseeker last looked at the Job Fair tab.
 *
 * PESO CDO, 2026-09-16: the red number on PESO Events counted the fairs the
 * jobseeker could join, and it stayed there after they had opened the tab and
 * read them. A number that does not clear when you have looked at the thing it
 * points to stops meaning anything.
 *
 * Opening the tab stamps this, and the number counts only fairs announced
 * after it — so a new fair raises it again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->timestamp('job_fair_seen_at')->nullable()->after('sms_opt_in');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->dropColumn('job_fair_seen_at');
        });
    }
};
