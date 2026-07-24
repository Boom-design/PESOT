<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── STEP 1: Add new FK column ──
        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->foreignId('jobseeker_nsrp_registration_id')
                  ->nullable()
                  ->after('jobseeker_registration_id');
        });

        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->foreign('jobseeker_nsrp_registration_id', 'jwe_nsrp_reg_id_fk')
                  ->references('id')->on('jobseeker_nsrp_registrations')
                  ->onDelete('cascade');
        });

        // ── STEP 2: Migrate data — map old registration_id to new nsrp_id ──
        $experiences = DB::table('jobseeker_work_experiences')->get();
        foreach ($experiences as $exp) {
            $nsrp = DB::table('jobseeker_nsrp_registrations')
                ->where('jobseeker_registration_id', $exp->jobseeker_registration_id)
                ->first();

            if ($nsrp) {
                DB::table('jobseeker_work_experiences')
                    ->where('id', $exp->id)
                    ->update(['jobseeker_nsrp_registration_id' => $nsrp->id]);
            }
        }

        // ── STEP 3: Drop old FK and column ──
        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->dropForeign(['jobseeker_registration_id']);
            $table->dropColumn('jobseeker_registration_id');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->foreignId('jobseeker_registration_id')
                  ->nullable()
                  ->constrained('jobseeker_registrations')
                  ->onDelete('cascade');
        });

        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->dropForeign('jwe_nsrp_reg_id_fk');
            $table->dropColumn('jobseeker_nsrp_registration_id');
        });
    }
};