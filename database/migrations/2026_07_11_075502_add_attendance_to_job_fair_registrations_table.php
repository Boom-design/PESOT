<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->boolean('is_attended')->default(false)->after('is_early');
            $table->timestamp('attended_at')->nullable()->after('is_attended');
        });
    }

    public function down(): void
    {
        Schema::table('job_fair_registrations', function (Blueprint $table) {
            $table->dropColumn(['is_attended', 'attended_at']);
        });
    }
};