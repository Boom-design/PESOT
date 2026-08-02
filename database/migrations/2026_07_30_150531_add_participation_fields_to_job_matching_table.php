<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->timestamp('inhouse_participation_notified_at')->nullable()->after('inhouse_participation');
            $table->string('office_participation')->nullable()->after('inhouse_participation_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_matching', function (Blueprint $table) {
            $table->dropColumn(['inhouse_participation_notified_at', 'office_participation']);
        });
    }
};