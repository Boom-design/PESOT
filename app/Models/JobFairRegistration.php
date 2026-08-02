<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobFairRegistration extends Model
{
    protected $fillable = [
        'job_fair_id',
        'user_id',
        'slip_number',
        'is_early',
        'is_attended',
        'attended_at',
        'attendance_notified_at',
    ];

    protected $casts = [
        'is_early'                => 'boolean',
        'is_attended'             => 'boolean',
        'attended_at'             => 'datetime',
        'attendance_notified_at'  => 'datetime',
    ];

    public function jobFair()
    {
        return $this->belongsTo(JobFairEvent::class, 'job_fair_id');
    }

    public function jobseeker()
    {
        return $this->belongsTo(JobseekerRegistration::class, 'user_id');
    }
}