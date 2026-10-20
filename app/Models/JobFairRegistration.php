<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobFairRegistration extends Model
{
    protected $primaryKey = 'job_fair_registrations_id';

    protected $fillable = [
        'job_fair_id',
        'user_id',
        'slip_number',
        'is_early',
        'is_attended',
        'attended_at',
        'attendance_notified_at',
        'cancelled_at',
    ];

    protected $casts = [
        'is_early'                => 'boolean',
        'is_attended'             => 'boolean',
        'attended_at'             => 'datetime',
        'attendance_notified_at'  => 'datetime',
        'cancelled_at'            => 'datetime',
    ];

    /**
     * The people still expected at the fair.
     *
     * Someone who pulled out keeps their row — the slip number is handed out
     * as a count of the rows, so a hole in the table would give the next
     * person a slip somebody else is carrying — but they are not on any list
     * the desk reads, not in the attendance sheet, and not in the audience of
     * a text message. Every query that asks "who is coming" asks through here.
     *
     * Deliberately not a global scope. The slip counter has to see the
     * cancelled rows, and so does the check that stops a person rejoining, so
     * a scope that hid them everywhere by default would have two exceptions
     * that are easy to forget to write.
     */
    public function scopeNotCancelled($query)
    {
        return $query->whereNull('cancelled_at');
    }

    public function jobFair()
    {
        return $this->belongsTo(JobFairEvent::class, 'job_fair_id');
    }

    public function jobseeker()
    {
        return $this->belongsTo(JobseekerRegistration::class, 'user_id');
    }
}