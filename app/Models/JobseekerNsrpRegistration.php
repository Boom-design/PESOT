<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobseekerNsrpRegistration extends Model
{
    protected $table = 'jobseeker_nsrp_registrations';

    protected $primaryKey = 'jobseeker_nsrp_registrations_id';

    protected $fillable = [
    'jobseeker_registration_id',
    'type',
    'employment_type', 'employed_sub_type', 'self_employed_specify',
        'months_looking', 'unemployed_reason', 'terminated_abroad_country',
        'unemployed_other', 'is_ofw', 'is_former_ofw',
        'ofw_country', 'latest_deployment_country', 'return_month',
        'is_4ps', 'household_id',
        'work_type', 'preferred_occupations', 'local_locations', 'overseas_locations',
        'language_proficiency', 'other_language',
        'currently_in_school', 'education',
        'trainings', 'training_certificates',
        'eligibilities', 'licenses',
        'other_skills', 'other_skills_specify',
        'certification_agreed', 'certification_date',
        'status',
    ];

    protected $casts = [
        'preferred_occupations' => 'array',
        'local_locations'       => 'array',
        'overseas_locations'    => 'array',
        'language_proficiency'  => 'array',
        'education'             => 'array',
        'trainings'             => 'array',
        'training_certificates' => 'array',
        'eligibilities'         => 'array',
        'licenses'              => 'array',
        'other_skills'          => 'array',
        'is_ofw'                => 'boolean',
        'is_former_ofw'         => 'boolean',
        'is_4ps'                => 'boolean',
        'currently_in_school'   => 'boolean',
        'certification_agreed'  => 'boolean',
    ];

    public function registration()
{
    return $this->belongsTo(JobseekerRegistration::class, 'jobseeker_registration_id');
}

public function workExperiences()
{
    return $this->hasMany(JobseekerWorkExperience::class, 'jobseeker_nsrp_registration_id');
}

public function certifications()
{
    return $this->hasMany(JobseekerCertification::class, 'jobseeker_nsrp_registration_id');
}
}