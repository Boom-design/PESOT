<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the jobseeker_nsrp_registrations table primary key from the generic
 * "id" to the descriptive "jobseeker_nsrp_registrations_id".
 *
 * The constraint from jobseeker_work_experiences uses the custom short name
 * "jwe_nsrp_reg_id_fk" (the conventional name exceeds MySQL's 64 character
 * identifier limit), so it is dropped and recreated under that same name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->dropForeign('jwe_nsrp_reg_id_fk');
        });
        Schema::table('jobseeker_certifications', function (Blueprint $table) {
            $table->dropForeign('jobseeker_certifications_jobseeker_nsrp_registration_id_foreign');
        });

        Schema::table('jobseeker_nsrp_registrations', function (Blueprint $table) {
            $table->renameColumn('id', 'jobseeker_nsrp_registrations_id');
        });

        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->foreign('jobseeker_nsrp_registration_id', 'jwe_nsrp_reg_id_fk')
                ->references('jobseeker_nsrp_registrations_id')->on('jobseeker_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('jobseeker_certifications', function (Blueprint $table) {
            $table->foreign('jobseeker_nsrp_registration_id', 'jc_nsrp_fk')
                ->references('jobseeker_nsrp_registrations_id')->on('jobseeker_nsrp_registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->dropForeign('jwe_nsrp_reg_id_fk');
        });
        Schema::table('jobseeker_certifications', function (Blueprint $table) {
            $table->dropForeign('jc_nsrp_fk');
        });

        Schema::table('jobseeker_nsrp_registrations', function (Blueprint $table) {
            $table->renameColumn('jobseeker_nsrp_registrations_id', 'id');
        });

        Schema::table('jobseeker_work_experiences', function (Blueprint $table) {
            $table->foreign('jobseeker_nsrp_registration_id', 'jwe_nsrp_reg_id_fk')
                ->references('id')->on('jobseeker_nsrp_registrations')->onDelete('cascade');
        });
        Schema::table('jobseeker_certifications', function (Blueprint $table) {
            $table->foreign('jobseeker_nsrp_registration_id', 'jobseeker_certifications_jobseeker_nsrp_registration_id_foreign')
                ->references('id')->on('jobseeker_nsrp_registrations')->onDelete('cascade');
        });
    }
};
