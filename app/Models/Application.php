<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory;

    protected $table = 'job_matching';

    protected $fillable = [
    'job_id',
    'jobseeker_id',
    'status',
    'match_percentage',
    'inhouse_participation',
    'inhouse_participation_notified_at',
    'office_participation',
];

    protected $casts = [
        'inhouse_participation_notified_at' => 'datetime',
    ];

    // Relationship: Application belongs to a Job
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    // Relationship: Application belongs to a Jobseeker (JobseekerRegistration)
    public function jobseeker()
    {
        return $this->belongsTo(JobseekerRegistration::class, 'jobseeker_id');
    }
}