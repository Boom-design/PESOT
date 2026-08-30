<?php

namespace App\Support;

use App\Models\InhouseSchedule;
use App\Models\Job;
use App\Models\JobFairEvent;
use Illuminate\Support\Collection;

/**
 * What each staff role sees on its activity calendar.
 *
 * One builder for all four roles, so LRA, SRA, Job Vacancy and Job Fair cannot
 * end up looking at different versions of the same office.
 *
 * | Role         | Sees                                                   |
 * |--------------|--------------------------------------------------------|
 * | LRA          | office days · job fair · in-house (local + overseas)     |
 * | SRA          | office days · job fair · in-house (local + overseas)     |
 * | Job Vacancy  | office days · job fair                                  |
 * | Job Fair     | office days · job fair                                  |
 *
 * Company Interview is deliberately absent from the calendar. PESO is involved in
 * the posting only — once it is live the screening happens at the employer's own
 * office, so it is not a PESO activity and would mark nearly every day for nothing.
 */
class StaffCalendar
{
    /** Colour and icon per activity type. Also drives the on-screen legend. */
    public const TYPES = [
        'office'   => ['label' => 'PESO Schedule', 'color' => '#7c3aed', 'icon' => 'ph-users-three'],
        'job_fair' => ['label' => 'Job Fair',      'color' => '#0ea5e9', 'icon' => 'ph-calendar-dots'],
        'inhouse'  => ['label' => 'In-house',      'color' => '#f59e0b', 'icon' => 'ph-buildings'],
        // Ang employer ra ang nakakita niini: ang interview nga siya mismo ang
        // nag-host sa iyang kaugalingong lugar, wala sa kalendaryo sa opisina.
        'company_interview' => ['label' => 'Company Interview', 'color' => '#10b981', 'icon' => 'ph-briefcase'],
    ];

    /**
     * Who sees in-house on their calendar.
     *
     * LRA and SRA share the PESO Office venue, so each needs to see the
     * other's bookings before picking a day. Admin is here because the admin
     * calendar is the master view — the office days are set from it, and you
     * cannot sensibly book a meeting without seeing what it would collide
     * with.
     */
    public static function rolesWithInhouse(): array
    {
        return ['lra', 'sra', 'admin'];
    }

    /**
     * ['Y-m-d' => [item, …]] for a jobseeker.
     *
     * A jobseeker sees what PESO is running that they could turn up to: every
     * job fair, and every in-house interview day that has already been
     * accepted. Two things are deliberately left out.
     *
     * Office days are internal — a staff meeting is not something a jobseeker
     * attends, and marking it would only make the calendar look busy.
     *
     * Requested-but-not-yet-approved in-house days are left out as well. LRA
     * can still reject the date, so showing it would have jobseekers planning
     * around a day that may never happen.
     */
    public static function forJobseeker(): array
    {
        return self::jobFairItems()
            ->concat(self::inhouseItems()->filter(fn($i) => $i['state'] === 'confirmed'))
            ->filter(fn($i) => !empty($i['date']))
            ->groupBy('date')
            ->map(fn($group) => $group->values())
            ->toArray();
    }

