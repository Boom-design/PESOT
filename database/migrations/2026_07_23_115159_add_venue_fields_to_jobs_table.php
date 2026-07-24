<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_qualifications', 'venue_type')) {
                $table->string('venue_type')->nullable()->after('preferred_time');
            }
            if (!Schema::hasColumn('job_qualifications', 'venue_address')) {
                $table->string('venue_address')->nullable()->after('venue_type');
            }
            foreach (['confirmed_date', 'confirmed_time', 'schedule_status', 'schedule_rejection_reason'] as $col) {
                if (Schema::hasColumn('job_qualifications', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_qualifications', function (Blueprint $table) {
            $table->dropColumn(['venue_type', 'venue_address']);
        });
    }
};