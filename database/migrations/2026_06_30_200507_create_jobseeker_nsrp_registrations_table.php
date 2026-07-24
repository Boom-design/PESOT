<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobseeker_nsrp_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jobseeker_registration_id')
                  ->unique()
                  ->constrained('jobseeker_registrations')
                  ->onDelete('cascade');

            // Employment Status
            $table->string('employment_type')->nullable();
            $table->string('employed_sub_type')->nullable();
            $table->string('self_employed_specify')->nullable();
            $table->string('months_looking')->nullable();
            $table->string('unemployed_reason')->nullable();
            $table->string('terminated_abroad_country')->nullable();
            $table->string('unemployed_other')->nullable();
            $table->boolean('is_ofw')->default(false);
            $table->boolean('is_former_ofw')->default(false);
            $table->string('ofw_country')->nullable();
            $table->string('latest_deployment_country')->nullable();
            $table->string('return_month')->nullable();
            $table->boolean('is_4ps')->default(false);
            $table->string('household_id')->nullable();

            // Job Preference
            $table->string('work_type')->nullable();
            $table->json('preferred_occupations')->nullable();
            $table->json('local_locations')->nullable();
            $table->json('overseas_locations')->nullable();

            // Language
            $table->json('language_proficiency')->nullable();
            $table->string('other_language')->nullable();

            // Education
            $table->boolean('currently_in_school')->nullable();
            $table->json('education')->nullable();

            // Trainings
            $table->json('trainings')->nullable();
            $table->json('training_certificates')->nullable();

            // Eligibility / License
            $table->json('eligibilities')->nullable();
            $table->json('licenses')->nullable();

            // Other Skills
            $table->json('other_skills')->nullable();
            $table->string('other_skills_specify')->nullable();

            // Certification
            $table->boolean('certification_agreed')->default(false);
            $table->string('certification_date')->nullable();

            // NSRP submission status (review status)
            $table->string('status')->default('submitted');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobseeker_nsrp_registrations');
    }
};