<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop old FK (pointed sa users)
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        // Step 2: I-convert ang existing data — company_id (users.id) → employer_nsrp_registrations.id
        $jobs = DB::table('jobs')->get();
        foreach ($jobs as $job) {
            $nsrp = DB::table('employer_nsrp_registrations')
                ->where('user_id', $job->company_id)
                ->first();
            if ($nsrp) {
                DB::table('jobs')->where('id', $job->id)->update(['company_id' => $nsrp->id]);
            }
        }

        // Step 3: Bag-ong FK — mo-point na sa employer_nsrp_registrations
        Schema::table('jobs', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('employer_nsrp_registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        $jobs = DB::table('jobs')->get();
        foreach ($jobs as $job) {
            $nsrp = DB::table('employer_nsrp_registrations')->where('id', $job->company_id)->first();
            if ($nsrp) {
                DB::table('jobs')->where('id', $job->id)->update(['company_id' => $nsrp->user_id]);
            }
        }

        Schema::table('jobs', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};