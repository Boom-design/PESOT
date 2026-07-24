<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['jobseeker_id']);
        });

        $apps = DB::table('applications')->get();
        foreach ($apps as $app) {
            $reg = DB::table('jobseeker_registrations')->where('user_id', $app->jobseeker_id)->first();
            if ($reg) {
                DB::table('applications')->where('id', $app->id)->update(['jobseeker_id' => $reg->id]);
            }
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('id')->on('jobseeker_registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['jobseeker_id']);
        });

        $apps = DB::table('applications')->get();
        foreach ($apps as $app) {
            $reg = DB::table('jobseeker_registrations')->where('id', $app->jobseeker_id)->first();
            if ($reg) {
                DB::table('applications')->where('id', $app->id)->update(['jobseeker_id' => $reg->user_id]);
            }
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};