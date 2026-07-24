<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobseeker_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // ── STEP 1: Personal Information ──
            $table->string('surname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('age')->nullable();
            $table->string('sex')->nullable();
            $table->string('religion')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('house_street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('municipality_city')->nullable();
            $table->string('province')->nullable();
            $table->string('tin')->nullable();
            $table->json('disabilities')->nullable();       // array of strings
            $table->string('disability_other')->nullable();
            $table->string('height')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('reg_email')->nullable();

            // ── STEP 2: Employment Status ──
            $table->string('employment_type')->nullable();  // employed / unemployed
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

            // ── STEP 3: Job Preference ──
            $table->string('work_type')->nullable();        // part_time / full_time
            $table->json('preferred_occupations')->nullable();  // array of 3
            $table->json('local_locations')->nullable();        // array of 3
            $table->json('overseas_locations')->nullable();     // array of 3

            // ── STEP 4: Language Proficiency ──
            $table->json('language_proficiency')->nullable();   // {English:{read,write,speak,understand},...}
            $table->string('other_language')->nullable();

            // ── STEP 5: Educational Background ──
            $table->boolean('currently_in_school')->nullable();
            $table->json('education')->nullable();              // {Elementary:{school_name,course,...},...}

            // ── STEP 6: Technical/Vocational ──
            $table->json('trainings')->nullable();              // [{course,hours,duration_from,...},...]

            // ── STEP 7: Eligibility & License ──
            $table->json('eligibilities')->nullable();          // [{eligibility,date_taken},...]
            $table->json('licenses')->nullable();               // [{license,valid_until},...]

            // ── STEP 8: Work Experience ──
            $table->json('work_experiences')->nullable();       // [{company_name,address,position,...},...]

            // ── STEP 9: Other Skills ──
            $table->json('other_skills')->nullable();           // array of strings
            $table->string('other_skills_specify')->nullable();

            // ── STEP 10: Certification ──
            $table->boolean('certification_agreed')->default(false);
            $table->string('certification_date')->nullable();

            // ── META ──
            $table->string('status')->default('submitted');     // submitted / reviewed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobseeker_registrations');
    }
};