<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->json('initial_vacancy_data')->nullable()->after('is_overseas');
            $table->boolean('initial_vacancy_confirmed')->default(false)->after('initial_vacancy_data');
        });
    }

    public function down(): void
    {
        Schema::table('employer_nsrp_registrations', function (Blueprint $table) {
            $table->dropColumn(['initial_vacancy_data', 'initial_vacancy_confirmed']);
        });
    }
};