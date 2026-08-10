<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobFairParticipant extends Model
{
    protected $primaryKey = 'job_fair_participants_id';

    protected $fillable = [
        'job_fair_id',
        'employer_id',
        'confirmation_status',
        'total_interviewed',
        'total_vacancies',
        'male_count',
        'female_count',
        'total_hired',
    ];

    public function jobFair()
    {
        return $this->belongsTo(JobFairEvent::class, 'job_fair_id');
    }

    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'employer_id');
    }
}