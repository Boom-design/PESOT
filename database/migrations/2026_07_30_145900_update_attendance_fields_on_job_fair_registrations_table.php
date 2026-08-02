<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Reset ang existing default-false values ngadto sa NULL (wala pa gi-confirm) ──
        DB::table('job_fair_registrations')->where('is_attended', false)->update(['is_attended' => null]);

        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->boolean('is_attended')->nullable()->default(null)->change();
            $table->timestamp('attendance_notified_at')->nullable()->after('attended_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->boolean('is_attended')->default(false)->change();
            $table->dropColumn('attendance_notified_at');
        });
    }
};