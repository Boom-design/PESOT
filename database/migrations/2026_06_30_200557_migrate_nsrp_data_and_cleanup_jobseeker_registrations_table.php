<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── STEP 1: Copy existing data to jobseeker_nsrp_registrations ──
        $hasTrainingCerts = Schema::hasColumn('jobseeker_registrations', 'training_certificates');

        $registrations = DB::table('jobseeker_registrations')->get();

        foreach ($registrations as $reg) {
            DB::table('jobseeker_nsrp_registrations')->insert([
                'jobseeker_registration_id' => $reg->id,
                'employment_type'           => $reg->employment_type,
                'employed_sub_type'         => $reg->employed_sub_type,
                'self_employed_specify'     => $reg->self_employed_specify,
                'months_looking'            => $reg->months_looking,
                'unemployed_reason'         => $reg->unemployed_reason,
                'terminated_abroad_country' => $reg->terminated_abroad_country,
                'unemployed_other'          => $reg->unemployed_other,
                'is_ofw'                    => $reg->is_ofw,
                'is_former_ofw'             => $reg->is_former_ofw,
                'ofw_country'               => $reg->ofw_country,
                'latest_deployment_country' => $reg->latest_deployment_country,
                'return_month'              => $reg->return_month,
                'is_4ps'                    => $reg->is_4ps,
                'household_id'              => $reg->household_id,
                'work_type'                 => $reg->work_type,
                'preferred_occupations'     => $reg->preferred_occupations,
                'local_locations'           => $reg->local_locations,
                'overseas_locations'        => $reg->overseas_locations,
                'language_proficiency'      => $reg->language_proficiency,
                'other_language'            => $reg->other_language,
                'currently_in_school'       => $reg->currently_in_school,
                'education'                 => $reg->education,
                'trainings'                 => $reg->trainings,
                'training_certificates'     => $hasTrainingCerts ? $reg->training_certificates : null,
                'eligibilities'             => $reg->eligibilities,
                'licenses'                  => $reg->licenses,
                'other_skills'              => $reg->other_skills,
                'other_skills_specify'      => $reg->other_skills_specify,
                'certification_agreed'      => $reg->certification_agreed,
                'certification_date'        => $reg->certification_date,
                'status'                    => $reg->status,
                'created_at'                => $reg->created_at,
                'updated_at'                => $reg->updated_at,
            ]);
        }

        // ── STEP 2: Drop migrated columns from jobseeker_registrations ──
        Schema::table('jobseeker_registrations', function (Blueprint $table) use ($hasTrainingCerts) {
            $table->dropColumn([
                'employment_type', 'employed_sub_type', 'self_employed_specify',
                'months_looking', 'unemployed_reason', 'terminated_abroad_country',
                'unemployed_other', 'is_ofw', 'is_former_ofw', 'ofw_country',
                'latest_deployment_country', 'return_month', 'is_4ps', 'household_id',
                'work_type', 'preferred_occupations', 'local_locations', 'overseas_locations',
                'language_proficiency', 'other_language', 'currently_in_school', 'education',
                'trainings', 'work_experiences', 'eligibilities', 'licenses',
                'other_skills', 'other_skills_specify', 'certification_agreed',
                'certification_date', 'status',
            ]);

            if ($hasTrainingCerts) {
                $table->dropColumn('training_certificates');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobseeker_registrations', function (Blueprint $table) {
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
            $table->string('work_type')->nullable();
            $table->json('preferred_occupations')->nullable();
            $table->json('local_locations')->nullable();
            $table->json('overseas_locations')->nullable();
            $table->json('language_proficiency')->nullable();
            $table->string('other_language')->nullable();
            $table->boolean('currently_in_school')->nullable();
            $table->json('education')->nullable();
            $table->json('trainings')->nullable();
            $table->json('work_experiences')->nullable();
            $table->json('eligibilities')->nullable();
            $table->json('licenses')->nullable();
            $table->json('other_skills')->nullable();
            $table->string('other_skills_specify')->nullable();
            $table->boolean('certification_agreed')->default(false);
            $table->string('certification_date')->nullable();
            $table->string('status')->default('submitted');
            $table->json('training_certificates')->nullable();
        });
    }
};