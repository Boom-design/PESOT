<?php

namespace App\Support;

use App\Models\Application;
use App\Models\Job;
use App\Models\JobFairEmploymentRequest;
use App\Models\JobFairEvent;
use App\Models\JobFairParticipant;
use App\Models\JobFairRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

// ── Ang mga listahan sa likod sa Job Fair nga report page.
// ──
// ── PESO Job Fair staff, 2026-08-23: "dapat ma download ang report into excel
// ── since naay daghan tabs ang report sa job fair, each ma download."
// ──
// ── Ang matag tab kaniadto naa sulod mismo sa reports(), ug ang export nga
// ── isulat tapad niini kinahanglan mokopya sa matag query. Sa adlaw nga usbon
// ── ang usa kanila, magkalahi na ang CSV ug ang makita sa screen — ug ang CSV
// ── mao ang moadto sa DOLE ug sa Mayor's Office. Mao nga usa ra ka lugar ang
// ── naghubad: ang page mo-paginate sa query, ang export mokuha sa tanan, ug
// ── parehas gyud nga pangutana ang ilang gipangutana.
class JobFairReport
{
    /** Ang tab nga ma-download, ug ang titulo nga isulat sa ulohan sa file. */
    public const TABS = [
        'attendance'        => 'Attendance',
        'companies'         => 'Participating Companies',
        'further_interview' => 'For Further Interview',
        'hots'              => 'Hired on the Spot',
        'summary'           => 'Post Job Fair Summary',
        'industry'          => 'Companies with Vacancies by Industry',
        'placement'         => 'Company Placement',
        'top_employers'     => 'Top 10 Employers',
    ];

    // Wala nay tab nga makadagan nga walay event. Ang Top Employers kay
    // kada-bulan kaniadto; karon ang ranggo iya sa usa ka fair, mao nga
    // kinahanglan gyud ug event sama sa uban.
    public const TABS_WITHOUT_EVENT = [];

    // ── Ang mga posting nga gidala sa mga employer niini nga event. Lima ka
    // ── tab ang nagsukad niini, mao nga usa ra ka kuha. ──
    public static function eventJobIds(?int $eventId): Collection
    {
        return $eventId
            ? JobFairEmploymentRequest::where('job_fair_id', $eventId)->pluck('job_id')
            : collect();
    }

    // ───────────────────────────────
    // TAB 1 — ATTENDANCE
    // ───────────────────────────────
    // ── Ang miabot ra.
    // ──
    // ── Usa ra ka tab para sa attendance, ug ania siya.
    // ──
    // ── Duha ka pangutana ang gipangutana sa opisina sa parehas nga listahan:
    // ──   "kinsa ang ni-join?"   — trabahoan ni sa adlaw sa fair, ug kini ang
    // ──                            listahan diin gipislit ang Mark Attended;
    // ──   "kinsa ang miabot?"    — kataposang rekord, ug kini ang moadto sa DOLE.
    // ──
    // ── Ang $state ang nagpili. Ang screen ug ang CSV managsama ug agi dinhi,
    // ── mao nga dili gyud sila magkalahi ug ihap. ──
    public const STATES = [
        'joined'   => 'All joined',
        'attended' => 'Attended only',
    ];

    public static function attendanceQuery(
        int $eventId,
        string $filter = 'all',
        string $state = 'attended',
        ?string $search = null
    ): Builder {
        return JobFairRegistration::with(['jobseeker.nsrp', 'jobseeker.user'])
            ->where('job_fair_id', $eventId)
            ->when($state !== 'joined', fn($q) => $q->where('is_attended', true))
            ->when($filter !== 'all', fn($q) =>
                $q->whereHas('jobseeker.nsrp', fn($n) => $n->whereIn('type', [$filter, 'both']))
            )
            // Sa pultahan sa fair, ang staff mangita pinaagi sa pangalan o sa
            // numero sa slip. Ang jobseeker_registrations walay `name` — first_name
            // ug surname ang naa.
            ->when($search, fn($q) => $q->where(function ($sub) use ($search) {
                $sub->where('slip_number', 'like', "%{$search}%")
                    ->orWhereHas('jobseeker', fn($j) =>
                        $j->where('first_name', 'like', "%{$search}%")
                          ->orWhere('surname', 'like', "%{$search}%")
                    );
            }));
    }

