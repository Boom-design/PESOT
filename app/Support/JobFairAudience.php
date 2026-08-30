<?php

namespace App\Support;

use App\Http\Controllers\ApplicationController;
use App\Models\EmployerNsrpRegistration;
use App\Models\Job;
use App\Models\JobFairEmploymentRequest;
use App\Models\JobFairEvent;
use App\Models\JobFairParticipant;
use App\Models\JobFairRegistration;
use App\Models\JobseekerRegistration;
use Illuminate\Support\Collection;

// ── Kinsa ang padad-an sa notice sa usa ka job fair event, ug kinsa ang dili.
// ──
// ── Usa ra ka lugar ang naghubad niini aron ang automatic nga reminder
// ── (jobfair:send-confirmation-reminders) ug ang manual nga send page dili gyud
// ── magkalahi ug listahan. Kung magkalahi sila, ang usa ka tawo makadawat ug
// ── text nga wala niya makita sa system, o balitok.
// ──
// ── PESO interview 2026-08-13 — duha ka managlahi nga lagda, ayaw sagola:
// ──   1. Ang pag-APPLY sa usa ka vacancy bukas sa TANAN, bisan 0% ang match.
// ──      "bahalag mangilad sila, si employer nay bahala ana, siya nay mo-verify
// ──      pag actual interview" — ug naa gyuy na-hire nga ubos ang marka.
// ──   2. Ang notice sa JOB FAIR kay para sa QUALIFIED ra. Bayad ang SMS, ug
// ──      ang mga employer nga mo-attend naay gipangita nga kalidad. ──
class JobFairAudience
{
    public const EMPLOYERS_ALL       = 'employers_all';
    public const EMPLOYERS_PENDING   = 'employers_pending';
    public const EMPLOYERS_CONFIRMED = 'employers_confirmed';
    public const JOBSEEKERS_QUALIFIED = 'jobseekers_qualified';
    public const JOBSEEKERS_EVENT    = 'jobseekers_event';

    public static function options(): array
    {
        return [
            self::EMPLOYERS_ALL => [
                'label' => 'Invited employers — all',
                'hint'  => 'Everyone invited to this event, whatever their response.',
            ],
            self::EMPLOYERS_PENDING => [
                'label' => 'Invited employers — not yet responded',
                'hint'  => 'A nudge for the ones still deciding.',
            ],
            self::EMPLOYERS_CONFIRMED => [
                'label' => 'Invited employers — confirmed only',
                'hint'  => 'Reminders and instructions for the ones attending.',
            ],
            self::JOBSEEKERS_QUALIFIED => [
                'label' => 'Qualified jobseekers',
                'hint'  => 'Jobseekers who reach ' . self::matchThreshold()
                           . '% on at least one posting brought to this event.',
            ],
            self::JOBSEEKERS_EVENT => [
                'label' => 'Jobseekers registered to this event',
                'hint'  => 'For event-day reminders — they already signed up.',
            ],
        ];
    }

    public static function label(string $key): string
    {
        return self::options()[$key]['label'] ?? $key;
    }

    // ── Pila ka employer ang kinahanglan mo-confirm sa dili pa masultihan ang
    // ── mga jobseeker. Lahi ni sa matchThreshold(). ──
    public static function threshold(): int
    {
        return (int) config('peso.jobfair.min_confirmed_employers', 3);
    }

    // ── Pila ka porsyento ang minimum aron matawag nga "qualified". ──
    public static function matchThreshold(): int
    {
        return (int) config('peso.jobfair.qualified_match_percentage', 75);
    }

    public static function confirmedCount(JobFairEvent $event): int
    {
        return JobFairParticipant::where('job_fair_id', $event->job_fair_events_id)
            ->where('confirmation_status', 'confirmed')
            ->count();
    }

    public static function gateMet(JobFairEvent $event): bool
    {
        return self::confirmedCount($event) >= self::threshold();
    }

    // ── Ang audience sa employer bukas kanunay. Ang "Qualified jobseekers"
    // ── naka-lock hangtod igo na ang na-confirm nga employer — mao nay daloy
    // ── sa opisina. Ang naka-register na sa event bukas gihapon, kay sila
    // ── mismo ang mi-sign up. ──
    public static function isUnlocked(JobFairEvent $event, string $key): bool
    {
        if ($key === self::JOBSEEKERS_QUALIFIED) {
            return self::gateMet($event);
        }
        return array_key_exists($key, self::options());
    }

    // ── Ang mga posting nga gidala sa mga employer niini nga event. ──
    public static function eventJobs(JobFairEvent $event): Collection
    {
        $jobIds = JobFairEmploymentRequest::where('job_fair_id', $event->job_fair_events_id)
            ->pluck('job_id');

        return $jobIds->isEmpty()
            ? collect()
            : Job::whereIn('job_qualifications_id', $jobIds)->get();
    }

