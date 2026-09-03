<?php

namespace App\Http\Controllers;

use App\Models\EmployerNsrpRegistration;
use App\Models\User;
use App\Models\JobseekerRegistration;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffWebController extends Controller
{
    // ───────────────────────────────
    // HELPER
    // ───────────────────────────────
    private function authStaff()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'staff') {
            Auth::logout();
            return null;
        }
        return $user;
    }

    // ───────────────────────────────
    // DASHBOARD
    // ───────────────────────────────
    public function dashboard()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;

        // Shared Today's Activities
        $todayJobFair = \App\Models\JobFairEvent::whereIn('status', ['ongoing', 'upcoming'])
            ->whereDate('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->take(5)->get();
        $todayInhouse = \App\Models\InhouseSchedule::where('status', 'accepted')
            ->whereDate('confirmed_date', now()->toDateString())
            ->take(5)->get();

        if ($staffRole === 'sra') {
            // ── Ang static nga quick-link cards sa ubos gitangtang, parehas sa
            // ── LRA: gi-usab lang nila ang sidebar sa porma sa card ug walay
            // ── numero nga gidala. Ang lima ka stat card sa ibabaw mao na ang
            // ── dashboard — ang matag usa nagsulti kung unsay naghulat sa desk
            // ── karon. Wala pay link ang mga card; hulaton sa kung asa gyud
            // ── mo-abli ang matag usa. ──

            // Ang overseas nga ahensya ra ang sa SRA. Usa ka basihan dinhi,
            // gigamit sa duha ka card sa ubos.
            $overseasEmployers = EmployerNsrpRegistration::query()
                ->whereHas('employer', fn($q) => $q->where('role', 'company'))
                ->where('is_overseas', true);

            // ── CARD 1 — Pending In-house Schedule ──
            // Parehas gyud nga basihan sa In-house Schedule nga page: duha ka
            // pultahan padulong sa opisina, ang schedule-only nga hangyo ug ang
            // in-house nga posting. Kung usa ra ang ihapon dinhi, ang card ug
            // ang listahan magsultig lain-laing numero.
            $pendingInhouse = \App\Models\InhouseSchedule::where('status', 'pending')
                    ->whereHas('employer', fn($q) => $q->where('is_overseas', true))
                    ->count()
                + \App\Models\Job::where('posting_status', 'pending')
                    ->where('schedule_type', 'inhouse')
                    ->whereHas('company', fn($q) => $q->where('is_overseas', true))
                    ->count();

            // ── CARD 2 — On-going Job Activities ──
            // Ang nahitabo KARON, sa tulo ka matang sa job activity: ang fair
            // nga nagpadayon, ang in-house nga gi-accept para karong adlawa, ug
            // ang company interview nga karon ang petsa.
            $ongoingActivities = \App\Models\JobFairEvent::where('status', 'ongoing')->count()
                + \App\Models\InhouseSchedule::where('status', 'accepted')
                    ->whereDate('confirmed_date', now()->toDateString())
                    ->whereHas('employer', fn($q) => $q->where('is_overseas', true))
                    ->count()
                + \App\Models\Job::where('schedule_type', 'company_interview')
                    ->whereDate('preferred_date', now()->toDateString())
                    ->whereHas('company', fn($q) => $q->where('is_overseas', true))
                    ->count();

            // ── CARD 3 — Pending Company Interview ──
            // Ang company interview walay approval nga agian — buhi na siya
            // pag-post — mao nga ang "pending" dinhi kay ang wala pa mahitabo:
            // karon o sa umaabot pa ang petsa.
            $pendingCompanyInterview = \App\Models\Job::where('schedule_type', 'company_interview')
                ->whereDate('preferred_date', '>=', now()->toDateString())
                ->whereHas('company', fn($q) => $q->where('is_overseas', true))
                ->count();

            // ── CARD 4 — Total Overseas Employer ──
            // Parehas nga kahulogan sa Registered Employer: kompanya, overseas,
            // gi-approve nga papel, dili inactive.
            $overseasEmployerTotal = (clone $overseasEmployers)
                ->whereHas('requirement', fn($q) => $q->where('status', 'approved'))
                ->whereNull('dormant_at')
                ->count();

            // ── CARD 5 — Total Jobseeker (Overseas) ──
            $jobseekerTotal = JobseekerRegistration::whereHas('nsrp', fn($q) =>
                $q->whereIn('type', ['overseas', 'both'])
            )->count();

            // ── CARD 6 — Total Jobs ──
            // Parehas gyud nga basihan sa Job Vacancies Solicited nga report,
            // nga mao pud ang ablihan sa pagpindot niini nga card.
            $totalJobsSolicited = $this->overseasSolicitedVacancies()->count();

            return view('staff.sra.dashboard', compact(
                'pendingInhouse', 'ongoingActivities', 'pendingCompanyInterview',
                'overseasEmployerTotal', 'jobseekerTotal', 'totalJobsSolicited',
                'todayJobFair', 'todayInhouse'
            ));

        } elseif ($staffRole === 'lra') {
            // ── Ang stat card mao na ang quick link. Ang static nga cards sa
            // ── ubos gitangtang: gi-usab lang nila ang sidebar sa porma sa
            // ── card, ug walay numero nga gidala. Ang duha nga nahibilin
            // ── nagsulti ug numero UG mo-abli sa page nga gi-ihap.
            $query = JobseekerRegistration::with(['user', 'nsrp'])
                ->whereHas('nsrp', fn($q) => $q->whereIn('type', ['local', 'both']));

            $jobseekerTotal = $query->count();
            $recent         = $query->latest()->take(5)->get();

            // ── Parehas gyud nga basihan sa Registered Employer nga tab:
            // ── kompanya, local, gi-approve nga papel, dili inactive. Kung
            // ── lahi ang pag-ihap dinhi, ang card ug ang listahan nga
            // ── giablihan sa pagpindot magsultig lain-laing numero. ──
            $employerTotal = EmployerNsrpRegistration::query()
                ->whereHas('employer', fn($q) => $q->where('role', 'company'))
                ->where('is_overseas', false)
                ->whereHas('requirement', fn($q) => $q->where('status', 'approved'))
                ->whereNull('dormant_at')
                ->count();

            return view('staff.lra.dashboard', compact(
                'employerTotal', 'jobseekerTotal', 'recent', 'todayJobFair', 'todayInhouse'
            ));

        } elseif ($staffRole === 'job_fair') {
            // ── Ang "Total Notifications Sent" gitangtang. Ang pagpadala ug
            // ── text manual na ug gikan na sa bakante mismo, mao nga ang
            // ── kinatibuk-ang ihap sa na-send wala nay trabaho nga gitudlo.
            // ── Ang tulo ka numero dinhi mao ang gitrabaho sa desk karon. ──

            // Ang bakante nga naghulat pa ug fair. Parehas nga basihan sa
            // Job Fair Vacancies nga page, aron ang card ug ang listahan
            // magsultig usa ra ka numero.
            $pendingVacancies = $this->jobFairPendingPostings()->count();

            // Ang fair nga nagpadayon karon.
            $ongoingEvents = \App\Models\JobFairEvent::where('status', 'ongoing')
                ->orderBy('event_date')
                ->get();

            // Pila ka employer ang tinuod nga niapil sa usa ka fair. Gi-ihap
            // ang kompanya, dili ang laray: usa ka employer nga niapil sa
            // tulo ka fair usa ra gihapon ka employer.
            $participatingEmployers = \App\Models\JobFairParticipant::whereIn(
                    'confirmation_status', ['accepted', 'confirmed']
                )->distinct()->count('employer_id');

            return view('staff.job_fair.dashboard', compact(
                'pendingVacancies', 'ongoingEvents', 'participatingEmployers',
                'todayJobFair', 'todayInhouse'
            ));

        } elseif ($staffRole === 'job_vacancy') {
            // ── Ang tulo ka stat card kay quick link na sa Employers page,
            // ── mao nga ang numero kinahanglan pareho gyud sa makita nila
            // ── didto. Kaniadto nag-ihap ni ug requirement row sa tibuok
            // ── sistema — apil ang overseas, nga sa SRA man — mao nga ang
            // ── card nag-ingon ug usa ka numero ug ang tab lain. Ang
            // ── basihan karon pareho sa employers(): kompanya, local, ug
            // ── ang parehas nga kahulogan sa kada tab. ──
            $companies = EmployerNsrpRegistration::query()
                ->whereHas('employer', fn($q) => $q->where('role', 'company'))
                ->where('is_overseas', false);

            // Pending Employer Account: wala pay gipasa nga requirement, o gipasa na apan
            // wala pa ma-desisyonan.
            $pendingCount = (clone $companies)
                ->where(fn($q) =>
                    $q->whereDoesntHave('requirement')
                      ->orWhereHas('requirement', fn($r) => $r->where('status', 'pending'))
                )->count();

            // Registered Employer: gi-ihap ang kompanya, dili ang papel.
            $approvedCount = (clone $companies)
                ->whereHas('requirement', fn($r) => $r->where('status', 'approved'))
                ->whereNull('dormant_at')
                ->count();

            // Ang mga approved nga naay papel nga mahurot sulod sa usa ka
            // semana. Kini ang gikinahanglan ug aksyon karon — dili ang
            // rejected, nga naa naman sa Pending Employer Account tab ug gihulat na lang
            // ang bag-ong pasa sa employer.
            $expiringCount = (clone $companies)
                ->whereHas('requirement', fn($r) => $r->where('status', 'approved')->expiringSoon())
                ->whereNull('dormant_at')
                ->count();

            return view('staff.job_vacancy.dashboard', compact(
                'pendingCount', 'approvedCount', 'expiringCount',
                'todayJobFair', 'todayInhouse'
            ));
        }

        return redirect()->route('login');
    }

    // ───────────────────────────────
    // REGISTRATIONS LIST (SRA / LRA)
    // ───────────────────────────────
    public function registrations()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        if (!in_array($staffRole, ['lra', 'sra'], true)) return redirect()->route('staff.dashboard');

        $search     = request('search');
        $occupation = request('occupation');
        $education  = request('education');

        $all = $this->jobseekerPool($staffRole, $search, $occupation, $education);

        // Manual nga pagination: ang educational attainment gikuha sa PHP, dili
        // sa SQL - usa siya ka JSON nga mapa sa lima ka lebel, ug ang gipangita
        // mao ang pinakataas nga napun-an. Wala kana masulti sa usa ka WHERE.
        $perPage = 10;
        $page    = \Illuminate\Pagination\Paginator::resolveCurrentPage();

        $registrations = new \Illuminate\Pagination\LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        $registrations->withQueryString();

        $occupationOptions = $this->occupationOptions($staffRole);
        $educationOptions  = \App\Support\JobseekerProfile::EDUCATION_LEVELS;

        $view = $staffRole === 'sra' ? 'staff.sra.registrations.index' : 'staff.lra.registrations.index';

        return view($view, compact(
            'registrations', 'search', 'occupation', 'education',
            'occupationOptions', 'educationOptions'
        ));
    }

    /**
     * The jobseekers this desk answers for, narrowed by what was asked for.
     *
     * PESO LRA, 2026-08-26: the desk is asked for candidates by trade and by
     * schooling - "give me the caregivers", "give me the college graduates".
     * Both are answers the jobseeker already gave on the NSRP form, so nothing
     * new is asked of anyone; they were only never searchable.
     */
    private function jobseekerPool(string $staffRole, ?string $search, ?string $occupation, ?string $education)
    {
        $allowedClass = $staffRole === 'sra' ? ['overseas', 'both'] : ['local', 'both'];

        $rows = JobseekerRegistration::with(['user', 'applications', 'nsrp'])
            ->whereHas('nsrp', fn($q) => $q->whereIn('type', $allowedClass))
            ->when($search, fn($q) => $q->where(function ($outer) use ($search) {
                $outer->where('first_name', 'like', "%{$search}%")
                      ->orWhere('surname', 'like', "%{$search}%")
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                                                       ->orWhere('email', 'like', "%{$search}%"));
            }))
            // Ang preferred_occupations usa ka JSON nga lista. Ang LIKE sa
            // hilaw nga teksto igo na dinhi ug modagan sa MariaDB nga walay
            // JSON nga function.
            ->when($occupation, fn($q) => $q->whereHas('nsrp', fn($n) =>
                $n->where('preferred_occupations', 'like', '%' . $occupation . '%')
            ))
            ->latest()
            ->get();

        if ($education) {
            $rows = $rows->filter(fn($registration) =>
                \App\Support\JobseekerProfile::highestEducation($registration->nsrp) === $education
            );
        }

        return $rows->values();
    }

    /** Ang trabaho nga tinuod nga gisulat sa mga jobseeker niini nga desk. */
    private function occupationOptions(string $staffRole): array
    {
        $allowedClass = $staffRole === 'sra' ? ['overseas', 'both'] : ['local', 'both'];

        return \App\Models\JobseekerNsrpRegistration::whereIn('type', $allowedClass)
            ->pluck('preferred_occupations')
            ->flatMap(fn($list) => is_array($list) ? $list : (json_decode($list ?? '[]', true) ?: []))
            ->filter()
            ->map(fn($occupation) => trim($occupation))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /** Ang gipakita nga listahan, isip Excel nga file. */
    public function exportRegistrations()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        if (!in_array($staffRole, ['lra', 'sra'], true)) return redirect()->route('staff.dashboard');

        $search     = request('search');
        $occupation = request('occupation');
        $education  = request('education');

        $rows = $this->jobseekerPool($staffRole, $search, $occupation, $education);

        $preamble = array_values(array_filter([
            ['PESO Cagayan de Oro - Registered Jobseekers'],
            ['Desk', $staffRole === 'sra' ? 'Overseas (SRA)' : 'Local (LRA)'],
            $occupation ? ['Preferred occupation', $occupation] : null,
            $education  ? ['Educational attainment', $education] : null,
            $search     ? ['Search', $search] : null,
            ['Total', $rows->count()],
            ['Generated', now()->format('Y-m-d H:i')],
        ]));

        return \App\Support\ExcelExport::stream(
            'peso-jobseekers-' . now()->format('Ymd') . '.xlsx',
            ['#', 'Name', 'Sex', 'Age', 'Contact', 'Classification', 'Educational Attainment', 'Preferred Occupations', 'Registered'],
            $rows->values()->map(fn($registration, $i) => [
                $i + 1,
                trim(($registration->first_name ?? '') . ' ' . ($registration->surname ?? '')),
                $registration->sex,
                $registration->age,
                $registration->contact_number,
                ucfirst($registration->nsrp->type ?? ''),
                \App\Support\JobseekerProfile::highestEducation($registration->nsrp) ?: 'None',
                collect($registration->nsrp->preferred_occupations ?? [])->filter()->implode(', '),
                optional($registration->created_at)->format('Y-m-d'),
            ]),
            $preamble
        );
    }

    // ───────────────────────────────
    // VIEW SINGLE REGISTRATION
    // ───────────────────────────────
    public function viewRegistration($id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $registration = JobseekerRegistration::with(['user', 'nsrp.workExperiences'])->findOrFail($id);
$nsrp = $registration->nsrp;
        $staffRole    = $staff->staff_role;

        // ── Ang gisulat sa jobseeker sa NSRP nga porma kay iyang giingon lang:
        // ── wala kadto ni-agi sa sistema, ug walay lain nga makapamatuod niini.
        // ── Kini nga listahan lahi — kini ang trabaho nga naagi mismo sa PESO,
        // ── gikan sa aplikasyon nga gitiman-an sa employer ug 'hired'. Mao nga
        // ── managbulag sila sa VIII: usa ka giingon, usa ka natala.
        $placements = \App\Models\Application::with('job.company')
            ->where('jobseeker_id', $registration->jobseeker_registrations_id)
            ->where('status', 'hired')
            ->orderByDesc('hired_at')
            ->get();

        $isOverseas = $staffRole === 'sra';

        // ── Ang listahan sa bakante gigamit ra sa porma nga "Apply to a Job
        // ── Posting", ug kana nga porma para sa walk-in ra. Ang jobseeker nga
        // ── siya mismo ang nag-rehistro naay account ug siya na ang mo-apply,
        // ── mao nga walay porma sa iyang panid ug walay hinungdan nga pangitaon
        // ── ang mga bakante. ──
        $openJobs = collect();

        if ($registration->is_walk_in) {
            $existingAppliedJobIds = \App\Models\Application::where('jobseeker_id', $registration->jobseeker_registrations_id)->pluck('job_id')->toArray();

            $openJobs = \App\Models\Job::with('company')
                ->where('status', 'open')
                ->whereHas('company', fn($q) => $q->where('is_overseas', $isOverseas))
                ->whereNotIn('job_qualifications_id', $existingAppliedJobIds)
                ->where(function ($q) {
                    $q->whereNull('deadline')->orWhereDate('deadline', '>=', now()->toDateString());
                })
                ->latest()
                ->get();
        }

        if ($staffRole === 'sra') {
    return view('staff.sra.registrations.show', compact('registration', 'nsrp', 'openJobs', 'placements'));
} elseif ($staffRole === 'lra') {
    return view('staff.lra.registrations.show', compact('registration', 'nsrp', 'openJobs', 'placements'));
}

        return redirect()->route('staff.dashboard');
    }

    // ───────────────────────────────
    // JOB FAIR — VIEW ONLY (LRA/SRA)
    // ───────────────────────────────
    /**
     * Who turned up for what — the jobseekers who joined an in-house interview
     * or registered for a job fair.
     *
     * Split by the jobseeker's own classification, not the employer's. LRA
     * answers for the local jobseeker and SRA for the one bound overseas, and
     * "Both" belongs to each of them: that person is in both markets, so both
     * desks have to be able to see them.
     */
    public function participants()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        if (!in_array($staffRole, ['lra', 'sra'], true)) return redirect()->route('staff.dashboard');

        // ── Ang LRA nagbasa na ug lain nga butang dinhi.
        // ──
        // ── Kining tab gitawag na ug Job Fair, ug ang gipangita didto mao ang
        // ── EMPLOYER nga niapil sa usa ka fair, dili ang jobseeker. Ang SRA
        // ── naa nay kaugalingong Job Fair nga tab nga naghimo niini, mao nga
        // ── wala siya giusab — ang iyang Participants nagpabilin. ──
        if ($staffRole === 'lra') {
            return $this->jobFairEmployerList();
        }

        $tab    = request('tab', 'inhouse') === 'jobfair' ? 'jobfair' : 'inhouse';
        $search = request('search');

        $allowedClass = $staffRole === 'lra' ? ['local', 'both'] : ['overseas', 'both'];

        $byClassification = fn($q) => $q->whereHas('nsrp', fn($n) => $n->whereIn('type', $allowedClass));

        $byName = function ($q) use ($search) {
            if (!$search) return;
            $q->where(fn($n) => $n->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('surname', 'like', "%{$search}%"));
        };

        // ── IN-HOUSE — the jobseeker said yes to taking part. ──
        $inhouse = \App\Models\Application::with(['job.company', 'jobseeker.nsrp'])
            ->where('inhouse_participation', 'accepted')
            ->whereHas('job', fn($j) => $j->where('schedule_type', 'inhouse'))
            ->whereHas('jobseeker', $byClassification)
            ->when($search, fn($q) => $q->whereHas('jobseeker', $byName))
            ->latest()
            ->paginate(5, ['*'], 'inhousePage')
            ->withQueryString();

        // ── JOB FAIR — registered for the event. ──
        $jobfair = \App\Models\JobFairRegistration::with(['jobFair', 'jobseeker.nsrp'])
            ->whereHas('jobseeker', $byClassification)
            ->when($search, fn($q) => $q->whereHas('jobseeker', $byName))
            ->latest()
            ->paginate(5, ['*'], 'jobfairPage')
            ->withQueryString();

        return view('staff.participants.index', compact(
            'staffRole', 'tab', 'search', 'inhouse', 'jobfair'
        ));
    }

    /**
     * The employers on one job fair, for the LRA's Job Fair tab.
     *
     * One fair at a time, picked from a dropdown: an employer's answer only
     * means anything against the fair it was asked about, so a list of every
     * participation ever would put three answers from one company side by side
     * with nothing to tell them apart.
     *
     * Local employers only, the same split the rest of this desk's pages use.
     */
    private function jobFairEmployerList()
    {
        $search = request('search');

        // Pinakabag-o ang una: ang fair nga giandam karon mao ang gipangutana,
        // dili ang natapos na sa miaging tuig.
        $events = \App\Models\JobFairEvent::orderByDesc('event_date')
            ->orderByDesc('job_fair_events_id')
            ->get();

        // Ang gipili gikan sa URL; kung wala, ang pinakabag-o nga fair. Ang
        // page dili gyud maghulat ug pagpili sa dili pa siya magpakita ug bisan
        // unsa — ang lamesa naa gihapon bisan walay event nga nahimo pa.
        $eventId = (int) request('event') ?: null;
        $event   = $eventId
            ? $events->firstWhere('job_fair_events_id', $eventId)
            : $events->first();

        // ── Ang mitubag ug oo ra. Ang naghulat pa, ang mibalibad ug ang wala
        // ── mitubag dili sila apil sa fair, ug ang pagbutang nila sa parehas
        // ── nga lamesa naghimo sa listahan nga dili masaligan isip tubag sa
        // ── "kinsa ang moadto". Ang lokal moadto dayon sa 'confirmed';
        // ── ang 'accepted' naa dinhi kay didto mohunong ang oo sa overseas —
        // ── walay lokal nga mahulog didto, apan walay laray nga mawala kung
        // ── mausab kana. ──
        $participants = $event
            ? \App\Models\JobFairParticipant::with('employer.employer')
                ->where('job_fair_id', $event->job_fair_events_id)
                ->whereIn('confirmation_status', ['accepted', 'confirmed'])
                ->whereHas('employer', fn($e) => $e->where('is_overseas', false))
                ->when($search, fn($q) => $q->whereHas('employer', fn($e) =>
                    $e->where('company_name', 'like', "%{$search}%")))
                ->get()
                ->sortBy(fn($row) => $row->employer->company_name ?? '')
                ->values()
            : collect();

        // Pila ka bakante ang gidala sa matag employer ngadto niini nga fair.
        // Usa ka query para sa tibuok lamesa, dili usa kada laray.
        $vacancyCounts = $event
            ? \App\Models\JobFairEmploymentRequest::where('job_fair_id', $event->job_fair_events_id)
                ->selectRaw('employer_id, COUNT(*) as total')
                ->groupBy('employer_id')
                ->pluck('total', 'employer_id')
            : collect();

        return view('staff.participants.jobfair', [
            'events'        => $events,
            'event'         => $event,
            'participants'  => $participants,
            'vacancyCounts' => $vacancyCounts,
            'search'        => $search,
        ]);
    }

    // ───────────────────────────────
    // OVERSEAS LINE-UP — SRA picks who goes to the fair
    // ───────────────────────────────

    /**
     * The overseas agencies eligible for a fair, and which of them SRA has put on it.
     *
     * PESO SRA, 2026-08-26: the head of the office decides which agencies are
     * brought to a job fair, and SRA asks her before adding them. That
     * conversation happens in the office, so the system cannot hold the
     * permission itself — only who acted on it. Which is why nothing here is
     * automatic: an overseas agency joins a fair because a person put them on
     * it, and their name is on the record.
     */
    /**
     * The overseas line-up shown inside the SRA's Job Fair tab.
     *
     * Returns [events, chosen event, one row per eligible agency (with its
     * invitation, if it has one), the invitations on this fair, the agencies
     * awaiting a decision, the industries that can be picked, the one picked,
     * and how many agencies are still uninvited].
     * It had a page of its own until the office asked for one tab fewer; the
     * work belongs to the fair, so it is drawn with the fair.
     */
    private function overseasLineupData(): array
    {
        // Ang fair nga wala pa nahuman ug modawat ug overseas. Ang uban walay
        // pulos dinhi — walay lugar nga mapilian.
        $events = \App\Support\JobFairInvites::upcomingEvents()
            ->filter(fn($event) => $event->catersTo(true))
            ->values();

        $eventId = request('lineup_event');
        $event   = $eventId
            ? $events->firstWhere('job_fair_events_id', (int) $eventId)
            : $events->first();

        $onTheList = $event
            ? \App\Models\JobFairParticipant::with(['employer', 'invitedBy', 'sraDecidedBy'])
                ->where('job_fair_id', $event->job_fair_events_id)
                ->whereHas('employer', fn($q) => $q->where('is_overseas', true))
                ->get()
            : collect();

        // ── ANG INDUSTRIYA NGA MAPILIAN ──
        //
        // Ang fair mismo ang nagsulti kung unsang industriya ang iyang gipangita,
        // ug kana ang mapilian. Ang fair nga walay gipili nga industriya modawat
        // sa tanan, mao nga ang lista kuhaon gikan sa mga ahensya nga naa: usa ka
        // pagpili nga walay laray sa likod niya kay pagpili nga dili pilion.
        $eligible = $event
            ? \App\Support\JobFairInvites::eligibleFor($event)
                ->filter(fn($employer) => (bool) $employer->is_overseas)
                ->sortBy(fn($employer) => mb_strtolower((string) $employer->company_name))
                ->values()
            : collect();

        $industries = collect((array) ($event->target_industries ?? []))
            ->whenEmpty(fn() => $eligible->pluck('industry_group'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Ang gipili. Kung dili siya sakop sa mapilian, isipon nga walay gipili —
        // ang usa ka industriya nga gisulod pinaagi sa URL dili makapakita ug
        // ahensya nga dili angay sa fair.
        $industry = request('lineup_industry');
        if (!$industries->contains($industry)) {
            $industry = null;
        }

        if ($industry) {
            $eligible = $eligible
                ->filter(fn($employer) => $employer->industry_group === $industry)
                ->values();
        }

        // ── USA KA LAMESA ──
        //
        // Duha ka lamesa ang naa dinhi kaniadto — ang na-invite na ug ang
        // mahimo pang i-invite — ug ang parehas nga ahensya molukso gikan sa
        // ubos ngadto sa ibabaw pag-pindot sa buton. Usa na lang: ang tanan nga
        // ahensya nga haom niining fair, na-invite na o wala pa. Ang gidala sa
        // matag laray mao ang iyang tubag, kung naa, ug ang buton motungha lang
        // sa wala pa napadad-i.
        $byEmployer = $onTheList->keyBy('employer_id');

        $rows = $eligible->map(fn($employer) => (object) [
            'employer'    => $employer,
            'participant' => $byEmployer->get($employer->employer_nsrp_registrations_id),
        ])->values();

        $available = $rows->filter(fn($row) => $row->participant === null)->values();

        // ── Ang naghulat sa desisyon sa SRA — mitubag na sila ug oo.
        // ──
        // ── Gilain sila sa kinatibuk-ang listahan kay lahi ang gipangayo nila:
        // ── ang listahan sa taas basahon, kini aksyonan. Kung tapadon sila,
        // ── ang tulo ka ahensya nga naghulat mahulog sa taliwala sa kaluhaan
        // ── nga wala na magkinahanglan ug bisan unsa. ──
        $awaiting = $onTheList
            ->filter(fn($participant) => $participant->awaitingSelection())
            ->sortBy('responded_at')
            ->values();

        return [$events, $event, $rows, $onTheList, $awaiting, $industries, $industry, $available->count()];
    }

    /** Put the chosen agencies on the fair, and record on whose word. */
    public function inviteOverseasToJobFair(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if ($staff->staff_role !== 'sra') return redirect()->route('staff.dashboard');

        $event = \App\Models\JobFairEvent::findOrFail($id);

        if ($event->status === 'completed') {
            return back()->with('error', 'This job fair is over. No further invitations can be sent.');
        }

        if (!$event->catersTo(true)) {
            return back()->with('error', 'This job fair does not cater to overseas employers.');
        }

        $request->validate([
            'employer_ids'     => ['required', 'array', 'min:1'],
            'employer_ids.*'   => ['integer'],
            'permission_note'  => ['required', 'string', 'max:255'],
        ], [
            'employer_ids.required'    => 'Select at least one agency to add.',
            'permission_note.required' => 'Record who cleared this and when — it is the only trace the permission leaves.',
        ]);

        // Gitandi batok sa listahan mismo, dili sa exists: ang employer nga
        // participant na, o wala pa na-approve ang requirements, o lokal, dili
        // gyud angay makasulod pinaagi sa usa ka gi-usab nga porma.
        $available = \App\Support\JobFairInvites::notYetInvited($event, true);
        $chosen    = $available->whereIn('employer_nsrp_registrations_id', $request->employer_ids)->values();

        if ($chosen->isEmpty()) {
            return back()->with('error', 'None of those agencies can be added to this job fair.');
        }

        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();

        $sent = \App\Support\JobFairInvites::invite(
            $event,
            $chosen,
            $staffRecord?->staff_id,
            $request->permission_note
        );

        $window = (int) config('peso.jobfair.confirm_window_days');

        return back()->with('success', $sent . ' agency(ies) added to ' . $event->title . '. They have '
            . $window . ' days to confirm, until ' . now()->addDays($window)->format('M d, Y') . '.');
    }

    /**
     * Let the agencies that said yes into the fair, or turn them away.
     *
     * PESO SRA, 2026-09-01: the invitation and the slot are two decisions. SRA
     * invites after asking the PESO head, the agency answers, and then SRA
     * looks at who answered and takes the ones the fair can hold. Without this
     * step the agency's own yes was the whole decision, and the desk that did
     * the inviting had no say in what came back.
     *
     * Only an agency sitting at 'accepted' can be decided on. A row that is
     * already confirmed, declined by the agency itself, or still unanswered is
     * not waiting for anybody, and pressing a button here must not rewrite it.
     */
    public function decideOverseasSelection(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if ($staff->staff_role !== 'sra') return redirect()->route('staff.dashboard');

        $request->validate([
            'decision' => ['required', 'in:confirmed,not_selected'],
            // Ang rason gikinahanglan lang kung ang ahensya ipahawa. Ang
            // pagpasulod nagsulti sa iyang kaugalingon; ang pagbalibad dili —
            // ug ang ahensya nga mitubag ug oo angay masayod ngano.
            'reason'   => ['required_if:decision,not_selected', 'nullable', 'string', 'max:255'],
        ], [
            'reason.required_if' => 'Say why this agency is not being brought to the fair.',
        ]);

        $participant = \App\Models\JobFairParticipant::with(['jobFair', 'employer'])
            ->findOrFail($id);

        if (!$participant->employer || !$participant->employer->is_overseas) {
            return back()->with('error', 'This employer is not an overseas agency.');
        }

        if (!$participant->awaitingSelection()) {
            return back()->with('error', 'That agency is not waiting for a decision.');
        }

        if ($participant->jobFair && $participant->jobFair->event_date->isPast()) {
            return back()->with('error', 'This job fair has already taken place.');
        }

        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
        $confirmed   = $request->decision === 'confirmed';

        $participant->update([
            'confirmation_status' => $request->decision,
            'sra_decided_by'      => $staffRecord?->staff_id,
            'sra_decided_at'      => now(),
            'sra_decision_note'   => $request->reason,
        ]);

        $event = $participant->jobFair;

        \App\Models\Announcement::sendToEmployers([
            'type'           => $confirmed ? 'job_fair_slot_confirmed' : 'job_fair_not_selected',
            'title'          => $confirmed ? 'Job Fair Slot Confirmed 🎊' : 'Job Fair Slot Not Available',
            'message'        => $confirmed
                ? 'PESO has confirmed your slot for ' . $event->title . ' on '
                  . $event->event_date->format('M d, Y') . ' at ' . $event->venue
                  . '. Please post a job vacancy for this event.'
                : 'PESO could not bring your agency to ' . $event->title . ' on '
                  . $event->event_date->format('M d, Y') . '. Reason: ' . $request->reason
                  . ' You may still be invited to a future job fair.',
            'reference_type' => 'job_fair',
            'reference_id'   => $event->job_fair_events_id,
        ], [$participant->employer_id]);

        // Ang jobseeker giingnan lang karon, dili sa pagtubag sa ahensya: karon
        // pa siya tinuod nga moadto sa fair.
        if ($confirmed) {
            $jobseekerIds = \App\Models\Application::whereHas('job', fn($q) =>
                $q->where('company_id', $participant->employer_id)
            )->pluck('jobseeker_id')->unique();

            \App\Models\Announcement::sendToJobseekers([
                'type'           => 'job_fair_invitation',
                'title'          => 'Job Fair Invitation 🎉',
                'message'        => $participant->employer->company_name . ' has confirmed participation in '
                                    . $event->title . ' on ' . $event->event_date->format('M d, Y')
                                    . ' at ' . $event->venue . '. You are invited to attend!',
                'reference_type' => 'job_fair',
                'reference_id'   => $event->job_fair_events_id,
            ], $jobseekerIds);
        }

        $name = $participant->employer->company_name;

        return back()->with('success', $confirmed
            ? $name . ' is now confirmed for ' . $event->title . '.'
            : $name . ' was not selected for ' . $event->title . '. The agency has been told why.');
    }

    public function jobFairViewOnly()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if (!in_array($staff->staff_role, ['lra', 'sra', 'job_vacancy'])) return redirect()->route('staff.dashboard');

        $staffRole = $staff->staff_role;
        $events = \App\Models\JobFairEvent::orderByDesc('event_date')->paginate(3, ['*'], 'event_page');

        $jobs = null;
        $totalApprovedJobs = 0;

        // ── Ang gipili nga fair. ──
        //
        // PESO, 2026-08-30: kining tab kay monitoring — ang pag-approve sa
        // posting naa sa Job Fair desk, dili dinhi. Mao nga ang gilista mao ra
        // ang APPROVED, ug ang pangutana sa desk kay "unsa ang naa sa fair nga
        // ni" — busa usa ka dropdown sa event, dili usa ka sala sa posting
        // status. Ang pending ug rejected wala na: wala silay mahimo niini.
        $eventId = request('event_id');

        // ── Job Vacancy ra ang naay lista sa posting dinhi.
        // ──
        // ── PESO SRA: gitangtang ang Job Fair Postings sa SRA. Monitoring ra
        // ── siya — ang pag-approve iya sa Job Fair desk — ug ang tab niya karon
        // ── usa ra ka butang: ang pag-imbita sa ahensya nga haom sa fair. Ang
        // ── LRA nagbasa gihapon sa events nga lamesa sa ubos. ──
        if ($staffRole === 'job_vacancy') {
            $isOverseas = false;

            $baseJobQuery = \App\Models\Job::where('schedule_type', 'job_fair')
                ->where('posting_status', 'approved')
                ->whereHas('company', fn($n) => $n->where('is_overseas', $isOverseas));

            $totalApprovedJobs = (clone $baseJobQuery)->count();

            $jobs = (clone $baseJobQuery)->with('company')
                ->when($eventId, fn($q) => $q->where('requested_job_fair_id', $eventId))
                ->latest()
                ->paginate(5, ['*'], 'job_page')
                ->withQueryString();
        }

        // Ang tanang fair nga mapilian sa dropdown — bag-o ang una, kay didto
        // man ang trabaho.
        $jobFairOptions = \App\Models\JobFairEvent::orderByDesc('event_date')
            ->get(['job_fair_events_id', 'title', 'event_date']);

        // ── Ang overseas nga line-up, sulod na niining page.
        // ──
        // ── Kaniadto naa siyay kaugalingong tab. Ang tanan nga gibuhat niya iya
        // ── man sa fair, mao nga dinhi na siya. SRA ra ang mobasa niini — siya
        // ── ra man ang makapili kung kinsang ahensya ang dad-on sa fair.
        // ──
        // ── Duha ka panel, dili nag-uban-uban: ang postings ug ang pag-imbita.
        // ── Kung tapadon sila, ang pag-imbita mahulog sa ubos sa tibuok lamesa
        // ── ug kinahanglan pang i-scroll siya pangitaon. ──
        $panel = 'invite';

        [$lineupEvents, $lineupEvent, $lineupRows, $lineupOnTheList, $lineupAwaiting,
         $lineupIndustries, $lineupIndustry, $lineupUninvited] =
            $staffRole === 'sra'
                ? $this->overseasLineupData()
                : [collect(), null, collect(), collect(), collect(), collect(), null, 0];

        return view('staff.inhouse.jobfair', compact(
            'events', 'staffRole', 'jobs', 'eventId', 'jobFairOptions', 'totalApprovedJobs',
            'panel', 'lineupEvents', 'lineupEvent', 'lineupRows', 'lineupOnTheList', 'lineupAwaiting',
            'lineupIndustries', 'lineupIndustry', 'lineupUninvited'
        ));
    }

    // ───────────────────────────────
    // JOB FAIR — EVENTS
    // ───────────────────────────────
    public function jobFairEvents()
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $view   = request('view', 'events');
        $status = request('status', 'upcoming');
        $search = request('search');

        $allEvents = \App\Models\JobFairEvent::orderByDesc('event_date')->get();

        $events = \App\Models\JobFairEvent::with(['creator', 'participants'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
                                        ->orWhere('venue', 'like', "%{$search}%"))
            ->latest()
            ->paginate(5);

        $totalUpcoming  = \App\Models\JobFairEvent::where('status', 'upcoming')->count();
        $totalOngoing   = \App\Models\JobFairEvent::where('status', 'ongoing')->count();
        $totalCompleted = \App\Models\JobFairEvent::where('status', 'completed')->count();

        $participants          = null;
        $confirmedCount        = 0;
        $pendingCount          = 0;
        $acceptedCount         = 0;
        $declinedCount         = 0;
        $notSelectedCount      = 0;
        $expiredCount          = 0;
        $participantLocal      = 0;
        $participantOverseas   = 0;
        $employmentRequests    = collect();
        $employerOpenPostings  = collect();
        $notYetInvited         = collect();
        $participantFilter     = request('participant_filter', 'all');

        if ($view === 'participants' && request('event_id')) {
            // ── PESO Job Fair staff, 2026-08-23: kinahanglan nila makita ang
            // ── local ug overseas nga employer apil ang mga bakante nila. Ang
            // ── filter mao ang nagbulag sa duha; ang employer.employer mao ang
            // ── users nga row diin naa ang email — ang employer_nsrp_registrations
            // ── walay email nga kolum, mao nga "None" gyud ang na-print sukad. ──
            // ── Ang mitubag ug oo ra ang makita.
            // ──
            // ── Kining lamesa mao ang tubag sa "kinsa ang moadto sa fair".
            // ── Ang naghulat pa, ang mibalibad ug ang wala mitubag dili
            // ── moadto, ug ang pagbutang nila sa parehas nga lamesa naghimo
            // ── sa listahan nga dili mabasa isip tubag. Ang lokal moadto
            // ── dayon sa 'confirmed'; ang 'accepted' mao ang hunongan sa oo
            // ── sa overseas samtang wala pa mopili ang SRA. ──
            $participantQuery = \App\Models\JobFairParticipant::with(['employer.employer'])
                ->where('job_fair_id', request('event_id'))
                ->whereIn('confirmation_status', ['accepted', 'confirmed'])
                ->when($participantFilter !== 'all', fn($q) =>
                    $q->whereHas('employer', fn($e) =>
                        $e->where('is_overseas', $participantFilter === 'overseas'))
                );

            $participants = $participantQuery->latest()->paginate(10)->withQueryString();

            $employmentRequests = \App\Models\JobFairEmploymentRequest::with('job')
                ->where('job_fair_id', request('event_id'))
                ->get()
                ->groupBy('employer_id');

            // ── Ang employer nga wala pa nagpili ug bakante para niini nga fair
            // ── naa ra bay bakante? Kung naa, ipakita — mao gyud kana ang
            // ── gipangita sa staff, ug ang "No jobs selected yet" nga linya
            // ── nagtago sa tubag. ──
            $employerOpenPostings = \App\Models\Job::where('schedule_type', 'job_fair')
                ->where('posting_status', 'approved')
                ->whereIn('company_id', $participants->pluck('employer_id'))
                ->get(['job_qualifications_id', 'company_id', 'title'])
                ->groupBy('company_id');

            $baseParticipants = \App\Models\JobFairParticipant::where('job_fair_id', request('event_id'));

            $confirmedCount = (clone $baseParticipants)->where('confirmation_status', 'confirmed')->count();
            $pendingCount   = (clone $baseParticipants)->where('confirmation_status', 'pending')->count();
            $declinedCount  = (clone $baseParticipants)->where('confirmation_status', 'declined')->count();
            $expiredCount   = (clone $baseParticipants)->where('confirmation_status', 'expired')->count();

            // ── Ang duha ka ihap sa pagpili sa SRA. Overseas ra ang mahulog
            // ── dinhi, ug sagad zero — apan kung dili sila ipakita, ang
            // ── ahensya nga naghulat mawala sa Total Invited nga walay
            // ── kolum nga makasulti asa siya nahimutang. ──
            $acceptedCount    = (clone $baseParticipants)->where('confirmation_status', 'accepted')->count();
            $notSelectedCount = (clone $baseParticipants)->where('confirmation_status', 'not_selected')->count();

            $participantLocal    = (clone $baseParticipants)->whereHas('employer', fn($e) => $e->where('is_overseas', false))->count();
            $participantOverseas = (clone $baseParticipants)->whereHas('employer', fn($e) => $e->where('is_overseas', true))->count();

            // ── Ang reserba. Sa adlaw nga gihimo ang event, ang tanan sulod sa
            // ── gipiling industriya na-invite na, mao nga kung mo-lapse ang usa
            // ── ka semana ug kulang pa, kining listahan ra ang mabilin. Wala ni
            // ── awtomatik nga gipadad-an — ang staff ang mopili, kay ang
            // ── paghukom nga ang hotel nga agency angay sa Manufacturing nga
            // ── fair kay hukom sa tawo. ──
            $eventForInvites = \App\Models\JobFairEvent::find(request('event_id'));
            if ($eventForInvites && $eventForInvites->status !== 'completed') {
                $notYetInvited = \App\Support\JobFairInvites::notYetInvited($eventForInvites, false);
            }
        }

        // ── Ang attendance naa na sa Reports, dili na dinhi.
        // ──
        // ── Duha ka tab kaniadto ang nagpakita sa parehas nga mga tawo ug
        // ── managlahi ug ihap. Karon usa ra: Reports, tab nga Attendance, uban
        // ── ang Mark Attended ug ang pagpili tali sa "All joined" ug
        // ── "Attended only". ──
        $event = request('event_id') ? \App\Models\JobFairEvent::find(request('event_id')) : null;

        return view('staff.job_fair.events.index', compact(
            'events', 'status', 'totalUpcoming', 'totalOngoing', 'totalCompleted',
            'allEvents', 'participants', 'confirmedCount', 'pendingCount', 'declinedCount',
            'expiredCount', 'acceptedCount', 'notSelectedCount', 'notYetInvited',
            'participantFilter', 'participantLocal', 'participantOverseas', 'employerOpenPostings',
            'employmentRequests', 'event'
        ));
    }

    public function createJobFairEvent()
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        return view('staff.job_fair.events.create', array_merge(
            $this->jobFairIndustryPicture(),
            ['takenDates' => $this->jobFairTakenDates()]
        ));
    }

    /**
     * The dates that already hold a job fair.
     *
     * One fair a day: the office has one set of staff. The rule was only ever
     * enforced on save, so the desk filled the whole form before being told the
     * day was gone. The picker reads this and says so the moment a date is
     * chosen; the unique rule on the column is still what actually decides.
     */
    private function jobFairTakenDates(?int $exceptId = null): array
    {
        return \App\Models\JobFairEvent::when($exceptId,
                fn($q) => $q->where('job_fair_events_id', '!=', $exceptId))
            ->pluck('event_date')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))
            ->values()
            ->all();
    }

    // ── Kinsa ang naa didto, gibahin sa industriya.
    // ──
    // ── Ang staff mopili ug industriya nga gipangita sa fair, ug ang gipili
    // ── mao ang mopili sa padad-an. Kung dili niya makita ang gidaghanon sa
    // ── dili pa siya mo-save, mahimo siyang mopadala ug imbitasyon nga walay
    // ── makadawat ug wala siyay paagi nga masayod.
    // ──
    // ── Ang $unclassified mao ang pahimangno: kining mga employer dili gyud
    // ── maapil kung naay gipiling industriya. ──
    private function jobFairIndustryPicture(): array
    {
        $employers = EmployerNsrpRegistration::whereHas('employer', fn($q) =>
                $q->where('role', 'company')
                  ->where('status', 'approved')
                  ->whereHas('employerRequirement', fn($r) => $r->where('status', 'approved'))
            )
            ->get(['industry_group', 'is_overseas']);

        return [
            'industryCounts' => collect(EmployerNsrpRegistration::INDUSTRY_GROUPS)
                ->mapWithKeys(fn($group) => [$group => [
                    'local'    => $employers->where('industry_group', $group)->where('is_overseas', false)->count(),
                    'overseas' => $employers->where('industry_group', $group)->where('is_overseas', true)->count(),
                ]])
                ->all(),
            'unclassified' => $employers->whereNull('industry_group')->count(),
        ];
    }

    public function storeJobFairEvent(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $cater    = (array) $request->input('cater', []);
        $leadDays = (int) config('peso.jobfair.create_lead_days');
        $earliest = now()->addDays($leadDays);

        $request->validate([
            'title'             => 'required|string|max:255',
            'event_date'        => 'required|date|after_or_equal:' . $earliest->format('Y-m-d') . '|unique:job_fair_events,event_date',
            'event_time'        => 'required|date_format:H:i|after_or_equal:'
                                    . \App\Support\JobFairEventHours::EARLIEST . '|before_or_equal:'
                                    . \App\Support\JobFairEventHours::LATEST,
            'venue'             => 'required|string|max:255',
            'venue_address'     => 'nullable|string|max:255',
            'cater'             => 'required|array|min:1',
            'cater.*'           => 'in:local,overseas',
            // ── Ang target kinahanglan na.
            // ──
            // ── Numero gihapon siya nga gisulat sa opisina ug dili utlanan —
            // ── walay employer nga balibaran kung maabot na siya. Apan ang
            // ── pagtsek sa usa ka tipo nga walay gisulat nga gidaghanon nag-
            // ── invite nga walay gilauman, mao nga ang tipo nga gi-tsek
            // ── kinahanglan naay numero. ──
            'local_capacity'    => [Rule::requiredIf(in_array('local', $cater, true)), 'nullable', 'integer', 'min:1'],
            'overseas_capacity' => [Rule::requiredIf(in_array('overseas', $cater, true)), 'nullable', 'integer', 'min:1'],
            // Which industries this fair is looking for. Absent means all of
            // them, which is how every event behaved before today.
            'target_industries'   => 'nullable|array',
            'target_industries.*' => [Rule::in(EmployerNsrpRegistration::INDUSTRY_GROUPS)],
            // A fair held for PWD applicants. Absent means an ordinary fair,
            // which is how every event behaved before today.
            'pwd_only'            => 'nullable|boolean',
        ], [
            'event_date.after_or_equal' => 'Event date must be at least ' . $leadDays . ' days from today (earliest: ' . $earliest->format('M d, Y') . ').',
            'event_date.unique'         => 'A job fair event is already scheduled on this date. Please choose a different date.',
            'event_time.after_or_equal' => 'Event time must be between 8:00 AM and 5:00 PM.',
            'event_time.before_or_equal' => 'Event time must be between 8:00 AM and 5:00 PM.',
            'cater.required'            => 'Please check at least one employer type (Local or Overseas) to invite.',
            'local_capacity.required'    => 'Enter how many local employers this fair is aiming for.',
            'overseas_capacity.required' => 'Enter how many overseas employers this fair is aiming for.',
        ]);

        $this->validateJobFairIndustries($request);

        // ── Dili ma-book ang adlaw nga puliki ang opisina. Lahi ni sa holiday:
        // ── ang holiday pahimangno ra, apan kung naa'y meeting o training,
        // ── anaa didto ang tanang staff nga mo-atiman sa job fair. ──
        if ($why = \App\Support\OfficeCalendar::reason($request->event_date, true)) {
            return back()->withInput()->with('error',
                'That date is taken by a PESO office schedule. ' . $why);
        }

        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();

        [$localCapacity, $overseasCapacity] = $this->jobFairTargets($request, $cater);

        $event = \App\Models\JobFairEvent::create([
            'created_by'        => $staffRecord->staff_id,
            'title'             => $request->title,
            'event_date'        => $request->event_date,
            'event_time'        => $request->event_time,
            'venue'             => $request->venue,
            'venue_address'     => $request->venue_address,
            'cater'             => array_values($cater),
            'target_industries' => $this->jobFairTargetIndustries($request),
            'pwd_only'          => $request->boolean('pwd_only'),
            'local_capacity'    => $localCapacity,
            'overseas_capacity' => $overseasCapacity,
            // Kept as the combined figure so existing reads of employer_capacity
            // still see a sensible total.
            'employer_capacity' => $localCapacity === null && $overseasCapacity === null
                                    ? null
                                    : (int) $localCapacity + (int) $overseasCapacity,
            'status'            => 'upcoming',
        ]);

        // ── Ang pag-invite naa na sa JobFairInvites, dili na dinhi.
        // ──
        // ── Duha ka gutlo ang nagpadala ug invitation: dinhi, ug ang pag-
        // ── approve sa requirements sa usa ka employer. Kung magbulag-bulag
        // ── ang ilang kahulogan sa "kinsa ang angay i-invite", magkalahi sila.
        // ── Ang `cater` ug ang `target_industries` nga gi-save sa taas ang
        // ── mopili — wala nay lain nga mobasa sa $cater human niini. ──
        $invited = \App\Support\JobFairInvites::invite(
            $event,
            \App\Support\JobFairInvites::autoEligibleFor($event)
        );

        // ── WALAY POSTING NGA MO-ABLI DINHI.
        // ──
        // ── PESO Job Fair staff, 2026-08-26: ang pag-create ug event dili
        // ── pagdapit sa bisan unsang bakante. Ang bag-ong event walay posting
        // ── hangtod nga ang desk mismo mo-post niini gikan sa Job Postings —
        // ── salaan sa industriya, tsekan, ug Post Selected.
        // ──
        // ── Kaniadto mo-abli dayon dinhi ang mga naghulat nga posting kung ang
        // ── event sulod na sa lima ka adlaw nga window. Sakto pa kadto samtang
        // ── ang posting mosulod sa fair nga siya ra; karon ang desk na ang
        // ── mopili, mao nga ang pag-abli mahitabo lang human niya mapili.
        // ──
        // ── Ang gidawat nga posting mo-abli sa duha ka lugar, ug wala nay lain:
        // ── ang acceptPostingIntoFair (kung duol na ang fair niadtong taknaa)
        // ── ug ang jobfair:open-postings nga command sa T-minus. ──
        $openDate = $event->event_date->copy()->subDays(\App\Support\JobFairPostingWindow::daysBefore());
        $note     = ' No vacancy is on it yet — post them from Job Postings. '
                  . 'Whatever is on it by ' . $openDate->format('M d, Y') . ' goes live that day, '
                  . \App\Support\JobFairPostingWindow::daysBefore() . ' days before the event.';

        // ── Isulti gyud ang gidaghanon. Kung ang gipiling industriya walay
        // ── employer, ang "employers notified" mabakak, ug walay paagi nga
        // ── mahibaw-an sa staff nga walay nakadawat. ──
        $who = $invited > 0
            ? $invited . ' employer(s) invited.'
            : 'No employer matched this event yet — check the employer types and industries you chose.';

        return redirect()->route('staff.jobfair.events')
            ->with('success', 'Job Fair event created. ' . $who . $note);
    }

    // ── Ang target nga gidaghanon kada tipo, o null kung wala gisulat.
    // ──
    // ── Ang tipo nga wala gi-tsek walay target bisan unsa pa ang nahibilin sa
    // ── iyang input. Ang null ug ang 0 managlahi karon: ang null nagpasabot
    // ── nga wala pa nakadesisyon ang opisina, ang 0 wala nay kahulogan kay ang
    // ── tipo mismo ang naa na sa `cater`. ──
    private function jobFairTargets(Request $request, array $cater): array
    {
        return [
            in_array('local', $cater) && $request->filled('local_capacity')
                ? (int) $request->local_capacity : null,
            in_array('overseas', $cater) && $request->filled('overseas_capacity')
                ? (int) $request->overseas_capacity : null,
        ];
    }

    /**
     * The fair has to say which industries it is after.
     *
     * Nothing is ticked when the form opens now, and an untouched form used to
     * be read as "all industries" — the fair inviting everyone by silence
     * rather than by choice. "All industries" is still a valid answer; it just
     * has to be given.
     */
    private function validateJobFairIndustries(Request $request): void
    {
        if ($request->boolean('all_industries')) {
            return;
        }

        if (empty(array_filter((array) $request->input('target_industries', [])))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'target_industries' => 'Choose the industries this event is looking for, or tick All industries.',
            ]);
        }
    }

    // ── Ang gipangita nga industriya, o null nga nagpasabot ug tanan.
    // ──
    // ── Ang "All industries" nga checkbox sa porma mao ang default. Kung siya
    // ── ang gi-tsek, walay industriya nga gi-save — parehas gyud sa gibuhat sa
    // ── tanan nga event sa wala pa gipangayo sa opisina ang targeted nga
    // ── imbitasyon. ──
    private function jobFairTargetIndustries(Request $request): ?array
    {
        if ($request->boolean('all_industries')) {
            return null;
        }

        $industries = array_values(array_filter((array) $request->input('target_industries', [])));

        return $industries ?: null;
    }

    public function editJobFairEvent($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $event = \App\Models\JobFairEvent::findOrFail($id);

        return view('staff.job_fair.events.edit', array_merge(
            ['event' => $event, 'takenDates' => $this->jobFairTakenDates((int) $id)],
            $this->jobFairIndustryPicture()
        ));
    }

    public function updateJobFairEvent(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $cater = (array) $request->input('cater', []);

        $request->validate([
            'title'      => 'required|string|max:255',
            'event_date' => 'required|date|unique:job_fair_events,event_date,' . $id . ',job_fair_events_id',
            // Parehas sa storeJobFairEvent. Kaniadto bisan unsang teksto
            // dawaton diri, mao nga ang oras nga gisalikway sa paghimo mahimo
            // ra pud ipasulod pinaagi sa pag-edit.
            'event_time' => 'required|date_format:H:i|after_or_equal:'
                            . \App\Support\JobFairEventHours::EARLIEST . '|before_or_equal:'
                            . \App\Support\JobFairEventHours::LATEST,
            'venue'      => 'required|string|max:255',
            'venue_address' => 'nullable|string|max:255',
            // Walay 'status' dinhi. Ang porma wala na siya gipadala — ang
            // UpdateJobFairEventStatuses nga command ang nagbutang niini gikan
            // sa petsa sa event.
            // ── Ang tipo ug ang industriya mahimo na usbon human mahimo ang
            // ── event.
            // ──
            // ── Kaniadto sa paghimo ra siya mabutang, mao nga ang event nga
            // ── Local ra ang na-tsek dili na gyud maabli sa overseas — bisan
            // ── unsa pa. Ang bugtong dalan kay tangtangon ang event ug himoon
            // ── pag-usab, ug mawala ang tanan nga na-confirm na. ──
            'cater'             => 'required|array|min:1',
            'cater.*'           => 'in:local,overseas',
            'local_capacity'    => [Rule::requiredIf(in_array('local', $cater, true)), 'nullable', 'integer', 'min:1'],
            'overseas_capacity' => [Rule::requiredIf(in_array('overseas', $cater, true)), 'nullable', 'integer', 'min:1'],
            'target_industries'   => 'nullable|array',
            'target_industries.*' => [Rule::in(EmployerNsrpRegistration::INDUSTRY_GROUPS)],
            // A fair held for PWD applicants. Absent means an ordinary fair,
            // which is how every event behaved before today.
            'pwd_only'            => 'nullable|boolean',
        ], [
            'event_date.unique'          => 'A job fair event is already scheduled on this date. Please choose a different date.',
            'event_time.after_or_equal'  => 'Event time must be between 8:00 AM and 5:00 PM.',
            'event_time.before_or_equal' => 'Event time must be between 8:00 AM and 5:00 PM.',
            'cater.required'             => 'Please check at least one employer type (Local or Overseas) to invite.',
            'local_capacity.required'    => 'Enter how many local employers this fair is aiming for.',
            'overseas_capacity.required' => 'Enter how many overseas employers this fair is aiming for.',
        ]);

        $this->validateJobFairIndustries($request);

        if ($why = \App\Support\OfficeCalendar::reason($request->event_date, true)) {
            return back()->withInput()->with('error',
                'That date is taken by a PESO office schedule. ' . $why);
        }

        $event = \App\Models\JobFairEvent::findOrFail($id);

        [$localCapacity, $overseasCapacity] = $this->jobFairTargets($request, $cater);

        // ── Walay pagsusi nga "dili mapakubsan ubos sa na-confirm na" dinhi.
        // ──
        // ── Naa siya kaniadto, ug husto siya kaniadto: ang numero utlanan man,
        // ── mao nga ang pagpakubs niini ubos sa na-confirm makahimo sa event
        // ── nga sobra sa iyang kaugalingon nga limit. Ang numero target na ra
        // ── karon — walay bisan unsa nga gitandi kaniya — mao nga walay
        // ── mabuak kung usbon siya bisan asa. ──
        $event->update([
            'title'             => $request->title,
            'event_date'        => $request->event_date,
            'event_time'        => $request->event_time,
            'venue'             => $request->venue,
            'venue_address'     => $request->venue_address,
            'cater'             => array_values($cater),
            'target_industries' => $this->jobFairTargetIndustries($request),
            'pwd_only'          => $request->boolean('pwd_only'),
            'local_capacity'    => $localCapacity,
            'overseas_capacity' => $overseasCapacity,
            // Gibilin isip kinatibuk-an aron ang daan nga pagbasa sa
            // employer_capacity makakita gihapon ug makatarunganon nga gidaghanon.
            'employer_capacity' => $localCapacity === null && $overseasCapacity === null
                                    ? null
                                    : (int) $localCapacity + (int) $overseasCapacity,
        ]);

        // ── Ang pagdugang ug tipo o industriya kay pag-abli sa pultahan, mao
        // ── nga ablihan siya gyud. Ang employer nga na-approve na kaniadto
        // ── dili na gyud moagi pag-usab sa approveRequirement, mao nga kung
        // ── dili dinhi mo-invite, ang pag-usab sa porma magbag-o ra sa porma
        // ── ug walay makadawat. Ang na-invite na gilaktawan sa helper mismo. ──
        $invited = \App\Support\JobFairInvites::invite(
            $event->refresh(),
            \App\Support\JobFairInvites::autoEligibleFor($event)
        );

        return redirect()->route('staff.jobfair.events')
            ->with('success', 'Job Fair event updated successfully!'
                . ($invited > 0 ? ' ' . $invited . ' newly eligible employer(s) invited.' : ''));
    }

    // ───────────────────────────────
    // INVITE MORE EMPLOYERS
    // ───────────────────────────────
    // ── Kung mo-lapse ang imbitasyon ug kulang pa ang na-confirm, kining lihok
    // ── mao ang "mangita ug lain nga employer" nga gisulti sa staff.
    // ──
    // ── Manwal siya tinuyo. Ang tanan sulod sa gipiling industriya na-invite na
    // ── sa unang adlaw, mao nga ang mabilin kay ang mga gawas niini — ug ang
    // ── paghukom nga haom sila sa fair kay hukom sa opisina, dili lagda nga
    // ── masulat sa code. ──
    public function inviteMoreEmployers(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $event = \App\Models\JobFairEvent::findOrFail($id);

        if ($event->status === 'completed') {
            return back()->with('error', 'This job fair is over. No further invitations can be sent.');
        }

        $request->validate([
            'employer_ids'   => ['required', 'array', 'min:1'],
            'employer_ids.*' => ['integer'],
        ], [
            'employer_ids.required' => 'Select at least one employer to invite.',
        ]);

        // Gitandi batok sa listahan mismo, dili sa exists: ang employer nga
        // participant na, o wala pa na-approve ang requirements, o sayop nga
        // tipo para niini nga fair, dili gyud angay makasulod pinaagi sa usa ka
        // gi-usab nga porma.
        $available = \App\Support\JobFairInvites::notYetInvited($event, false);

        $chosen = $available->whereIn('employer_nsrp_registrations_id', $request->employer_ids)->values();

        if ($chosen->isEmpty()) {
            return back()->with('error', 'None of those employers can be invited to this job fair.');
        }

        $sent = \App\Support\JobFairInvites::invite($event, $chosen);

        $window = (int) config('peso.jobfair.confirm_window_days');

        return back()->with('success', $sent . ' employer(s) invited. They have ' . $window
            . ' days to confirm, until ' . now()->addDays($window)->format('M d, Y') . '.');
    }

    public function deleteJobFairEvent($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        \App\Models\JobFairEvent::findOrFail($id)->delete();

        return back()->with('success', 'Job Fair event deleted.');
    }

    // ───────────────────────────────
    // JOB FAIR — walay checkAndNotifyJobFair dinhi
    // ───────────────────────────────
    //
    // Ang employer nga wala pa mo-tubag gipahinumdoman na karon sa
    // jobfair:send-confirmation-reminders, dili na sa usa ka buton nga
    // hinumdoman pa sa staff nga pindoton.
    //
    // Ang notice sa jobseeker gikan na sa Fair Vacancies: ang staff mo-abli sa
    // usa ka bakante, makita niya ang applicant nga gibahin sa marka, ug ang
    // buton naa sa Highly Qualified ug Qualified nga tab lang. Naka-gate gihapon
    // siya sa JobFairAudience::gateMet() hangtod igo na ang na-confirm nga
    // employer — mao nga walay nawala sa pagkuha sa buton.
    //
    // Walay sendJobFairInvitation pud dinhi. Naa siya kaniadto, apan walay
    // bisan usa ka Blade nga nagtawag kaniya — dili gyud siya maabot gikan sa
    // UI, mao nga usa ka tuig siyang nagpakaaron-ingnon nga tubag sa problema
    // nga wala gyud niya nasulbad. Gipulihan siya sa tulo ka automatic nga
    // pagtawag sa JobFairInvites: sa paghimo sa event, sa pag-approve sa
    // requirements, ug sa pagdugang sa kwota.

    // ── Walay jobFairParticipants dinhi.
    // ──
    // ── Naa siya kaniadto, uban sa kaugalingon niyang page ug route, apan
    // ── walay bisan usa ka link nga miabot kaniya — gipulihan siya sa
    // ── Participants nga tab sa events/index. Nag-ihap pa gani siya ug
    // ── confirmation_status = 'accepted', nga wala sa enum (pending,
    // ── confirmed, declined), mao nga ang iyang Accepted nga card zero gyud
    // ── kanunay. Ang page nga dili maabot ug sayop ang gi-ihap dili angay
    // ── ayohon, angay tangtangon. ──

    // ───────────────────────────────
    // JOB FAIR — APPLICANTS OF ONE FAIR VACANCY
    // ───────────────────────────────
    //
    // Ang Notification nga nav nawala na, ug uban niya ang blast page nga
    // nagpili ug event, nagsulat ug mensahe, ug nagpadala sa tibuok audience.
    //
    // PESO Job Fair staff: bayad ang matag text, ug ang PhilSMS wala pa naavail,
    // mao nga ang pagpadala kinahanglan gamay ug tinuyo. Ang notice karon
    // magsugod gikan sa bakante mismo — ang staff mo-abli sa usa ka fair
    // vacancy, makita niya ang applicant nga gibahin sa marka, ug ang buton naa
    // sa ibabaw sa listahan nga iyang gitan-aw. Usa ka pindot, usa ka grupo.
    //
    // Tulo ka tab, apan duha ra ang naay buton. Ang Not Qualified walay buton
    // ug dili gyud siya masulod sa route bisan i-post pa ang porma direkta.

    /**
     * The three match tiers of one job fair vacancy, and the text message that
     * goes to the top two.
     */
    public function jobFairApplicants($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $job = \App\Models\Job::with('company')
            ->where('schedule_type', 'job_fair')
            ->findOrFail($id);

        $filter = request('filter', 'highly');
        if (!in_array($filter, ['highly', 'qualified', 'not_qualified'], true)) {
            $filter = 'highly';
        }

        $search = request('search');

        $applicants = $this->jobFairTierQuery($job->job_qualifications_id, $filter)
            ->with(['jobseeker', 'jobseeker.nsrp'])
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($j) => $j
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('surname', 'like', "%{$search}%")))
            ->orderByDesc('match_percentage')
            ->paginate(10)
            ->withQueryString();

        $event = $this->jobFairEventOf($job);

        // Kinsa gyud ang maabot sa text — human sa pagkuha sa na-text na, sa
        // walay numero, ug sa mi-opt out. Wala ni nagsalig sa tab nga giablihan:
        // usa ra ka buton ang naa, ug ang padad-an niini kanunay nga ang
        // Highly Qualified ug ang Qualified.
        $recipients = $this->jobFairTierRecipients($job, 'notifiable');

        return view('staff.job_fair.applicants', [
            'job'               => $job,
            'applicants'        => $applicants,
            'filter'            => $filter,
            'totalHighly'       => $this->jobFairTierQuery($job->job_qualifications_id, 'highly')->count(),
            'totalQualified'    => $this->jobFairTierQuery($job->job_qualifications_id, 'qualified')->count(),
            'totalNotQualified' => $this->jobFairTierQuery($job->job_qualifications_id, 'not_qualified')->count(),
            'event'             => $event,
            'gateMet'           => $event ? \App\Support\JobFairAudience::gateMet($event) : false,
            'confirmedCount'    => $event ? \App\Support\JobFairAudience::confirmedCount($event) : 0,
            'threshold'         => \App\Support\JobFairAudience::threshold(),
            'smsText'           => $event ? $this->jobFairApplicantSmsText($job, $event) : null,
            'smsLive'           => \App\Support\PhilSms::enabled(),
            'reachable'         => $recipients['reachable'],
            'alreadyNotified'   => $recipients['already'],
        ]);
    }

    /**
     * Send the job fair notice to one tier of applicants.
     */
    public function notifyJobFairApplicants(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        // Walay tier nga gikuha gikan sa request.
        //
        // Usa ka buton, usa ka padad-an: ang Highly Qualified ug ang Qualified.
        // Ang Not Qualified dili gyud maabot sa text, ug dili na siya
        // makasulod bisan pag hilabtan ang porma — walay input nga mahimong
        // usbon aron makasulod siya.

        $job = \App\Models\Job::with('company')
            ->where('schedule_type', 'job_fair')
            ->findOrFail($id);

        $back = redirect()->route('staff.jobfair.postings.applicants', [
            'id' => $job->job_qualifications_id,
        ]);

        $event = $this->jobFairEventOf($job);
        if (!$event) {
            return $back->with('error', 'This vacancy is not on a job fair yet. Post it to a fair first.');
        }

        // Parehas nga gate sa nawala nga send page: walay jobseeker nga
        // masultihan hangtod igo na ang employer nga mi-confirm nga motambong.
        if (!\App\Support\JobFairAudience::gateMet($event)) {
            return $back->with('error', 'Only ' . \App\Support\JobFairAudience::confirmedCount($event)
                . ' of ' . \App\Support\JobFairAudience::threshold()
                . ' employers have confirmed for ' . $event->title
                . '. Jobseekers cannot be notified yet.');
        }

        $throttleKey = 'jobfair-applicant-notify:' . $staff->staff_id;
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $wait = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            return $back->with('error', 'Too many notifications sent in a short time. Please wait ' . $wait . ' seconds.');
        }
        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 600);

        $resolved  = $this->jobFairTierRecipients($job, 'notifiable');
        $rows      = $resolved['rows'];
        $breakdown = $resolved['breakdown'];

        if ($rows->isEmpty()) {
            return $back->with('error', $resolved['already'] > 0
                ? 'Every qualified applicant has already been notified about this vacancy.'
                : 'No applicant reaches the qualified mark for this vacancy yet.');
        }

        $smsText   = $this->jobFairApplicantSmsText($job, $event);
        $tierLabel = 'qualified';
        $company   = $job->company->company_name ?? 'an employer';
        $eventDate = $event->event_date?->format('M d, Y') ?? 'the event date';

        // Usa ka announcement kada tawo, dili sendToJobseekers, kay ang
        // sangputanan sa text gitipigan sa matag laray.
        $created = [];
        foreach ($rows as $row) {
            $created[] = [
                'number'       => $row['number'],
                'announcement' => \App\Models\Announcement::create([
                    'type'           => 'job_fair',
                    'title'          => 'Job Fair Vacancy For You 💼',
                    'message'        => 'You qualify for "' . $job->title . '" by ' . $company
                                        . ', which is being brought to ' . $event->title
                                        . ' on ' . $eventDate . '. Bring your resume and a valid ID.',
                    // Ang jobseeker nga mo-klik moadto sa bakante mismo. Kini
                    // pud ang timailhan nga na-text na siya para niini nga
                    // bakante, mao nga ang sunod nga pindot dili na mag-doble.
                    'reference_type' => 'job',
                    'reference_id'   => $job->job_qualifications_id,
                    'jobseeker_id'   => $row['id'],
                    'sms_status'     => 'pending',
                ]),
            ];
        }

        $result      = \App\Support\PhilSms::send(array_column($breakdown['sendable'], 'number'), $smsText);
        $now         = now();
        $sentNumbers = array_flip($result['sent']);

        foreach ($created as $entry) {
            $number       = $entry['number'];
            $announcement = $entry['announcement'];

            if ($number !== null && isset($sentNumbers[$number])) {
                $announcement->update([
                    'sms_status'  => 'sent',
                    'sms_sent_at' => $now,
                    'sms_error'   => null,
                ]);
            } elseif ($number !== null && isset($result['failed'][$number])) {
                $announcement->update([
                    'sms_status' => 'failed',
                    'sms_error'  => \Illuminate\Support\Str::limit($result['failed'][$number], 250),
                ]);
            } else {
                // Walay numero, dili magamit nga numero, o mi-opt out — ang
                // in-app nga notice naa gihapon, walay ma-text lang.
                $announcement->update(['sms_status' => 'not_applicable']);
            }
        }

        $sentCount   = count($result['sent']);
        $failedCount = count($result['failed']);
        $skipped     = $rows->count() - $sentCount - $failedCount;

        $summary = $rows->count() . ' ' . $tierLabel . ' applicant(s) notified, '
                 . $sentCount . ' text message(s) sent';
        if (!\App\Support\PhilSms::enabled()) {
            $summary .= ' (test mode — nothing was actually sent)';
        }
        if ($failedCount > 0) {
            $summary .= ', ' . $failedCount . ' failed';
        }
        if ($skipped > 0) {
            $summary .= ', ' . $skipped . ' with no usable number';
        }
        if ($resolved['already'] > 0) {
            $summary .= ', ' . $resolved['already'] . ' already notified earlier';
        }

        return $back->with($failedCount > 0 ? 'warning' : 'success', $summary . '.');
    }

    /**
     * One tier of applicants on one vacancy. The same boundaries the employer
     * screen uses, so a person sits in the same band wherever they are read.
     */
    private function jobFairTierQuery(int $jobId, string $filter)
    {
        $query = \App\Models\Application::where('job_id', $jobId);

        return match ($filter) {
            'highly'        => $query->where('match_percentage', '>=', 75),
            'qualified'     => $query->whereBetween('match_percentage', [50, 74.99]),
            'not_qualified' => $query->where('match_percentage', '<', 50),
            // Ang padad-an sa text: ang duha ka ibabaw nga tier isip usa. Usa
            // ra ka buton ang naa sa page, mao nga usa ra pud ka listahan —
            // ang pagbahin niini sa tab maghimo sa desk nga mopindot ug duha
            // ka higayon para sa usa ka bakante.
            'notifiable'    => $query->where('match_percentage', '>=', 50),
            default         => $query,
        };
    }

    /**
     * Which fair a vacancy was posted to. A vacancy is carried to one fair at a
     * time, so the newest request is the answer.
     */
    private function jobFairEventOf(\App\Models\Job $job): ?\App\Models\JobFairEvent
    {
        $request = \App\Models\JobFairEmploymentRequest::where('job_id', $job->job_qualifications_id)
            ->latest('job_fair_employment_requests_id')
            ->first();

        return $request
            ? \App\Models\JobFairEvent::find($request->job_fair_id)
            : null;
    }

    /**
     * Everyone in one tier who has not been texted about this vacancy yet,
     * split into who can receive a text and who cannot.
     */
    private function jobFairTierRecipients(\App\Models\Job $job, string $filter): array
    {
        $applications = $this->jobFairTierQuery($job->job_qualifications_id, $filter)
            ->with('jobseeker')
            ->get();

        // Ang na-notify na para niini nga bakante wala na apila. Bayad ang matag
        // text, ug ang pagpindot pag-usab dili angay mag-doble sa gasto.
        $notified = \App\Models\Announcement::where('type', 'job_fair')
            ->where('reference_type', 'job')
            ->where('reference_id', $job->job_qualifications_id)
            ->whereNotNull('jobseeker_id')
            ->pluck('jobseeker_id')
            ->flip();

        $rows    = collect();
        $already = 0;

        foreach ($applications as $application) {
            $registration = $application->jobseeker;
            if (!$registration) continue;

            if ($notified->has($registration->jobseeker_registrations_id)) {
                $already++;
                continue;
            }

            $rows->push(\App\Support\JobFairAudience::jobseekerRow($registration));
        }

        $breakdown = \App\Support\JobFairAudience::breakdown($rows);

        return [
            'rows'      => $rows,
            'breakdown' => $breakdown,
            'reachable' => count($breakdown['sendable']),
            'already'   => $already,
        ];
    }

    /**
     * The text message itself. Staff does not type it — one wording for every
     * send keeps the length, and therefore the cost, predictable, and it is
     * shown on screen before the button is pressed.
     */
    private function jobFairApplicantSmsText(\App\Models\Job $job, \App\Models\JobFairEvent $event): string
    {
        $when = $event->event_date?->format('M d, Y') ?? '';
        $time = $event->event_time
            ? \Carbon\Carbon::parse($event->event_time)->format('g:i A')
            : '';

        $text = 'PESO CDO: You qualify for ' . $job->title
              . ' (' . ($job->company->company_name ?? 'an employer') . ')'
              . ' at ' . $event->title
              . ($when ? ' on ' . $when : '')
              . ($time ? ' ' . $time : '')
              . ($event->venue ? ', ' . $event->venue : '')
              . '. Bring your resume and valid ID. Do not reply.';

        // Tulo ka message part ang kinatas-an, parehas sa nawala nga send page.
        return \Illuminate\Support\Str::limit($text, 459, '');
    }

    // ───────────────────────────────
    // JOB FAIR — JOB POSTINGS (Closed = naghulat pa sa event; Open = na-post na)
    // ───────────────────────────────
    public function jobFairPostings()
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        // ── Ang pula nga ihap sa sidebar mapawong dinhi. ──
        // ── Ang pangutana niini mao ang "abliha kini nga pahina"; abli na
        // ── siya, mao nga natubag na. Ang bakante nga ma-approve human niini
        // ── mopasiga niya pag-usab, kay mas bag-o ang ilang updated_at kay sa
        // ── marka. Ang marka anaa sa staff nga laray, dili sa user — si
        // ── authStaff() nagbalik ug User. ──
        $staff->staff?->forceFill(['postings_seen_at' => now()])->save();

        // Ang tab mao na ang tubag sa employer sa imbitasyon. Ang bisan unsa
        // nga wala sa tulo mahulog sa Pending, imbis nga hilom nga molista sa
        // tanan.
        $invite = request('invite', 'pending');
        if (!array_key_exists($invite, self::JOB_FAIR_INVITE_TABS)) {
            $invite = 'pending';
        }
        $search = request('search');

        // ── Ang sala nga gigamit sa desk sa dili pa siya mo-post ug tinapok.
        // ──
        // ── PESO Job Fair staff, 2026-08-26: ang fair nga para sa Education ra
        // ── modawat sa Education ra. Imbis nga basahon ang tibuok listahan ug
        // ── pilion usa-usa, salaan sa industriya, tsekan ang tanan, ug i-post.
        // ── Ang wala sa listahan gikuha gikan sa INDUSTRY_GROUPS lang, aron ang
        // ── query string dili makasulod ug bisan unsa. ──
        $industry = request('industry');
        if (!in_array($industry, EmployerNsrpRegistration::INDUSTRY_GROUPS, true)) {
            $industry = null;
        }

        // ── Ang listahan sa bakante nga gibahin sa open ug closed gitangtang,
        // ── uban ang PWD nga sala. Ang laray karon usa ka employer ug ang
        // ── iyang tubag sa imbitasyon; ang bakante nga iyang dad-on gilista
        // ── sulod sa maong laray. ──

        // Ang fair nga mapilian sa pag-dawat sa usa ka posting. Ang nahuman na
        // wala apil — walay pulos ang pagsulod ug bakante sa fair nga milabay.
        $events = \App\Support\JobFairInvites::upcomingEvents();

        // ── Pila ka naghulat nga bakante ang sakop sa matag fair.
        // ──
        // ── Kini ang numero nga anaa sa buton: ang desk mopili ug fair ug
        // ── makakita dayon kung pila ang mosulod. Ang tibuok naghulat nga
        // ── listahan ang gisukod, dili ang usa ka pahina — ang buton mokuha
        // ── man sa tanan, dili sa napulo nga makita karon. ──
        $waitingAll = $this->jobFairPendingPostings();

        $fitCounts = $events->mapWithKeys(fn($event) => [
            $event->job_fair_events_id => $waitingAll
                ->filter(fn($job) => $this->postingFitsFair($job, $event))
                ->count(),
        ])->all();

        // ── ANG LARAY KARON: USA KA EMPLOYER, USA KA IMBITASYON ──
        //
        // PESO Job Fair staff, 2026-09-02: ang gipangutana sa desk dinhi mao
        // ang "kinsa ang mitubag ug kinsa ang wala pa", ug ang tubag naa sa
        // employer, dili sa bakante. Ang duha ka tab kaniadto nagbahin sa
        // bakante sa open ug closed, nga tubag sa lain nga pangutana.
        $invitations = $this->jobFairInvitationRows($invite, $search, $industry);

        // ── Ang buton nga "Post All Job Vacancies": kada fair, pila ka bakante
        // ── ang naghulat pa nga makita sa jobseeker. Ang numero gikwenta dinhi
        // ── aron ang gipakita sa buton ug ang tinuod nga i-abli parehas gyud,
        // ── ug aron ang fair nga walay hulat mopatay sa buton. ──
        $openable = $events->map(fn($event) => [
            'id'      => $event->job_fair_events_id,
            'title'   => $event->title,
            'date'    => $event->event_date,
            'waiting' => \App\Support\JobFairPostingWindow::pendingPostings($event)->count(),
            'inRange' => \App\Support\JobFairPostingWindow::opensFor($event),
        ])->values();

        return view('staff.job_fair.postings', [
            'invite'        => $invite,
            'invitations'   => $invitations['rows'],
            'inviteCounts'  => $invitations['counts'],
            'vacanciesFor'  => $invitations['vacancies'],
            'events'        => $events,
            'openable'      => $openable,
            'openDaysBefore' => \App\Support\JobFairPostingWindow::daysBefore(),
            'fitCounts'     => $fitCounts,
            'waitingTotal'  => $waitingAll->count(),
            'industry'      => $industry,
            'industries'    => EmployerNsrpRegistration::INDUSTRY_GROUPS,
        ]);
    }

    /** The three answers an employer can have given, and which statuses each holds. */
    private const JOB_FAIR_INVITE_TABS = [
        'pending'  => ['pending'],
        // Ang lokal moadto dayon sa 'confirmed'; ang overseas mohunong sa
        // 'accepted' samtang wala pa mopili ang SRA. Duha ka porma sa oo.
        'accepted' => ['accepted', 'confirmed'],
        'declined' => ['declined'],
    ];

    /**
     * The employers invited to a fair, on the tab asked for.
     *
     * Each row carries the vacancies that employer would bring — the ones
     * already attached to that fair, or their approved job fair postings when
     * they have not chosen yet — and how many jobseekers those vacancies
     * would match. The match count is a suggestion, not a promise: it says
     * what is out there today, and the desk reads it as "this is worth
     * bringing", not as a roster.
     */
    private function jobFairInvitationRows(string $invite, ?string $search, ?string $industry): array
    {
        $filtered = fn() => \App\Models\JobFairParticipant::query()
            ->when($search, fn($q) => $q->whereHas('employer', fn($e) =>
                $e->where('company_name', 'like', "%{$search}%")))
            ->when($industry, fn($q) => $q->whereHas('employer', fn($e) =>
                $e->where('industry_group', $industry)));

        $counts = [];
        foreach (self::JOB_FAIR_INVITE_TABS as $tab => $statuses) {
            $counts[$tab] = $filtered()->whereIn('confirmation_status', $statuses)->count();
        }

        $rows = $filtered()
            ->with(['employer.employer', 'jobFair'])
            ->whereIn('confirmation_status', self::JOB_FAIR_INVITE_TABS[$invite])
            ->latest('job_fair_participants_id')
            ->paginate(10)
            ->withQueryString();

        return [
            'rows'      => $rows,
            'counts'    => $counts,
            'vacancies' => $this->jobFairInvitationVacancies($rows),
        ];
    }

    /**
     * The vacancies behind each row, keyed by "employerId:fairId".
     *
     * Two queries for the whole page rather than two per row. The tier counts
     * use the same boundaries as every other screen, so a jobseeker sits in
     * the same band wherever they are read.
     */
    private function jobFairInvitationVacancies($rows): array
    {
        $employerIds = $rows->pluck('employer_id')->unique()->all();
        $fairIds     = $rows->pluck('job_fair_id')->unique()->all();

        if (empty($employerIds)) {
            return [];
        }

        $jobs = \App\Models\Job::withCount([
                'applications as highly_count'    => fn($q) => $q->where('match_percentage', '>=', 75),
                'applications as qualified_count' => fn($q) => $q->whereBetween('match_percentage', [50, 74.99]),
            ])
            ->where('schedule_type', 'job_fair')
            ->whereIn('company_id', $employerIds)
            ->get()
            ->keyBy('job_qualifications_id');

        // Gipili na ba niya kung asa nga bakante ang dad-on sa maong fair?
        $attached = \App\Models\JobFairEmploymentRequest::whereIn('job_fair_id', $fairIds)
            ->whereIn('employer_id', $employerIds)
            ->get()
            ->groupBy(fn($request) => $request->employer_id . ':' . $request->job_fair_id);

        $byCompany = $jobs->groupBy('company_id');

        $out = [];
        foreach ($rows as $row) {
            $key = $row->employer_id . ':' . $row->job_fair_id;

            // Wala pa siya mopili — ang iyang buhi nga job fair nga posting ang
            // gipakita. Kini gyud ang gipangita: pila untay madala niya.
            $out[$key] = isset($attached[$key])
                ? $attached[$key]->map(fn($request) => $jobs->get($request->job_id))->filter()->values()
                : ($byCompany->get($row->employer_id, collect())->values());
        }

        return $out;
    }

    // ───────────────────────────────
    // JOB FAIR — TAKE EVERY WAITING VACANCY THAT FITS
    // ───────────────────────────────
    /**
     * Fill one fair with the vacancies that belong to it.
     *
     * PESO Job Fair staff, 2026-09-01: the desk does not read a waiting
     * vacancy and rule on it. The ruling was made when the fair was created —
     * an Education fair takes Education, a PWD fair takes the vacancies that
     * accept PWD applicants — so the fair already knows which of them are its
     * own. The desk picks the fair and says go.
     *
     * That is why there is no per-posting Accept or Reject any more. Five
     * employers waiting on the Hospitality fair were five decisions the desk
     * had already made once, on the day it wrote the fair.
     *
     * The fit is asked of the event itself, the same two questions the fair
     * has always answered: does it take this employer, and does it take this
     * vacancy. Anything that fails either is left waiting for the fair that
     * does want it — it is not rejected, and the employer hears nothing.
     */
    // ───────────────────────────────
    // JOB FAIR — SHOW THE VACANCIES TO JOBSEEKERS
    // ───────────────────────────────
    /**
     * Open every vacancy on one fair, in one press.
     *
     * PESO Job Fair staff, 2026-09-02: the desk decides the moment the list
     * goes public — normally five days before the fair, once the employers
     * that are coming have answered. Until then the vacancies sit closed:
     * announcing a vacancy a month before the day it can be applied for buries
     * it under everything posted since.
     *
     * The press is not undoable in any useful sense — the employers are told
     * their posting is live and the matching jobseekers are notified — so the
     * button asks first.
     */
    public function openJobFairPostings(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $request->validate([
            'job_fair_id' => ['required', 'exists:job_fair_events,job_fair_events_id'],
        ], [
            'job_fair_id.required' => 'Choose which job fair to post.',
        ]);

        $event  = \App\Models\JobFairEvent::findOrFail($request->job_fair_id);
        $opened = \App\Support\JobFairPostingWindow::openAll($event);

        if ($opened === 0) {
            return back()->with('error',
                'No vacancy on ' . $event->title . ' is waiting to be posted. '
                . 'Either every one of them is already live, or no employer has '
                . 'brought a vacancy to this fair yet.');
        }

        return back()->with('success',
            $opened . ' vacancy(s) on ' . $event->title . ' are now visible to jobseekers. '
            . 'The employers were told their posting is live, and the jobseekers whose '
            . 'preferred work matches were notified.');
    }

    public function approveFittingJobFairJobs(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $request->validate([
            'job_fair_id' => ['required', 'exists:job_fair_events,job_fair_events_id'],
        ], [
            'job_fair_id.required' => 'Choose which job fair these vacancies join.',
        ]);

        $event = \App\Models\JobFairEvent::findOrFail($request->job_fair_id);

        // Nabasa sa dili pa ang lihok: ang lihok mismo ang mag-timan-an niini.
        $wasAnnounced = $event->jobseekers_invited_at !== null;

        $waiting = $this->jobFairPendingPostings();
        $fitting = $waiting->filter(fn($job) => $this->postingFitsFair($job, $event));

        if ($fitting->isEmpty()) {
            return back()->with('error',
                'No waiting vacancy fits ' . $event->title . '. '
                . 'A vacancy joins a fair only when the fair takes its industry, '
                . 'its employer and its applicants.');
        }

        $accepted = 0;
        $refused  = [];

        foreach ($fitting as $job) {
            if ($why = $this->acceptPostingIntoFair($job, $event)) {
                $refused[] = '"' . $job->title . '" ' . '—' . ' ' . $why;
                continue;
            }
            $accepted++;
        }

        $skipped = $waiting->count() - $fitting->count();

        $note = $accepted . ' vacancy(s) posted to ' . $event->title . '. '
            . ($skipped > 0
                ? $skipped . ' other waiting vacancy(s) did not fit this fair and are still waiting. '
                : '')
            . \App\Support\JobFairPostingWindow::liveNote($event)
            . $this->jobseekerAnnouncementNote($event, $wasAnnounced);

        if (!$refused) {
            return back()->with('success', $note);
        }

        // Ginganlan gyud ang wala nakasulod. Ang "8 of 10" nga walay ngalan
        // mopakuti sa desk sa pagpangita kung kinsa ang duha.
        return back()->with($accepted ? 'warning' : 'error',
            $note . ' Not posted: ' . implode(' ', $refused));
    }

    /**
     * Every vacancy still waiting for a fair.
     *
     * A vacancy past its own deadline is not waiting for anything — it is
     * gone, and it lives in Archived Job Postings.
     */
    private function jobFairPendingPostings()
    {
        return \App\Models\Job::with('company')
            ->where('schedule_type', 'job_fair')
            ->where('posting_status', 'pending')
            ->withinDeadline()
            ->get();
    }

    /** Does this fair take this vacancy? The fair's own two questions. */
    private function postingFitsFair(\App\Models\Job $job, \App\Models\JobFairEvent $event): bool
    {
        return $event->catersTo((bool) optional($job->company)->is_overseas)
            && !$event->postingMismatch($job);
    }

    /**
     * One line saying whether this action is what announced the fair.
     *
     * The desk needs to know a blast went out under their click, and needs to
     * not be told again on the next posting. Read from the stamp, so the
     * sentence and the actual send cannot disagree.
     */
    private function jobseekerAnnouncementNote(\App\Models\JobFairEvent $event, bool $wasAnnounced): string
    {
        if ($wasAnnounced || $event->fresh()->jobseekers_invited_at === null) {
            return '';
        }

        return ' Jobseekers have been told the fair is happening.';
    }

    /**
     * Put one vacancy on one fair, or say why it cannot go.
     *
     * Both the single Accept and Post Selected run through here, so the rule
     * the modal enforces and the rule the batch enforces cannot drift apart.
     * Returns null on success, or the sentence to show the staff.
     */
    private function acceptPostingIntoFair(\App\Models\Job $job, \App\Models\JobFairEvent $event): ?string
    {
        if ($job->schedule_type !== 'job_fair') {
            return 'This job is not a job fair posting.';
        }

        if ($job->posting_status !== 'pending') {
            return 'Only pending job postings can be accepted.';
        }

        if ($event->status === 'completed') {
            return 'That job fair is over. Choose an upcoming one.';
        }

        if (!$event->catersTo((bool) optional($job->company)->is_overseas)) {
            return 'That job fair does not cater to this employer.';
        }

        // ── Ang event mismo ang mopili.
        // ──
        // ── PESO Job Fair staff, 2026-08-26: ang fair nga para sa PWD modawat
        // ── lang sa bakante nga nagdawat kanila, ug ang fair nga nangita ug usa
        // ── ka industriya modawat lang sa bakante nga sakop niini. Gipugngan
        // ── dinhi ug gipugngan sa picker: ang gitago sa porma dili gyud angay
        // ── makasulod pinaagi sa pag-post sa laing job_fair_id. ──
        if ($why = $event->postingMismatch($job)) {
            return $why;
        }

        // ── Ang deadline nga mas sayo pa kay sa adlaw sa fair magpatay sa
        // ── posting sa dili pa siya magamit. ──
        if ($job->deadline && $job->deadline->lt($event->event_date)) {
            $job->update(['deadline' => $event->event_date->toDateString()]);
        }

        // ── Kung duol na ang fair, ang posting nga karon pa ma-approve mo-abli
        // ── dayon — kay ang scheduled command dili na mobalik sa maong window.
        // ── Kung layo pa, magpabilin siyang closed hangtod sa T-minus.
        // ──
        // ── Ang gipangutana kay ANG FAIR nga gisudlan, dili kung naa bay bisan
        // ── unsang fair nga duol na: ang gidawat sa Oktubre nga fair dili angay
        // ── mo-abli tungod sa Septyembre. ──
        $windowOpen = \App\Support\JobFairPostingWindow::opensFor($event);

        $job->update([
            'posting_status'        => 'approved',
            'status'                => $windowOpen ? 'open' : 'closed',
            'remarks'               => null,
            'requested_job_fair_id' => $event->job_fair_events_id,
        ]);

        \App\Models\JobFairEmploymentRequest::firstOrCreate([
            'job_fair_id' => $event->job_fair_events_id,
            'employer_id' => $job->company_id,
            'job_id'      => $job->job_qualifications_id,
        ]);

        // ── Ang jobseeker masayod nga naay fair.
        // ──
        // ── PESO, 2026-08-26: ang fair tinuod na sa jobseeker sa higayon nga
        // ── naa nay bakante nga iyang kaadtoan, ug kana nga higayon mao kini.
        // ── Kausa ra kada fair — ang helper mismo ang nagbantay niini, mao
        // ── nga ang pag-post ug napulo ka bakante usa ra gihapon ka anunsyo. ──
        \App\Support\JobFairInvites::inviteJobseekers($event);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'job_approved',
            'title'          => 'Job Posting Accepted ✅',
            'message'        => 'Your job posting "' . $job->title . '" was accepted into '
                . $event->title . ' on ' . $event->event_date->format('M d, Y') . '. '
                . \App\Support\JobFairPostingWindow::liveNote($event),
            'reference_type' => 'job',
            'reference_id'   => $job->job_qualifications_id,
        ], $job->company_id);

        return null;
    }

    // ───────────────────────────────
    // EMPLOYER REQUIREMENTS
    // ───────────────────────────────
    public function employerRequirements()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $search    = request('search');
        $status    = request('status', 'pending');
        $staffRole = $staff->staff_role;

        if ($staffRole === 'lra') {
            $requirements = \App\Models\EmployerRequirement::with(['employer.jobs.applications'])
                ->whereIn('status', ['approved', 'expired'])
                ->whereHas('employer', fn($u) => $u->where('is_overseas', false))
                ->when($search, fn($q) => $q->whereHas('employer', fn($u) =>
                    $u->where('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                ))
                ->latest()
                ->paginate(10);

            return view('staff.requirements.index', compact('requirements', 'status', 'staffRole'));
        }

        if ($staffRole === 'sra') {
            $sraStatus    = request('status', 'approved');
            $requirements = \App\Models\EmployerRequirement::with(['employer.jobs.applications'])
                ->whereHas('employer', fn($u) => $u->where('is_overseas', true))
                ->when($sraStatus !== 'all', fn($q) => $q->where('status', $sraStatus))
                ->when($search, fn($q) => $q->whereHas('employer', fn($u) =>
                    $u->where('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                ))
                ->latest()
                ->paginate(10);

            $status = $sraStatus;
            return view('staff.requirements.index', compact('requirements', 'status', 'staffRole'));
        }

        $requirements = \App\Models\EmployerRequirement::with('employer')
            ->whereHas('employer', fn($u) => $u->where('is_overseas', false))
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->whereHas('employer', fn($u) =>
                $u->where('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(10);

        return view('staff.requirements.index', compact('requirements', 'status', 'staffRole'));
    }

    public function viewEmployerRequirement($id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole   = $staff->staff_role;
        $requirement = \App\Models\EmployerRequirement::with('employer')->findOrFail($id);
        return view('staff.requirements.show', compact('requirement', 'staffRole'));
    }

    /**
     * Accept one document, and only that one.
     *
     * PESO Job Vacancy staff, 2026-09-01: the desk reads the folder a paper at
     * a time. Before this there was a single Approve for the whole folder, so
     * accepting the business permit put the company in Registered Employer with
     * four documents nobody had opened.
     *
     * Nothing here moves the company. The company moves on approveRequirement,
     * which now refuses until every paper is in this list.
     */
    public function acceptRequirementDocument($id, $field)
    {
        [$requirement, $redirect] = $this->requirementForLocalReview($id);
        if ($redirect) return $redirect;

        if (!in_array($field, \App\Models\EmployerRequirement::REVIEWED_DOCUMENTS, true)) {
            return back()->with('error', 'That is not a reviewable document.');
        }

        if ($requirement->status !== 'pending') {
            return back()->with('error', 'This folder is no longer under review.');
        }

        // Ang papel nga wala gi-upload dili madawat. Ang pagdawat sa wala
        // gipadala mao ang pagpirma sa blangko nga papel.
        if (!$requirement->$field) {
            return back()->with('error', 'That document has not been submitted yet.');
        }

        // Ang usa ka papel usa ra ka hukom. Ang pag-approve karon nagwagtang
        // sa reject nga gihimo ganina, aron ang duha dili magkasumpaki.
        $requirement->update([
            'approved_fields' => collect($requirement->approved_fields ?: [])
                ->push($field)->unique()->values()->all(),
            'rejected_fields' => collect($requirement->rejected_fields ?: [])
                ->reject(fn($f) => $f === $field)->values()->all(),
            'rejection_notes' => collect($requirement->rejection_notes ?: [])
                ->forget($field)->all(),
        ]);

        $left = count($requirement->fresh()->documentsNotYetDecided());

        return back()->with('success', $left === 0
            ? 'All documents reviewed.'
            : 'Document approved. ' . $left . ' document(s) still to review.');
    }

    /**
     * Reject one document, and only that one.
     *
     * The employer hears nothing yet. The folder goes back on the Decline
     * button, which sends every rejected paper in one notice — a company that
     * has three wrong documents should be told three times in one message, not
     * receive three separate messages.
     */
    public function rejectRequirementDocument(Request $request, $id, $field)
    {
        [$requirement, $redirect] = $this->requirementForLocalReview($id);
        if ($redirect) return $redirect;

        if (!in_array($field, \App\Models\EmployerRequirement::REVIEWED_DOCUMENTS, true)) {
            return back()->with('error', 'That is not a reviewable document.');
        }

        if ($requirement->status !== 'pending') {
            return back()->with('error', 'This folder is no longer under review.');
        }

        $request->validate(
            ['reason' => 'required|string|max:255'],
            ['reason.required' => 'Say what is wrong with this document.']
        );

        $requirement->update([
            'rejected_fields' => collect($requirement->rejected_fields ?: [])
                ->push($field)->unique()->values()->all(),
            'rejection_notes' => collect($requirement->rejection_notes ?: [])
                ->put($field, $request->reason)->all(),
            'approved_fields' => collect($requirement->approved_fields ?: [])
                ->reject(fn($f) => $f === $field)->values()->all(),
        ]);

        $left = count($requirement->fresh()->documentsNotYetDecided());

        return back()->with('success', $left === 0
            ? 'All documents reviewed. Send the folder back to the employer below.'
            : 'Document rejected. ' . $left . ' document(s) still to review.');
    }

    /** Ibalik ang usa ka papel sa wala pa madawat — sayop nga pindot, o gibasa pag-usab. */
    public function undoRequirementDocument($id, $field)
    {
        [$requirement, $redirect] = $this->requirementForLocalReview($id);
        if ($redirect) return $redirect;

        if ($requirement->status !== 'pending') {
            return back()->with('error', 'This folder is no longer under review.');
        }

        $requirement->update([
            'approved_fields' => collect($requirement->approved_fields ?: [])
                ->reject(fn($f) => $f === $field)->values()->all(),
            'rejected_fields' => collect($requirement->rejected_fields ?: [])
                ->reject(fn($f) => $f === $field)->values()->all(),
            'rejection_notes' => collect($requirement->rejection_notes ?: [])
                ->forget($field)->all(),
        ]);

        return back()->with('success', 'Document returned to unreviewed.');
    }

    /**
     * The folder, plus the check that this desk is allowed to read it.
     *
     * Same rule the approve and reject actions have always used: local goes to
     * Job Vacancy, overseas to SRA. Written once so the three actions cannot
     * drift apart.
     */
    private function requirementForLocalReview($id): array
    {
        $staff = $this->authStaff();
        if (!$staff) return [null, redirect()->route('login')];

        $requirement = \App\Models\EmployerRequirement::with('employer')->findOrFail($id);
        $isOverseas  = $requirement->employer->is_overseas ?? false;

        if ($isOverseas) {
            // Ang SRA nagdawat sa tibuok folder sa usa ka pindot gikan sa
            // listahan — walay per-document nga lakang didto, ug walay porma
            // dinhi nga makaabot niini.
            return [null, back()->with('error', 'Overseas requirements are reviewed by SRA staff.')];
        }

        if ($staff->staff_role !== 'job_vacancy') {
            return [null, back()->with('error', 'Only Job Vacancy staff can review local employer requirements.')];
        }

        return [$requirement, null];
    }

    public function approveRequirement($id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $requirement = \App\Models\EmployerRequirement::with('employer')->findOrFail($id);

        $isOverseas = $requirement->employer->is_overseas ?? false;
        if ($isOverseas && $staff->staff_role !== 'sra') {
            return back()->with('error', 'Only SRA staff can approve overseas employer requirements.');
        }
        if (!$isOverseas && $staff->staff_role !== 'job_vacancy') {
            return back()->with('error', 'Only Job Vacancy staff can approve local employer requirements.');
        }

        // ── Ang tanang papel kinahanglan nabasa na.
        // ──
        // ── PESO Job Vacancy staff, 2026-09-01: ang pagdawat sa business
        // ── permit nagdala sa kompanya sa Registered Employer samtang upat pa
        // ── ka papel ang wala pa naablihan. Ang buton dinhi mao ang katapusan
        // ── nga pag-uyon sa tibuok folder, dili ang una.
        // ──
        // ── Ang SRA wala giapil: usa ka pindot gikan sa listahan ang iyaha,
        // ── walay per-document nga lakang didto nga mahurot. ──
        if (!$isOverseas && !$requirement->allDocumentsDecided()) {
            $left = collect($requirement->documentsNotYetDecided())
                ->map(fn($f) => \App\Models\EmployerRequirement::documentLabel($f))
                ->implode(', ');

            return back()->with('error',
                'Use the Approve or Reject button beside each document first. '
                . 'Still to review: ' . $left . '.');
        }

        // Ang gibalibaran nga papel dili ma-approve pinaagi sa pag-agi sa lain.
        // Ang folder nga naay sayop mobalik sa employer, dili moadto sa
        // Registered Employer.
        if (!$isOverseas && $requirement->hasRejectedDocuments()) {
            $bad = collect($requirement->rejected_fields)
                ->map(fn($f) => \App\Models\EmployerRequirement::documentLabel($f))
                ->implode(', ');

            return back()->with('error',
                'You rejected: ' . $bad . '. Send the folder back to the employer, '
                . 'or approve those documents first.');
        }

        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
        $requirement->update([
            'status'      => 'approved',
            'reviewed_by' => $staffRecord->staff_id ?? null,
            'remarks'     => null,
        ]);

        // ── Reactivation. Ang employer nga na-restrict tungod sa expired nga
        // ── Business Permit mobalik og 'approved' dinhi mismo — walay lain nga
        // ── buton nga pangitaon sa staff. Ang 'deactivated' wala gihilabti:
        // ── kana kay desisyon sa Admin, dili masulbad sa usa ka papel. ──
        $employerUser = $requirement->employer?->employer;
        $wasRestricted = $employerUser && $employerUser->status === 'restricted';
        if ($wasRestricted) {
            $employerUser->update(['status' => 'approved']);
        }

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'requirements_approved',
            'title'          => 'Requirements Approved ✅',
            'message'        => $wasRestricted
                ? 'Your renewed requirements have been approved by PESO staff. Job posting and job fair invitations are active again.'
                : 'Your submitted requirements have been approved by PESO staff. You can now request in-house interviews and post job vacancies.',
            'reference_type' => 'employer_requirement',
            'reference_id'   => $requirement->employer_requirements_id,
        ], $requirement->user_id);

        // ── Karon pa siya nahimong eligible, mao nga karon pa siya makasulod
        // ── sa mga job fair nga naay lugar para sa iyang tipo.
        // ──
        // ── Kaniadto ang invitation gipadala kausa ra — sa mismong gutlo sa
        // ── paghimo sa event. Ang employer nga ni-rehistro human niadto wala
        // ── gyud makadawat, ug walay buton nga makapadala kaniya. ──
        $invited = 0;
        if ($requirement->employer) {
            $invited = \App\Support\JobFairInvites::inviteToOpenEvents($requirement->employer);
        }

        return redirect()->route('staff.requirements')
            ->with('success', ($wasRestricted
                    ? 'Requirements approved. The employer account has been reactivated.'
                    : 'Requirements approved successfully.')
                . ($invited > 0
                    ? ' They were also invited to ' . $invited . ' upcoming job fair event(s).'
                    : ''));
    }

    public function rejectRequirement(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $requirement = \App\Models\EmployerRequirement::with('employer')->findOrFail($id);

        $isOverseas = $requirement->employer->is_overseas ?? false;
        if ($isOverseas && $staff->staff_role !== 'sra') {
            return back()->with('error', 'Only SRA staff can reject overseas employer requirements.');
        }
        if (!$isOverseas && $staff->staff_role !== 'job_vacancy') {
            return back()->with('error', 'Only Job Vacancy staff can reject local employer requirements.');
        }

        // ── SRA rejection: simple reason ra, walay per-document checklist ──
        if ($isOverseas) {
            $request->validate(['remarks' => 'required|string|max:500']);
            $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
            $requirement->update([
                'status'      => 'rejected',
                'reviewed_by' => $staffRecord->staff_id ?? null,
                'remarks'     => $request->remarks,
            ]);

            \App\Models\Announcement::sendToEmployers([
                'type'           => 'requirements_rejected',
                'title'          => 'Requirements Rejected ❌',
                'message'        => 'Please resubmit your requirements. Reason: ' . $request->remarks,
                'reference_type' => 'employer_requirement',
                'reference_id'   => $requirement->employer_requirements_id,
            ], $requirement->user_id);

            return redirect()->route('staff.requirements')  
                ->with('success', 'Requirements rejected.');
        }

        $request->validate([
            'remarks'           => 'required|string|max:500',
            'rejected_fields'   => 'required|array|min:1',
            'rejected_fields.*' => 'in:business_permit,sec_dti,company_profile,no_pending_case_certificate,vacancy_posting',
        ], [
            'rejected_fields.required' => 'Please check at least one document that is incorrect or missing.',
        ]);
        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
        // Ang gibalik nga papel dili na dawat. Kung magpabilin siya sa
        // approved_fields, ang resubmit sa employer moabot nga "nadawat na" ug
        // ang desk dili na makabasa sa bag-o nga kopya.
        // Ang per-document nga hinungdan magpabilin para lang sa papel nga
        // tinuod nga gibalibaran. Ang gitangtang sa desk sa katapusang gutlo
        // dili na magdala ug hinungdan sa kompanya.
        $notes = collect($requirement->rejection_notes ?: [])
            ->only($request->rejected_fields)
            ->all();

        $requirement->update([
            'status'          => 'rejected',
            'reviewed_by'     => $staffRecord->staff_id ?? null,
            'remarks'         => $request->remarks,
            'rejected_fields' => $request->rejected_fields,
            'rejection_notes' => $notes,
            'approved_fields' => collect($requirement->approved_fields ?: [])
                ->reject(fn($f) => in_array($f, $request->rejected_fields, true))
                ->values()
                ->all(),
        ]);

        // Ang employer nagbasa sa usa ka mensahe, dili lima. Ang matag papel
        // nagdala sa kaugalingong hinungdan kung gisulat siya sa desk.
        $rejectedLabels = collect($request->rejected_fields)
            ->map(function ($f) use ($notes) {
                $label = \App\Models\EmployerRequirement::documentLabel($f);
                return isset($notes[$f]) ? $label . ' (' . $notes[$f] . ')' : $label;
            })
            ->implode('; ');

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'requirements_rejected',
            'title'          => 'Requirements Rejected ❌',
            'message'        => 'Please resubmit the following document(s): ' . $rejectedLabels . '. Reason: ' . $request->remarks,
            'reference_type' => 'employer_requirement',
            'reference_id'   => $requirement->employer_requirements_id,
        ], $requirement->user_id);

        return redirect()->route('staff.requirements')
            ->with('success', 'Requirements rejected.');
    }

    // ───────────────────────────────
    // EMPLOYERS PAGE (LRA/SRA/Job Vacancy)
    // ───────────────────────────────
    public function employers()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;

        // PESO, 2026-08-26: ang Job Fair desk wala nay page dinhi. Dili siya
        // mo-approve ug account — ang Job Vacancy ug ang SRA ang naghimo
        // niana — mao nga ang bugtong butang nga iya niya sa employer mao ang
        // pagdala ug walk-in ngadto sa fair, ug kana nagpuyo na sa Job Postings.
        if ($staffRole === 'job_fair') {
            return redirect()->route('staff.jobfair.postings');
        }
        $tab       = $staffRole === 'lra' ? 'approved' : request('tab', 'pre');
        $search    = request('search');

        // ── Ang sala sa industriya.
        // ──
        // ── PESO, 2026-08-26: ang desk kinahanglan makatan-aw sa mga
        // ── Manufacturing lang, o sa Education lang, ug ma-download kadto.
        // ── Gikuha gikan sa INDUSTRY_GROUPS lang, aron ang query string dili
        // ── makasulod ug bisan unsa. ──
        $industry = request('industry');
        if (!in_array($industry, EmployerNsrpRegistration::INDUSTRY_GROUPS, true)) {
            $industry = null;
        }

        // ── Ang "Nearly Expired Requirements" nga stat card sa dashboard
        // ── mo-landing dinhi. Sala lang ni sa Approved tab: usa ka kompanya
        // ── nga wala pa ma-approve walay papel nga mahurot. ──
        $expiringOnly = $tab === 'approved' && request('filter') === 'expiring';

        // ── Usa ka kompanya nga gikan sa bell. ──
        //
        // Ang notice sa inactivity naghisgot ug usa ka kompanya, mao nga ang
        // link mo-abot sa page nga naay maong laray ug mo-ilhan siya. Kung
        // ang desk ang mo-page pinaagi sa kamot, ang iyang ?page= ang mo-daog.
        $highlight = (int) request('highlight') ?: null;

        // ── USA KA ROW KADA KOMPANYA, DILI KADA ACCOUNT.
        // ──
        // ── PESO IT, 2026-08-26: usa ka HR mahimong maghawid ug duha ka
        // ── kompanya sa usa ka email. Ang gi-approve sa desk kay ang
        // ── ESTABLISEMENTO — kaugalingong papel, kaugalingong permit,
        // ── kaugalingong bakante — mao nga ang listahan mo-ihap ug
        // ── kompanya. Kung mag-ihap ni ug account, ang ikaduhang kompanya
        // ── dili gyud makita sa desk ug dili gyud ma-approve. ──
        $baseQuery = EmployerNsrpRegistration::query()
            ->whereHas('employer', fn($q) => $q->where('role', 'company'))
            ->when($staffRole === 'lra' || $staffRole === 'job_vacancy',
                fn($q) => $q->where('is_overseas', false))
            ->when($staffRole === 'sra',
                fn($q) => $q->where('is_overseas', true))
            ->when($industry, fn($q) => $q->where('industry_group', $industry))
            ->when($search, fn($q) => $q->where(fn($w) =>
                $w->where('company_name', 'like', "%{$search}%")
                  ->orWhereHas('employer', fn($u) => $u->where('email', 'like', "%{$search}%"))
            ));

        // ── Ang employer nga na-disable kay hunong na sa pagpasa ug bakante
        // ── ug wala mitubag sa email. Ang gi-una mao ang nakatubag na — sila
        // ── ang naghulat sa desisyon sa staff. ──
        if ($tab === 'dormant') {
            $employers = (clone $baseQuery)
                ->whereNotNull('dormant_at')
                ->with('employer')
                ->get()
                ->sortBy(fn($c) => [
                    $c->inactivity_responded_at ? 0 : 1,
                    -optional($c->dormant_at)->timestamp,
                ])
                ->values();

            $page = $this->pageForHighlight(
                $employers->pluck('employer_nsrp_registrations_id')->all(), $highlight, 5
            );

            $employers = new \Illuminate\Pagination\LengthAwarePaginator(
                $employers->forPage($page, 5),
                $employers->count(),
                5,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } elseif ($tab === 'pre') {
            $preQuery = (clone $baseQuery)
                ->where(fn($q) =>
                    $q->whereDoesntHave('requirement')
                      ->orWhereHas('requirement', fn($q2) => $q2->where('status', 'pending'))
                )
                ->latest();

            $employers = (clone $preQuery)
                ->with('employer')
                ->paginate(5, ['*'], 'page', $this->pageForHighlight(
                    (clone $preQuery)->pluck('employer_nsrp_registrations_id')->all(), $highlight, 5
                ))
                ->withQueryString();
        } else {
            $approvedQuery = (clone $baseQuery)
                ->whereHas('requirement', fn($q) => $q->where('status', 'approved'))
                // Ang inactive naa sa kaugalingong tab. Kung magpakita sila
                // dinhi pud, duha ka lugar ang parehas nga employer ug walay
                // makahibalo asa siya angay tan-awon.
                ->whereNull('dormant_at')
                ->when($expiringOnly, fn($q) =>
                    $q->whereHas('requirement', fn($r) => $r->expiringSoon()))
                ->latest();

            $employers = (clone $approvedQuery)
                ->with(['employer', 'requirement', 'jobs.applications' => fn($q) => $q->where('status', 'hired')])
                ->paginate(5, ['*'], 'page', $this->pageForHighlight(
                    (clone $approvedQuery)->pluck('employer_nsrp_registrations_id')->all(), $highlight, 5
                ))
                ->withQueryString();
        }

        $totalPre = (clone $baseQuery)
            ->where(fn($q) =>
                $q->whereDoesntHave('requirement')
                  ->orWhereHas('requirement', fn($q2) => $q2->where('status', 'pending'))
            )->count();

        $totalApproved = (clone $baseQuery)
            ->whereHas('requirement', fn($q) => $q->where('status', 'approved'))
            ->whereNull('dormant_at')
            ->count();

        $totalDormant = (clone $baseQuery)
            ->whereNotNull('dormant_at')
            ->count();

        $totalExpiring = (clone $baseQuery)
            ->whereHas('requirement', fn($q) => $q->where('status', 'approved')->expiringSoon())
            ->whereNull('dormant_at')
            ->count();

        return view('staff.employers.index', [
            'employers'     => $employers,
            'staffRole'     => $staffRole,
            'tab'           => $tab,
            'totalPre'      => $totalPre,
            'totalApproved' => $totalApproved,
            'totalDormant'  => $totalDormant,
            'totalExpiring' => $totalExpiring,
            'expiringOnly'  => $expiringOnly,
            'highlight'     => $highlight,
            'industry'      => $industry,
            'industries'    => EmployerNsrpRegistration::INDUSTRY_GROUPS,
        ]);
    }

    /**
     * Which page of a list a highlighted row falls on.
     *
     * A bell notification names one company. Landing on page one of a list the
     * company is not on leaves the staff hunting for the row the notice just
     * told them about, so the link carries ?highlight= and this works out the
     * page. An explicit ?page= wins: that is the desk paging by hand, and it
     * would be maddening for the list to keep jumping back.
     *
     * The ids are read in the same order the page is built in, so the maths
     * cannot drift from what is actually shown.
     */
    private function pageForHighlight(array $orderedIds, ?int $highlight, int $perPage): int
    {
        if (!$highlight || request()->filled('page')) {
            return max((int) request('page', 1), 1);
        }

        $index = array_search($highlight, array_map('intval', $orderedIds), true);

        return $index === false ? 1 : intdiv($index, $perPage) + 1;
    }

    // ───────────────────────────────
    // EMPLOYERS — EXCEL
    // ───────────────────────────────
    /**
     * The approved employers this desk owns, as a spreadsheet.
     *
     * PESO, 2026-08-26: the desk filters the list to one industry and wants
     * that list on paper. The filter and the download read the same query, so
     * what downloads is exactly what is on screen — nothing wider.
     *
     * Local belongs to Job Vacancy, overseas to SRA. The desk that cannot see
     * an employer on screen cannot pull it into a file either.
     */
    public function exportEmployers()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        if (!in_array($staffRole, ['lra', 'sra', 'job_vacancy'], true)) {
            return redirect()->route('staff.dashboard');
        }

        $industry = request('industry');
        if (!in_array($industry, EmployerNsrpRegistration::INDUSTRY_GROUPS, true)) {
            $industry = null;
        }
        $search = request('search');

        $companies = EmployerNsrpRegistration::query()
            ->whereHas('employer', fn($q) => $q->where('role', 'company'))
            ->when($staffRole === 'lra' || $staffRole === 'job_vacancy',
                fn($q) => $q->where('is_overseas', false))
            ->when($staffRole === 'sra', fn($q) => $q->where('is_overseas', true))
            ->whereHas('requirement', fn($q) => $q->where('status', 'approved'))
            ->whereNull('dormant_at')
            ->when($industry, fn($q) => $q->where('industry_group', $industry))
            ->when($search, fn($q) => $q->where(fn($w) =>
                $w->where('company_name', 'like', "%{$search}%")
                  ->orWhereHas('employer', fn($u) => $u->where('email', 'like', "%{$search}%"))
            ))
            ->with(['employer', 'jobs.applications'])
            ->orderBy('company_name')
            ->get();

        $preamble = array_values(array_filter([
            ['Report', 'Registered Employer'],
            ['Desk', match ($staffRole) {
                'sra'          => 'Overseas (SRA)',
                'job_vacancy'  => 'Local (Job Vacancy)',
                default        => 'Local (LRA)',
            }],
            ['Industry', $industry ?: 'All industries'],
            $search ? ['Search', $search] : null,
            ['Total', $companies->count()],
            ['Generated', now()->format('Y-m-d H:i')],
        ]));

        return \App\Support\ExcelExport::stream(
            'peso-approved-employers-' . now()->format('Ymd') . '.xlsx',
            ['#', 'Company', 'Trade Name', 'Industry Group', 'Employer Type', 'Placement',
             'Contact Person', 'Position', 'Mobile', 'Email', 'Address',
             'Open Vacancies', 'Total Hired', 'Registered'],
            $companies->values()->map(function ($c, $i) {
                $jobs = $c->jobs;

                return [
                    $i + 1,
                    $c->company_name,
                    $c->trade_name ?: 'None',
                    $c->industry_group ?: 'Not set',
                    $c->employer_type ?: 'None',
                    $c->is_overseas ? 'Overseas' : 'Local',
                    $c->contact_person ?: 'None',
                    $c->position_title ?: 'None',
                    $c->mobile_number ?: 'None',
                    $c->employer->email ?? 'None',
                    collect([$c->est_barangay, $c->est_city_municipality, $c->est_province])
                        ->filter()->implode(', ') ?: 'None',
                    $jobs->where('status', 'open')->count(),
                    $jobs->flatMap->applications->where('status', 'hired')->count(),
                    optional($c->created_at)->format('Y-m-d'),
                ];
            }),
            $preamble
        );
    }

    // ───────────────────────────────
    // WALK-IN EMPLOYER — Job Vacancy staff encoding a company at the counter
    // ───────────────────────────────
    //
    // The jobseeker counter has had this since July (staff.nsrp). The employer
    // counter had nothing: a local employer who walked in was told to go home
    // and register online, and the vacancy they came to give was lost with
    // them. Staff now do the whole visit in one sitting — company, documents,
    // vacancy.
    //
    // Two things are deliberately different from the online wizard:
    //
    //   1. The requirements are REQUIRED and are saved already approved. The
    //      staff member is holding the papers; there is no second reviewer to
    //      wait for. `reviewed_by` records who that was.
    //   2. The staff never types a password. The system issues a temporary one,
    //      shown once on the Employers page, and `must_change_password` forces
    //      the employer to replace it at first login — the same handling the HR
    //      handover already uses.
    private function assertJobVacancyStaff()
    {
        $staff = $this->authStaff();
        if (!$staff) return [null, redirect()->route('login')];
        if ($staff->staff_role !== 'job_vacancy') {
            return [null, redirect()->route('staff.dashboard')
                ->with('error', 'Only Job Vacancy staff can open this page.')];
        }
        return [$staff, null];
    }

    // ── Duha ka counter ang mo-encode ug walk-in nga employer: ang Job Vacancy
    // ── para sa lokal, ug ang SRA para sa overseas. Ang role mao ang mo-desisyon
    // ── kung asa nga listahan mosulod ang kompanya — dili ang gipili nga
    // ── employer type — kay ang desk mismo ang nag-atiman kaniya. ──
    private function assertWalkinEmployerStaff()
    {
        $staff = $this->authStaff();
        if (!$staff) return [null, redirect()->route('login')];
        // Job Fair staff apil sukad 2026-08-26: naa'y employer nga moduol sa
        // adlaw sa fair kay nakabasa siya sa post sa Facebook, dala ang iyang
        // papel ug ang iyang bakante. Kung dili siya maka-encode, mapalta ang
        // employer sa fair nga iyang giadtoan.
        if (!in_array($staff->staff_role, ['job_vacancy', 'sra', 'job_fair'], true)) {
            return [null, redirect()->route('staff.dashboard')
                ->with('error', 'Only Job Vacancy, SRA and Job Fair staff can register a walk-in employer.')];
        }
        return [$staff, null];
    }

    // ── Ang duha ka feed sa calendar picker sa walk-in nga porma. Parehas
    // ── gyud sa gikaon sa modal sa employer, gikan sa samang App\Support\
    // ── InhouseBooking — mao nga ang adlaw nga puno sa usa puno pud sa usa. ──
    public function inhouseBookedDates()
    {
        $staff = $this->authStaff();
        if (!$staff) return response()->json(['error' => 'Unauthorized'], 401);

        return response()->json(['booked_dates' => \App\Support\InhouseBooking::bookedDates()]);
    }

    public function inhouseCheckDate(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff) return response()->json(['error' => 'Unauthorized'], 401);

        return response()->json(\App\Support\InhouseBooking::availability(
            $request->query('date'),
            $request->query('date_end'),
            $request->query('venue_type', 'peso_office')
        ));
    }

    public function walkinEmployer()
    {
        [$staff, $deny] = $this->assertWalkinEmployerStaff();
        if ($deny) return $deny;

        return view('staff.employers.walkin', [
            'staffRole'         => $staff->staff_role,
            // SRA = overseas nga counter, Job Vacancy = lokal. Ang Job Fair
            // walay kaugalingong merkado: modawat siya sa duha, ug ang employer
            // type mao ang mopili — parehas sa online nga registration.
            'isOverseas'        => $staff->staff_role === 'sra',
            // Ang calendar picker mao ra gihapon sa employer: ang puno nga
            // adlaw naka-disable ug pula, ang holiday dalag, ug ang live nga
            // pagsusi mosulti kung pila na ang naka-book.
            'earliestBookable'  => \App\Support\OfficeCalendar::earliestBookableDate(),
            'inhouseDailyLimit' => \App\Support\InhouseBooking::dailyLimit(),
            // Job Fair ra ang channel nga nagkinahanglan ug event. Kung walay
            // umaabot, ang checkbox dili mapili.
            'jobFairEvents'     => \App\Models\JobFairEvent::whereDate('event_date', '>=', today())
                                       ->where('status', '!=', 'completed')
                                       ->orderBy('event_date')
                                       ->get(),
        ]);
    }

    public function storeWalkinEmployer(Request $request)
    {
        [$staff, $deny] = $this->assertWalkinEmployerStaff();
        if ($deny) return $deny;

        $isJobFairDesk = $staff->staff_role === 'job_fair';

        // Ang SRA nga counter kay overseas, ang Job Vacancy kay lokal. Ang Job
        // Fair modawat sa duha, mao nga ang employer type ang mosulti — mao
        // gihapon ang lagda nga gisunod sa online nga registration.
        $isOverseas = $isJobFairDesk
            ? $request->employer_type === 'Overseas Recruitment Agency'
            : $staff->staff_role === 'sra';

        $requestedTypes = array_values(array_intersect(
            ['company_interview', 'inhouse', 'job_fair'],
            array_map('strval', (array) $request->input('schedule_types', []))
        ));

        // Ang Job Fair desk usa ra ang gibuhat: pagdala ug employer ngadto sa
        // fair. Ang in-house iya sa LRA ug ang company interview sa Job
        // Vacancy — dili sila iya niining counter.
        if ($isJobFairDesk) {
            $requestedTypes = ['job_fair'];
        }

        $wantsCompanyInterview = in_array('company_interview', $requestedTypes, true);
        $wantsInhouse          = in_array('inhouse', $requestedTypes, true);
        $wantsJobFair          = in_array('job_fair', $requestedTypes, true);

        $request->validate([
            // ── I. Establishment Details ──
            'company_name'          => 'required|string|max:255',
            'trade_name'            => 'nullable|string|max:255',
            'tin'                   => 'required|string|max:50',
            'tin_type'              => 'nullable|in:main,branch',
            'employer_type'         => 'required|string',
            'total_workforce'       => 'nullable|in:micro,small,medium,large',
            'line_of_business'      => 'nullable|string|max:255',
            'industry_group'        => ['required', \Illuminate\Validation\Rule::in(\App\Models\EmployerNsrpRegistration::INDUSTRY_GROUPS)],
            'est_barangay'          => 'nullable|string|max:255',
            'est_city_municipality' => 'nullable|string|max:255',
            'est_province'          => 'nullable|string|max:255',
            // ── II. Establishment Contact Details ──
            'contact_person'  => 'required|string|max:255',
            'position_title'  => 'required|string|max:255',
            'contact_title'   => 'nullable|string|max:20',
            'telephone_no'    => 'nullable|string|max:20',
            'mobile_number'   => ['required', 'string', new \App\Rules\MobileNumber],
            'fax_no'          => 'nullable|string|max:20',
            'email'           => 'required|email|unique:users,email',
            // ── III. Requirements — required here, unlike the online wizard,
            // ── because they are saved approved on the strength of the staff
            // ── member having seen them. ──
            'business_permit'             => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'sec_dti'                     => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'company_profile'             => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'no_pending_case_certificate' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'vacancy_posting'             => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            // Ang business permit tinuig — ang tuig ang gipangayo, dili petsa.
            'business_permit_year'                   => 'required|integer|min:' . (now()->year - 2) . '|max:' . (now()->year + 1),
            'company_logo'                           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'sec_dti_expires_at'                     => 'required|date|after:today',
            'company_profile_expires_at'             => 'required|date|after:today',
            'no_pending_case_certificate_expires_at' => 'required|date|after:today',
            'vacancy_posting_expires_at'             => 'required|date|after:today',
            // ── IV. The vacancy they came to give ──
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'location'    => 'required|string|max:255',
            'type'        => 'required|in:full_time,part_time,contractual,casual',
            'slots'       => 'required|integer|min:1',
            'salary'      => 'nullable|string|max:255',
            'deadline'    => 'nullable|date|after:today',
            // Gipangutana kay ang fair nga para sa PWD modawat lang sa bakante
            // nga "yes" dinhi. Kung wala ni, ang walk-in dili gyud makasulod
            // sa maong fair, ug walay porma nga makatul-id niini human.
            'accepts_disability' => 'required|in:yes,no',
            'disability_types'   => 'nullable|array',
            'disability_types.*' => 'string|max:50',
            // ── Ang channel. Parehas sa modal sa employer: usa ka fill-up,
            // ── mahimong daghang channel, apan usa ra gihapon ka set sa
            // ── bakante tungod sa posting_group_id. ──
            'schedule_types'   => 'required|array|min:1',
            'schedule_types.*' => 'in:inhouse,company_interview,job_fair',
            'company_interview_date' => [\Illuminate\Validation\Rule::requiredIf($wantsCompanyInterview), 'nullable', 'date',
                                         'after_or_equal:' . \App\Support\OfficeCalendar::earliestBookableDate()],
            'inhouse_date'     => [\Illuminate\Validation\Rule::requiredIf($wantsInhouse), 'nullable', 'date',
                                   'after_or_equal:' . \App\Support\OfficeCalendar::earliestBookableDate()],
            'inhouse_date_end' => 'nullable|date|after_or_equal:inhouse_date',
            'venue_type'       => [\Illuminate\Validation\Rule::requiredIf($wantsInhouse), 'nullable', 'in:peso_office,other'],
            'venue_address'    => 'required_if:venue_type,other|nullable|string|max:255',
            'job_fair_id'      => [\Illuminate\Validation\Rule::requiredIf($wantsJobFair), 'nullable',
                                   'exists:job_fair_events,job_fair_events_id'],
            // ── Certification ──
            'certification_agreed' => 'required|accepted',
        ], [
            'email.unique' => 'This email address is already registered. Search the Employers list instead of encoding a second account.',
            'certification_agreed.accepted' => 'The employer must agree to the certification and authorization.',
            'schedule_types.required' => 'Pick at least one schedule type for this vacancy.',
            'accepts_disability.required' => 'Ask the employer whether this vacancy accepts applicants with disability.',
            'company_interview_date.required' => 'Pick the preferred date for the Company Interview posting.',
            'inhouse_date.required'   => 'Pick the preferred date for the In-house posting.',
            'company_interview_date.after_or_equal' => \App\Support\OfficeCalendar::leadTimeMessage(),
            'inhouse_date.after_or_equal'           => \App\Support\OfficeCalendar::leadTimeMessage(),
            'inhouse_date_end.after_or_equal'       => 'The last available date cannot be before the first.',
            'venue_type.required'     => 'Pick the venue for the In-house interview.',
            'job_fair_id.required'    => 'Pick which job fair this vacancy is for.',
        ]);

        // ── Sarado ang PESO sa holiday. Ang date picker mo-disable na niini,
        // ── apan ang browser dili basihan — ang POST mahimong moabot bisan asa.
        // ── Ang in-house nga window gilaktawan kung labaw pa sa usa ka adlaw:
        // ── walay daot kung naa'y holiday sulod niini, laing adlaw na lang ang
        // ── pilion sa employer. ──
        $inhouseIsOneDay = !$request->filled('inhouse_date_end')
            || $request->input('inhouse_date_end') === $request->input('inhouse_date');

        $holidays = \App\Support\Holidays::aroundNow();
        foreach ([
            'company_interview_date' => $request->input('company_interview_date'),
            'inhouse_date'           => $inhouseIsOneDay ? $request->input('inhouse_date') : null,
        ] as $field => $picked) {
            if ($picked && isset($holidays[$picked])) {
                return back()->withInput()->withErrors([
                    $field => 'PESO is closed on ' . $holidays[$picked]
                              . ' (' . \Carbon\Carbon::parse($picked)->format('M d, Y') . '). Please pick another date.',
                ]);
            }
        }

        // Parehas gyud nga lagda sa gisunod sa employer online — usa ra ka
        // code ang naghubad niini, tan-awa ang App\Support\InhouseBooking.
        if ($wantsInhouse) {
            if ($why = \App\Support\InhouseBooking::blockedWindowError($request->inhouse_date, $request->inhouse_date_end)) {
                return back()->withInput()->with('error', $why);
            }
            if ($request->venue_type === 'peso_office'
                && $why = \App\Support\InhouseBooking::capacityError($request->inhouse_date, $request->inhouse_date_end)) {
                return back()->withInput()->with('error', $why);
            }
        }

        // ── Ang fair mismo ang mopili.
        // ──
        // ── Ang Job Fair desk mag-encode niini ug modiretso na dayon sa fair,
        // ── mao nga walay approval screen nga makasalikway niini human. Ang
        // ── samang lagda sa approveJobFairJob gipangutana dinhi, aron ang
        // ── bakante nga dili modawat ug PWD dili makasulod sa PWD nga fair
        // ── pinaagi lang sa counter. ──
        if ($wantsJobFair && $request->job_fair_id) {
            $fairCheck = \App\Models\JobFairEvent::find($request->job_fair_id);
            $draft     = new \App\Models\Job([
                'accepts_disability' => $request->accepts_disability,
                'industry_group'     => $request->industry_group,
            ]);

            if ($fairCheck && $why = $fairCheck->postingMismatch($draft)) {
                return back()->withInput()->withErrors(['job_fair_id' => $why]);
            }
        }

        // Ang plaintext gibalik kausa ra sa nag-encode nga staff — wala gyud
        // kini gitipigan ug wala gi-email. Samang tawag sa HR handover.
        $tempPassword = \Illuminate\Support\Str::password(12, true, true, true, false);
        $staffId      = \App\Models\Staff::where('user_id', $staff->users_id)->value('staff_id');

        $documentFields = [
            'business_permit', 'sec_dti', 'company_profile',
            'no_pending_case_certificate', 'vacancy_posting',
        ];

        // Ang file gi-store sa gawas sa transaction: ang disk dili mo-rollback,
        // ug ang mga path kinahanglan andam na sa dili pa mosulod sa DB.
        $uploadedDocs = [];
        foreach ($documentFields as $field) {
            $uploadedDocs[$field] = $request->file($field)->store('employer_requirements', 'local');

            if ($field === 'business_permit') {
                $year = (int) $request->input('business_permit_year');
                $uploadedDocs['business_permit_year']       = $year;
                $uploadedDocs['business_permit_expires_at'] = \Carbon\Carbon::create($year, 12, 31)->toDateString();
                continue;
            }

            $uploadedDocs["{$field}_expires_at"] = $request->input("{$field}_expires_at");
        }

        if ($request->hasFile('company_logo')) {
            $uploadedDocs['company_logo'] = $request->file('company_logo')->store('employer_requirements', 'local');
        }

        try {
            $employer = \Illuminate\Support\Facades\DB::transaction(function () use (
                $request, $tempPassword, $staffId, $uploadedDocs, $requestedTypes, $isOverseas, $isJobFairDesk
            ) {
                $user = User::create([
                    'name'                 => $request->company_name,
                    'email'                => $request->email,
                    'phone'                => $request->mobile_number,
                    'password'             => \Illuminate\Support\Facades\Hash::make($tempPassword),
                    'role'                 => 'company',
                    'status'               => 'approved',
                    'must_change_password' => true,
                ]);

                $employer = \App\Models\EmployerNsrpRegistration::create([
                    'user_id'                   => $user->users_id,
                    'company_name'              => $request->company_name,
                    'contact_person'            => $request->contact_person,
                    'position_title'            => $request->position_title,
                    'mobile_number'             => $request->mobile_number,
                    'employer_type'             => $request->employer_type,
                    // Ang desk nga nag-encode mao ang mopili: SRA = overseas,
                    // Job Vacancy = lokal. Ang online nga registration nagbasa
                    // niini gikan sa employer type; dinhi ang tawo sa counter
                    // nay nahibalo, ug siya nay tubagon sa iyang listahan.
                    'is_overseas'               => $isOverseas,
                    'is_walk_in'                => true,
                    'trade_name'                => $request->trade_name,
                    'tin'                       => $request->tin,
                    'tin_type'                  => $request->tin_type,
                    'total_workforce'           => $request->total_workforce,
                    'line_of_business'          => $request->line_of_business,
                    'industry_group'            => $request->industry_group,
                    'est_barangay'              => $request->est_barangay,
                    'est_city_municipality'     => $request->est_city_municipality,
                    'est_province'              => $request->est_province,
                    'contact_title'             => $request->contact_title,
                    'telephone_no'              => $request->telephone_no,
                    'fax_no'                    => $request->fax_no,
                    'certification_agreed'      => true,
                    'certification_date'        => now()->toDateString(),
                    'initial_vacancy_data'      => [],
                    'initial_vacancy_confirmed' => true,
                ]);

                // `user_id` dinhi kay ang employer_nsrp_registrations_id, dili
                // ang users_id — daan na nga ngalan, tan-awa ang DocumentController.
                \App\Models\EmployerRequirement::create(array_merge([
                    'user_id'     => $employer->employer_nsrp_registrations_id,
                    'status'      => 'approved',
                    'reviewed_by' => $staffId,
                    'remarks'     => 'Walk-in registration. Documents presented at the PESO counter and verified by the encoding staff.',
                ], $uploadedDocs));

                // ── Usa ka job row kada channel, parehas sa gibuhat sa modal
                // ── sa employer. Ang una nga row mo-turol sa iyang kaugalingon
                // ── ug ang sunod didto pud mo-turol — mao nay posting_group_id,
                // ── ug mao nay hinungdan nga ang usa ka posisyon nga gi-post sa
                // ── tulo ka channel usa ra gihapon ka set sa bakante. ──
                $groupId  = null;
                $fairEvent = $request->job_fair_id
                    ? \App\Models\JobFairEvent::find($request->job_fair_id)
                    : null;

                foreach ($requestedTypes as $type) {
                    $scheduleDate = match ($type) {
                        'inhouse'           => $request->inhouse_date,
                        'company_interview' => $request->company_interview_date,
                        // Ang job fair walay pinili nga adlaw: ang adlaw sa
                        // event mismo ang petsa niini.
                        'job_fair'          => $fairEvent?->event_date->toDateString(),
                        default             => null,
                    };

                    // Sa in-house, ang gitanyag kay window. Ang kataposang adlaw
                    // niini ang gamiton isip fallback nga deadline — dili mosira
                    // ang posting samtang mahimo pa ang interview.
                    $scheduleEnd = $type === 'inhouse'
                        ? ($request->inhouse_date_end ?: $request->inhouse_date)
                        : null;

                    $job = \App\Models\Job::create(array_merge([
                        'company_id'         => $employer->employer_nsrp_registrations_id,
                        'title'              => $request->title,
                        'description'        => $request->description,
                        'location'           => $request->location,
                        'type'               => $request->type,
                        'industry_group'     => $request->industry_group,
                        'slots'              => $request->slots,
                        'accepts_disability' => $request->accepts_disability,
                        'disability_types'   => $request->accepts_disability === 'yes'
                                                    ? array_values((array) $request->input('disability_types', []))
                                                    : null,
                        'salary'             => $request->salary,
                        'deadline'           => $request->deadline ?: ($scheduleEnd ?: $scheduleDate),
                        'posting_type'       => 'direct',
                        'schedule_type'      => $type,
                        'preferred_date'     => $scheduleDate,
                        'preferred_date_end' => $scheduleEnd,
                        'venue_type'         => $type === 'inhouse' ? $request->venue_type : null,
                        'venue_address'      => $type === 'inhouse' && $request->venue_type === 'other'
                                                    ? $request->venue_address
                                                    : null,
                        // Ang in-house natawo nga pending: ang LRA ang tag-iya sa
                        // kalendaryo sa PESO Office, ug siya gihapon ang mo-accept
                        // niini bisan ang staff pa ang nag-encode.
                    ], \App\Support\JobPostingNotice::initialState($type)));

                    $groupId = $groupId ?? $job->job_qualifications_id;
                    $job->update(['posting_group_id' => $groupId]);

                    if ($type === 'job_fair') {
                        // Ang deadline nga mas sayo pa kay sa adlaw sa fair
                        // magpatay sa posting sa dili pa siya magamit.
                        if ($fairEvent && $job->deadline && $job->deadline->lt($fairEvent->event_date)) {
                            $job->update(['deadline' => $fairEvent->event_date->toDateString()]);
                        }

                        $job->update(['requested_job_fair_id' => $request->job_fair_id]);

                        if ($isJobFairDesk && $fairEvent) {
                            // Ang Job Fair desk mismo ang nag-encode niini sa
                            // counter, mao nga siya na ang nakadesisyon. Walay
                            // pulos nga ipadala niya ang iyang kaugalingong
                            // hangyo ngadto sa iyang kaugalingong pila.
                            $job->update([
                                'posting_status' => 'approved',
                                'status'         => \App\Support\JobFairPostingWindow::opensFor($fairEvent) ? 'open' : 'closed',
                            ]);

                            \App\Models\JobFairEmploymentRequest::firstOrCreate([
                                'job_fair_id' => $fairEvent->job_fair_events_id,
                                'employer_id' => $employer->employer_nsrp_registrations_id,
                                'job_id'      => $job->job_qualifications_id,
                            ]);

                            // Parehas sa acceptPostingIntoFair: ang unang
                            // bakante nga misulod sa fair mao ang nag-anunsyo
                            // niini sa jobseeker. Ang walk-in laing pultahan
                            // sa parehas nga lawak.
                            \App\Support\JobFairInvites::inviteJobseekers($fairEvent);
                        }
                    }
                }

                return $employer;
            });
        } catch (\Throwable $e) {
            // Ang na-upload nga file mabilin nga ilo kung mapakyas ang DB.
            foreach ($documentFields as $field) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($uploadedDocs[$field]);
            }
            throw $e;
        }

        // ── Ang in-house ra ang nagkinahanglan ug tubag gikan sa lain nga lamesa.
        // ── Ang Company Interview ug Job Fair kay kining maong staff mismo ang
        // ── nag-encode, walay hinungdan nga belan sila sa ilang kaugalingong
        // ── buhat. Ang LRA — o ang SRA kung overseas — mao ang tag-iya sa
        // ── kalendaryo, ug wala siya nakakita niining employer nga niagi sa
        // ── counter, mao nga ang bell ang mo-abot kaniya. Parehas nga porma sa
        // ── notice sa online nga posting (CompanyWebController::requestJob).
        if (in_array('inhouse', $requestedTypes, true)) {
            $venueLabel  = $request->venue_type === 'other' ? $request->venue_address : 'PESO Office';
            $windowLabel = ($request->inhouse_date_end && $request->inhouse_date_end !== $request->inhouse_date)
                ? \Carbon\Carbon::parse($request->inhouse_date)->format('M d') . ' – '
                  . \Carbon\Carbon::parse($request->inhouse_date_end)->format('M d, Y')
                : \Carbon\Carbon::parse($request->inhouse_date)->format('M d, Y');

            \App\Models\Announcement::sendToStaff([
                'type'           => 'job_posted_notice',
                'title'          => 'New In-house Posting 📅',
                'message'        => $employer->company_name . ' posted "' . $request->title
                                    . '" for an in-house interview, ' . $windowLabel . ' at ' . $venueLabel
                                    . '. Those dates are held while you decide, and the vacancy stays'
                                    . ' hidden from jobseekers until you accept.',
                // Walay reference_id: ang in-house modala sa In-house Schedule,
                // dili sa usa ka job row — parehas sa employer nga path.
                'reference_type' => 'inhouse_schedule',
                'reference_id'   => null,
            ], \App\Models\Staff::where('staff_role', $isOverseas ? 'sra' : 'lra')->pluck('staff_id'));
        }

        return redirect()->route('staff.employers', ['tab' => 'approved'])
            ->with('success', $employer->company_name . ' registered. Vacancy posted to '
                . collect($requestedTypes)->map(fn($type) => match ($type) {
                    'inhouse'           => 'In-house (waiting for LRA to accept the date)',
                    'company_interview' => 'Company Interview',
                    'job_fair'          => 'Job Fair',
                })->join(', ', ' and ') . '.')
            ->with('recovery_temp_password', $tempPassword)
            ->with('recovery_contact', $request->email);
    }

    // ───────────────────────────────
    // I-ABLI PAG-USAB ANG DORMANT NGA EMPLOYER
    // ───────────────────────────────
    //
    // Ang account gisira kay walay tubag sa pangutana sa opisina. Ang pag-abli
    // dili automatic bisan nakatubag na sila: ang tumong sa tibuok butang mao
    // nga adunay tawo sa opisina nga nakabasa kung unsa ang nahitabo sa
    // kompanya, ug siya ang mo-desisyon.
    public function enableDormantEmployer($id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        // Ang {id} kay ang KOMPANYA. Ang usa ka kompanya sa usa ka account
        // mahimong patay samtang ang usa buhi pa, mao nga ang pag-abli pag-usab
        // motumong sa establisemento ug dili sa tibuok account.
        $employer     = EmployerNsrpRegistration::with('employer')->findOrFail($id);
        $employerUser = $employer->employer;

        if (!$employerUser || $employerUser->role !== 'company' || !$employer->dormant_at) {
            return back()->with('error', 'That account is not inactive.');
        }

        // Samang lagda sa pag-approve sa requirements: ang lokal kay sa Job
        // Vacancy, ang overseas kay sa SRA.
        $isOverseas   = (bool) $employer->is_overseas;
        $allowedRoles = $isOverseas ? ['sra'] : ['job_vacancy'];

        if (!in_array($staff->staff_role, $allowedRoles, true)) {
            return back()->with('error', $isOverseas
                ? 'Only SRA staff can reopen an overseas employer account.'
                : 'Only Job Vacancy staff can reopen a local employer account.');
        }

        // Parehas gyud sa manual nga switch sa Update modal — usa ra ka lugar
        // ang naghubad kung unsa ang buksan pag-usab.
        $reopened = $this->countReopeningPostings($employer);

        \Illuminate\Support\Facades\DB::transaction(
            fn() => $this->restoreEmployerAccess($employer, $employerUser)
        );

        $this->mailEmployerReactivated($employer, $employerUser, $staff, $reopened);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'employer_account_reopened',
            'title'          => 'Your account is active again ✅',
            'message'        => 'PESO staff have reviewed your reply and reopened your account.'
                                . ' Your job postings are visible to jobseekers again.',
            'reference_type' => 'employer_inactivity',
            'reference_id'   => $employer->employer_nsrp_registrations_id,
        ], $employer->employer_nsrp_registrations_id);

        $this->announceEmployerInactive(
            $employer,
            ($employer->company_name ?? 'An employer') . ' was switched back on by '
                . trim($staff->name) . '.',
            'Employer switched back on ✅',
            false
        );

        return back()->with('success',
            ($employer->company_name ?? 'The employer') . ' can use their account again.');
    }

    // ───────────────────────────────
    // HR HANDOVER — i-ilis ang authorized contact sa usa ka employer account
    // ───────────────────────────────
    // PESO interview 2026-08-13: "Kung ang HR sa usa ka employer mo-resign...
    // pwede gamiton ang password reset basta adunay proper permission ug
    // verification. Para sa local employer, ang local staff ang mo-handle,
    // samtang para sa overseas employer, ang SRA ang mo-handle."
    //
    // Ang staff WALA gyud makakita o makabutang ug password. Ang gibuhat niya:
    // i-update ang contact details, dayon mo-padala ug reset code sa bag-ong
    // email — ang bag-ong HR ra mismo ang mo-set sa iyang password. Parehas
    // ra ni sa flow sa Forgot Password nga naa nay employer nga naggamit.
    public function transferEmployerAccount(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $employerUser = User::with('employerNsrp')->where('role', 'company')->findOrFail($id);
        $nsrp = $employerUser->employerNsrp;

        if (!$nsrp) {
            return back()->with('error', 'This employer has no NSRP registration record yet.');
        }

        // Parehas nga bahin sa trabaho sa Employers page mismo: ang local
        // employer sa lra/job_vacancy, ang overseas sa sra.
        //
        // Ang "view only" sa interview sa LRA nagpasabot nga dili sila
        // mo-approve ug employer — ug wala man gyud sila kaniadto. Ang pag-ilis
        // sa authorized contact nagpabilin kanila: sila ang gitawagan sa local
        // nga employer nga nawad-an ug HR.
        $isOverseas   = (bool) $nsrp->is_overseas;
        $allowedRoles = $isOverseas ? ['sra'] : ['lra', 'job_vacancy'];

        if (!in_array($staff->staff_role, $allowedRoles, true)) {
            return back()->with('error', $isOverseas
                ? 'Only SRA staff can change the contact on an overseas employer account.'
                : 'Only local staff (LRA or Job Vacancy) can change the contact on a local employer account.');
        }

        $validated = $request->validate(
            \App\Support\EmployerAccountRecovery::rules($employerUser->users_id) + [
                'account_status' => 'nullable|in:active,inactive',
            ],
            \App\Support\EmployerAccountRecovery::messages()
        );

        // ── ANG SWITCH ──
        // Ang pag-ilis sa contact iya sa tanang lokal nga staff; ang pagpatay sa
        // account kay sa nag-atiman ra sa maong employer, parehas sa pag-approve
        // sa requirements ug sa pag-abli sa dormant.
        $statusRoles   = $isOverseas ? ['sra'] : ['job_vacancy'];
        $wantsInactive = ($validated['account_status'] ?? null) === 'inactive';

        // Ang `dormant_at` ang tinuod nga timailhan, dili ang `users.status`
        // lamang: ang employer nga na-restrict tungod sa Business Permit
        // magpabiling inactive bisan lahi ang iyang status string.
        $currentlyInactive = $employerUser->status === 'dormant' || (bool) $nsrp->dormant_at;
        $requestedFlip     = isset($validated['account_status'])
            && $wantsInactive !== $currentlyInactive;

        if ($requestedFlip && !in_array($staff->staff_role, $statusRoles, true)) {
            return back()->with('error', $isOverseas
                ? 'Only SRA staff can switch an overseas employer account on or off.'
                : 'Only Job Vacancy staff can switch a local employer account on or off.');
        }

        $contactChanged = \App\Support\EmployerAccountRecovery::contactChanged($employerUser, $validated);

        // Walay giusab bisan usa: ayaw pagpakaaron-ingnon nga naay nahitabo.
        if (!$contactChanged && !$requestedFlip) {
            return back()->with('error', 'Nothing was changed on this account.');
        }

        $result = $contactChanged
            ? \App\Support\EmployerAccountRecovery::perform($employerUser, $validated, $staff)
            : null;

        if ($requestedFlip) {
            $this->setEmployerAccountStatus(
                $employerUser->fresh('employerNsrp'), $wantsInactive, $validated['reason'], $staff
            );
        }

        // Ang contact nga pag-ilis naay kaugalingong tubag — didto ang temporary
        // password nga makita kausa ra. Ang status idugang ra sa mensahe.
        if ($contactChanged) {
            return $this->employerRecoveryResponse($result, $validated, $requestedFlip
                ? ($wantsInactive
                    ? ' The account was also switched off, and their vacancies are now hidden.'
                    : ' The account was also switched back on.')
                : '');
        }

        return back()->with('success', ($nsrp->company_name ?? 'The employer')
            . ($wantsInactive
                ? ' can no longer sign in, and their open vacancies are hidden from jobseekers.'
                : ' can sign in again, and their vacancies are back.'));
    }

    // ───────────────────────────────
    // I-ON/OFF ANG EMPLOYER ACCOUNT
    // ───────────────────────────────
    //
    // Usa ra gyud ka kahimtang, bisan kinsa ang nagpatay niini: ang inactivity
    // sweep ug kining manual nga switch parehas og gibutang — `dormant`, uban sa
    // `dormant_at`, ug ang postings gitago. Mao nga usa ra ka tab ang tan-awon
    // sa staff, ug ang employer makasulod gihapon aron mo-sulat sa iyang rason,
    // bisan asa gikan ang pagpatay.
    //
    // `deactivated` ang wala gigamit dinhi: kana mo-babag sa login mismo
    // (UnifiedAuthController), ug ang tumong niini nga bahin mao gyud nga
    // makasulod sila aron mo-saysay.
    private function setEmployerAccountStatus(User $employerUser, bool $inactive, string $reason, User $staff): void
    {
        $employer = $employerUser->employerNsrp;
        $reopened = $inactive ? 0 : $this->countReopeningPostings($employer);

        \Illuminate\Support\Facades\DB::transaction(function () use ($employerUser, $employer, $inactive) {
            if ($inactive) {
                $employer->update(['dormant_at' => now()]);
                $employerUser->update(['status' => 'dormant']);

                // Samang marka nga gigamit sa sweep: "gisirado kay gipatay ang
                // account". Usa ra ka lagda ang nagbantay kung unsa ang buksan
                // pag-usab, bisan asa gikan ang pagpatay.
                \App\Models\Job::where('company_id', $employer->employer_nsrp_registrations_id)
                    ->where('status', 'open')
                    ->update(['status' => 'closed', 'dormant_closed_at' => now()]);

                return;
            }

            $this->restoreEmployerAccess($employer, $employerUser);
        });

        if (!$inactive) {
            $this->mailEmployerReactivated($employer, $employerUser, $staff, $reopened);
        }

        // Ang rekord sa gibuhat. Ang `employer_account_transfers` mao nay
        // nagtipig sa tanang gibuhat sa staff niining account, mao nga ang
        // pagpatay ug pagbuhi didto pud gitipigan.
        \App\Models\EmployerAccountTransfer::create([
            'employer_id'             => $employer->employer_nsrp_registrations_id,
            'performed_by'            => \App\Models\Staff::where('user_id', $staff->users_id)->value('staff_id'),
            'performed_by_user_id'    => $staff->users_id,
            'method'                  => 'status_change',
            'previous_contact_person' => $employer->contact_person,
            'previous_email'          => $employerUser->email,
            'new_contact_person'      => $employer->contact_person ?? $employerUser->name,
            'new_email'               => $employerUser->email,
            'reason'                  => $reason,
        ]);

        \App\Models\Announcement::sendToEmployers([
            'type'           => $inactive ? 'employer_account_inactive' : 'employer_account_reopened',
            'title'          => $inactive ? 'Your account is now inactive 🔒' : 'Your account is active again ✅',
            'message'        => $inactive
                ? 'PESO staff have set this account to inactive, so your vacancies are hidden from'
                  . ' jobseekers. Nothing was deleted. Sign in and tell us your status so the office'
                  . ' can switch it back on. Reason on record: ' . $reason
                : 'PESO staff have switched your account back on. Your vacancies are visible to'
                  . ' jobseekers again.',
            'reference_type' => 'employer_inactivity',
            'reference_id'   => $employer->employer_nsrp_registrations_id,
        ], $employer->employer_nsrp_registrations_id);

        // ── Ang tibuok opisina masayod. Ang nag-atiman ra ang makahimo sa
        // ── pag-abli pag-usab, apan ang uban dili angay makakita sa usa ka
        // ── employer nga kalit nawala sa listahan nga walay katin-awan. ──
        $this->announceEmployerInactive(
            $employer,
            $inactive
                ? ($employer->company_name ?? 'An employer') . ' was set to inactive by '
                  . trim($staff->name) . '. Reason: ' . $reason
                : ($employer->company_name ?? 'An employer') . ' was switched back on by '
                  . trim($staff->name) . '.',
            $inactive ? 'Employer set to inactive 🔒' : 'Employer switched back on ✅',
            $inactive
        );
    }

    // ── Usa ka notice sa nag-atiman nga desk nga naay link ngadto sa Inactive
    // ── tab, ug usa ka notice nga pahibalo ra para sa duha ka lain nga desk.
    // ── Ang reference_type gibiyaan nga null sa pahibalo: ang bell mo-fall
    // ── through ngadto sa notifications page, dili ngadto sa tab nga dili
    // ── nila maablihan. ──
    private function announceEmployerInactive(
        \App\Models\EmployerNsrpRegistration $employer,
        string $message,
        string $title,
        bool $inactive
    ): void {
        $ownerRole = $employer->is_overseas ? 'sra' : 'job_vacancy';
        $ownerName = $employer->is_overseas ? 'SRA' : 'Job Vacancy';

        \App\Models\Announcement::sendToStaff([
            'type'           => $inactive ? 'employer_account_inactive' : 'employer_account_reopened',
            'title'          => $title,
            'message'        => $message,
            'reference_type' => 'employer_inactivity',
            'reference_id'   => $employer->employer_nsrp_registrations_id,
        ], \App\Models\Staff::where('staff_role', $ownerRole)->pluck('staff_id'));

        \App\Models\Announcement::sendToStaff([
            'type'           => $inactive ? 'employer_account_inactive' : 'employer_account_reopened',
            'title'          => $title,
            'message'        => $message . ' ' . $ownerName . ' staff handle this account.',
            'reference_type' => null,
            'reference_id'   => null,
        ], \App\Models\Staff::whereNotIn('staff_role', [$ownerRole])
            ->whereIn('staff_role', ['job_vacancy', 'sra', 'lra'])
            ->pluck('staff_id'));
    }

    // ── Pila ka posting ang mobalik sa jobseekers kung buhion karon ang
    // ── account. Ihapon kini SA WALA PA ang pagbuhi — human niana wala nay
    // ── `dormant_closed_at` nga mailhan. Samang lagda sa `restoreEmployerAccess`. ──
    private function countReopeningPostings(?\App\Models\EmployerNsrpRegistration $employer): int
    {
        if (!$employer) return 0;

        return \App\Models\Job::where('company_id', $employer->employer_nsrp_registrations_id)
            ->whereNotNull('dormant_closed_at')
            ->where(function ($q) {
                $q->whereNull('deadline')->orWhereDate('deadline', '>=', today());
            })
            ->count();
    }

    // ── Ang employer nga inactive walay rason mo-adto sa site — mao gyud nay
    // ── hinungdan nga na-inactive siya. Ang bell maabot ra sa mosulod, mao nga
    // ── ang balita nga buhi na ang account gi-email, parehas sa pahimangno.
    // ──
    // ── Kung mapakyas ang mail server, ang account buhi gihapon: ang pag-email
    // ── dili angay mo-undang sa gibuhat sa staff, mao nga gi-log ra ang sayop. ──
    private function mailEmployerReactivated(
        ?\App\Models\EmployerNsrpRegistration $employer,
        User $employerUser,
        User $staff,
        int $reopenedPostings
    ): void {
        if (!$employerUser->email) return;

        try {
            \Illuminate\Support\Facades\Mail::to($employerUser->email)->send(
                new \App\Mail\EmployerAccountReactivated(
                    companyName:      $employer->company_name ?? $employerUser->name,
                    contactName:      $employer->contact_person ?? $employerUser->name,
                    staffName:        trim($staff->name),
                    reopenedPostings: $reopenedPostings,
                )
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Employer reactivation email failed to send', [
                'employer_id' => $employer?->employer_nsrp_registrations_id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    // ── Ang pagbuhi pag-usab sa usa ka gipatay nga account: ang manual nga
    // ── switch ug ang Inactive tab parehas gyud og buhaton, mao nga usa ra ka
    // ── lugar ang naghubad niini. ──
    private function restoreEmployerAccess(\App\Models\EmployerNsrpRegistration $employer, User $employerUser): void
    {
        // Ang orasan sa inactivity gilimpyohan pud: kung magpabilin ang
        // `inactivity_notified_at`, ang sweep mo-patay pag-usab ugma nga walay
        // bag-ong email — usa ka semana ang milabay na man.
        //
        // ANG TANANG UNOM, dili ang upat. Ang duha ka kolum sa ikaduhang sulat
        // gidugang sa 2026-08-30 ug kining paagi wala nakauban; ang employer
        // nga gibalik-abli nagdala gihapon sa `inactivity_second_notified_at`,
        // ug kana mao gyud ang gisalikway sa sweep. Ang sangputanan mao nga ang
        // hagdanan mamatay alang niana nga account: dili na siya mapangutana
        // pag-usab bisan unsa ka dugay siyang mohilom, ug ang disable nga
        // pag-agi mo-ihap kaniya nga "already with staff" hangtod sa hangtod.
        $employer->update([
            'dormant_at'                     => null,
            'inactivity_notified_at'         => null,
            'inactivity_second_notified_at'  => null,
            'inactivity_disable_prompted_at' => null,
            'inactivity_responded_at'        => null,
            'inactivity_status'              => null,
            'inactivity_response'            => null,
        ]);

        // Ang gisirado sa pagpatay sa account ra ang buksan pag-usab, ug kadto
        // ra nga wala pa malabyan sa deadline. Ang gisirado sa employer mismo o
        // sa deadline magpabiling sirado.
        \App\Models\Job::where('company_id', $employer->employer_nsrp_registrations_id)
            ->whereNotNull('dormant_closed_at')
            ->where(function ($q) {
                $q->whereNull('deadline')->orWhereDate('deadline', '>=', today());
            })
            ->update(['status' => 'open', 'dormant_closed_at' => null]);

        // Ang nabilin nga marka sa posting nga expired na, limpyohan pud —
        // kung dili, mabuhi sila sa sunod nga pag-abli.
        \App\Models\Job::where('company_id', $employer->employer_nsrp_registrations_id)
            ->whereNotNull('dormant_closed_at')
            ->update(['dormant_closed_at' => null]);

        // Kung na-expire ang Business Permit samtang gipatay siya, ang
        // 'restricted' ang sakto nga balikan — dili ang 'approved'. Kining
        // switch dili angay makatangtang sa laing block.
        $requirement = $employerUser->employerRequirement;
        $employerUser->update([
            'status' => ($requirement && $requirement->status === 'expired') ? 'restricted' : 'approved',
        ]);
    }

    // ── Parehas nga tubag sa staff ug sa admin. Ang temporary password gibalik
    // ── pinaagi sa flash, mao nga makita kausa ra — mawala kini sa refresh. ──
    private function employerRecoveryResponse(array $result, array $validated, string $extra = '')
    {
        if ($result['method'] === \App\Support\EmployerAccountRecovery::METHOD_TEMP_PASSWORD) {
            return back()
                ->with('recovery_temp_password', $result['temp_password'])
                ->with('recovery_contact', $validated['new_contact_person'])
                ->with('success',
                    'Account handed to ' . $validated['new_contact_person']
                    . '. Read the temporary password below to them now — it is shown only once, '
                    . 'and they must change it the moment they log in.' . $extra);
        }

        if ($result['mail_failed']) {
            return back()->with('error',
                'The account was handed to ' . $validated['new_contact_person']
                . ', but the verification email could not be sent. Ask them to use "Forgot Password" on the login page.' . $extra);
        }

        return back()->with('success',
            'Account handed to ' . $validated['new_contact_person']
            . '. A 6-digit verification code was emailed to ' . $validated['new_email']
            . ' so they can set their own password.' . $extra);
    }

    // ───────────────────────────────
    // VIEW EMPLOYER MODAL DATA
    // ───────────────────────────────
    // ── Kinsang industriya kining employer.
    // ──
    // ── Bugtong field sa employer nga mausab sa staff. Ang job fair mo-invite
    // ── pinaagi sa industriya, mao nga ang employer nga walay industriya dili
    // ── gyud maapil sa fair nga naay gipiling industriya. Ang dropdown naa na
    // ── sa registration form sukad pa, apan wala siya na-save — mao nga ang
    // ── mga naka-rehistro na kaniadto dinhi ra ma-klasipika. ──
    public function updateEmployerIndustry(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        // LRA staff, 2026-08-23: view only para nila. Ang industriya gigamit sa
        // job fair, ug ang Job Fair staff ug ang Job Vacancy staff makabutang
        // gihapon niini — mao nga walay mawala nga kapabilidad sa opisina.
        if (!in_array($staff->staff_role, ['job_fair', 'job_vacancy', 'sra'], true)) {
            return back()->with('error', 'Your role can view employers here but not change them.');
        }

        $request->validate([
            'industry_group' => ['required', Rule::in(EmployerNsrpRegistration::INDUSTRY_GROUPS)],
        ]);

        // Ang {id} kay ang KOMPANYA, dili ang account: usa ka HR mahimong
        // maghawid ug duha, ug ang industriya kabtangan sa establisemento.
        $employer = EmployerNsrpRegistration::findOrFail($id);
        $employer->update(['industry_group' => $request->industry_group]);

        // ── Ang employer nga wala pay industriya wala maapil sa bisan unsang
        // ── targeted nga fair. Karon nga naa na siya, ang mga fair nga
        // ── nangita niini nga industriya angay siyang makuha — kay kung dili,
        // ── ang staff kinahanglan pa mangita sa matag event ug mag-invite pag-
        // ── usab pinaagi sa kamot, ug ang usa nga malimtan mao ang bakante nga
        // ── walay nakadungog. ──
        $invited = \App\Support\JobFairInvites::inviteToOpenEvents($employer);

        return back()->with('success',
            $employer->company_name . ' is now classified under ' . $request->industry_group . '.'
            . ($invited > 0
                ? ' They were also invited to ' . $invited . ' upcoming job fair event(s) looking for this industry.'
                : ''));
    }

    // ───────────────────────────────
    // IN-HOUSE SCHEDULES (MERGED: InhouseSchedule + Job schedule_type=inhouse)
    // ───────────────────────────────
    public function inhouseSchedules()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        if (!in_array($staffRole, ['lra', 'sra'])) return redirect()->route('staff.dashboard');

        $search = request('search');
        $page   = (int) request('page', 1);

        $roleFilter = function ($q) use ($staffRole) {
            if ($staffRole === 'lra') {
                $q->whereHas('company', fn($n) => $n->where('is_overseas', false));
            } elseif ($staffRole === 'sra') {
                $q->whereHas('company', fn($n) => $n->where('is_overseas', true));
            }
        };

        $employerRoleFilter = function ($q) use ($staffRole) {
            if ($staffRole === 'lra') {
                $q->whereHas('employer', fn($n) => $n->where('is_overseas', false));
            } elseif ($staffRole === 'sra') {
                $q->whereHas('employer', fn($n) => $n->where('is_overseas', true));
            }
        };

        // ── SOURCE 1: InhouseSchedule (schedule-only requests) — pending ra ──
        $scheduleItems = \App\Models\InhouseSchedule::with('employer')
            ->where('status', 'pending')
            ->where($employerRoleFilter)
            ->when($search, fn($q) => $q->whereHas('employer', fn($u) =>
                $u->where('company_name', 'like', "%{$search}%")
            ))
            ->get()
            ->map(function ($s) {
                $s->source    = 'schedule';
                $s->sort_date = $s->created_at;
                return $s;
            });

        // ── SOURCE 2: Job (job posting + inhouse schedule combined).
        // ── Ang posting wala nay gi-approve — buhi na siya pag-post. Gilista
        // ── gihapon dinhi ang buhi nga in-house nga posting: kinahanglan
        // ── makita sa staff ang mga petsa nga naka-reserba, ug kung naa'y
        // ── posting nga dili angay, mahimo pa nila siyang tangtangon. ──
        $jobItems = \App\Models\Job::with('company')
            // ── Pending ra. Ang gi-approve na mobalhin sa In-house Job
            // ── Vacancy nga tab, nga nagbasa sa parehas nga posting_status
            // ── = 'approved' — mao nga usa ra ka lugar ang usa ka posting sa
            // ── matag higayon. Kaniadto nagpabilin siya dinhi human ma-accept,
            // ── ug ang page nga gitawag ug desisyon nagdala ug mga laray nga
            // ── nadesisyonan na. ──
            ->where('posting_status', 'pending')
            ->where('schedule_type', 'inhouse')
            ->where($roleFilter)
            ->when($search, fn($q) => $q->whereHas('company', fn($u) =>
                $u->where('company_name', 'like', "%{$search}%")
            ))
            ->get()
            ->map(function ($j) {
                $j->source = 'job';
                $j->sort_date = $j->created_at;
                $j->scheduled_count = ($j->venue_type === 'peso_office' && $j->preferred_date)
                    ? \App\Models\Job::where('schedule_type', 'inhouse')
                        ->where('venue_type', 'peso_office')
                        ->whereDate('preferred_date', $j->preferred_date)
                        ->where('posting_status', 'approved')
                        ->where('job_qualifications_id', '!=', $j->job_qualifications_id)
                        ->distinct('company_id')->count('company_id')
                    : 0;
                return $j;
            });

        // ── MERGE + SORT ──
        // ── Ang naghulat ug desisyon mo-una, dayon ang pinakabag-o. Kaniadto
        // ── petsa ra ang basihan, ug walay bisan unsa nga naghulat; karon nga
        // ── ang in-house mo-agi na usab sa approval, ang request nga gisumite
        // ── kagahapon malubong unta ubos sa napulo ka gi-approve na. ──
        $isWaiting = fn($item) => $item->source === 'schedule'
            ? $item->status === 'pending'
            : $item->posting_status === 'pending';

        $merged = $scheduleItems->concat($jobItems)
            ->sortByDesc('sort_date')
            ->sortByDesc($isWaiting)
            ->values();

        // ── Ang tulo ka stat card gitangtang. Ang duha kanila nag-ihap sa
        // ── parehas nga butang sa lain-laing pulong, ug ang ikatulo nag-ihap
        // ── sa mga laray nga naa na mismo sa ubos niini. Ang gidaghanon
        // ── mabasa sa lamesa. ──

        // ── MANUAL PAGINATION (kay gi-merge nato ang duha ka sources) ──
        // ── Tulo ka laray: ang matag laray naay petsa, oras, venue ug usa ka
        // ── desisyon nga himoon, mao nga ang napulo nahimong taas nga scroll. ──
        $perPage   = 3;
        $schedules = new \Illuminate\Pagination\LengthAwarePaginator(
            $merged->forPage($page, $perPage)->values(),
            $merged->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        return view('staff.inhouse.index', compact('schedules', 'staffRole'));
    }

    // ───────────────────────────────
    // STAFF ACTIVITY CALENDAR — usa ra ka feed para sa upat ka dashboard.
    //
    // Ang App\Support\StaffCalendar mao ang nagbuot kung unsa ang makita kada
    // role, mao nga dili magkalahi ang upat ka kalendaryo. Ang ngalan sa route
    // gibilin nga inhouse.calendarData: gigamit na siya sa upat ka blade.
    // ───────────────────────────────
    public function inhouseCalendarData()
    {
        $staff = $this->authStaff();
        if (!$staff) return response()->json(['error' => 'Unauthorized'], 401);

        $staffRole = $staff->staff_role;
        if (!in_array($staffRole, ['lra', 'sra', 'job_fair', 'job_vacancy'])) return response()->json(['error' => 'Unauthorized'], 401);

        // Ang holiday gimarkahan ug pula sa kalendaryo — pahimangno ra, dili
        // pugong. Mangayo gyud ang employer ug in-house o job fair bisan
        // holiday o weekend, ug mahimo gihapon i-book sa staff.
        //
        // Lahi ang `blocked`: kana ang adlaw nga naa'y meeting o training ang
        // opisina. Dili na siya mabook — anaa sa miting ang staff nga mo-atiman.
        return response()->json([
            'dates'    => \App\Support\StaffCalendar::forRole($staffRole),
            'holidays' => \App\Support\Holidays::aroundNow(),
            'blocked'  => \App\Support\OfficeCalendar::blockedDates(),
            'legend'   => \App\Support\StaffCalendar::TYPES,
        ]);
    }

    public function viewInhouseSchedule($id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $schedule = \App\Models\InhouseSchedule::with('employer', 'reviewer')->findOrFail($id);
        return view('staff.inhouse.show', compact('schedule'));
    }

    public function acceptInhouse(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $request->validate([
            'confirmed_time' => 'required',
        ]);

        $schedule = \App\Models\InhouseSchedule::with('employer')->findOrFail($id);

        // ── Ang gi-accept kay ang TIBUOK range nga gitanyag sa employer, dili
        // ── usa ka adlaw nga gipili sa staff. Ang employer na ang mag-desisyon
        // ── kung sa Aug 13 ba, 14, o 15 sila mag-interview — naka-hold man ang
        // ── tulo ka adlaw para nila, ug walay laing employer nga makasulod.
        // ── Ang confirmed_date mao ang sinugdanan sa range: kana ang gigamit sa
        // ── mga daan nga tan-awonan nga usa ra ka petsa ang gipaabot. ──
        $confirmedDate = $schedule->preferred_date;

        // ── Kung nagpuliki na ang opisina sulod sa range human ni-request ang
        // ── employer, dili ni pugngan dinhi — ang range ilaha na. Ang staff
        // ── makakita sa banggaan sa kalendaryo ug mahimong mo-reject kung
        // ── kinahanglan gyud. ──
        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
        $schedule->update([
            'status'           => 'accepted',
            'reviewed_by'      => $staffRecord->staff_id ?? null,
            'confirmed_date'   => optional($confirmedDate)->toDateString(),
            'confirmed_time'   => $request->confirmed_time,
            'rejection_reason' => null,
        ]);

        $schedule->refresh();
        $windowLabel = $schedule->schedule_window_label;
        $isRange     = !$schedule->preferred_date_last->isSameDay($schedule->preferred_date);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'inhouse_accepted',
            'title'          => 'In-house Schedule Accepted ✅',
            'message'        => 'Your in-house interview request has been accepted for '
                                . $windowLabel . ' at ' . \Carbon\Carbon::parse($request->confirmed_time)->format('h:i A')
                                . ' (' . ($schedule->venue_type === 'custom' ? $schedule->venue_address : 'PESO Office') . ').'
                                . ($isRange ? ' Those dates are reserved for you — hold the interview on whichever of them suits you.' : ''),
            'reference_type' => 'inhouse_schedule',
            'reference_id'   => $schedule->inhouse_schedules_id,
        ], $schedule->employer_id);

        // Notify ang tanan jobseekers nga naka-apply sa maong employer
        $venueLabel = match($schedule->venue_type) {
            'peso_office'  => 'PESO Office',
            'company_interview' => 'the employer\'s office',
            'custom'       => $schedule->venue_address,
            default        => 'PESO Office',
        };

        $userIds = \App\Models\Application::whereHas('job', function ($q) use ($schedule) {
            $q->where('company_id', $schedule->employer_id);
        })->pluck('jobseeker_id')->unique();

        \App\Models\Announcement::sendToJobseekers([
            'type'           => 'inhouse_schedule_confirmed',
            'title'          => 'In-house Interview Schedule 📅',
            'message'        => ($schedule->employer->company_name ?? 'The employer')
                                . ' has scheduled an in-house interview on ' . $windowLabel
                                . ' at ' . \Carbon\Carbon::parse($request->confirmed_time)->format('h:i A')
                                . ' (' . $venueLabel . ').'
                                . ($isRange ? ' The employer will tell you which of those days to come in.' : '')
                                . ' Check your Schedules for details.',
            'reference_type' => 'inhouse_schedule',
            'reference_id'   => $schedule->inhouse_schedules_id,
        ], $userIds);

        return redirect()->route('staff.inhouse')
            ->with('success', 'In-house schedule accepted and applicants notified!');
    }

    public function rejectInhouse(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $schedule = \App\Models\InhouseSchedule::with('employer')->findOrFail($id);
        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
        $schedule->update([
            'status'           => 'rejected',
            'reviewed_by'      => $staffRecord->staff_id ?? null,
            'rejection_reason' => $request->rejection_reason,
        ]);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'inhouse_rejected',
            'title'          => 'In-house Schedule Rejected ❌',
            'message'        => 'Your in-house interview request was rejected. Reason: ' . $request->rejection_reason,
            'reference_type' => 'inhouse_schedule',
            'reference_id'   => $schedule->inhouse_schedules_id,
        ], $schedule->employer_id);

        return redirect()->route('staff.inhouse')
            ->with('success', 'In-house schedule rejected.');
    }

    

    // ───────────────────────────────
    // JOB VACANCIES
    // ───────────────────────────────
    // ───────────────────────────────
    // POSTER SA USA KA POSTING — i-download isip file
    // ───────────────────────────────
    //
    // Ang staff mag-post sa bakante sa social media ug mag-print niini para sa
    // bulletin board sa gawas sa opisina. Ang poster naa na sa `public` nga
    // disk ug makita sa browser, apan ang pagtan-aw dili igo — kinahanglan
    // siya nga file, mao nga gipugos ni nga attachment.
    public function downloadJobPoster($id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $job = \App\Models\Job::with('company')->findOrFail($id);

        // Ang kolum kay libre nga string ug ang download() modawat ug absolute
        // nga path. Kung dili susihon, ang usa ka sayop nga bili sa database
        // makapagawas ug bisan unsang file sa server.
        $path = (string) $job->poster_image;
        $inPosterFolder = str_starts_with($path, 'job_posters/') || str_starts_with($path, 'job_images/');

        if (!$path || !$inPosterFolder || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return back()->with('error', 'This posting has no poster image to download.');
        }

        $name = \Illuminate\Support\Str::slug(
            ($job->company->company_name ?? 'employer') . ' ' . $job->title
        ) . '.' . pathinfo($path, PATHINFO_EXTENSION);

        return response()->download(
            \Illuminate\Support\Facades\Storage::disk('public')->path($path),
            $name
        );
    }

    public function jobVacancies()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        $search    = request('search');

        // ── Usa ka bulan, dili open/closed ──
        //
        // PESO, 2026-08-30: gitangtang ang Open/Closed nga sala. Kining lista
        // usa ka rekord sa kung unsa ang gi-solicit; ang pagbahin niini sumala
        // kung modawat pa ba ug aplikante ang posting nagtubag sa pangutana nga
        // walay nangutana dinhi. Ang bulan mao ang ginagamit sa desk — parehas
        // sa gitipik sa mga report.
        //
        // Ang karon nga bulan ang default. Ang desk moabli niini para sa bulan
        // nga iyang gitrabaho, dili para sa tanang gi-post sukad — ang tibuok
        // kasaysayan mao ang taas nga lista nga kanunay niyang salaon pag-abli.
        // Ang 'all' mao ang tinuyo nga pag-ingon nga tanan: kung ang wala nga
        // bili ang gigamit para niini, ang default mobalik dayon ug dili gyud
        // maablihan ang tibuok lista.
        $month = request('month', now()->format('Y-m'));
        if ($month !== 'all' && !preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }
        $monthFilter = $month === 'all' ? null : $month;

        // Ang Pending Company Interview nga tab. Ang LRA walay company
        // interview, mao nga dili gyud siya makasulod dinhi.
        $isPendingInterview = $staffRole !== 'lra'
            && request('type') === 'company_interview_pending';

        $query = \App\Models\Job::with('company')
            ->when($staffRole === 'lra', function($q) {
                // LRA: local, In-house ra nga NA-APPROVE na (mabalhin diri gikan sa In-house tab)
                $q->whereHas('company', fn($n) => $n->where('is_overseas', false))
                  ->where('schedule_type', 'inhouse')
                  ->where('posting_status', 'approved');
            })
            ->when($staffRole === 'job_vacancy', function($q) {
                // Job Vacancy staff: local ra, duha ka tab.
                //
                // In-house: ang gi-approve na ni LRA. Basa ra ni — ang LRA ang
                // naghupot sa kalendaryo ug siya ang mo-accept. Ang Job Vacancy
                // nagkinahanglan makakita niini kay siya ang mo-post sa social
                // media ug sa bulletin board sa gawas sa opisina.
                $q->whereHas('company', fn($n) => $n->where('is_overseas', false));

                if (request('type') === 'inhouse') {
                    $q->where('schedule_type', 'inhouse')->where('posting_status', 'approved');
                } elseif (request('type') === 'company_interview_pending') {
                    // Pending Company Interview — ang wala pa nahitabo.
                    $this->pendingCompanyInterviewFilter($q);
                } else {
                    // Company Interview. Ang Job Fair naa sa kaugalingon niyang tab.
                    $q->where(function ($q2) {
                        $q2->whereNull('schedule_type')->orWhere('schedule_type', 'company_interview');
                    });
                }
            })
            ->when($staffRole === 'sra', function($q) {
                // SRA: separate nga tab para sa Company Interview ug In-house (query param 'type')
                $sraType = request('type', 'inhouse');
                $q->whereHas('company', fn($n) => $n->where('is_overseas', true));
                if ($sraType === 'inhouse') {
                    $q->where('schedule_type', 'inhouse')->where('posting_status', 'approved');
                } elseif ($sraType === 'company_interview_pending') {
                    $this->pendingCompanyInterviewFilter($q);
                } else {
                    $q->where(function ($q2) {
                        $q2->whereNull('schedule_type')->orWhere('schedule_type', 'company_interview');
                    });
                }
            })
            // Ang Pending Company Interview walay sala sa bulan. Ang lista
            // gitino sa petsa sa interview, dili sa petsa nga gi-post — ug ang
            // interview sa sunod bulan gi-post karong bulana, mao nga ang sala
            // sa bulan motago sa mga laray nga mao gyoy hinungdan sa tab.
            ->when($monthFilter && !$isPendingInterview, function ($q) use ($monthFilter) {
                [$y, $m] = explode('-', $monthFilter);
                $q->whereYear('created_at', $y)->whereMonth('created_at', $m);
            })
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('company', fn($u) =>
                    $u->where('company_name', 'like', "%{$search}%")
                )
            );

        $totalAll = (clone $query)->count();

        // Lima kada page. Ang desk mobasa niini usa-usa, ug ang taas nga page
        // magkinahanglan ug scroll — ang gipangita mahulog sa ubos sa screen.
        // Ang pinakaduol nga interview ang una sa pending nga lista — kana ang
        // sunod nga mahitabo. Sa laing tab, ang pinakabag-o nga gi-post.
        $jobs = ($isPendingInterview
                ? $query->orderBy('preferred_date')
                : $query->latest())
            ->paginate(5)
            ->withQueryString();

        return view('staff.jobs.index', compact(
            'jobs', 'staffRole', 'month', 'totalAll', 'isPendingInterview'
        ));
    }

    /**
     * The overseas vacancies this desk solicited.
     *
     * One definition for the SRA's Total Jobs card and for the Job Vacancies
     * Solicited report it opens. Two copies of this query is how a card comes
     * to state a number the list behind it does not agree with.
     */
    private function overseasSolicitedVacancies()
    {
        return \App\Models\Job::query()
            ->whereHas('company', fn($q) => $q->where('is_overseas', true))
            ->where('posting_status', 'approved')
            ->where(function ($q) {
                $q->whereNull('schedule_type')
                  ->orWhere('schedule_type', 'company_interview')
                  ->orWhere(function ($q2) {
                      $q2->where('schedule_type', 'inhouse')
                         ->where('posting_status', 'approved');
                  });
            });
    }

    /**
     * The company interviews that have not happened yet.
     *
     * A company interview is never approved by anybody — it goes live the
     * moment the employer posts it — so "pending" here cannot mean waiting for
     * a decision. It means waiting to happen: the interview date is today or
     * still ahead. A posting with no date at all is not on this list, because
     * there is nothing to say it is still coming.
     */
    private function pendingCompanyInterviewFilter($query): void
    {
        $query->where('schedule_type', 'company_interview')
            ->whereNotNull('preferred_date')
            ->whereDate('preferred_date', '>=', now()->toDateString());
    }

    public function createJobVacancy()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $employers = User::where('role', 'company')
            ->where('status', 'approved')
            ->whereHas('employerRequirement', fn($q) => $q->where('status', 'approved'))
            ->get();

        return view('staff.jobs.create', compact('employers'));
    }

    public function storeJobVacancy(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $request->validate([
            'company_id'     => 'required|exists:users,users_id',
            'title'          => 'required|string|max:255',
            'description'    => 'required|string',
            'location'       => 'required|string|max:255',
            'type'           => 'required|in:full_time,part_time,contractual,casual',
            'industry_group' => 'required|string|max:255',
            'slots'          => 'required|integer|min:1',
            'deadline'       => 'nullable|date',
        ]);

        $employerNsrp = \App\Models\EmployerNsrpRegistration::where('user_id', $request->company_id)->firstOrFail();

        \App\Models\Job::create([
            'company_id'     => $employerNsrp->employer_nsrp_registrations_id,
            'title'          => $request->title,
            'description'    => $request->description,
            'location'       => $request->location,
            'type'           => $request->type,
            'industry_group' => $request->industry_group,
            'slots'          => $request->slots,
            'deadline'       => $request->deadline,
            'status'         => 'open',
        ]);

        // ── Find jobseekers whose preferred_occupations match the job title ──
        $allJobseekerRegs = \App\Models\JobseekerRegistration::whereHas('user', fn($q) => $q->where('status', 'approved'))
            ->with('nsrp')
            ->get();

        $matchedIds = collect();
        $unmatchedIds = collect();
        $jobTitleLower = strtolower($request->title);

        foreach ($allJobseekerRegs as $jsReg) {
            $nsrp = $jsReg->nsrp;
            $preferred = $nsrp->preferred_occupations ?? [];
            $isMatch = false;
            foreach ($preferred as $occ) {
                if (strtolower(trim($occ)) === $jobTitleLower) {
                    $isMatch = true;
                    break;
                }
            }
            if ($isMatch) {
                $matchedIds->push($jsReg->jobseeker_registrations_id);
            } else {
                $unmatchedIds->push($jsReg->jobseeker_registrations_id);
            }
        }

        // ── Personalized notification for matched jobseekers ──
        if ($matchedIds->isNotEmpty()) {
            \App\Models\Announcement::sendToJobseekers([
                'type'           => 'job_match',
                'title'          => 'Matching Job Vacancy Found! 💼',
                'message'        => 'A job vacancy matching your preferred position "' . $request->title . '" is now available. Would you like to apply?',
                'reference_type' => 'job',
                'reference_id'   => null,
            ], $matchedIds);
        }

        // ── Generic notification for non-matched jobseekers ──
        if ($unmatchedIds->isNotEmpty()) {
            \App\Models\Announcement::sendToJobseekers([
                'type'           => 'job_posted',
                'title'          => 'New Job Vacancy Posted 💼',
                'message'        => 'A new job vacancy "' . $request->title . '" is now available. Check it out!',
                'reference_type' => 'job',
                'reference_id'   => null,
            ], $unmatchedIds);
        }

        return redirect()->route('staff.jobs')->with('success', 'Job vacancy posted successfully!');
    }

    public function editJobVacancy($id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $job       = \App\Models\Job::findOrFail($id);
        $employers = User::where('role', 'company')
            ->where('status', 'approved')
            ->whereHas('employerRequirement', fn($q) => $q->where('status', 'approved'))
            ->get();

        return view('staff.jobs.edit', compact('job', 'employers'));
    }

    public function updateJobVacancy(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $request->validate([
            'company_id'  => 'required|exists:users,users_id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'location'    => 'required|string|max:255',
            'type'        => 'required|in:full_time,part_time,contractual,casual',
            'slots'       => 'required|integer|min:1',
            'deadline'    => 'nullable|date',
            'status'      => 'required|in:open,closed',
        ]);

        $job = \App\Models\Job::findOrFail($id);
        $employerNsrp = \App\Models\EmployerNsrpRegistration::where('user_id', $request->company_id)->firstOrFail();
        $job->update([
            'company_id'  => $employerNsrp->employer_nsrp_registrations_id,
            'title'       => $request->title,
            'description' => $request->description,
            'location'    => $request->location,
            'type'        => $request->type,
            'slots'       => $request->slots,
            'deadline'    => $request->deadline,
            'status'      => $request->status,
        ]);

        return redirect()->route('staff.jobs')->with('success', 'Job vacancy updated successfully!');
    }


    // ───────────────────────────────
    // WALK-IN NSRP (LRA/SRA) — Staff encodes jobseeker walang account
    // ───────────────────────────────
    public function walkinNsrp()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if (!in_array($staff->staff_role, ['lra', 'sra'])) return redirect()->route('staff.dashboard');

        return view('staff.nsrp.index');
    }

    // ───────────────────────────────
    // WALK-IN NSRP OCR SCAN — Auto-fill text fields from uploaded photo
    // ───────────────────────────────
    public function nsrpScan(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff || !in_array($staff->staff_role, ['lra', 'sra'])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'nsrp_image'    => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'nsrp_images'   => 'nullable|array|max:2',
            'nsrp_images.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // ── Usa ka bond paper ang NSRP form, duha ka kilid. Dawaton nato ang
        // ── duha dungan; ang Python service mismo ang mo-ila kung asa ang
        // ── atubangan ug asa ang likod, mao nga walay han-ay nga sundon sa
        // ── staff. Gidawat gihapon ang usa ka hulagway para sa daan nga UI. ──
        $images = $request->file('nsrp_images') ?: array_filter([$request->file('nsrp_image')]);

        if (empty($images)) {
            return response()->json(['error' => 'Please choose at least one photo of the form.'], 422);
        }

        $data       = [];
        $confidence = [];
        $pagesRead  = [];
        $seconds    = 0;

        foreach ($images as $image) {
            $image->store('nsrp_scans', 'local');

            try {
                $response = \Illuminate\Support\Facades\Http::timeout(config('services.ocr.timeout'))
                    ->attach('image', file_get_contents($image->getRealPath()), $image->getClientOriginalName())
                    ->post(rtrim(config('services.ocr.url'), '/') . '/scan');
            } catch (\Throwable $e) {
                \Log::warning('NSRP OCR service unreachable: ' . $e->getMessage());

                return response()->json([
                    'error' => 'The scanner is not running. Please fill out the form manually.',
                ], 503);
            }

            if ($response->failed()) {
                return response()->json([
                    'error' => $response->json('error') ?? 'The scan failed. Please fill out the form manually.',
                ], 422);
            }

            $result      = $response->json();
            $pagesRead[] = $result['page'] ?? null;
            $seconds    += $result['seconds'] ?? 0;

            foreach ($result['fields'] ?? [] as $name => $field) {
                $data[$name]       = $field['value'];
                $confidence[$name] = $field['confidence'];
            }

            $data = array_merge($data, $this->mapNsrpCheckboxes($result['checkboxes'] ?? []));
        }

        return response()->json([
            'success'    => true,
            'pages'      => array_values(array_filter($pagesRead)),
            'seconds'    => round($seconds, 1),
            'data'       => $data,
            'confidence' => $confidence,
        ]);
    }

    // ── Ang ticked nga kahon gikan sa scanner, gihubad ngadto sa mga value nga
    // ── gidawat sa form. Ang ngalan sa checkbox sa map gituyo nga pareho sa
    // ── option key sa form (new_entrant, skill_driver...), mao nga ang uban
    // ── deretso ra — ang gilista dinhi mao ra ang tinuod nga magkalahi. ──
    private function mapNsrpCheckboxes(array $checkboxes): array
    {
        $ticked = [];
        foreach ($checkboxes as $name => $box) {
            if (!empty($box['ticked'])) {
                $ticked[$name] = $box;
            }
        }

        $first = function (string $group) use ($ticked) {
            foreach ($ticked as $box) {
                if (($box['group'] ?? null) === $group) {
                    return $box['value'];
                }
            }
            return null;
        };

        $all = function (string $group) use ($ticked) {
            $values = [];
            foreach ($ticked as $box) {
                if (($box['group'] ?? null) === $group) {
                    $values[] = $box['value'];
                }
            }
            return $values;
        };

        $data = [];

        // Pareho na ang spelling sa form, diretso ra.
        foreach (['sex', 'civil_status'] as $group) {
            if ($value = $first($group)) {
                $data[$group] = $value;
            }
        }

        if ($disabilities = $all('disabilities')) {
            $data['disabilities'] = $disabilities;
        }
        if ($skills = $all('other_skills')) {
            $data['other_skills'] = $skills;
        }

        // Ang form nagagamit ug lowercase key, ang papel ug tinuod nga pulong.
        if ($employment = $first('employment')) {
            $data['employment_type'] = strtolower($employment);
        }
        if ($work = $first('work_type')) {
            $data['work_type'] = $work === 'Part-time' ? 'part_time' : 'full_time';
        }
        foreach (['employed_sub' => 'employed_sub_type', 'unemployed_reason' => 'unemployed_reason'] as $group => $field) {
            foreach ($ticked as $name => $box) {
                if (($box['group'] ?? null) === $group) {
                    $data[$field] = $name;   // ang checkbox name mao na ang option key
                    break;
                }
            }
        }

        // Kung parehong Local ug Overseas ang gimarkahan, "both" — kini tinuod
        // nga mahitabo ug dili ni sayop sa pagbasa.
        $locations = $all('classification');
        if (count($locations) === 2) {
            $data['classification_type'] = 'both';
        } elseif (count($locations) === 1) {
            $data['classification_type'] = $locations[0];
        }

        foreach (['is_ofw', 'is_former_ofw', 'is_4ps', 'currently_in_school'] as $group) {
            $value = $first($group);
            if ($value !== null) {
                $data[$group] = (bool) $value;
            }
        }

        foreach (['English', 'Filipino', 'Mandarin'] as $language) {
            if ($skills = $all($language)) {
                $data['language_proficiency'][$language] = $skills;
            }
        }

        return $data;
    }

    // ── Duha ka helper ang naa kaniadto dinhi: preprocessNsrpImage (grayscale,
    // ── contrast, sharpen pinaagi sa GD) ug parseWalkinNsrpText (label-matching
    // ── nga regex). Salin silang duha sa panahon nga ang PHP mismo ang misulay
    // ── pagbasa sa porma gamit ang Tesseract. Wala nay nagtawag kanila.
    // ──
    // ── Ang pag-andam sa hulagway naa na sa align.py — perspective warp ngadto
    // ── sa usa ka kanunay nga gidak-on — ug ang pagkuha sa mga field naa sa
    // ── pipeline.py, nga nagbasa gikan sa mapa sa koordinado imbes mangita ug
    // ── label sa usa ka bugnaw nga string. ──

    public function storeWalkinNsrp(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if (!in_array($staff->staff_role, ['lra', 'sra'])) return redirect()->route('staff.dashboard');

        $request->validate([
            'surname'           => 'required|string|max:255',
            'first_name'        => 'required|string|max:255',
            'date_of_birth'     => 'required|date',
            'age'               => 'required|integer|min:1',
            'sex'               => 'required|string',
            'civil_status'      => 'required|string',
            'perm_province'          => 'nullable|string|max:255',
            'perm_municipality_city' => 'nullable|string|max:255',
            'perm_barangay'          => 'nullable|string|max:255',
            'perm_house_street'      => 'nullable|string|max:255',
            'house_street'           => 'required|string|max:255',
            'barangay'               => 'required|string|max:255',
            'municipality_city'      => 'required|string|max:255',
            'province'               => 'required|string|max:255',
            'contact_number'    => ['required', 'string', new \App\Rules\MobileNumber],
            // ── Kinahanglanon na, dili na opsyonal.
            // ──
            // ── Ang email mao ang usa sa duha ka butang nga makapasumpay niini
            // ── nga rekord sa account nga himoon sa maong tawo ugma (tan-awa ang
            // ── UnifiedAuthController::linkWalkInRecord). Kung wala siya, ang
            // ── bugtong dalan mao ang numero — nga opsyonal pud sa registration
            // ── form — ug ang tawo mapugos ug sulat pag-usab sa tibuok NSRP,
            // ── ug duha na ang rekord sa usa ka tawo. Pangayoa siya samtang
            // ── naa pa ang tawo sa atubangan sa staff. ──
            'reg_email'         => 'required|email|max:255',
            'employment_type'   => 'required|string',
            'work_type'         => 'required|string',
            'preferred_occupations.0' => 'required|string',
            'certification_date'      => 'required|string',
            'certification_agreed'       => 'required',
            'training_certificates.*'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // ══════════════════════════════════════════
        // PART 1: Personal Info → jobseeker_registrations (walay user_id)
        // ══════════════════════════════════════════
        $registrationData = [
            'user_id'           => null,
            'is_walk_in'        => true,
            'surname'           => $request->surname,
            'first_name'        => $request->first_name,
            'middle_name'       => $request->middle_name,
            'suffix'            => $request->suffix,
            'date_of_birth'     => $request->date_of_birth,
            'age'               => $request->age,
            'sex'               => $request->sex,
            'civil_status'      => $request->civil_status,
            'religion'          => $request->religion,
            'tin'                    => $request->tin,
            'perm_house_street'      => $request->perm_house_street,
            'perm_barangay'          => $request->perm_barangay,
            'perm_municipality_city' => $request->perm_municipality_city,
            'perm_province'          => $request->perm_province,
            'same_as_permanent'      => $request->has('same_as_permanent') ? true : false,
            'house_street'           => $request->house_street,
            'barangay'               => $request->barangay,
            'municipality_city'      => $request->municipality_city,
            'province'               => $request->province,
            'height'            => $request->height,
            'weight'            => $request->weight,
            'contact_number'    => $request->contact_number,
            'reg_email'         => $request->reg_email,
            'disabilities'      => $request->disabilities ?? [],
            'disability_other'  => $request->disability_other,
        ];

        $registration = JobseekerRegistration::create($registrationData);

        // ══════════════════════════════════════════
        // PART 2: NSRP Form Data → jobseeker_nsrp_registrations
        // ══════════════════════════════════════════
        $nsrpData = [
            'jobseeker_registration_id' => $registration->jobseeker_registrations_id,
            'type'               => $staff->staff_role === 'sra' ? 'overseas' : 'local',
            'employment_type'   => $request->employment_type,
            'is_ofw'            => $request->is_ofw ?? false,
            'is_former_ofw'     => $request->is_former_ofw ?? false,
            'is_4ps'            => $request->is_4ps ?? false,
            'household_id'      => $request->household_id,
            'work_type'         => $request->work_type,
            'preferred_occupations' => array_filter($request->preferred_occupations ?? []),
            'education'         => $request->education ?? [],
            'other_skills'      => $request->other_skills ?? [],
            'other_skills_specify' => $request->other_skills_specify,
            'certification_agreed' => true,
            'certification_date'   => $request->certification_date,
            'status'               => 'submitted',
            'employed_sub_type'         => $request->employed_sub_type,
            'self_employed_specify'     => $request->self_employed_specify,
            'months_looking'            => $request->months_looking,
            'unemployed_reason'         => $request->unemployed_reason,
            'terminated_abroad_country' => $request->terminated_abroad_country,
            'unemployed_other'          => $request->unemployed_other,
            'ofw_country'               => $request->ofw_country,
            'latest_deployment_country' => $request->latest_deployment_country,
            'return_month'              => $request->return_month,
            'local_locations'           => array_filter($request->local_locations ?? []),
            'overseas_locations'        => array_filter($request->overseas_locations ?? []),
            'language_proficiency'      => $request->language_proficiency ?? [],
            'other_language'            => !empty(array_filter($request->other_language ?? [])) ? json_encode(array_filter($request->other_language ?? [])) : null,
            'currently_in_school'       => $request->currently_in_school ?? false,
            'trainings'                 => $request->trainings ?? [],
            'eligibilities'             => $request->eligibilities ?? [],
            'licenses'                  => $request->licenses ?? [],
        ];

        $uploadedCerts = [];
        if ($request->hasFile('training_certificates')) {
            foreach ($request->file('training_certificates') as $idx => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('certificates', 'local');
                    $uploadedCerts[$idx] = $path;
                }
            }
        }
        $nsrpData['training_certificates'] = $uploadedCerts;

        $nsrp = \App\Models\JobseekerNsrpRegistration::create($nsrpData);

        // ══════════════════════════════════════════
        // PART 3: Work Experiences
        // ══════════════════════════════════════════
        $workExperiences = $request->work_experiences ?? [];
        foreach ($workExperiences as $exp) {
            if (!empty($exp['company_name']) && !empty($exp['position'])) {
                \App\Models\JobseekerWorkExperience::create([
                    'jobseeker_nsrp_registration_id' => $nsrp->jobseeker_nsrp_registrations_id,
                    'company_name'      => $exp['company_name'],
                    'position'          => $exp['position'],
                    'industry'          => $exp['industry'] ?? null,
                    'date_from'         => $exp['date_from'] ?? null,
                    'date_to'           => (!empty($exp['is_current'])) ? 'present' : ($exp['date_to'] ?? null),
                    'is_current'        => isset($exp['is_current']) ? true : false,
                    'employment_status' => $exp['employment_status'] ?? null,
                ]);
            }
        }

        // ══════════════════════════════════════════
        // PART 4: Certifications
        // ══════════════════════════════════════════
        $safeParseDate = function ($value) {
            if (empty($value)) return null;
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        };

        foreach ($request->eligibilities ?? [] as $e) {
            if (!empty($e['name'])) {
                \App\Models\JobseekerCertification::create([
                    'jobseeker_nsrp_registration_id' => $nsrp->jobseeker_nsrp_registrations_id,
                    'category'    => 'eligibility',
                    'name'        => $e['name'],
                    'date_taken'  => $safeParseDate($e['date_taken'] ?? null),
                ]);
            }
        }

        foreach ($request->licenses ?? [] as $l) {
            if (!empty($l['name'])) {
                \App\Models\JobseekerCertification::create([
                    'jobseeker_nsrp_registration_id' => $nsrp->jobseeker_nsrp_registrations_id,
                    'category'     => 'license',
                    'name'         => $l['name'],
                    'valid_until'  => $safeParseDate($l['valid_until'] ?? null),
                ]);
            }
        }

        return redirect()->route('staff.nsrp')
            ->with('success', 'Walk-in jobseeker NSRP registration saved successfully!');
    }

    // ───────────────────────────────
    // APPROVE JOB REQUEST
    // ───────────────────────────────
    public function approveJob(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $job = \App\Models\Job::with('company')->findOrFail($id);

        $isOverseas = $job->company->is_overseas ?? false;
        $isJobFair  = $job->schedule_type === 'job_fair';
        $isInhouse  = $job->schedule_type === 'inhouse';

        // ── Access control ──
        // Overseas (bisan unsa nga schedule_type): SRA ra
        // Local In-house: LRA ra
        // Local Company Interview / Job Fair: Job Vacancy staff ra
        if ($isOverseas) {
            if ($staff->staff_role !== 'sra') {
                return back()->with('error', 'Only SRA staff can approve overseas job postings.');
            }
        } elseif ($isInhouse) {
            if ($staff->staff_role !== 'lra') {
                return back()->with('error', 'Only LRA staff can approve local in-house job postings.');
            }
        } else {
            if ($staff->staff_role !== 'job_vacancy') {
                return back()->with('error', 'Only Job Vacancy staff can approve local job postings.');
            }
        }

        // ── Ang job fair nga posting magpabilin nga CLOSED human ma-approve.
        // ── Mo-abli siya sa jobfair:open-postings, pila ka adlaw sa dili pa ang
        // ── fair — gawas kung sulod na sa maong window karon, kay ang command
        // ── dili na mobalik sa niaging adlaw. ──
        $windowOpen = $isJobFair && \App\Support\JobFairPostingWindow::isOpen();
        $goesLive   = !$isJobFair || $windowOpen;

        // ── In-house: ang gi-approve kay ang TIBUOK range nga gitanyag sa
        // ── employer. Naka-hold kadtong mga adlawa para nila ug ang employer
        // ── na ang mag-desisyon kung asa gyud didto sila mag-interview — dili
        // ── ang staff. Ang confirmed_date mao ang sinugdanan sa range. ──
        $confirmedDate = $isInhouse ? $job->preferred_date : null;

        $job->update([
            'posting_status' => 'approved',
            'status'         => $goesLive ? 'open' : 'closed',
            'remarks'        => null,
        ] + ($confirmedDate ? ['confirmed_date' => $confirmedDate->toDateString()] : []));

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'job_approved',
            'title'          => 'Job Posting Approved ✅',
            'message'        => match (true) {
                $isJobFair => 'Your job posting "' . $job->title . '" has been approved for job fair use. '
                              . \App\Support\JobFairPostingWindow::liveNote(),
                // Ang employer nagtanyag ug range; kadtong mga adlawa iya na —
                // kinahanglan niyang mabasa nga naka-hold sila.
                (bool) $confirmedDate => 'Your job posting "' . $job->title . '" has been approved and is now live. '
                              . 'Your in-house interview dates are confirmed for ' . $job->schedule_window_label . '.',
                default    => 'Your job posting "' . $job->title . '" has been approved and is now live!',
            },
            'reference_type' => 'job',
            'reference_id'   => $job->job_qualifications_id,
        ], $job->company_id);

        // ── Ang jobseeker masultihan ra kung tinuod nga makita na niya ang
        // ── bakante. Ang notice nga dili ma-klik kay wala'y pulos. ──
        if ($goesLive) {
            \App\Support\JobPostingNotice::announce($job);
        }

        return back()->with('success', $isJobFair
            ? 'Job fair posting approved. ' . \App\Support\JobFairPostingWindow::liveNote()
            : 'Job posting approved and is now live!');
    }

    // ───────────────────────────────
    // REJECT JOB REQUEST
    // ───────────────────────────────
    public function rejectJob(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $request->validate(['remarks' => 'required|string|max:500']);

        $job = \App\Models\Job::with('company')->findOrFail($id);

        $isOverseas = $job->company->is_overseas ?? false;
        $isInhouse  = $job->schedule_type === 'inhouse';

        if ($isOverseas) {
            if ($staff->staff_role !== 'sra') {
                return back()->with('error', 'Only SRA staff can reject overseas job postings.');
            }
        } elseif ($isInhouse) {
            if ($staff->staff_role !== 'lra') {
                return back()->with('error', 'Only LRA staff can reject local in-house job postings.');
            }

            // PESO, 2026-08-26: LRA decides the date, not the posting. While the
            // booking is still pending, rejecting it releases the days and closes
            // the vacancy that was waiting on them — that is theirs to do. Once
            // the posting is live it belongs to the employer, and only the
            // employer takes it down.
            if ($job->posting_status !== 'pending') {
                return back()->with('error',
                    'This posting is already live. LRA decides the in-house date; only the employer can remove the posting.');
            }
        } else {
            if ($staff->staff_role !== 'job_vacancy') {
                return back()->with('error', 'Only Job Vacancy staff can reject local job postings.');
            }
        }

        $job->update([
            'posting_status' => 'rejected',
            'status'         => 'closed',
            'remarks'        => $request->remarks,
        ]);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'job_rejected',
            'title'          => 'Job Posting Rejected ❌',
            'message'        => 'Your job posting "' . $job->title . '" was rejected. Reason: ' . $request->remarks,
            'reference_type' => 'job',
            'reference_id'   => $job->job_qualifications_id,
        ], $job->company_id);

        return back()->with('success', 'Job posting rejected.');
    }

    // ───────────────────────────────
    // QUALIFIED APPLICANTS — per job
    // ───────────────────────────────
    public function qualifiedApplicants($jobId)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        if (!in_array($staffRole, ['lra', 'sra', 'job_vacancy']))
            return redirect()->route('staff.dashboard');

        $job    = \App\Models\Job::with('company')->findOrFail($jobId);
        $filter = request('filter', 'highly');

        $query = \App\Models\Application::with(['jobseeker', 'jobseeker.nsrp'])
            ->where('job_id', $jobId);

        if ($filter === 'highly') {
            $query->where('match_percentage', '>=', 75);
        } elseif ($filter === 'qualified') {
            $query->whereBetween('match_percentage', [50, 74.99]);
        } elseif ($filter === 'not_qualified') {
            $query->where('match_percentage', '<', 50);
        }

        $applicants        = $query->orderByDesc('match_percentage')->paginate(4);
        $totalHighly       = \App\Models\Application::where('job_id', $jobId)->where('match_percentage', '>=', 75)->count();
        $totalQualified    = \App\Models\Application::where('job_id', $jobId)->whereBetween('match_percentage', [50, 74.99])->count();
        $totalNotQualified = \App\Models\Application::where('job_id', $jobId)->where('match_percentage', '<', 50)->count();

        return view('staff.jobs.qualified', compact(
            'job', 'applicants', 'staffRole', 'filter',
            'totalHighly', 'totalQualified', 'totalNotQualified'
        ));
    }

    // ───────────────────────────────
    // REPORTS — JOB VACANCY STAFF (Job Vacancies Solicited)
    // ───────────────────────────────
    // ───────────────────────────────
    // REPORTS — full details sa usa ka closed nga job posting
    // ───────────────────────────────
    public function reportJobDetails($jobId)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        // Rekord sa posting ra — walay applicant data. Ang na-hire naa sa
        // Placed/Referred nga tabs sa Reports.
        $job = \App\Models\Job::with('company')
            ->withCount(['applications as hired_count' => fn($q) => $q->where('status', 'hired')])
            ->findOrFail($jobId);

        return view('staff.reports.job', compact('job'));
    }

    // ───────────────────────────────
    // EMPLOYER REPORT — what came of each in-house interview
    // ───────────────────────────────
    // ── LRA staff, 2026-08-23: usa ka semana human sa in-house interview,
    // ── makita nila ang report sa maong employer ug ang status sa matag
    // ── jobseeker.
    // ──
    // ── LRA ra sa karon. Ugma pa ang interview sa SRA, ug ang paghatag niini
    // ── kanila karon mao ang pagtag-an sa ilang proseso. ──
    public function exportInhouseEmployerReport()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if ($staff->staff_role !== 'lra') {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Only LRA staff can download the employer report.');
        }

        $search = request('search');
        $rows   = \App\Support\InhouseEmployerReport::rows(false, $search);

        return \App\Support\ExcelExport::stream(
            'peso-employer-report-' . now()->format('Ymd') . '.xlsx',
            \App\Support\InhouseEmployerReport::COLUMNS,
            $rows,
            array_filter([
                ['PESO Cagayan de Oro - Employer Report (In-house Interviews)'],
                ['Coverage', 'Interviews held at least '
                    . \App\Support\InhouseEmployerReport::delayDays() . ' days ago'],
                $search ? ['Search', $search] : null,
                ['Generated', now()->format('Y-m-d H:i')],
            ])
        );
    }

    public function jobVacancyReports()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if ($staff->staff_role !== 'job_vacancy') return redirect()->route('staff.dashboard');

        $search = request('search');
        $tab    = request('tab', 'vacancies');
        $month  = request('month', now()->format('Y-m')); // format: YYYY-MM
        $topEmployersFilter = request('top_employers_filter', 'monthly');
        $topEmployersMonth = request('top_employers_month');
        $topEmployersYear = request('top_employers_year');

        [$year, $mon] = explode('-', $month);

        $query = $this->jobVacancyReportQuery($year, $mon, $search);

        // ── Total kay sa TIBUOK filtered set (dili lang sa current page), mao nga clone una ang query ──
        // ── groupLeaders: ang usa ka position nga gi-post sa daghang schedule
        // ── type kay usa ra gihapon ka set sa bakante, dili tag-usa kada row. ──
        $totalVacancies = (clone $query)->groupLeaders()->sum('slots');
        $jobs = $query->orderBy('title')->paginate(5)->withQueryString();

        // ── TOP 10 EMPLOYERS — kinsa ang naghatag ug pinakadaghang bakante.
        // ──
        // ── Kaniadto giihap ang pag-apil sa Company Interview. Ang opisina
        // ── nangayo sa tinuod nga sukod: pila ka bakante ang gihatag, dili pila
        // ── ka higayon nga miapil. Ang groupLeaders() gikinahanglan — ang usa
        // ── ka posisyon nga gi-post sa tulo ka channel usa ra gihapon ka set sa
        // ── bakante, ug kung wala kini, tulo ka pilo ang ihap.
        // ──
        // ── Naka-sort na pag-render, mao nga ang pag-abli sa tab mao na ang
        // ── pag-sort — walay buton nga pindoton. ──
        $topEmployers = collect();
        if ($tab === 'top_employers') {
            $topQuery = \App\Models\Job::with('company')
                ->where('posting_status', 'approved')
                ->whereHas('company', fn($q) => $q->where('is_overseas', false))
                ->groupLeaders();

            if ($topEmployersFilter === 'yearly') {
                $selectedYear = $topEmployersYear ?: now()->year;
                $topQuery->whereYear('updated_at', $selectedYear);
            } else {
                $selectedMonth = $topEmployersMonth ?: now()->format('Y-m');
                [$selectedYear, $selectedMonth] = array_pad(explode('-', $selectedMonth), 2, now()->month);
                $topQuery->whereYear('updated_at', $selectedYear)
                    ->whereMonth('updated_at', $selectedMonth);
            }

            $topEmployers = $topQuery->get()
                ->groupBy('company_id')
                ->map(fn($jobs) => [
                    'employer'        => $jobs->first()->company,
                    'total_vacancies' => (int) $jobs->sum('slots'),
                    'posting_count'   => $jobs->count(),
                ])
                ->sortByDesc('total_vacancies')
                ->take(10)
                ->values();
        }

        // ── Ang report sa opisina nga gi-upload. Bulag gyud ni sa mga numero sa
        // ── sistema — walay bisan usa ka laray dinhi nga giapil sa taas. ──
        $importedReports = collect();
        if ($tab === 'imported') {
            $importedReports = \App\Models\JobVacancyImportedReport::latest()->get();
        }

        // Ang pikas nga bahin sa desk — tan-awa ang App\Support\CompanyInterviewReport.
        $companyInterviews = $tab === 'company_interview'
            ? \App\Support\CompanyInterviewReport::paginate($year, $mon, $search)
            : null;

        return view('staff.job_vacancy.reports', compact(
            'jobs', 'month', 'totalVacancies', 'search', 'tab',
            'topEmployers', 'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear',
            'importedReports', 'companyInterviews'
        ));
    }

    // ── Usa ra ka lugar nga naghubad sa "unsa ang sulod sa Job Vacancies nga
    // ── report", aron ang gipakita nga lamesa ug ang gi-download nga file dili
    // ── gyud magkalahi ug laray. ──
    private function jobVacancyReportQuery(string $year, string $mon, ?string $search)
    {
        return \App\Models\Job::with('company')
            ->where('posting_status', 'approved')
            ->whereHas('company', fn($q) => $q->where('is_overseas', false))
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $mon)
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('company', fn($u) => $u->where('company_name', 'like', "%{$search}%"))
            );
    }

    /** Ang mga kolum sa Job Vacancies nga report, sa samang han-ay sa lamesa. */
    private const JOB_VACANCY_REPORT_COLUMNS = [
        'No.', 'Job Title', 'No. of Vacancies', 'Age', 'Sex', 'Civil Status',
        'Educational Attainment', 'Work Experience', 'Employer Name',
    ];

    private function jobVacancyReportRow(\App\Models\Job $job, int $number): array
    {
        $sexMap = ['Any' => 'M/F', 'Male' => 'M', 'Female' => 'F'];

        return [
            $number,
            strtoupper($job->title),
            $job->slots,
            ($job->age_required && $job->age_min && $job->age_max)
                ? $job->age_min . '-' . $job->age_max
                : 'None',
            $sexMap[$job->sex_preference] ?? ($job->sex_preference ?? 'None'),
            ($job->civil_status && $job->civil_status !== 'Any') ? strtoupper($job->civil_status) : 'None',
            $job->education_required ? strtoupper($job->education_required) : 'None',
            $job->experience_months ? $job->experience_months . ' mo/s' : 'None',
            strtoupper($job->company->company_name ?? 'None'),
        ];
    }

    // ── I-download ang report sa sistema isip .xlsx — tan-awa ang nota sa
    // ── ExcelExport kung ngano. ──
    public function exportJobVacancyReport()
    {
        [$staff, $deny] = $this->assertJobVacancyStaff();
        if ($deny) return $deny;

        $search = request('search');
        $month  = request('month', now()->format('Y-m'));
        [$year, $mon] = array_pad(explode('-', $month), 2, now()->format('m'));

        $jobs = $this->jobVacancyReportQuery($year, $mon, $search)
            ->orderBy('title')
            ->get();

        $rows = [];
        foreach ($jobs as $i => $job) {
            $rows[] = $this->jobVacancyReportRow($job, $i + 1);
        }

        $totalVacancies = $this->jobVacancyReportQuery($year, $mon, $search)
            ->groupLeaders()->sum('slots');

        return \App\Support\ExcelExport::stream(
            'peso-job-vacancies-' . $month . '.xlsx',
            self::JOB_VACANCY_REPORT_COLUMNS,
            $rows,
            array_filter([
                // Hyphen, dili em-dash — tan-awa ang samang nota sa exportReports.
                ['PESO Cagayan de Oro - Job Vacancies Solicited'],
                ['Month', \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y')],
                ['Total no. of vacancies', $totalVacancies],
                $search ? ['Search', $search] : null,
                ['Generated', now()->format('Y-m-d H:i')],
            ])
        );
    }

    // ── Ang kaugalingong excel report sa opisina. Gitipigan, gipakita, ug
    // ── ma-download balik — wala gyud gisagol sa numero sa sistema. ──
    public function importJobVacancyReport(Request $request)
    {
        [$staff, $deny] = $this->assertJobVacancyStaff();
        if ($deny) return $deny;

        $request->validate([
            'title'  => 'required|string|max:120',
            'period' => 'nullable|date_format:Y-m',
            'file'   => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ], [
            'title.required' => 'Give this report a name so you can tell it apart later.',
            // Ang mimes mo-salikway sad sa file nga walay sulod, kay walay
            // matag-an nga tipo sa 0 ka byte — mao nga giapil ang duha ka
            // hinungdan sa usa ka mensahe.
            'file.mimes'     => 'Only an Excel workbook (.xlsx, .xls) or a CSV file can be read, and it must not be empty.',
            'file.max'       => 'The file must be 5 MB or smaller.',
        ]);

        // ── Ang mimes dili kasaligan sa Windows: ang file nga gi-save sa Excel
        // ── moabot usahay nga text/plain o application/vnd.ms-excel. Ang
        // ── extension ang kataposang pagsusi, ug ang mensahe nagsulti sa
        // ── lakang nga kinahanglan buhaton — dili lang "invalid file". ──
        $file = $request->file('file');
        if (!in_array(strtolower($file->getClientOriginalExtension()), \App\Support\SpreadsheetImport::ALLOWED, true)) {
            return back()->withInput()->with('error',
                'That is a .' . $file->getClientOriginalExtension() . ' file. Upload the report as an '
                . 'Excel workbook (.xlsx) or a CSV.');
        }

        $parsed = \App\Support\SpreadsheetImport::read($file);

        if (!$parsed['headers']) {
            return back()->withInput()->with('error',
                'That file has no column headings on its first row, so there is nothing to show.');
        }

        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();

        \App\Models\JobVacancyImportedReport::create([
            'uploaded_by'       => $staffRecord?->staff_id,
            'title'             => $request->title,
            'period'            => $request->period,
            'original_filename' => $file->getClientOriginalName(),
            'headers'           => $parsed['headers'],
            'rows'              => $parsed['rows'],
            'row_count'         => count($parsed['rows']),
        ]);

        $note = $parsed['truncated']
            ? ' The file was longer than ' . \App\Support\SpreadsheetImport::MAX_ROWS . ' rows or wider than '
              . \App\Support\SpreadsheetImport::MAX_COLUMNS . ' columns, so only the first part was read.'
            : '';

        return redirect()->route('staff.reports.employers', ['tab' => 'imported'])
            ->with('success', '"' . $request->title . '" imported — ' . count($parsed['rows']) . ' row(s).' . $note);
    }

    public function downloadJobVacancyImportedReport($id)
    {
        [$staff, $deny] = $this->assertJobVacancyStaff();
        if ($deny) return $deny;

        $report = \App\Models\JobVacancyImportedReport::findOrFail($id);

        return \App\Support\ExcelExport::stream(
            'peso-imported-' . str($report->title)->slug() . '-' . $report->created_at->format('Ymd') . '.xlsx',
            $report->headers,
            $report->rows,
            array_filter([
                ['PESO Cagayan de Oro - Imported Report'],
                ['Report', $report->title],
                $report->period ? ['Period', $report->period] : null,
                ['Original file', $report->original_filename],
                ['Imported', $report->created_at->format('Y-m-d H:i')],
                // Isulat gyud sa file mismo. Kung dili, ang usa ka kopya niini
                // nga nagsuroy-suroy mahimong sayop nga basahon nga gikan sa
                // sistema ang mga numero.
                ['Source', 'Uploaded by PESO staff. Not generated from system records.'],
            ])
        );
    }

    public function deleteJobVacancyImportedReport($id)
    {
        [$staff, $deny] = $this->assertJobVacancyStaff();
        if ($deny) return $deny;

        $report = \App\Models\JobVacancyImportedReport::findOrFail($id);
        $title  = $report->title;
        $report->delete();

        return redirect()->route('staff.reports.employers', ['tab' => 'imported'])
            ->with('success', '"' . $title . '" removed.');
    }

    // ───────────────────────────────
    // REPORTS (LRA/SRA/Job Fair)
    // ───────────────────────────────
    public function reports()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        $reportView = $staffRole === 'sra' ? request('report_view', 'staff') : 'staff';

        if ($staffRole === 'job_fair' || ($staffRole === 'sra' && $reportView === 'jobfair')) {
            $isSraJobFairView = $staffRole === 'sra';
            // Ang Attendance ang default. Kini ang unang gipangita sa desk
            // human sa fair — kinsa ang nitunga — ug ang Post Job Fair Summary
            // usa ka tapos nga dokumento, dili ang unang pangutana.
            $tab      = request('tab', 'attendance');
            if ($isSraJobFairView && $tab === 'placement') $tab = 'attendance';
            $eventId  = request('event_id');
            $allEvents = \App\Models\JobFairEvent::orderByDesc('event_date')->get();
            $event     = $eventId ? \App\Models\JobFairEvent::find($eventId) : null;

            // ── Ang matag listahan sa ubos naa na sa JobFairReport.
            // ──
            // ── Dinhi sila kaniadto, ug ang download nga gipangayo sa Job Fair
            // ── staff kinahanglan mokopya unta sa matag query. Karon usa ra
            // ── ka lugar ang naghubad: ang page mo-paginate, ang CSV mokuha sa
            // ── tanan, ug dili gyud sila magkalahi ug ihap. ──
            $eventJobIds = \App\Support\JobFairReport::eventJobIds($eventId ? (int) $eventId : null);

            // ── TAB 1: ATTENDANCE ──
            // ──
            // ── Usa ra ka attendance nga tab sa tibuok sistema. Kaniadto duha:
            // ── usa sa Job Fair Events (ang trabahoan, uban ang Mark Attended)
            // ── ug usa dinhi (ang rekord, ang miabot ra) — ug managlahi ug ihap
            // ── ang duha, mao nga nagduha-duha ang staff kung asa ang tinuod.
            // ──
            // ── Ang $attendanceState ang nagpuli sa duha ka tab: "joined" mao
            // ── ang panahon sa fair, "attended" mao ang ipasa sa DOLE. Ang CSV
            // ── mosunod sa parehas nga pagpili. ──
            $attendanceFilter = request('attendance_filter', 'all');
            $attendanceState  = request('attendance_state', $isSraJobFairView ? 'attended' : 'joined');
            if (!array_key_exists($attendanceState, \App\Support\JobFairReport::STATES)) {
                $attendanceState = 'joined';
            }
            $attendanceSearch = request('attendance_search');
            $registrations = null;
            $totalRegistered = $totalAttended = 0;

            if ($tab === 'attendance' && $eventId) {
                if ($isSraJobFairView) $attendanceFilter = 'overseas';

                $registrations = \App\Support\JobFairReport::attendanceQuery(
                        (int) $eventId, $attendanceFilter, $attendanceState, $attendanceSearch
                    )
                    ->latest()->paginate(10)->withQueryString();

                $totals = \App\Support\JobFairReport::attendanceTotals((int) $eventId);
                $totalRegistered = $totals['registered'];
                $totalAttended   = $totals['attended'];
            }

            // ── TAB 2: LIST OF LOCAL/OVERSEAS COMPANIES FOR JOB FAIR ──
            $companiesLocal       = collect();
            $companiesOverseas    = collect();
            $companyVacancyTotals = ['local' => 0, 'overseas' => 0];

            if ($tab === 'companies' && $eventId) {
                $confirmed = \App\Support\JobFairReport::confirmedCompanies((int) $eventId);

                // ── Lima kada panid, ug lain ang page name sa matag listahan.
                // ──
                // ── Ang duha ka listahan gitapok kaniadto, tibuok. Sa fair nga
                // ── naay 300 ka lokal nga kompanya, ang overseas naa sa ubos sa
                // ── 300 ka laray — walay makakita niini gawas kung mo-scroll
                // ── siya sa tibuok. Ug kung usa ra ang page name, ang pagbalhin
                // ── sa usa ka listahan mobalhin pud sa lain. ──
                $paginate = function ($rows, string $pageName) {
                    $page = \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);

                    return (new \Illuminate\Pagination\LengthAwarePaginator(
                        $rows->forPage($page, 5)->values(),
                        $rows->count(),
                        5,
                        $page,
                        [
                            'path'     => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                            'pageName' => $pageName,
                        ]
                    ))->withQueryString();
                };

                // Ang TOTAL sa papel kay sa tibuok listahan, dili sa lima ka
                // laray nga makita karon. Gikwenta sa dili pa ma-paginate.
                $companyVacancyTotals = [
                    'local'    => (int) $confirmed->filter(fn($p) => !($p->employer->is_overseas ?? false))->sum('vacancies'),
                    'overseas' => (int) $confirmed->filter(fn($p) => $p->employer->is_overseas ?? false)->sum('vacancies'),
                ];

                $companiesLocal = $paginate(
                    $confirmed->filter(fn($p) => !($p->employer->is_overseas ?? false))->values(),
                    'local_page'
                );
                $companiesOverseas = $paginate(
                    $confirmed->filter(fn($p) => $p->employer->is_overseas ?? false)->values(),
                    'overseas_page'
                );
            }

            // ── TAB 3: LIST OF FURTHER INTERVIEW (waiting status) ──
            $furtherInterview = null;
            if ($tab === 'further_interview' && $eventId) {
                $furtherInterview = \App\Support\JobFairReport::furtherInterviewQuery($eventJobIds, $isSraJobFairView)
                    ->paginate(10)->withQueryString();
            }

            // ── TAB 4: HOTS — hired for a job brought to this event (dili na i-match ang eksaktong petsa, kay ang job mismo naka-scope na sa event via eventJobIds) ──
            $hots = null;
            if ($tab === 'hots' && $eventId && $event) {
                $hots = \App\Support\JobFairReport::hotsQuery($eventJobIds, $isSraJobFairView)
                    ->paginate(10)->withQueryString();
            }

            // ── TAB 5: POST JOB FAIR SUMMARY REPORT ──
            $summaryParticipants = collect();
            $summaryTotals = ['vacancies' => 0, 'interviewed' => 0, 'male' => 0, 'female' => 0, 'qualified' => 0, 'hired' => 0];

            // Ang linya sa ubos sa papel: pila ka tawo ang niapil sa fair,
            // gibahin sa lokal ug overseas. Lahi ni sa kolum sa ibabaw — didto
            // ang aplikasyon ang gi-ihap, dinhi ang tawo.
            $summaryRegistrants = ['local' => 0, 'overseas' => 0];

            if ($tab === 'summary' && $eventId) {
                $summaryParticipants = \App\Support\JobFairReport::summaryRows((int) $eventId, $isSraJobFairView);
                $summaryTotals       = \App\Support\JobFairReport::summaryTotals($summaryParticipants);
                $summaryRegistrants  = \App\Support\JobFairReport::registrantTotals((int) $eventId);
            }

            // ── TAB 6: TOTAL COMPANIES WITH VACANCIES (per Industry Group) ──
            $industryLocal    = collect();
            $industryOverseas = collect();

            if ($tab === 'industry' && $eventId) {
                $industryTotals   = \App\Support\JobFairReport::industryTotals($eventJobIds);
                $industryLocal    = $industryTotals['local'];
                $industryOverseas = $industryTotals['overseas'];
            }

            // ── TAB: TOP EMPLOYERS — kinsa ang nagdala ug pinakadaghang bakante
            // ── niining maong fair. Iya na sa usa ka event, dili na kada bulan:
            // ── ang pangutana kay kinsa ang nagdala ug trabaho ngadto sa fair.
            // ──
            // ── PESO Job Fair staff, 2026-09-02: dili na siya nagsalig sa
            // ── pagpili ug event. Kung walay gipili, ang ranggo sa tanang fair
            // ── — mao kana ang "Top 10 Employers" nga gipangayo. Kung naay
            // ── gipili nga fair, ang ranggo niadto lang. ──
            // ── Duha ka lamesa ang naa sa papel, ug walay usa nila naghisgot
            // ── ug employer: ang gipangita nga TRABAHO, ug ang bahin sa matag
            // ── INDUSTRIYA. Ang ulohan mao ang run down: pila ka kompanya ug
            // ── pila ka bakante, gibahin sa lokal ug overseas. ──
            // Ang listahan sa bakante nga gipasa sa siyudad. Parehas nga
            // datos sa Participating Companies, lahi ang pangutana: dinhi ang
            // bakante ang gi-ihap, dili ang tawo nga hikapon.
            $vacancyList = collect();
            if ($tab === 'vacancy_list') {
                $vacancyList = \App\Support\JobFairReport::localVacancyList($event, $isSraJobFairView);
            }

            $topOccupations = collect();
            $industryShares = ['rows' => [], 'total' => 0, 'unclassified' => ['quantity' => 0, 'share' => 0]];
            $runDown        = null;

            if ($tab === 'top_employers') {
                $topOccupations = \App\Support\JobFairReport::topOccupations($event, $isSraJobFairView);
                $industryShares = \App\Support\JobFairReport::industryShares($event, $isSraJobFairView);
                $runDown        = \App\Support\JobFairReport::runDownTotals($event, $isSraJobFairView);
            }

            // ── TAB 7: COMPANY PLACEMENT REPORT (local only, hired AFTER event date) ──
            $placementReport = null;
            if ($tab === 'placement' && $eventId && $event) {
                $placementReport = \App\Support\JobFairReport::placementQuery($eventJobIds, $event)
                    ->paginate(15)->withQueryString();
            }

            // ── TAB 9: ANG KAUGALINGON NGA REPORT SA STAFF ──
            // ── Gitipigan lang ug gipakita. Walay bisan usa sa mga numero sa
            // ── ibabaw nga nagbasa niini. ──
            // ── Ang tanan nga na-import, dili kadto ra sa usa ka fair.
            // ──
            // ── PESO Job Fair staff, 2026-09-02: kining tab wala na sa
            // ── dropdown sa event. Ang report nga gi-import iya gihapon sa
            // ── usa ka fair — gipakita ang ngalan sa fair sa matag laray —
            // ── apan ang listahan mao ang "unsa ang akong gi-upload", ug kana
            // ── matubag nga walay pagpili. ──
            $importedReports = collect();
            if ($tab === 'imported') {
                $importedReports = \App\Models\JobFairImportedReport::with(['uploader', 'jobFair'])
                    ->when($eventId, fn($q) => $q->where('job_fair_id', $eventId))
                    ->latest()
                    ->get();
            }

            // ── Nahuman na nga posting — milabay ang deadline o napuno ang
            // ── slots. Buhi gihapon ang Job row, mao nga bukas ang full details. ──
            $archivedJobs = \App\Models\Job::with('company')
                ->inactive()
                ->withCount([
                    'applications as hired_count' => fn($q) => $q->where('status', 'hired'),
                ])
                ->latest()
                ->paginate(5, ['*'], 'archived_page')
                ->withQueryString();

            return view('staff.reports.index', compact(
                'staffRole', 'tab', 'allEvents', 'event', 'eventId',
                'registrations', 'attendanceFilter', 'attendanceState', 'attendanceSearch',
                'totalRegistered', 'totalAttended',
                'companiesLocal', 'companiesOverseas', 'companyVacancyTotals',
                'furtherInterview', 'hots',
                'summaryParticipants', 'summaryTotals', 'summaryRegistrants',
                'industryLocal', 'industryOverseas',
                'placementReport', 'isSraJobFairView', 'reportView',
                'topOccupations', 'industryShares', 'runDown', 'vacancyList',
                'importedReports', 'archivedJobs'
            ));
        }

        if (!in_array($staffRole, ['lra', 'sra'])) return redirect()->route('staff.dashboard');

        $search      = request('search');
        $tab         = request('tab', 'registered');

        // Ang Archived Job Postings iya sa nagdumala sa posting — Job Vacancy
        // staff sa local, SRA sa overseas. Kung moabot ang LRA pinaagi sa daan
        // nga link, ibalik siya sa iyang unang tab imbes ipakita ang blangko.
        if ($staffRole === 'lra' && $tab === 'archived') {
            $tab = 'registered';
        }
        // ── PESO interview 2026-08-13: start date / end date sa reports.
        // ── Ang range gi-apply sa tulo ka listahan sa ubos, ug parehas nga
        // ── range ang gidala sa CSV ug sa printed nga kopya. ──
        $range       = \App\Support\DateRange::fromRequest(request());
        $jobseekerType = $staffRole === 'lra' ? ['local', 'both'] : ['overseas', 'both'];
        $isOverseas    = $staffRole === 'sra'; // employer classification: true = overseas (SRA), false = local (LRA)
        $registeredView = request('registered_view', 'all'); // 'all' = tanang registered jobseekers; 'inhouse' = ni-Join sa in-house

        // ── TAB 1a: ALL registered jobseekers (local/overseas base sa role) ──
        $registeredAllQuery = JobseekerRegistration::with(['user', 'nsrp'])
            ->whereHas('nsrp', fn($q) => $q->whereIn('type', $jobseekerType))
            ->when($search, fn($q) => $q->where(function ($sub) use ($search) {
                $sub->where('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            }))
            // Ang petsa sa pag-register ang basihan sa listahan sa registered.
            ->tap(fn($q) => $range->apply($q, 'jobseeker_registrations.created_at'));

        $totalRegisteredAll = (clone $registeredAllQuery)->count();

        // ── TAB 1b: jobseekers nga ni-Accept/Join sa in-house interview (Application.inhouse_participation) ──
        $registeredQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->where('inhouse_participation', 'accepted')
            ->whereHas('job', fn($q) => $q->where('schedule_type', 'inhouse'))
            ->whereHas('job.company', fn($q) => $q->where('is_overseas', $isOverseas))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
            ))
            // Ang updated_at sa job_matching mao ang adlaw nga na-set ang
            // status — mao nga kana ang tinuod nga petsa sa panghitabo.
            ->tap(fn($q) => $range->apply($q, 'job_matching.updated_at'));

        $totalRegistered = (clone $registeredQuery)->count();

        // ── TAB 2: PLACED APPLICANTS — status = hired ──
        $placedQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->where('status', 'hired')
            ->whereHas('jobseeker.nsrp', fn($r) => $r->whereIn('type', $jobseekerType))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($j) =>
                // Ang jobseeker_registrations walay `name` ug walay `email`.
                // Ang pangalan kay first_name + surname, ug ang email tua sa
                // users. Ang daan nga porma dinhi mo-500 sa matag pagpangita.
                $j->where('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"))
            ))
            ->tap(fn($q) => $range->apply($q, 'job_matching.updated_at'));

        $totalPlaced = (clone $placedQuery)->count();

        // ── TAB 3: JOB APPLICANTS REFERRED — status = waiting OR rejected ──
        $referredQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->whereIn('status', ['waiting', 'rejected'])
            ->whereHas('jobseeker.nsrp', fn($r) => $r->whereIn('type', $jobseekerType))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($j) =>
                // Ang jobseeker_registrations walay `name` ug walay `email`.
                // Ang pangalan kay first_name + surname, ug ang email tua sa
                // users. Ang daan nga porma dinhi mo-500 sa matag pagpangita.
                $j->where('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"))
            ))
            ->tap(fn($q) => $range->apply($q, 'job_matching.updated_at'));

        $totalReferred = (clone $referredQuery)->count();

        // ── Ang bulan sa Job Vacancies Solicited.
        // ──
        // ── 'all' kay tinuyo nga pag-ingon nga tanan. Ang stat card sa
        // ── dashboard nag-ihap sa tibuok, ug kung ang report kanunay nga
        // ── mo-sala ug bulan, ang numero nga gipindot ug ang lista nga
        // ── moabli magkalahi — ug walay makahibalo asa sa duha ang tinuod. ──
        $vacancyMonth = request('vacancy_month', now()->format('Y-m'));
        if ($vacancyMonth !== 'all' && !preg_match('/^\d{4}-\d{2}$/', $vacancyMonth)) {
            $vacancyMonth = now()->format('Y-m');
        }

        $solicitedJobs = collect();
        $totalVacanciesSolicited = 0;
        if ($staffRole === 'sra') {
            $vacancyQuery = $this->overseasSolicitedVacancies()
                ->with('company')
                ->when($vacancyMonth !== 'all', function ($q) use ($vacancyMonth) {
                    [$y, $m] = explode('-', $vacancyMonth);
                    $q->whereYear('updated_at', $y)->whereMonth('updated_at', $m);
                })
                ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('company', fn($u) => $u->where('company_name', 'like', "%{$search}%"))
                );

            $totalVacanciesSolicited = (clone $vacancyQuery)->count();
            $solicitedJobs = $tab === 'vacancies' ? $vacancyQuery->latest()->paginate(10) : collect();
        }

        // I-paginate lang ang active tab/sub-view para dili mabug-atan
        $registeredParticipants = ($tab === 'registered' && $registeredView === 'inhouse') ? $registeredQuery->latest()->paginate(10) : null;
        $registeredAll          = ($tab === 'registered' && $registeredView === 'all')     ? $registeredAllQuery->latest()->paginate(10) : null;
        $placedApplications     = $tab === 'placed'     ? $placedQuery->latest()->paginate(10)     : null;
        $referredApplications   = $tab === 'referred'   ? $referredQuery->latest()->paginate(10)   : null;

        // ── TAB: TOP EMPLOYERS — pila ka in-house nga interview ang gidala sa
        // ── usa ka employer sulod sa gipiling panahon.
        // ──
        // ── LRA ra. PESO SRA, 2026-08-26: ang ranggo sa overseas nga employer
        // ── napulot na ngadto sa job fair nga reports, diin ang giihap mao ang
        // ── bakante nga ilang gidala sa fair. Gitago ang tab sa screen ug
        // ── gibalibaran sad dinhi, kay ang tab maabot man pinaagi sa URL. ──
        $topEmployersFilter = request('top_employers_filter', 'monthly');
        $topEmployersMonth  = request('top_employers_month');
        $topEmployersYear   = request('top_employers_year');
        $topEmployersByCompanyInterviews = collect();

        if ($tab === 'top_employers' && $staffRole === 'sra') {
            return redirect()->route('staff.reports')
                ->with('info', 'Top Employers for overseas is in the Job Fair reports, ranked by the vacancies each agency brings to a fair.');
        }

        if ($tab === 'top_employers') {
            $inhouseQuery = \App\Models\Job::with('company')
                ->where('schedule_type', 'inhouse')
                ->where('posting_status', 'approved')
                ->whereHas('company', fn($q) => $q->where('is_overseas', $staffRole === 'sra'));

            if ($topEmployersFilter === 'yearly') {
                $selectedYear = $topEmployersYear ?: now()->year;
                $inhouseQuery->whereYear('updated_at', $selectedYear);
            } else {
                $selectedMonth = $topEmployersMonth ?: now()->format('Y-m');
                [$selYear, $selMon] = array_pad(explode('-', $selectedMonth), 2, now()->month);
                $inhouseQuery->whereYear('updated_at', $selYear)->whereMonth('updated_at', $selMon);
            }

            $topEmployersByCompanyInterviews = $inhouseQuery->get()
                ->groupBy('company_id')
                ->map(fn($group) => [
                    'employer'        => $group->first()->company,
                    'interview_count' => $group->count(),
                ])
                ->sortByDesc('interview_count')
                ->take(5)
                ->values();
        }

        // ── Nahuman na nga posting — milabay ang deadline o napuno ang
        // ── slots. Buhi gihapon ang Job row, mao nga bukas ang full details. ──
        // Walay date range dinhi — walay kontrol sa screen para niini, ug ang
        // listahan nga hilom nga na-filter sa usa ka URL parameter nga dili
        // makita kay bakak nga listahan.
        $archivedJobs = $staffRole === 'lra'
            ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5, 1, ['path' => request()->url()])
            : \App\Models\Job::with('company')
                ->inactive()
                ->withGroupHiredCount()
                ->withCount([
                    'applications as hired_count' => fn($q) => $q->where('status', 'hired'),
                ])
                ->latest()
                ->paginate(5, ['*'], 'archived_page')
                ->withQueryString();

        // ── Chart: pila ka na-placed kada bulan sulod sa gipili nga range.
        // ── Ang pag-group sa PHP, dili sa SQL — gamay ra ang row ug dili
        // ── magkalahi ang date function sa MySQL ug sa SQLite. ──
        $placedChart = (clone $placedQuery)->get(['job_matching.updated_at'])
            ->groupBy(fn($a) => $a->updated_at->format('M Y'))
            ->map->count();

        // ── TAB: EMPLOYER REPORT — what came of each in-house interview.
        // ──
        // ── LRA staff, 2026-08-23: usa ka semana human sa in-house interview,
        // ── makita nila ang report sa maong employer ug ang status sa matag
        // ── jobseeker. LRA ra sa karon; ugma pa ang interview sa SRA. ──
        $employerPostings = collect();
        $employerRoomOnly = collect();

        if ($tab === 'employer_report' && $staffRole === 'lra') {
            $paginate = function (\Illuminate\Support\Collection $rows, int $perPage, string $pageName) {
                $page = (int) request($pageName, 1);

                return new \Illuminate\Pagination\LengthAwarePaginator(
                    $rows->forPage($page, $perPage)->values(),
                    $rows->count(),
                    $perPage,
                    $page,
                    [
                        'path'     => request()->url(),
                        'pageName' => $pageName,
                        'query'    => request()->query(),
                    ]
                );
            };

            // Ang CSV wala mag-paginate — ang tibuok listahan gihapon ang
            // gi-download, gisala ra sa parehas nga pangita.
            $employerPostings = $paginate(
                \App\Support\InhouseEmployerReport::completedPostings(false, $search), 3, 'er_page'
            );
            $employerRoomOnly = $paginate(
                \App\Support\InhouseEmployerReport::completedScheduleOnly(false, $search), 5, 'room_page'
            );
        }

        // The two lists the desks were missing: who was given a room and who
        // was declined, and (SRA only) the interviews employers ran themselves.
        // Both desks answer for in-house; company interviews overseas are the
        // SRA's, the local ones belong to the Job Vacancy desk's own report.
        $inhouseReport = $tab === 'schedules'
            ? \App\Support\InhouseScheduleReport::paginate($isOverseas, $search)
            : null;

        $companyInterviews = ($tab === 'company_interview' && $isOverseas)
            ? \App\Support\CompanyInterviewReport::paginate(null, null, $search, true)
            : null;

        // ── TAB: EMPLOYER REPORTS — pila ang gikuha sa matag employer, ug kinsa.
        // ──
        // ── Ang "Total Hired" nga numero sa Registered Employer nga listahan
        // ── nagsulti ug ihap lang; kining tab nagsulti kung kinsa ang mga
        // ── tawo luyo sa maong numero. Mao nga ang numero didto mo-link diri.
        // ──
        // ── Walay date range dinhi tinuyo. Ang numero nga gi-click sa listahan
        // ── sa employer kay tibuok panahon; kung mo-sala ni ug petsa, ang
        // ── giklik nga numero ug ang mabasa dinhi magkalahi, ug walay
        // ── makahibalo asa sa duha ang tinuod. ──
        $employerHires   = null;
        $employerFocusId = (int) request('employer') ?: null;

        if ($tab === 'employer_hires') {
            $employerHires = EmployerNsrpRegistration::query()
                ->whereHas('employer', fn($q) => $q->where('role', 'company'))
                ->where('is_overseas', $isOverseas)
                ->whereHas('requirement', fn($q) => $q->where('status', 'approved'))
                // Usa ka kompanya nga giablihan gikan sa listahan sa employer.
                ->when($employerFocusId, fn($q) =>
                    $q->where('employer_nsrp_registrations_id', $employerFocusId))
                ->when($search, fn($q) => $q->where(fn($w) =>
                    $w->where('company_name', 'like', "%{$search}%")
                      ->orWhereHas('employer', fn($u) => $u->where('email', 'like', "%{$search}%"))
                ))
                ->with([
                    'employer',
                    'jobs.applications' => fn($q) => $q->where('status', 'hired')->with('jobseeker'),
                ])
                ->orderBy('company_name')
                ->paginate(5, ['*'], 'page')
                ->withQueryString();
        }

        return view('staff.reports.index', compact(
            'staffRole', 'tab', 'registeredView', 'reportView', 'range', 'placedChart',
            'registeredParticipants', 'registeredAll', 'placedApplications', 'referredApplications',
            'totalRegistered', 'totalRegisteredAll', 'totalPlaced', 'totalReferred',
            'vacancyMonth', 'solicitedJobs', 'totalVacanciesSolicited',
            'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear', 'topEmployersByCompanyInterviews',
            'employerPostings', 'employerRoomOnly', 'employerHires', 'employerFocusId',
            'archivedJobs', 'inhouseReport', 'companyInterviews'
        ));
    }

    // ───────────────────────────────
    // REPORTS — CSV download ug printable nga kopya (LRA / SRA)
    // ───────────────────────────────
    // PESO interview 2026-08-13: "ang AO ang responsable sa pag-submit sa
    // report... mahimong i-download ug i-print, ug adunay mga report nga
    // kinahanglan adunay signatory... isumite sa Mayor's Office ug DOL."
    public function exportReports(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if (!in_array($staff->staff_role, ['lra', 'sra'], true)) {
            return redirect()->route('staff.reports');
        }

        $staffRole     = $staff->staff_role;
        $range         = \App\Support\DateRange::fromRequest($request);
        $jobseekerType = $staffRole === 'lra' ? ['local', 'both'] : ['overseas', 'both'];
        // Ang Archived Job Postings walay export: rekord kadto sa posting mismo,
        // dili ihap sa tawo sulod sa usa ka panahon.
        $tab           = in_array($request->input('tab'), ['registered', 'placed', 'referred'], true)
                         ? $request->input('tab') : 'placed';

        if ($tab === 'registered') {
            $records = JobseekerRegistration::with(['user', 'nsrp'])
                ->whereHas('nsrp', fn($q) => $q->whereIn('type', $jobseekerType))
                ->tap(fn($q) => $range->apply($q, 'jobseeker_registrations.created_at'))
                ->latest()->get();

            $title   = 'Registered Jobseekers';
            $columns = ['Name', 'Sex', 'Age', 'Barangay', 'City / Municipality', 'Contact Number', 'Registered'];
            $rows = $records->map(fn($r) => [
                trim("{$r->surname}, {$r->first_name} {$r->middle_name}"),
                $r->sex ?? '',
                $r->age ?? '',
                $r->barangay ?? '',
                $r->municipality_city ?? '',
                $r->contact_number ?? '',
                $r->created_at->format('Y-m-d'),
            ]);
        } else {
            $statuses = $tab === 'placed' ? ['hired'] : ['waiting', 'rejected'];

            $records = \App\Models\Application::with(['jobseeker', 'job.company'])
                ->whereIn('status', $statuses)
                ->whereHas('jobseeker.nsrp', fn($r) => $r->whereIn('type', $jobseekerType))
                ->tap(fn($q) => $range->apply($q, 'job_matching.updated_at'))
                ->latest()->get();

            $title   = $tab === 'placed' ? 'Placed Applicants' : 'Job Applicants Referred';
            $columns = ['Jobseeker', 'Job Position', 'Employer', 'Status', 'Date'];
            $rows = $records->map(fn($a) => [
                trim(($a->jobseeker->surname ?? '') . ', ' . ($a->jobseeker->first_name ?? '')),
                $a->job->title ?? '',
                $a->job->company->company_name ?? '',
                ucfirst($a->status),
                $a->updated_at->format('Y-m-d'),
            ]);
        }

        $unitLabel = $staffRole === 'lra' ? 'Local Employment (LRA)' : 'Overseas Employment (SRA)';

        if ($request->input('format') === 'print') {
            return view('reports.print', [
                'title'      => $title,
                'subtitle'   => $unitLabel,
                'range'      => $range,
                'columns'    => $columns,
                'rows'       => $rows,
                'totals'     => ['Total records' => $records->count()],
                'preparedBy' => $staff->name,
            ]);
        }

        return \App\Support\ExcelExport::stream(
            'peso-' . str($title)->slug() . '-' . now()->format('Ymd') . '.xlsx',
            $columns,
            $rows,
            [
                // Hyphen, dili em-dash — tan-awa ang samang nota sa
                // CompanyWebController::exportReports.
                ['PESO Cagayan de Oro - ' . $title],
                ['Unit', $unitLabel],
                ['Covering period', $range->label()],
                ['Generated', now()->format('Y-m-d H:i')],
            ]
        );
    }

    // ───────────────────────────────
    // JOB FAIR — DOWNLOAD A REPORT TAB
    // ───────────────────────────────
    //
    // PESO Job Fair staff, 2026-08-23: every tab of the job fair report has to
    // come out as a file they can open in Excel. Until now exportReports() was
    // the only export in the system and it turns LRA/SRA away at the door
    // (staff_role check), so the Job Fair staff could download nothing at all.
    //
    // Separate from exportReports rather than folded into it: that one is
    // scoped by a date range for the three lists that go to the Mayor's Office,
    // this one is scoped by a single job fair event. Sharing a method would
    // mean one of the two scopes is always being ignored.
    public function exportJobFairReport(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        // Parehas ra sa nakakita sa page: ang Job Fair staff, ug ang SRA kung
        // anaa siya sa job fair nga panglantaw.
        $isSraJobFairView = $staff->staff_role === 'sra' && request('report_view') === 'jobfair';
        if ($staff->staff_role !== 'job_fair' && !$isSraJobFairView) {
            return redirect()->route('staff.reports');
        }

        // ── redirect(), dili back(): ang back() sa usa ka link nga walay
        // ── referer mobalik sa mismong URL sa export, ug malikos siya. ──
        $refuse = fn(string $why) => redirect()
            ->route('staff.reports', array_filter([
                'tab'         => $request->input('tab'),
                'event_id'    => $request->input('event_id'),
                'report_view' => $request->input('report_view'),
            ]))
            ->with('error', $why);

        $tab = $request->input('tab');
        if (!array_key_exists($tab, \App\Support\JobFairReport::TABS)) {
            return $refuse('That report cannot be downloaded.');
        }

        // ── Ang SRA dili makakita sa Company Placement sa page (local ra siya),
        // ── mao nga dili sad siya maka-download niini. Kung dili, ang tab nga
        // ── gitago maabot gihapon pinaagi sa URL. ──
        if ($isSraJobFairView && $tab === 'placement') {
            return $refuse('That report cannot be downloaded.');
        }

        $needsEvent = !in_array($tab, \App\Support\JobFairReport::TABS_WITHOUT_EVENT, true);
        $event      = $request->filled('event_id')
            ? \App\Models\JobFairEvent::find($request->input('event_id'))
            : null;

        if ($needsEvent && !$event) {
            return $refuse('Select a job fair event before downloading this report.');
        }

        $dataset = \App\Support\JobFairReport::dataset($event, $tab, $isSraJobFairView, [
            'attendance_filter'    => $isSraJobFairView ? 'overseas' : $request->input('attendance_filter', 'all'),
            'attendance_state'     => $request->input('attendance_state', $isSraJobFairView ? 'attended' : 'joined'),
            'attendance_search'    => $request->input('attendance_search'),
            'top_employers_filter' => $request->input('top_employers_filter', 'monthly'),
            'top_employers_month'  => $request->input('top_employers_month'),
            'top_employers_year'   => $request->input('top_employers_year'),
        ]);

        // ── Ang ulohan sa file. Kung wala ni, ang usa ka CSV nga naa na sa
        // ── folder sa staff walay mausulti kung asa nga fair siya gikan. ──
        // Ang uban nga titulo naay "Job Fair" na sa sulod — ang "Post Job Fair
        // Summary" mahimong "Job Fair Post Job Fair Summary" kung basta idugtong.
        $heading = str_contains($dataset['title'], 'Job Fair')
            ? $dataset['title']
            : 'Job Fair ' . $dataset['title'];

        $preamble = [
            // Hyphen, dili em-dash — tan-awa ang samang nota sa exportReports.
            ['PESO Cagayan de Oro - ' . $heading],
        ];

        if ($event) {
            $preamble[] = ['Event', $event->title];
            $preamble[] = ['Event date', $event->event_date->format('Y-m-d')];
            $preamble[] = ['Venue', $event->venue];
        }



        if ($tab === 'attendance' && !$isSraJobFairView) {
            $filter = $request->input('attendance_filter', 'all');
            $preamble[] = ['Jobseekers shown', $filter === 'all' ? 'Local and Overseas' : ucfirst($filter) . ' only'];

            // Ang file kinahanglan mosulti kung tanan ba nga ni-join ang naa
            // sulod o ang miabot ra — managlahi kaayo ang duha ka numero.
            $state = $request->input('attendance_state', 'joined');
            $preamble[] = ['Rows included',
                \App\Support\JobFairReport::STATES[$state] ?? \App\Support\JobFairReport::STATES['joined']];
        }

        if ($isSraJobFairView) {
            $preamble[] = ['Unit', 'Skills Registry Assistant (overseas only)'];
        }

        $preamble[] = ['Generated', now()->format('Y-m-d H:i')];

        return \App\Support\ExcelExport::stream(
            'peso-job-fair-' . str($dataset['title'])->slug() . '-' . now()->format('Ymd') . '.xlsx',
            $dataset['columns'],
            $dataset['rows'],
            $preamble
        );
    }

    // ───────────────────────────────
    // JOB FAIR — THE STAFF'S OWN REPORT
    // ───────────────────────────────
    //
    // PESO Job Fair staff, 2026-08-23: they keep a report by hand that the
    // system does not produce, and they need it inside the system.
    //
    // "Ilahi ang report nga gikan sa system ug ang excel report nga gi import."
    // The two are separate and stay separate — nothing uploaded here is ever
    // added into a system figure. It is read, shown as a table, and can be
    // downloaded again.
    public function importJobFairReport(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $request->validate([
            'job_fair_id' => 'required|exists:job_fair_events,job_fair_events_id',
            'title'       => 'required|string|max:120',
            'file'        => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ], [
            'title.required' => 'Give this report a name so you can tell it apart later.',
            // Ang mimes mo-salikway sad sa file nga walay sulod, kay walay
            // matag-an nga tipo sa 0 ka byte — mao nga giapil ang duha ka
            // hinungdan sa usa ka mensahe.
            'file.mimes'     => 'Only an Excel workbook (.xlsx, .xls) or a CSV file can be read, and it must not be empty.',
            'file.max'       => 'The file must be 5 MB or smaller.',
        ]);

        // ── Ang mimes dili kasaligan sa Windows: ang file nga gi-save sa Excel
        // ── moabot usahay nga text/plain o application/vnd.ms-excel. Ang
        // ── extension ang kataposang pagsusi, ug ang mensahe nagsulti sa
        // ── lakang nga kinahanglan buhaton — dili lang "invalid file". ──
        $file = $request->file('file');
        if (!in_array(strtolower($file->getClientOriginalExtension()), \App\Support\SpreadsheetImport::ALLOWED, true)) {
            return back()->withInput()->with('error',
                'That is a .' . $file->getClientOriginalExtension() . ' file. Upload the report as an '
                . 'Excel workbook (.xlsx) or a CSV.');
        }

        $parsed = \App\Support\SpreadsheetImport::read($file);

        if (!$parsed['headers']) {
            return back()->withInput()->with('error',
                'That file has no column headings on its first row, so there is nothing to show.');
        }

        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();

        \App\Models\JobFairImportedReport::create([
            'job_fair_id'       => $request->job_fair_id,
            'uploaded_by'       => $staffRecord?->staff_id,
            'title'             => $request->title,
            'original_filename' => $file->getClientOriginalName(),
            'headers'           => $parsed['headers'],
            'rows'              => $parsed['rows'],
            'row_count'         => count($parsed['rows']),
        ]);

        $note = $parsed['truncated']
            ? ' The file was longer than ' . \App\Support\SpreadsheetImport::MAX_ROWS . ' rows or wider than '
              . \App\Support\SpreadsheetImport::MAX_COLUMNS . ' columns, so only the first part was read.'
            : '';

        return back()->with('success',
            '"' . $request->title . '" imported — ' . count($parsed['rows']) . ' row(s).' . $note);
    }

    // ── I-download balik ang gi-import, sa samang porma nga gisulod. ──
    public function downloadJobFairImportedReport($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $report = \App\Models\JobFairImportedReport::with('jobFair')->findOrFail($id);

        return \App\Support\ExcelExport::stream(
            'peso-imported-' . str($report->title)->slug() . '-' . $report->created_at->format('Ymd') . '.xlsx',
            $report->headers,
            $report->rows,
            [
                // Hyphen, dili em-dash — tan-awa ang samang nota sa exportReports.
                ['PESO Cagayan de Oro - Imported Report'],
                ['Report', $report->title],
                ['Event', $report->jobFair->title ?? ''],
                ['Original file', $report->original_filename],
                ['Imported', $report->created_at->format('Y-m-d H:i')],
                // Isulat gyud sa file mismo. Kung dili, ang usa ka kopya niini
                // nga nagsuroy-suroy mahimong sayop nga basahon nga gikan sa
                // sistema ang mga numero.
                ['Source', 'Uploaded by PESO staff. Not generated from system records.'],
            ]
        );
    }

    public function deleteJobFairImportedReport($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $report = \App\Models\JobFairImportedReport::findOrFail($id);
        $title  = $report->title;
        $report->delete();

        return back()->with('success', '"' . $title . '" removed.');
    }

    // ───────────────────────────────
    // JOB FAIR — MARK ATTENDANCE
    // ───────────────────────────────
    public function markJobFairAttendance($registrationId)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $registration = \App\Models\JobFairRegistration::findOrFail($registrationId);

        if ($registration->is_attended) {
            return back()->with('info', 'This jobseeker is already marked as attended.');
        }

        $registration->update([
            'is_attended' => true,
            'attended_at' => now(),
        ]);

        return back()->with('success', 'Attendance marked successfully!');
    }

    public function unmarkJobFairAttendance($registrationId)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $registration = \App\Models\JobFairRegistration::with('jobFair')->findOrFail($registrationId);

        if (($registration->jobFair->status ?? null) === 'completed') {
            return back()->with('error', 'Cannot unmark attendance for a completed job fair event.');
        }

        $registration->update([
            'is_attended' => false,
            'attended_at' => null,
        ]);

        return back()->with('success', 'Attendance unmarked.');
    }

    // ───────────────────────────────
    // PROFILE
    // ───────────────────────────────
    public function showProfile()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        return view('staff.profile', compact('staff'));
    }

    public function updateProfile(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->users_id . ',users_id',
            'phone' => ['nullable', 'string', new \App\Rules\MobileNumber],
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->hasFile('profile_photo')) {
            if ($staff->profile_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($staff->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $staff->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => \App\Rules\PasswordPolicy::required(),
        ]);

        if (!Hash::check($request->current_password, $staff->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $staff->update(['password' => Hash::make($request->new_password)]);
        return back()->with('success', 'Password changed successfully.');
    }

    // ───────────────────────────────
    // NOTIFICATIONS
    // ───────────────────────────────
    public function markNotificationRead($id)
    {
        $staffRecord = \App\Models\Staff::where('user_id', Auth::id())->first();
        if (!$staffRecord) return response()->json(['error' => 'Unauthorized'], 401);

        \App\Models\Announcement::where('announcements_id', $id)
            ->where('staff_id', $staffRecord->staff_id)
            ->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead()
    {
        $staffRecord = \App\Models\Staff::where('user_id', Auth::id())->first();
        if (!$staffRecord) return back();

        \App\Models\Announcement::where('staff_id', $staffRecord->staff_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function notifications()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
        $notifications = $staffRecord
            ? \App\Models\Announcement::where('staff_id', $staffRecord->staff_id)->latest()->get()
            : collect();

        return view('staff.notifications.index', compact('notifications'));
    }

    // ── AJAX — fresh unread count + latest notifications, para live ang bell (dili na kinahanglan i-reload ang page) ──
    public function notificationsFetch()
    {
        $staff = $this->authStaff();
        if (!$staff) return response()->json(['error' => 'Unauthorized'], 401);

        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
        if (!$staffRecord) return response()->json(['unread_count' => 0, 'notifications' => []]);

        $unreadCount = \App\Models\Announcement::where('staff_id', $staffRecord->staff_id)->where('is_read', false)->count();

        $notifications = \App\Models\Announcement::where('staff_id', $staffRecord->staff_id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notif) {
                return [
                    'id'       => $notif->announcements_id,
                    'title'    => $notif->title,
                    'message'  => $notif->message,
                    'time_ago' => $notif->created_at->diffForHumans(),
                    'is_read'  => (bool) $notif->is_read,
                    'url'      => $notif->staffLinkUrl(),
                ];
            });

        return response()->json(['unread_count' => $unreadCount, 'notifications' => $notifications]);
    }
}