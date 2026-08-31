<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InhouseSchedule extends Model
{
    protected $primaryKey = 'inhouse_schedules_id';

    protected $fillable = [
        'employer_id',
        'reviewed_by',
        'preferred_date',
        // The employer offers a window; staff confirm one day inside it. A
        // single-day request has the end equal to the start.
        'preferred_date_end',
        'preferred_time',
        'num_applicants',
        'venue_type',
        'venue_address',
        'notes',
        'status',
        'rejection_reason',
        'confirmed_date',
        'confirmed_time',
    ];

    protected $casts = [
        'preferred_date'     => 'date',
        'preferred_date_end' => 'date',
        'confirmed_date'     => 'date',
        'job_positions'      => 'array',
    ];

    /** The last day of the offered window; a one-day request ends where it starts. */
    public function getPreferredDateLastAttribute()
    {
        return $this->preferred_date_end ?: $this->preferred_date;
    }

    public function getScheduleWindowLabelAttribute(): string
    {
        if (!$this->preferred_date) return 'None';

        $last = $this->preferred_date_last;

        if (!$last || $last->isSameDay($this->preferred_date)) {
            return $this->preferred_date->format('M d, Y');
        }

        return $this->preferred_date->format('M d') . ' – ' . $last->format('M d, Y');
    }

    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'employer_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Staff::class, 'reviewed_by');
    }
}