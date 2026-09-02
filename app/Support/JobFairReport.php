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
        'top_employers'     => 'Top 10 Occupation and Industry Share',
        'vacancy_list'      => 'List of Local Job Vacancies',
    ];

    // Ang tab nga dili nagsalig sa pagpili ug event.
    //
    // PESO Job Fair staff, 2026-09-02: ang Top 10 Employers, ang My Imported
    // Reports ug ang Archived Job Postings dili bahin sa usa ka fair — sila
    // ang naa dayon pag-abli sa page. Ang uban nga tab walay masulti kung
    // walay event nga gipili, apan kining tulo naay tubag kanunay.
    public const TABS_WITHOUT_EVENT = ['top_employers'];

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
        $companies = JobFairParticipant::with(['employer', 'jobFair'])
            ->where('job_fair_id', $eventId)
            ->where('confirmation_status', 'confirmed')
            ->get();

        // ── Pila ka bakante ang gidala sa matag kompanya.
        // ──
        // ── Ang papel nga gipasa sa DOLE naay NO. OF VACANCIES nga kolum ug
        // ── TOTAL sa ubos, mao nga ang numero kinahanglan naa sa laray. Usa
        // ── ka query para sa tibuok lamesa, dili usa kada kompanya. ──
        $vacancies = JobFairEmploymentRequest::where('job_fair_id', $eventId)
            ->get()
            ->groupBy('employer_id')
            ->map(fn($requests) => (int) Job::whereIn('job_qualifications_id', $requests->pluck('job_id'))->sum('slots'));

        return $companies->each(function (JobFairParticipant $participant) use ($vacancies) {
            $participant->vacancies = (int) ($vacancies[$participant->employer_id] ?? 0);
        });
    }

    // ───────────────────────────────
    // TAB — LIST OF LOCAL JOB VACANCIES
    // ───────────────────────────────
    //
    // Ang ikaduha nga papel sa kompanya, ug lahi siya sa una.
    //
    // Ang una (LIST OF AGENCIES) para sa DOLE: naay representative ug contact
    // info. Kini para sa Cagayan de Oro City Jobs Placement Bureau, ug tulo ka
    // kolum ra: kompanya, address, pila ka bakante. Duha ka opisina, duha ka
    // pangutana, mao nga duha ka lamesa — dili nila mahulip ang usag usa.

    /** Usa ka laray kada kompanya: ngalan, address, ug pila ka bakante. */
    public static function localVacancyList(?JobFairEvent $event, bool $overseasOnly): Collection
    {
        return self::reportJobs($event, $overseasOnly)
            ->filter(fn($job) => $job->company !== null)
            ->groupBy('company_id')
            ->map(function ($jobs) {
                $company = $jobs->first()->company;

                return [
                    'company'   => $company->company_name ?? 'None',
                    'address'   => collect([
                        $company->est_barangay ?? null,
                        $company->est_city_municipality ?? null,
                        $company->est_province ?? null,
                    ])->filter()->implode(', '),
                    'vacancies' => (int) $jobs->sum('slots'),
                    'overseas'  => (bool) ($company->is_overseas ?? false),
                ];
            })
            // Alpabeto, sama sa papel — mao ni ang han-ay nga gipangita sa
            // desk kung mangita siyag usa ka ngalan sa gi-print nga listahan.
            ->sortBy('company')
            ->values();
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

    /**
     * Pila ka tawo ang niapil sa fair, gibahin sa lokal ug overseas.
     *
     * Ang "Total No. of Registrants" sa ubos sa papel. Lahi siya sa kolum sa
     * ibabaw: didto ang aplikasyon ang gi-ihap, mao nga ang usa ka tawo nga
     * miduol sa tulo ka employer tulo didto — dinhi usa ra gihapon siya.
     *
     * Ang "both" nga jobseeker giihap sa duha, sama sa gibuhat sa tibuok
     * sistema: naa siya sa duha ka merkado, ug duha ka desk ang nagtubag para
     * niya.
     */
    public static function registrantTotals(int $eventId): array
    {
        $base = JobFairRegistration::where('job_fair_id', $eventId);

        return [
            'local' => (clone $base)->whereHas('jobseeker.nsrp',
                fn($n) => $n->whereIn('type', ['local', 'both']))->count(),
            'overseas' => (clone $base)->whereHas('jobseeker.nsrp',
                fn($n) => $n->whereIn('type', ['overseas', 'both']))->count(),
        ];
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
        // Walay event nga gipili: ang ranggo sa tanang fair, nga mao ang
        // gipangayo — "kinsa ang pinakadako nga nagdala ug bakante sa PESO",
        // dili "kinsa ang pinakadako sa Oktubre nga fair".
        $jobIds = $event
            ? self::eventJobIds($event->job_fair_events_id)
            : JobFairEmploymentRequest::pluck('job_id');

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
    // TAB 7 — TOP 10 OCCUPATION, AND THE INDUSTRY SHARE
    // ───────────────────────────────
    //
    // Ang papel nga gipasa sa PESO sa DOLE (Post JF, MARCH 2026) naay duha ka
    // lamesa niini nga panid, ug walay usa nila nga naghisgot ug employer:
    //
    //   NO. | OCCUPATION | NUMBER          — top 10 nga gipangita nga trabaho
    //   MAJOR INDUSTRY GROUP | QUANTITY | % SHARE
    //
    // Ang "Top 10 Employers" nga naa kaniadto tubag sa lain nga pangutana. Ang
    // gipangayo sa DOLE mao ang TRABAHO ug ang INDUSTRIYA, dili kinsa ang
    // nagdala niini.

    /** Ang gipangita nga trabaho, gi-ranggo sa gidaghanon sa bakante. */
    public static function topOccupations(?JobFairEvent $event, bool $overseasOnly, int $limit = 10): Collection
    {
        return self::reportJobs($event, $overseasOnly)
            // Ang titulo mao ang trabaho. Gi-normalize ang guhit ug ang
            // kaso, kay ang "Production Worker" ug ang "PRODUCTION WORKER"
            // usa ra ka trabaho ug dili duha ka laray sa report.
            ->groupBy(fn($job) => mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string) $job->title))))
            ->map(fn($jobs, $title) => [
                'occupation' => $title,
                'number'     => (int) $jobs->sum('slots'),
                'postings'   => $jobs->count(),
            ])
            ->filter(fn($row) => $row['occupation'] !== '')
            ->sortByDesc('number')
            ->take($limit)
            ->values();
    }

    /**
     * Ang bakante kada major industry group, uban ang bahin niini sa tibuok.
     *
     * Gibalik ang TANANG grupo, apil ang zero. Ang papel usa ka porma nga
     * naay nakaimprinta nga laray, ug ang blangko usa ka tubag: walay
     * Construction niini nga fair. Ang pagtago sa zero maghimo sa report nga
     * lain ang porma kada bulan.
     */
    public static function industryShares(?JobFairEvent $event, bool $overseasOnly): array
    {
        $jobs  = self::reportJobs($event, $overseasOnly);
        $total = (int) $jobs->sum('slots');

        $byGroup = $jobs->groupBy(fn($job) => $job->industry_group ?: 'Unclassified')
            ->map(fn($rows) => (int) $rows->sum('slots'));

        $rows = [];
        foreach (\App\Models\EmployerNsrpRegistration::INDUSTRY_GROUPS as $group) {
            $quantity = (int) ($byGroup[$group] ?? 0);
            $rows[$group] = [
                'group'    => $group,
                'quantity' => $quantity,
                'share'    => $total > 0 ? round($quantity / $total * 100, 2) : 0.0,
            ];
        }

        // Ang wala na-classify nga employer dili itago: kung wala siya
        // gilista, ang % share dili moabot sa 100 ug walay makasulti ngano.
        $unclassified = (int) ($byGroup['Unclassified'] ?? 0);

        return [
            'rows'         => $rows,
            'total'        => $total,
            'unclassified' => [
                'quantity' => $unclassified,
                'share'    => $total > 0 ? round($unclassified / $total * 100, 2) : 0.0,
            ],
        ];
    }

    /**
     * Ang ulohan sa papel: pila ka kompanya ug pila ka bakante, gibahin sa
     * lokal ug overseas.
     */
    public static function runDownTotals(?JobFairEvent $event, bool $overseasOnly): array
    {
        $jobs = self::reportJobs($event, $overseasOnly);

        $split = fn(bool $overseas) => $jobs->filter(
            fn($job) => (bool) ($job->company->is_overseas ?? false) === $overseas
        );

        $local    = $split(false);
        $overseas = $split(true);

        return [
            'companies'          => $jobs->pluck('company_id')->unique()->count(),
            'vacancies'          => (int) $jobs->sum('slots'),
            'local_companies'    => $local->pluck('company_id')->unique()->count(),
            'local_vacancies'    => (int) $local->sum('slots'),
            'overseas_companies' => $overseas->pluck('company_id')->unique()->count(),
            'overseas_vacancies' => (int) $overseas->sum('slots'),
        ];
    }

    /**
     * Ang Major Industry Group nga lamesa, gipatag para sa spreadsheet.
     *
     * Parehas nga upat ka ulohan sa papel, ug ang zero gisulat gihapon: ang
     * blangko usa ka tubag — walay Construction niini nga fair — ug ang report
     * kinahanglan parehas ug porma kada bulan aron siya matandi.
     */
    public static function industryShareRows(?JobFairEvent $event, bool $overseasOnly): Collection
    {
        $shares = self::industryShares($event, $overseasOnly);
        $rows   = collect();

        foreach (self::INDUSTRY_SECTIONS as $section => $groups) {
            $rows->push([$section, '', '']);

            foreach ($groups as $group) {
                $row = $shares['rows'][$group] ?? ['quantity' => 0, 'share' => 0];
                $rows->push([
                    $group,
                    $row['quantity'] ?: '',
                    $row['quantity'] ? number_format($row['share'], 2) . '%' : '',
                ]);
            }
        }

        if ($shares['unclassified']['quantity'] > 0) {
            $rows->push([
                'No industry group set',
                $shares['unclassified']['quantity'],
                number_format($shares['unclassified']['share'], 2) . '%',
            ]);
        }

        return $rows->push([
            'TOTAL',
            $shares['total'],
            $shares['total'] > 0 ? '100.00%' : '',
        ]);
    }

    /**
     * Ang upat ka ulohan sa papel, ug kung unsang grupo ang naa sa matag usa.
     *
     * Ang atoa nga INDUSTRY_GROUPS mao gyud ang listahan sa DOLE — usa ra ang
     * kalainan: gihiusa nato ang Agriculture ug ang Fishing sa usa ka linya.
     */
    public const INDUSTRY_SECTIONS = [
        'AGRICULTURE' => ['Agriculture, Hunting and Forestry, Fishing'],
        'INDUSTRY'    => ['Mining and Quarrying', 'Manufacturing', 'Construction'],
        'SERVICES'    => [
            'Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods',
            'Hotel and Restaurants',
            'Transport, Storage and Communications',
            'Financial Intermediation',
            'Real Estate, Renting and Business Activities',
            'Public Administration and Defense, Compulsory Social Security',
            'Education',
            'Health and Social Work',
            'Other Community, Social and Personal Activities',
            'Extra-territorial Organization and Bodies',
        ],
        'OVERSEAS MANPOWER SERVICES' => ['Overseas Manpower Services'],
    ];

    /**
     * Ang posting nga gibasa sa tulo ka lamesa sa ibabaw.
     *
     * Kung naay event, kadto ra nga fair. Kung wala, ang tanang posting nga
     * nadala na sa bisan unsang fair — mao kana ang "run down" nga gipangayo
     * sa DOLE sa dili pa ang sunod nga fair.
     */
    private static function reportJobs(?JobFairEvent $event, bool $overseasOnly): Collection
    {
        $jobIds = $event
            ? self::eventJobIds($event->job_fair_events_id)
            : JobFairEmploymentRequest::pluck('job_id');

        if ($jobIds->isEmpty()) {
            return collect();
        }

        return Job::with('company')
            ->whereIn('job_qualifications_id', $jobIds)
            ->when($overseasOnly, fn($q) => $q->whereHas('company', fn($c) => $c->where('is_overseas', true)))
            ->get();
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
                // Parehas nga kolum sa papel nga gipasa sa DOLE, ug ang duha
                // sa tumoy (Confirmed on / On DOLE list) gibilin: rekord sila
                // sa opisina, ug wala sila makasamok sa gi-print nga porma.
                'columns' => ['NO.', 'Name of Agency', 'Name of Representative', 'Address',
                              'Contact Info', 'No. of Vacancies', 'Local/Overseas',
                              'Confirmed on', 'On DOLE list'],
                // Usa ka klase, parehas sa screen ug sa papel: ang Job Fair
                // desk mokuha sa lokal, ang SRA sa overseas.
                'rows'    => self::confirmedCompanies($eventId)
                    ->filter(fn($p) => (bool) ($p->employer->is_overseas ?? false) === $overseasOnly)
                    ->sortBy(fn($p) => mb_strtolower($p->employer->company_name ?? ''))
                    ->values()
                    ->map(fn($p, $i) => [
                        $i + 1,
                        $p->employer->company_name ?? '',
                        $p->employer->contact_person ?? '',
                        collect([$p->employer->est_barangay ?? null,
                                 $p->employer->est_city_municipality ?? null,
                                 $p->employer->est_province ?? null])->filter()->implode(', '),
                        $p->employer->mobile_number ?? $p->employer->telephone_no ?? '',
                        $p->vacancies ?? 0,
                        ($p->employer->is_overseas ?? false) ? 'Overseas' : 'Local',
                        $p->responded_at?->format('Y-m-d') ?? '',
                        $p->confirmedBeforeCutoff() ? 'Yes' : 'No',
                    ]),
            ],

            'further_interview' => [
                'title'   => $title,
                // Parehas nga kolum sa papel. Ang duha sa tumoy blangko sa
                // tinuyo: ang tubag moabot usa ka bulan human sa fair, ug ang
                // papel gi-print ug gisulatan sa kamot.
                'columns' => ['No.', 'Name', 'Gender', 'Address', 'Tel/Cellphone Number',
                              'Job Position', 'Hiring Company', 'Local/Overseas',
                              'Status After 1 Month — Hired', 'Status After 1 Month — Not Hired'],
                'rows'    => self::furtherInterviewQuery($eventJobs, $overseasOnly)->get()
                    ->values()
                    ->map(fn($a, $i) => [
                        $i + 1,
                        $fullName($a->jobseeker),
                        $a->jobseeker->sex ?? '',
                        collect([$a->jobseeker->present_barangay ?? null,
                                  $a->jobseeker->present_city_municipality ?? null])->filter()->implode(', '),
                        $a->jobseeker->contact_number ?? '',
                        $a->job->title ?? '',
                        $a->job->company->company_name ?? '',
                        ($a->job->company->is_overseas ?? false) ? 'Overseas' : 'Local',
                        '',
                        '',
                    ]),
            ],

            'hots' => [
                'title'   => $title,
                'columns' => ['No.', 'Name', 'Gender', 'Address', 'Tel/Cellphone Number',
                              'Job Position', 'Hiring Company', 'Local/Overseas'],
                'rows'    => self::hotsQuery($eventJobs, $overseasOnly)->get()
                    ->values()
                    ->map(fn($a, $i) => [
                        $i + 1,
                        $fullName($a->jobseeker),
                        $a->jobseeker->sex ?? '',
                        collect([$a->jobseeker->present_barangay ?? null,
                                  $a->jobseeker->present_city_municipality ?? null])->filter()->implode(', '),
                        $a->jobseeker->contact_number ?? '',
                        $a->job->title ?? '',
                        $a->job->company->company_name ?? '',
                        ($a->job->company->is_overseas ?? false) ? 'Overseas' : 'Local',
                    ]),
            ],

            'summary' => (function () use ($eventId, $overseasOnly, $title) {
                $rows   = self::summaryRows($eventId, $overseasOnly);
                $totals = self::summaryTotals($rows);

                $lines = $rows->values()->map(fn($p, $i) => [
                    $i + 1,
                    $p->employer->company_name ?? '',
                    $p->vacancies, $p->interviewed, $p->female, $p->qualified, $p->hired,
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
                    'columns' => ['NO', 'Name of Employer', 'No. of Vacancies', 'No. of Applicants (Total)', 'No. of Applicants (Female)', 'Cluster Qualified', 'Hired on the Spot'],
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

            // ── Duha ka lamesa sa usa ka file.
            // ──
            // ── Ang papel naay Top 10 Occupation ug Major Industry Group sa
            // ── parehas nga panid, mao nga ang download pud. Ang blangko nga
            // ── laray tali nila mao ang nagbulag; ang ikaduhang ulohan gisulat
            // ── isip laray aron dili siya mawala sa CSV. ──
            'top_employers' => [
                'title'   => $title,
                'columns' => ['NO.', 'OCCUPATION', 'NUMBER'],
                'rows'    => self::topOccupations($event, $overseasOnly)
                    ->values()
                    ->map(fn($entry, $i) => [$i + 1, $entry['occupation'], $entry['number']])
                    ->concat([
                        ['', '', ''],
                        ['MAJOR INDUSTRY GROUP', 'QUANTITY', '% SHARE'],
                    ])
                    ->concat(self::industryShareRows($event, $overseasOnly)),
            ],

            'vacancy_list' => [
                'title'   => $title,
                'columns' => ['No.', 'Name of Company/Office', 'Address', 'No. of Vacancies'],
                'rows'    => self::localVacancyList($event, $overseasOnly)
                    ->map(fn($row, $i) => [$i + 1, $row['company'], $row['address'], $row['vacancies']])
                    ->push(['', 'TOTAL', '', self::localVacancyList($event, $overseasOnly)->sum('vacancies')]),
            ],

            default => ['title' => $title, 'columns' => [], 'rows' => collect()],
        };
    }
}
