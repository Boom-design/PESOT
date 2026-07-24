<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->dropForeign(['jobseeker_id']);
        });

        foreach (DB::table('inhouse_participants')->get() as $row) {
            $reg = DB::table('jobseeker_registrations')->where('user_id', $row->jobseeker_id)->first();
            if ($reg) {
                DB::table('inhouse_participants')->where('id', $row->id)->update(['jobseeker_id' => $reg->id]);
            }
        }

        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('id')->on('jobseeker_registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->dropForeign(['jobseeker_id']);
        });

        foreach (DB::table('inhouse_participants')->get() as $row) {
            $reg = DB::table('jobseeker_registrations')->where('id', $row->jobseeker_id)->first();
            if ($reg) {
                DB::table('inhouse_participants')->where('id', $row->id)->update(['jobseeker_id' => $reg->user_id]);
            }
        }

        Schema::table('inhouse_participants', function (Blueprint $table) {
            $table->foreign('jobseeker_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};