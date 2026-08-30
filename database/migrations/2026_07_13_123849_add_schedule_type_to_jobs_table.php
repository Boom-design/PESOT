<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table((new \App\Models\Job)->getTable(), function (Blueprint $table) {
            $table->string('schedule_type')->default('company_interview')->after('status');
            $table->date('preferred_date')->nullable()->after('schedule_type');
            $table->string('preferred_time')->nullable()->after('preferred_date');
        });
    }

    public function down(): void
    {
        Schema::table((new \App\Models\Job)->getTable(), function (Blueprint $table) {
            $table->dropColumn(['schedule_type', 'preferred_date', 'preferred_time']);
        });
    }
};