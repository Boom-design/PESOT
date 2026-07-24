<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->longText('training_certificates')->nullable()->after('trainings');
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
            $table->dropColumn('training_certificates');
        });
    }
};