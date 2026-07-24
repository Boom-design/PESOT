<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── EMPLOYER_REQUIREMENTS ──
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['reviewed_by']);
        });

        foreach (DB::table('employer_requirements')->get() as $row) {
            $nsrp = DB::table('employer_nsrp_registrations')->where('user_id', $row->user_id)->first();
            $update = [];
            if ($nsrp) $update['user_id'] = $nsrp->id;

            if ($row->reviewed_by) {
                $staff = DB::table('staff')->where('user_id', $row->reviewed_by)->first();
                $update['reviewed_by'] = $staff->id ?? null;
            }
            if (!empty($update)) {
                DB::table('employer_requirements')->where('id', $row->id)->update($update);
            }
        }

        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('staff')->onDelete('set null');
        });

        // ── INHOUSE_SCHEDULES ──
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->dropForeign(['employer_id']);
            $table->dropForeign(['reviewed_by']);
        });

        foreach (DB::table('inhouse_schedules')->get() as $row) {
            $nsrp = DB::table('employer_nsrp_registrations')->where('user_id', $row->employer_id)->first();
            $update = [];
            if ($nsrp) $update['employer_id'] = $nsrp->id;

            if ($row->reviewed_by) {
                $staff = DB::table('staff')->where('user_id', $row->reviewed_by)->first();
                $update['reviewed_by'] = $staff->id ?? null;
            }
            if (!empty($update)) {
                DB::table('inhouse_schedules')->where('id', $row->id)->update($update);
            }
        }

        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->foreign('employer_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('staff')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['reviewed_by']);
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->dropForeign(['employer_id']);
            $table->dropForeign(['reviewed_by']);
        });

        Schema::table('employer_requirements', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->foreign('employer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};