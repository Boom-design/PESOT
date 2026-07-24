<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        foreach (DB::table('job_fair_registrations')->get() as $row) {
            $reg = DB::table('jobseeker_registrations')->where('user_id', $row->user_id)->first();
            if ($reg) {
                DB::table('job_fair_registrations')->where('id', $row->id)->update(['user_id' => $reg->id]);
            }
        }

        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('jobseeker_registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        foreach (DB::table('job_fair_registrations')->get() as $row) {
            $reg = DB::table('jobseeker_registrations')->where('id', $row->user_id)->first();
            if ($reg) {
                DB::table('job_fair_registrations')->where('id', $row->id)->update(['user_id' => $reg->user_id]);
            }
        }

        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};