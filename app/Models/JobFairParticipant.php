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
        'sra_decided_by',
        'sra_decided_at',
        'sra_decision_note',
        'responded_at',
        'total_interviewed',
        'total_vacancies',
        'male_count',
        'female_count',
        'total_hired',
    ];

    protected $casts = [
        'invited_at'     => 'datetime',
        'responded_at'   => 'datetime',
        'sra_decided_at' => 'datetime',
    ];

    /** Ang staff nga nagpili niini nga employer. Null kung ang sistema ang nag-invite. */
    public function invitedBy()
    {
        return $this->belongsTo(Staff::class, 'invited_by', 'staff_id');
    }

    /** Ang staff nga nagpasulod o nagpalta niini sa fair. Null kung wala pa siya nadesisyonan. */
    public function sraDecidedBy()
    {
        return $this->belongsTo(Staff::class, 'sra_decided_by', 'staff_id');
    }

    /**
     * Mitubag na ang employer, apan wala pa mopili ang SRA.
     *
     * Overseas ra ang mahulog dinhi. Ang lokal walay tawo nga nagpili sa iyang
     * imbitasyon, mao nga ang iyang oo kay oo dayon.
     */
    public function awaitingSelection(): bool
    {
        return $this->confirmation_status === 'accepted';
    }

    /** Mitubag ang employer ug oo, apan wala siya gidala sa opisina sa fair. */
    public function wasNotSelected(): bool
    {
        return $this->confirmation_status === 'not_selected';
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
     *
     * Ang petsa nga giihap mao ang adlaw nga nahimo siyang apil, dili ang adlaw
     * nga siya mitubag. Para sa lokal parehas ra sila. Para sa overseas dili:
     * mahimong mitubag siya ug oo sa ikatulo nga adlaw ug gipili sa SRA sa
     * ikanapulog-duha — ug ang naa sa listahan nga gipasa sa DOLE mao ang
     * gipili, mao nga ang ulahing petsa mao ang tinuod nga tubag.
     */
    public function confirmedBeforeCutoff(): bool
    {
        if ($this->confirmation_status !== 'confirmed') {
            return false;
        }

        $cutoff  = $this->jobFair?->dole_cutoff_at;
        $enteredAt = $this->sra_decided_at ?: $this->responded_at;

        return !$cutoff || !$enteredAt || $enteredAt->lte($cutoff->copy()->endOfDay());
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