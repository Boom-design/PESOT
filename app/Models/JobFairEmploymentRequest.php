<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobFairEmploymentRequest extends Model
{
    protected $primaryKey = 'job_fair_employment_requests_id';

    protected $fillable = [
        'job_fair_id',
        'employer_id',
        'job_id',
    ];

    public function jobFair()
    {
        return $this->belongsTo(JobFairEvent::class, 'job_fair_id');
    }

    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'employer_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }
}