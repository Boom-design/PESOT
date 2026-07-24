<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Smart Matching Fields
            $table->boolean('height_required')->default(false)->after('slots');
            $table->string('height_minimum')->nullable()->after('height_required');
            $table->boolean('age_required')->default(false)->after('height_minimum');
            $table->integer('age_min')->nullable()->after('age_required');
            $table->integer('age_max')->nullable()->after('age_min');
            $table->enum('sex_preference', ['Any', 'Male', 'Female'])->default('Any')->after('age_max');
            $table->string('education_required')->nullable()->after('sex_preference');
            $table->boolean('experience_required')->default(false)->after('education_required');
            $table->integer('experience_years')->nullable()->after('experience_required');
            $table->json('skills_required')->nullable()->after('experience_years');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'height_required',
                'height_minimum',
                'age_required',
                'age_min',
                'age_max',
                'sex_preference',
                'education_required',
                'experience_required',
                'experience_years',
                'skills_required',
            ]);
        });
    }
};