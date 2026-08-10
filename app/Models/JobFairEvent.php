<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobFairEvent extends Model
{
    protected $primaryKey = 'job_fair_events_id';

    protected $fillable = [
        'created_by',
        'title',
        'event_date',
        'event_time',
        'venue',
        'employer_capacity',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(Staff::class, 'created_by');
    }

    public function participants()
    {
        return $this->hasMany(\App\Models\JobFairParticipant::class, 'job_fair_id');
    }

    public function employmentRequests()
    {
        return $this->hasMany(\App\Models\JobFairEmploymentRequest::class, 'job_fair_id');
    }

    public function registrations()
    {
        return $this->hasMany(\App\Models\JobFairRegistration::class, 'job_fair_id');
    }
}