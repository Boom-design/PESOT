<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory;

    protected $table = 'job_matching';

    protected $primaryKey = 'job_matching_id';

    protected $fillable = [
    'job_id',
    'jobseeker_id',
    'status',
    'hired_at',
    'start_date',
    'match_percentage',
    'inhouse_participation',
    'inhouse_participation_notified_at',
    'company_interview_participation',
];

    protected $casts = [
        'inhouse_participation_notified_at' => 'datetime',
        'hired_at' => 'datetime',
        'start_date' => 'date',
    ];

    /**
     * The jobseeker withdrew after they had already joined.
     *
     * Not the same answer as 'declined'. Declining is the first answer to the
     * invitation — the person never joined. Cancelling comes after joining,
     * once they are on the employer's list and on their own PESO Events page,
     * and the employer has planned the venue around them. It cannot be taken
     * back, and the page says so before the button is pressed.
     *
     * Every count in the system tests for 'accepted', so a cancelled row
     * leaves those counts on its own.
     */
    public const CANCELLED = 'cancelled';

    /** The participation answer for this posting, whichever column holds it. */
    public function participationFor(?string $scheduleType): ?string
    {
        return match ($scheduleType) {
            'inhouse'           => $this->inhouse_participation,
            'company_interview' => $this->company_interview_participation,
            default             => null,
        };
    }

    // Relationship: Application belongs to a Job
    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    // Relationship: Application belongs to a Jobseeker (JobseekerRegistration)
    public function jobseeker()
    {
        return $this->belongsTo(JobseekerRegistration::class, 'jobseeker_id');
    }
}