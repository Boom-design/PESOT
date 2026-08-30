<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class JobFairParticipant extends Model
{
    protected $primaryKey = 'job_fair_participants_id';

    protected $fillable = [
        'job_fair_id',
        'employer_id',
        'confirmation_status',
        'invited_at',
        'invited_by',
        'permission_note',
        'responded_at',
        'total_interviewed',
        'total_vacancies',
        'male_count',
        'female_count',
        'total_hired',
    ];

    protected $casts = [
        'invited_at'   => 'datetime',
        'responded_at' => 'datetime',
    ];

    /** Ang staff nga nagpili niini nga employer. Null kung ang sistema ang nag-invite. */
    public function invitedBy()
    {
        return $this->belongsTo(Staff::class, 'invited_by', 'staff_id');
    }

    /**
     * The day this invitation stops waiting.
     *
     * PESO Job Fair staff, 2026-08-23: an employer has a week to answer, after
     * which the staff looks for someone else. Read from config so the office
     * can change the week without a developer.
     *
     * Null when invited_at is missing, which no new row can be — only a row
     * written before the column existed, and the migration backfilled those.
     */
    public function expiresAt(): ?Carbon
    {
        return $this->invited_at
            ? $this->invited_at->copy()->addDays((int) config('peso.jobfair.confirm_window_days'))
            : null;
    }

    /**
     * Days left to answer. Negative once the deadline has passed, so a caller
     * can tell "two days left" from "two days late".
     */
    public function daysToRespond(): ?int
    {
        $expires = $this->expiresAt();

        return $expires ? (int) today()->diffInDays($expires->copy()->startOfDay(), false) : null;
    }

    public function isLapsed(): bool
    {
        return $this->confirmation_status === 'expired';
    }

    public function isWaiting(): bool
    {
        return in_array($this->confirmation_status, ['pending', 'expired'], true);
    }

    /**
     * Was this confirmation in hand when the office sent its list to DOLE?
     *
     * An event with no cutoff stamped has not reached that day yet, so every
     * confirmation still counts as in time.
     */
    public function confirmedBeforeCutoff(): bool
    {
        if ($this->confirmation_status !== 'confirmed') {
            return false;
        }

        $cutoff = $this->jobFair?->dole_cutoff_at;

        return !$cutoff || !$this->responded_at || $this->responded_at->lte($cutoff->copy()->endOfDay());
    }

    public function jobFair()
    {
        return $this->belongsTo(JobFairEvent::class, 'job_fair_id');
    }

    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'employer_id');
    }
}