    // Tulo ka kahimtang ang is_attended: null = ni-join, wala pa mitubag;
    // true = miabot; false = misulti nga dili siya makaadto. Usa ra ka lugar
    // ang naghubad niini, aron parehas ang badge sa screen ug ang teksto sa file.
    public static function attendanceLabel(?bool $isAttended): string
    {
        return match ($isAttended) {
            true    => 'Attended',
            false   => 'Said they cannot come',
            default => 'Joined — no reply',
        };
    }

    public static function attendanceTotals(int $eventId): array
    {
        $base = JobFairRegistration::where('job_fair_id', $eventId);

        return [
            'registered' => (clone $base)->count(),
            'attended'   => (clone $base)->where('is_attended', true)->count(),
            'no_reply'   => (clone $base)->whereNull('is_attended')->count(),
            'local'      => (clone $base)->whereHas('jobseeker.nsrp', fn($n) => $n->whereIn('type', ['local', 'both']))->count(),
            'overseas'   => (clone $base)->whereHas('jobseeker.nsrp', fn($n) => $n->whereIn('type', ['overseas', 'both']))->count(),
        ];
    }

    // ───────────────────────────────
    // TAB 2 — PARTICIPATING COMPANIES
    // ───────────────────────────────
    public static function confirmedCompanies(int $eventId): Collection
    {
        // Ang jobFair gikinahanglan sa confirmedBeforeCutoff(): kung wala, ang
        // matag laray mo-query pag-usab para sa parehas nga event.
        return JobFairParticipant::with(['employer', 'jobFair'])
            ->where('job_fair_id', $eventId)
            ->where('confirmation_status', 'confirmed')
            ->get();
    }

    // ───────────────────────────────
    // TAB 3 — FOR FURTHER INTERVIEW
    // ───────────────────────────────
    public static function furtherInterviewQuery(Collection $eventJobIds, bool $overseasOnly): Builder
    {
        return Application::with(['jobseeker.nsrp', 'job.company'])
            ->whereIn('job_id', $eventJobIds)
            ->where('status', 'waiting')
            ->when($overseasOnly, fn($q) => $q->whereHas('job.company', fn($c) => $c->where('is_overseas', true)))
            ->latest();
    }

    // ───────────────────────────────
    // TAB 4 — HIRED ON THE SPOT
    // ───────────────────────────────
    public static function hotsQuery(Collection $eventJobIds, bool $overseasOnly): Builder
    {
        return Application::with(['jobseeker', 'job.company'])
            ->whereIn('job_id', $eventJobIds)
            ->where('status', 'hired')
            ->when($overseasOnly, fn($q) => $q->whereHas('job.company', fn($c) => $c->where('is_overseas', true)))
            ->latest();
    }

    // ───────────────────────────────
    // TAB 5 — POST JOB FAIR SUMMARY
    // ───────────────────────────────
    public static function summaryRows(int $eventId, bool $overseasOnly): Collection
    {
        return JobFairParticipant::with('employer')
            ->where('job_fair_id', $eventId)
            ->where('confirmation_status', 'confirmed')
            ->when($overseasOnly, fn($q) => $q->whereHas('employer', fn($e) => $e->where('is_overseas', true)))
            ->get()
            ->map(function (JobFairParticipant $p) use ($eventId) {
                $jobIds = JobFairEmploymentRequest::where('job_fair_id', $eventId)
                    ->where('employer_id', $p->employer_id)
                    ->pluck('job_id');

                $apps = Application::with('jobseeker')->whereIn('job_id', $jobIds)->get();

                $p->vacancies   = Job::whereIn('job_qualifications_id', $jobIds)->sum('slots');
                $p->interviewed = $apps->count();
                $p->male        = $apps->filter(fn($a) => strtolower($a->jobseeker->sex ?? '') === 'male')->count();
                $p->female      = $apps->filter(fn($a) => strtolower($a->jobseeker->sex ?? '') === 'female')->count();
                $p->qualified   = $apps->where('status', 'qualified')->count();
                $p->hired       = $apps->where('status', 'hired')->count();

                return $p;
            });
    }