    /**
     * ['Y-m-d' => [item, …]] for one employer.
     *
     * PESO, 2026-08-26: the employer dashboard used to list "Today\'s
     * Activities" — the fairs and interviews happening on this one day, and
     * nothing else. An interview four days out was invisible until the morning
     * it arrived. The same calendar the jobseeker and the office use answers
     * this properly: click a day, read what is on it.
     *
     * Narrower than the office view, deliberately. The employer sees:
     *
     *   - the job fairs they were invited to, whatever they answered;
     *   - their own in-house days, requested or reserved;
     *   - their own company-interview days.
     *
     * They do not see PESO office days — the office being in a meeting is not
     * theirs to know — and they do not see other employers\' bookings.
     */
    public static function forEmployer(int $companyId): array
    {
        $items = collect();

        // ── The fairs they were invited to ──
        $fairIds = \App\Models\JobFairParticipant::where('employer_id', $companyId)
            ->pluck('job_fair_id');

        $fairs = $fairIds->isEmpty()
            ? collect()
            : JobFairEvent::whereIn('job_fair_events_id', $fairIds)
                ->whereIn('status', ['upcoming', 'ongoing'])
                ->get();

        foreach ($fairs as $e) {
            $items->push([
                'date'   => optional($e->event_date)->format('Y-m-d'),
                'type'   => 'job_fair',
                'label'  => 'Job Fair',
                'title'  => $e->title,
                'detail' => $e->venue,
                'time'   => $e->event_time ? \Carbon\Carbon::parse($e->event_time)->format('h:i A') : null,
                'state'  => 'confirmed',
            ]);
        }

        // ── Their own in-house days ──
        $schedules = InhouseSchedule::where('employer_id', $companyId)
            ->whereIn('status', ['pending', 'accepted'])
            ->get();

        foreach ($schedules as $schedule) {
            if (!$schedule->preferred_date) continue;

            $rawTime = $schedule->confirmed_time ?: $schedule->preferred_time;

            foreach (self::span($schedule->preferred_date, $schedule->preferred_date_last) as $iso) {
                $items->push([
                    'date'   => $iso,
                    'type'   => 'inhouse',
                    'label'  => 'In-house Interview',
                    'title'  => $schedule->status === 'accepted' ? 'Reserved for you' : 'Waiting for PESO to confirm',
                    'detail' => $schedule->venue_type === 'custom' ? $schedule->venue_address : 'PESO Office',
                    'time'   => $rawTime ? \Carbon\Carbon::parse($rawTime)->format('h:i A') : null,
                    'state'  => $schedule->status === 'accepted' ? 'confirmed' : 'pending',
                ]);
            }
        }

        // ── Their own postings that carry a day ──
        $jobs = Job::where('company_id', $companyId)
            ->whereIn('schedule_type', ['inhouse', 'company_interview'])
            ->whereIn('posting_status', ['pending', 'approved'])
            ->whereNotNull('preferred_date')
            ->get();

        foreach ($jobs as $job) {
            $isInhouse = $job->schedule_type === 'inhouse';

            foreach (self::span($job->preferred_date, $job->preferred_date_last) as $iso) {
                $items->push([
                    'date'   => $iso,
                    'type'   => $isInhouse ? 'inhouse' : 'company_interview',
                    'label'  => $isInhouse ? 'In-house Interview' : 'Company Interview',
                    'title'  => $job->title,
                    'detail' => $isInhouse
                        ? ($job->venue_type === 'other' ? $job->venue_address : 'PESO Office')
                        : $job->location,
                    'time'   => null,
                    'state'  => $job->posting_status === 'approved' ? 'confirmed' : 'pending',
                ]);
            }
        }

        return $items
            ->filter(fn($i) => !empty($i['date']))
            ->groupBy('date')
            ->map(fn($group) => $group->values())
            ->toArray();
    }

    /**
     * ['Y-m-d' => [item, …]] for one staff role.
     *
     * Each item: type, label, title, detail, time, state (pending|confirmed).
     */
    public static function forRole(string $role): array
    {
        $items = collect();

        // ── Office days — every role, always. When the office is in a meeting
        // ── nobody is free to run anything. ──
        $items = $items->concat(self::officeItems());

        // ── Job fair — every role. The whole office works a fair. ──
        $items = $items->concat(self::jobFairItems());

        // ── In-house — LRA and SRA, and both of them see local AND overseas.
        // ── They share the PESO Office venue, so each has to see the other's
        // ── bookings before choosing a day. Seeing is not acting: the approval
        // ── routes still split on is_overseas. ──
        if (in_array($role, self::rolesWithInhouse(), true)) {
            $items = $items->concat(self::inhouseItems());
        }

        return $items
            ->filter(fn($i) => !empty($i['date']))
            ->groupBy('date')
            ->map(fn($group) => $group->values())
            ->toArray();
    }

    // ───────────────────────────────
    // SOURCES
    // ───────────────────────────────