    // ── Ang mga jobseeker nga qualified sa BISAN USA sa mga posting sa event.
    // ──
    // ── Ang pag-ihap kay jobseeker × job. Usa ra ni ka higayon modagan kada
    // ── event sa pagpindot sa staff — dili siya request nga sagad — ug ang
    // ── loop mo-undang dayon pag-abot sa threshold. ──
    public static function qualifiedRegistrations(JobFairEvent $event): Collection
    {
        $jobs = self::eventJobs($event);
        if ($jobs->isEmpty()) {
            return collect();
        }

        $threshold  = self::matchThreshold();
        $controller = new ApplicationController();

        return JobseekerRegistration::with('nsrp')
            ->whereHas('user', fn($q) => $q->where('status', 'approved'))
            ->whereHas('nsrp')
            ->get()
            ->filter(function (JobseekerRegistration $registration) use ($jobs, $controller, $threshold) {
                foreach ($jobs as $job) {
                    $breakdown = $controller->computeMatchBreakdownByRegistrationId(
                        $registration->jobseeker_registrations_id,
                        $job
                    );
                    if (($breakdown['percentage'] ?? 0) >= $threshold) {
                        return true;
                    }
                }
                return false;
            })
            ->values();
    }

    // ── Usa ka listahan sa mga tawo, parehas ang porma bisan employer o
    // ── jobseeker, aron usa ra ang code nga mo-padala. ──
    public static function resolve(JobFairEvent $event, string $key): Collection
    {
        return match ($key) {
            self::EMPLOYERS_ALL       => self::employers($event, null),
            self::EMPLOYERS_PENDING   => self::employers($event, 'pending'),
            self::EMPLOYERS_CONFIRMED => self::employers($event, 'confirmed'),
            self::JOBSEEKERS_QUALIFIED => self::qualifiedRegistrations($event)
                                            ->map(fn($r) => self::jobseekerRow($r)),
            self::JOBSEEKERS_EVENT    => self::eventJobseekers($event),
            default                   => collect(),
        };
    }

    private static function employers(JobFairEvent $event, ?string $status): Collection
    {
        $employerIds = JobFairParticipant::where('job_fair_id', $event->job_fair_events_id)
            ->when($status, fn($q) => $q->where('confirmation_status', $status))
            ->pluck('employer_id');

        if ($employerIds->isEmpty()) {
            return collect();
        }

        return EmployerNsrpRegistration::whereIn('employer_nsrp_registrations_id', $employerIds)
            ->get()
            ->map(fn(EmployerNsrpRegistration $employer) => [
                'kind'       => 'employer',
                'id'         => $employer->employer_nsrp_registrations_id,
                'name'       => $employer->company_name,
                'raw_number' => $employer->mobile_number,
                'number'     => PhoneNumber::normalize($employer->mobile_number),
                'opted_out'  => !((bool) ($employer->sms_opt_in ?? true)),
            ])
            ->values();
    }

    private static function eventJobseekers(JobFairEvent $event): Collection
    {
        $regIds = JobFairRegistration::where('job_fair_id', $event->job_fair_events_id)
            ->pluck('user_id');

        if ($regIds->isEmpty()) {
            return collect();
        }

        return JobseekerRegistration::whereIn('jobseeker_registrations_id', $regIds)
            ->get()
            ->map(fn(JobseekerRegistration $r) => self::jobseekerRow($r))
            ->values();
    }

    private static function jobseekerRow(JobseekerRegistration $registration): array
    {
        return [
            'kind'       => 'jobseeker',
            'id'         => $registration->jobseeker_registrations_id,
            'name'       => trim(($registration->first_name ?? '') . ' ' . ($registration->surname ?? '')),
            'raw_number' => $registration->contact_number,
            'number'     => PhoneNumber::normalize($registration->contact_number),
            'opted_out'  => !((bool) ($registration->sms_opt_in ?? true)),
        ];
    }

    // ── Kinsa ang tinuod nga makadawat ug text, ug kinsa ang dili ug ngano.
    // ── Ang gi-skip makadawat gihapon sa in-app nga notification. ──
    public static function breakdown(Collection $recipients): array
    {
        $sendable = $noNumber = $invalid = $optedOut = [];

        foreach ($recipients as $recipient) {
            if ($recipient['opted_out']) {
                $optedOut[] = $recipient;
                continue;
            }
            if (empty($recipient['raw_number'])) {
                $noNumber[] = $recipient;
                continue;
            }
            if ($recipient['number'] === null) {
                $invalid[] = $recipient;
                continue;
            }
            $sendable[] = $recipient;
        }

        return [
            'total'             => $recipients->count(),
            'sendable'          => $sendable,
            'skipped_no_number' => $noNumber,
            'skipped_invalid'   => $invalid,
            'skipped_opted_out' => $optedOut,
        ];
    }
}
