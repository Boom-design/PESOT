<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('religion')->nullable()->after('sex_preference');
            $table->string('civil_status')->nullable()->after('religion');
            $table->text('other_qualifications')->nullable()->after('civil_status');
            $table->string('accepts_disability')->nullable()->after('other_qualifications');
            $table->json('disability_types')->nullable()->after('accepts_disability');
            $table->string('course_major')->nullable()->after('disability_types');
            $table->string('license')->nullable()->after('course_major');
            $table->string('eligibility')->nullable()->after('license');
            $table->string('certification')->nullable()->after('eligibility');
            $table->string('language')->nullable()->after('certification');
            $table->string('preferred_residence')->nullable()->after('language');
            $table->integer('experience_months')->nullable()->after('preferred_residence');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'religion',
                'civil_status',
                'other_qualifications',
                'accepts_disability',
                'disability_types',
                'course_major',
                'license',
                'eligibility',
                'certification',
                'language',
                'preferred_residence',
                'experience_months',
            ]);
        });
    }
};