    public static function summaryTotals(Collection $rows): array
    {
        $totals = [];

        foreach (['vacancies', 'interviewed', 'male', 'female', 'qualified', 'hired'] as $key) {
            $totals[$key] = $rows->sum($key);
        }

        return $totals;
    }

    // ───────────────────────────────
    // TAB 6 — COMPANIES WITH VACANCIES, BY INDUSTRY GROUP
    // ───────────────────────────────
    public static function industryTotals(Collection $eventJobIds): array
    {
        $jobs = Job::with('company')->whereIn('job_qualifications_id', $eventJobIds)->get();

        return [
            'local' => $jobs->filter(fn($j) => !($j->company->is_overseas ?? false))
                ->groupBy('industry_group')
                ->map(fn($group) => $group->sum('slots')),
            'overseas' => $jobs->filter(fn($j) => $j->company->is_overseas ?? false)
                ->groupBy('industry_group')
                ->map(fn($group) => $group->sum('slots')),
        ];
    }

    // ───────────────────────────────
    // TAB 7 — TOP EMPLOYERS
    // ───────────────────────────────
    /**
     * Kinsa ang nagdala ug pinakadaghang bakante niining maong fair.
     *
     * PESO SRA, 2026-08-26: "top 10 employer, mga employer na daghan gina offer
     * na job vacancies sa job fair event."
     *
     * Kaniadto pila ka fair ang giapilan sa employer ang giihap — laing
     * pangutana kana, ug wala kini nagsulti kung kinsa ang nagdala ug trabaho.
     * Karon ang giihap mao ang slots sa mga posting nga tinuod nga gidala sa
     * maong event.
     *
     * Ang numero gikan sa posting mismo, dili sa job_fair_participants
     * .total_vacancies: kanang kolum gi-type sa tawo, ug wala pa gyud
     * masulbad kinsa ang mag-encode niini. Ang slots gibutang sa employer
     * dihang nag-post siya, mao nga naa siya kanunay.
     */
    public static function topEmployers(?JobFairEvent $event, bool $overseasOnly, int $limit = 10): Collection
    {
        $jobIds = self::eventJobIds($event?->job_fair_events_id);

        if ($jobIds->isEmpty()) {
            return collect();
        }

        return Job::with('company')
            ->whereIn('job_qualifications_id', $jobIds)
            ->when($overseasOnly, fn($q) => $q->whereHas('company', fn($c) => $c->where('is_overseas', true)))
            ->get()
            ->groupBy('company_id')
            ->map(fn($jobs) => [
                'employer'        => $jobs->first()->company,
                'total_vacancies' => (int) $jobs->sum('slots'),
                'posting_count'   => $jobs->count(),
            ])
            ->filter(fn($entry) => $entry['employer'] !== null)
            ->sortByDesc('total_vacancies')
            ->take($limit)
            ->values();
    }

    // ───────────────────────────────
    // TAB 8 — COMPANY PLACEMENT
    // ───────────────────────────────
    // ── Local ra, ug ang na-hire HUMAN sa adlaw sa fair — kadtong wala pa
    // ── na-hire sa mismong adlaw apan gikuha gihapon tungod sa fair. ──
    public static function placementQuery(Collection $eventJobIds, JobFairEvent $event): Builder
    {
        return Application::with(['jobseeker', 'job.company'])
            ->whereIn('job_id', $eventJobIds)
            ->where('status', 'hired')
            ->whereHas('job.company', fn($q) => $q->where('is_overseas', false))
            ->whereDate('updated_at', '>', $event->event_date)
            ->latest();
    }

