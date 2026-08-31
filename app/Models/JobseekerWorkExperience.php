<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobseekerWorkExperience extends Model
{
    protected $table = 'jobseeker_work_experiences';

    protected $primaryKey = 'jobseeker_work_experiences_id';

    protected $fillable = [
        'jobseeker_nsrp_registration_id',
        'company_name',
        'position',
        'industry',
        'date_from',
        'date_to',
        'is_current',
        'employment_status',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function nsrpRegistration()
    {
        return $this->belongsTo(JobseekerNsrpRegistration::class, 'jobseeker_nsrp_registration_id');
    }
}