    private static function officeItems(): Collection
    {
        $out = collect();

        foreach (\App\Models\OfficeCalendarEvent::orderBy('start_date')->get() as $event) {
            foreach (OfficeCalendar::daysOf($event) as $iso) {
                $out->push([
                    'date'   => $iso,
                    'type'   => 'office',
                    'label'  => $event->type_label,
                    'title'  => $event->title,
                    'detail' => $event->location,
                    'time'   => $event->time_label,
                    'state'  => 'confirmed',
                ]);
            }
        }

        return $out;
    }

    private static function jobFairItems(): Collection
    {
        return JobFairEvent::whereIn('status', ['upcoming', 'ongoing'])
            ->get()
            ->map(fn($e) => [
                'date'   => optional($e->event_date)->format('Y-m-d'),
                'type'   => 'job_fair',
                'label'  => 'Job Fair',
                'title'  => $e->title,
                'detail' => $e->venue,
                'time'   => $e->event_time ? \Carbon\Carbon::parse($e->event_time)->format('h:i A') : null,
                'state'  => 'confirmed',
            ]);
    }

    /**
     * In-house from both sources: the schedule-only request and the job posting
     * that carries a schedule.
     *
     * A request covers a range of days, and every day of it is marked. Once
     * accepted, that whole range is held for that employer — nobody else can
     * book any day in it, and the employer picks which of those days they
     * actually interview on. So the marking does not shrink to one day on
     * approval; only the dot fills in, from hollow (still requested) to solid
     * (reserved).
     */
    private static function inhouseItems(): Collection
    {
        $out = collect();

        $push = function (string $iso, string $company, bool $overseas, string $state, ?string $time, ?string $venue) use (&$out) {
            $out->push([
                'date'   => $iso,
                'type'   => 'inhouse',
                'label'  => $overseas ? 'In-house (Overseas)' : 'In-house (Local)',
                'title'  => $company,
                'detail' => $venue,
                'time'   => $time,
                'state'  => $state,
            ]);
        };

        // ── Source 1: schedule-only requests ──
        $schedules = InhouseSchedule::with('employer')
            ->whereIn('status', ['pending', 'accepted'])
            ->get();

        foreach ($schedules as $schedule) {
            if (!$schedule->preferred_date) continue;

            $company  = $schedule->employer->company_name ?? '—';
            $overseas = (bool) ($schedule->employer->is_overseas ?? false);
            $venue    = $schedule->venue_type === 'custom' ? $schedule->venue_address : 'PESO Office';
            $state    = $schedule->status === 'accepted' ? 'confirmed' : 'pending';

            $rawTime = $schedule->confirmed_time ?: $schedule->preferred_time;
            $time    = $rawTime ? \Carbon\Carbon::parse($rawTime)->format('h:i A') : null;

            foreach (self::span($schedule->preferred_date, $schedule->preferred_date_last) as $iso) {
                $push($iso, $company, $overseas, $state, $time, $venue);
            }
        }

        // ── Source 2: job postings whose schedule type is in-house ──
        $jobs = Job::with('company')
            ->where('schedule_type', 'inhouse')
            ->whereIn('posting_status', ['pending', 'approved'])
            ->whereNotNull('preferred_date')
            ->get();

        foreach ($jobs as $job) {
            $company  = $job->company->company_name ?? '—';
            $overseas = (bool) ($job->company->is_overseas ?? false);
            $venue    = $job->venue_type === 'other' ? $job->venue_address : 'PESO Office';
            $state    = $job->posting_status === 'approved' ? 'confirmed' : 'pending';

            foreach (self::span($job->preferred_date, $job->preferred_date_last) as $iso) {
                $push($iso, $company, $overseas, $state, null, $venue);
            }
        }

        return $out;
    }

    /** Every ISO day from start to end inclusive. */
    private static function span($start, $end): array
    {
        if (!$start) return [];

        $cursor = $start->copy();
        $last   = $end && $end->gte($start) ? $end : $start;
        $days   = [];

        while ($cursor->lte($last)) {
            $days[] = $cursor->toDateString();
            $cursor->addDay();
        }

        return $days;
    }
}
