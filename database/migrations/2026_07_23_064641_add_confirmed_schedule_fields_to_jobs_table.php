<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->date('confirmed_date')->nullable()->after('preferred_time');
            $table->time('confirmed_time')->nullable()->after('confirmed_date');
            $table->string('schedule_status')->nullable()->after('confirmed_time');
            $table->text('schedule_rejection_reason')->nullable()->after('schedule_status');
        });

        // I-default ang naa nang In-house records ngadto sa 'pending'
        DB::table('job_qualifications')
            ->where('schedule_type', 'inhouse')
            ->whereNull('schedule_status')
            ->update(['schedule_status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropColumn(['confirmed_date', 'confirmed_time', 'schedule_status', 'schedule_rejection_reason']);
        });
    }
};