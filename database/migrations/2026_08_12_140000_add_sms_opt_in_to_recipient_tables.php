<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a jobseeker or an employer turn off PESO text messages.
 *
 * Defaults to true so nobody silently stops receiving notices when this ships —
 * opting out has to be a choice the person actually makes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->boolean('sms_opt_in')->default(true)->after('contact_number');
        });

        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->boolean('sms_opt_in')->default(true)->after('mobile_number');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->dropColumn('sms_opt_in');
        });

        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->dropColumn('sms_opt_in');
        });
    }
};
