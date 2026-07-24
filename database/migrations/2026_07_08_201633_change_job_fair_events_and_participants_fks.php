<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── JOB_FAIR_EVENTS ──
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        foreach (DB::table('job_fair_events')->get() as $row) {
            $staff = DB::table('staff')->where('user_id', $row->created_by)->first();
            if ($staff) {
                DB::table('job_fair_events')->where('id', $row->id)->update(['created_by' => $staff->id]);
            }
        }

        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('staff')->onDelete('cascade');
        });

        // ── JOB_FAIR_PARTICIPANTS ──
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dropForeign(['employer_id']);
        });

        foreach (DB::table('job_fair_participants')->get() as $row) {
            $nsrp = DB::table('employer_nsrp_registrations')->where('user_id', $row->employer_id)->first();
            if ($nsrp) {
                DB::table('job_fair_participants')->where('id', $row->id)->update(['employer_id' => $nsrp->id]);
            }
        }

        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->foreign('employer_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->dropForeign(['employer_id']);
        });

        Schema::table('job_fair_events', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('job_fair_participants', function (Blueprint $table) {
            $table->foreign('employer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};