<?php

namespace App\Models;

use Carbon\Carbon;
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
        'cater',
        'target_industries',
        'pwd_only',
        'jobseekers_invited_at',
        'employer_capacity',
        'local_capacity',
        'overseas_capacity',
        'dole_cutoff_at',
        'status',
    ];

    protected $casts = [
        'event_date'        => 'date',
        'dole_cutoff_at'    => 'date',
        'cater'             => 'array',
        'target_industries' => 'array',
        'pwd_only'          => 'boolean',
        'jobseekers_invited_at' => 'datetime',
    ];

    /**
     * Does this fair want local employers, or overseas ones?
     *
     * Stored rather than inferred from the capacity. The capacity is now only
     * a target the office writes down, and a fair may want overseas employers
     * without having decided how many.
     */
    public function catersTo(bool $isOverseas): bool
    {
        return in_array($isOverseas ? 'overseas' : 'local', (array) $this->cater, true);
    }

    /**
     * Is this employer's industry one the fair is looking for?
     *
     * No target industries means all of them, which is how every event
     * behaved before the office asked for targeted invitations. An employer
     * who has not said what industry they are in is not matched by a fair
     * that named one — there is nothing to match them against.
     */
    public function wantsIndustry(?string $industryGroup): bool
    {
        if (empty($this->target_industries)) {
            return true;
        }

        return $industryGroup !== null
            && in_array($industryGroup, $this->target_industries, true);
    }

    /**
     * Does this fair take this vacancy?
     *
     * PESO Job Fair staff, 2026-08-26: the desk decides which fair a posting
     * joins, and two things on the event decide for them. A fair held for PWD
     * applicants takes only the vacancies whose employer said they accept
     * them, and a fair looking for one industry takes only the vacancies in it.
     * A fair that is neither takes anything, which is how every event behaved
     * before today.
     *
     * Read from the posting, not from the employer: an establishment may run
     * one vacancy that accepts PWD applicants and another that does not, and
     * the industry is asked again on every posting.
     */
    public function wantsPosting(Job $job): bool
    {
        return $this->postingMismatch($job) === null;
    }

    /**
     * Why this fair will not take this vacancy, or null when it will.
     *
     * One sentence, written for the staff who is looking at the posting. The
     * refusal in the controller and the note in the picker both read it, so
     * the reason on screen is the reason the save gives back.
     */
    public function postingMismatch(Job $job): ?string
    {
        if ($this->pwd_only && !$job->acceptsPwd()) {
            return $this->title . ' is for PWD applicants, and this vacancy does not accept them. '
                . 'The employer sets that on the posting itself.';
        }

        if (!$this->wantsIndustry($job->industry_group)) {
            return $this->title . ' is looking for ' . implode(', ', (array) $this->target_industries)
                . '. This vacancy is under ' . ($job->industry_group ?: 'no industry group') . '.';
        }

        return null;
    }

    /**
     * The day the office submits its confirmed roster to DOLE.
     *
     * Project manager, 2026-08-23: ten days before the fair the employer slots
     * are meant to be filled, and that roster is what goes to DOLE. Computed
     * from the event date; dole_cutoff_at records the day it actually happened.
     */
    public function doleCutoffDate(): Carbon
    {
        return $this->event_date
            ->copy()
            ->subDays((int) config('peso.jobfair.dole_cutoff_days_before'));
    }

    public function pastDoleCutoff(): bool
    {
        return $this->dole_cutoff_at !== null;
    }

    /**
     * Days until the fair. Negative once it has passed.
     *
     * Counted from midnight, not from now(): now() carries the rest of today
     * and turns a whole number of days into 2.73.
     */
    public function daysUntil(): int
    {
        return (int) today()->diffInDays($this->event_date, false);
    }

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