    // ───────────────────────────────
    // ANG CSV
    // ───────────────────────────────
    /**
     * Ang usa ka tab, gipatag alang sa usa ka spreadsheet.
     *
     * Mo-return ug ['title', 'columns', 'rows']. Ang duha ka tab nga naay
     * duha ka listahan sa screen — companies ug industry — nahimong usa ka
     * lamesa dinhi nga naay Type nga kolum. Usa ra ka file kada tab: ang
     * opisina naay usa ka porma nga pun-on, dili duha.
     */
    public static function dataset(?JobFairEvent $event, string $tab, bool $overseasOnly, array $options = []): array
    {
        $eventId    = $event?->job_fair_events_id;
        $eventJobs  = self::eventJobIds($eventId);
        $title      = self::TABS[$tab] ?? $tab;
        $fullName   = fn($person) => trim(($person->first_name ?? '') . ' ' . ($person->surname ?? '')) ?: '';

        return match ($tab) {

            'attendance' => [
                'title'   => $title,
                // Naa ang Attendance nga kolum: kung "All joined" ang gipili sa
                // screen, tulo ka managlahi nga tawo ang naa sa file, ug ang
                // magbabasa kinahanglan makakita kung kinsa ang miabot gyud.
                'columns' => ['#', 'Slip No.', 'Jobseeker', 'Type', 'Attendance', 'Attended At'],
                'rows'    => self::attendanceQuery(
                        $eventId,
                        $options['attendance_filter'] ?? 'all',
                        $options['attendance_state'] ?? 'attended',
                        $options['attendance_search'] ?? null
                    )
                    ->latest()->get()
                    ->values()
                    ->map(fn($r, $i) => [
                        $i + 1,
                        $r->slip_number,
                        $fullName($r->jobseeker),
                        ucfirst($r->jobseeker->nsrp->type ?? ''),
                        self::attendanceLabel($r->is_attended === null ? null : (bool) $r->is_attended),
                        $r->attended_at?->format('Y-m-d H:i') ?? '',
                    ]),
            ],

            // Kining file mao ang moadto sa DOLE. Ang "Confirmed on" ug ang
            // "On DOLE list" nga kolum mao ang nagsulti kung kinsa ang naa na sa
            // kamot sa opisina sa adlaw nga gipasa ang listahan, ug kinsa ang
            // miabot human niana. Kung wala pa maabot ang adlaw, ang tanan
            // "Yes" — walay gipasa nga lain pa.
            'companies' => [
                'title'   => $title,
                'columns' => ['#', 'Type', 'Company', 'Address', 'Employer Type', 'Industry Group',
                              'Confirmed on', 'On DOLE list'],
                'rows'    => self::confirmedCompanies($eventId)
                    ->filter(fn($p) => !$overseasOnly || ($p->employer->is_overseas ?? false))
                    // Local una, dayon overseas — parehas sa han-ay sa screen.
                    ->sortBy(fn($p) => [
                        (int) ($p->employer->is_overseas ?? 0),
                        mb_strtolower($p->employer->company_name ?? ''),
                    ])
                    ->values()
                    ->map(fn($p, $i) => [
                        $i + 1,
                        ($p->employer->is_overseas ?? false) ? 'Overseas' : 'Local',
                        $p->employer->company_name ?? '',
                        trim(($p->employer->est_barangay ?? '') . ' ' . ($p->employer->est_city_municipality ?? '')),
                        $p->employer->employer_type ?? '',
                        $p->employer->industry_group ?? 'Not set',
                        $p->responded_at?->format('Y-m-d') ?? '',
                        $p->confirmedBeforeCutoff() ? 'Yes' : 'No',
                    ]),
            ],

            'further_interview' => [
                'title'   => $title,
                'columns' => ['#', 'Jobseeker', 'Type', 'Job Applied', 'Company', 'Date'],
                'rows'    => self::furtherInterviewQuery($eventJobs, $overseasOnly)->get()
                    ->values()
                    ->map(fn($a, $i) => [
                        $i + 1,
                        $fullName($a->jobseeker),
                        ucfirst($a->jobseeker->nsrp->type ?? ''),
                        $a->job->title ?? '',
                        $a->job->company->company_name ?? '',
                        $a->updated_at->format('Y-m-d'),
                    ]),
            ],

            'hots' => [
                'title'   => $title,
                'columns' => ['#', 'Name', 'Gender', 'Position', 'Hiring Company', 'Local/Overseas', 'Date Hired'],
                'rows'    => self::hotsQuery($eventJobs, $overseasOnly)->get()
                    ->values()
                    ->map(fn($a, $i) => [
                        $i + 1,
                        $fullName($a->jobseeker),
                        $a->jobseeker->sex ?? '',
                        $a->job->title ?? '',
                        $a->job->company->company_name ?? '',
                        ($a->job->company->is_overseas ?? false) ? 'Overseas' : 'Local',
                        $a->updated_at->format('Y-m-d'),
                    ]),
            ],

            'summary' => (function () use ($eventId, $overseasOnly, $title) {
                $rows   = self::summaryRows($eventId, $overseasOnly);
                $totals = self::summaryTotals($rows);

                $lines = $rows->values()->map(fn($p, $i) => [
                    $i + 1,
                    $p->employer->company_name ?? '',
                    $p->vacancies, $p->interviewed, $p->male, $p->female, $p->qualified, $p->hired,
                ]);

                // Ang screen naay totals sa ibabaw; ang file naay parehas nga
                // numero sa kataposang linya, aron ang gi-print ug ang gitan-aw
                // managsama.
                $lines->push(['', 'TOTAL',
                    $totals['vacancies'], $totals['interviewed'], $totals['male'],
                    $totals['female'], $totals['qualified'], $totals['hired'],
                ]);

                return [
                    'title'   => $title,
                    'columns' => ['#', 'Employer', 'Vacancies', 'Interviewed', 'Male', 'Female', 'Qualified', 'Hired'],
                    'rows'    => $lines,
                ];
            })(),

            'industry' => (function () use ($eventJobs, $overseasOnly, $title) {
                $totals = self::industryTotals($eventJobs);
                $lines  = collect();

                foreach (['Local' => 'local', 'Overseas' => 'overseas'] as $label => $key) {
                    if ($overseasOnly && $key === 'local') {
                        continue;
                    }
                    foreach ($totals[$key] as $group => $vacancies) {
                        $lines->push([$label, $group ?: 'Uncategorized', $vacancies]);
                    }
                }

                return [
                    'title'   => $title,
                    'columns' => ['Type', 'Industry Group', 'Total Vacancies'],
                    'rows'    => $lines,
                ];
            })(),

            'placement' => [
                'title'   => $title,
                'columns' => ['#', 'Name of Applicant', 'Gender', 'Position', 'Company', 'Date Hired'],
                'rows'    => $event
                    ? self::placementQuery($eventJobs, $event)->get()
                        ->values()
                        ->map(fn($a, $i) => [
                            $i + 1,
                            $fullName($a->jobseeker),
                            $a->jobseeker->sex ?? '',
                            $a->job->title ?? '',
                            $a->job->company->company_name ?? '',
                            $a->updated_at->format('Y-m-d'),
                        ])
                    : collect(),
            ],

            'top_employers' => [
                'title'   => $title,
                'columns' => ['#', 'Employer', 'Local/Overseas', 'Postings Brought', 'Vacancies Offered'],
                'rows'    => self::topEmployers($event, $overseasOnly)
                    ->values()
                    ->map(fn($entry, $i) => [
                        $i + 1,
                        $entry['employer']->company_name ?? 'Unknown Employer',
                        ($entry['employer']->is_overseas ?? false) ? 'Overseas' : 'Local',
                        $entry['posting_count'],
                        $entry['total_vacancies'],
                    ]),
            ],

            default => ['title' => $title, 'columns' => [], 'rows' => collect()],
        };
    }
}
