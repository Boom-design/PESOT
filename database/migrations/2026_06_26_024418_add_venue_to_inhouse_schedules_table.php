<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->enum('venue_type', ['peso_office', 'custom'])->default('peso_office')->after('num_applicants');
            $table->string('venue_address')->nullable()->after('venue_type');
        });
    }

    public function down(): void
    {
        Schema::table('inhouse_schedules', function (Blueprint $table) {
            $table->dropColumn(['venue_type', 'venue_address']);
        });
    }
};