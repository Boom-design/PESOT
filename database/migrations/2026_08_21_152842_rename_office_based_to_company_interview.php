<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Office Based" becomes "Company Interview".
 *
 * Three PESO staff were interviewed about whether they could follow the system
 * on their own. Two of them stopped at this one word and could not say what it
 * meant; explained as "the interview happens at the company itself" it was
 * immediately obvious. The term was the problem, not the feature.
 *
 * Every step here is an UPDATE or a rename. Nothing is dropped, and down()
 * reverses each one, so the change can be walked back without losing a row.
 *
 * `venue_type = 'office_based'` on an in-house schedule is a SEPARATE thing —
 * it means the employer's own office rather than PESO's — and is renamed to
 * `company_office` in the same pass so the two never share a word again.
 * `venue_type = 'peso_office'` is left exactly as it is.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('job_qualifications')
            ->where('schedule_type', 'office_based')
            ->update(['schedule_type' => 'company_interview']);

        DB::table('job_archives')
            ->where('schedule_type', 'office_based')
            ->update(['schedule_type' => 'company_interview']);

        DB::table('inhouse_schedules')
            ->where('venue_type', 'office_based')
            ->update(['venue_type' => 'company_office']);

        Schema::table('job_matching', function (Blueprint $table) {
            $table->renameColumn('office_participation', 'company_interview_participation');
        });

        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->string('schedule_type')->default('company_interview')->change();
        });

        // The bell keeps every notification it has ever sent, and jobseekers and
        // employers can still open the old ones. Leaving them would mean the
        // people this rename is for still read the old word.
        // "for an office based schedule on" becomes "for a company interview on" —
        // the article has to move with the noun or every old notification reads
        // "for an company interview".
        DB::table('announcements')
            ->where('message', 'like', '%office based schedule%')
            ->update(['message' => DB::raw(
                "REPLACE(message, 'an office based schedule', 'a company interview')"
            )]);

        DB::table('announcements')
            ->where('message', 'like', '%office based schedule%')
            ->update(['message' => DB::raw(
                "REPLACE(message, 'office based schedule', 'company interview')"
            )]);
    }

    public function down(): void
    {
        DB::table('announcements')
            ->where('message', 'like', '%a company interview%')
            ->update(['message' => DB::raw(
                "REPLACE(message, 'a company interview', 'an office based schedule')"
            )]);

        DB::table('announcements')
            ->where('message', 'like', '%company interview%')
            ->update(['message' => DB::raw(
                "REPLACE(message, 'company interview', 'office based schedule')"
            )]);

        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->string('schedule_type')->default('office_based')->change();
        });

        Schema::table('job_matching', function (Blueprint $table) {
            $table->renameColumn('company_interview_participation', 'office_participation');
        });

        DB::table('inhouse_schedules')
            ->where('venue_type', 'company_office')
            ->update(['venue_type' => 'office_based']);

        DB::table('job_archives')
            ->where('schedule_type', 'company_interview')
            ->update(['schedule_type' => 'office_based']);

        DB::table('job_qualifications')
            ->where('schedule_type', 'company_interview')
            ->update(['schedule_type' => 'office_based']);
    }
};
