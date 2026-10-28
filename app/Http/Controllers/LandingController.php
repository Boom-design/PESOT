<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $jobType = $request->input('job_type', 'all'); // all | local | overseas | job_fair

        $filters = $this->jobTypeFilter($jobType);

        // ── onePerGroup(): usa ka card kada POSISYON, dili kada channel. Ang
        // ── usa ka bakante nga gi-post sa Company Interview ug In-house kay duha ka
        // ── row nga managsama ug titulo, kompanya, lokasyon ug slots — sa mata
        // ── sa bisita sa landing page, kaduha ra siya nagpakita sa parehas nga
        // ── trabaho. Ang card dinhi walay schedule badge ug modal ra ang
        // ── giablihan, mao nga walay impormasyon nga mawala sa pag-usa. ──
        $query = Job::with('company')->active()->tap($filters)->onePerGroup($filters);

        $jobs = $query->latest()->get();

        // Ang ihap kinahanglan mo-uyon sa listahan: Job::active() usa ra ang
        // kahulogan sa "buhi pa" — abli, wala pa malabyi ang deadline, ug wala
        // pa mapuno ang slots sa tibuok posting group. Ug tungod kay ang
        // listahan usa ra ka card kada posisyon, ang ihap kinahanglan mo-ihap
        // ug posisyon usab — kung dili, "2 open jobs" ang isulti niya samtang
        // usa ra ka card ang makita.
        $openJobsCount = Job::active()->onePerGroup()->count();

        $heroImages = $this->heroImages();

        $jobFairs = $this->jobFairsOnShow();

        // ── ANG LANDING PAGE NAGBASA SAMA SA POSTER, DILI SAMA SA LISTAHAN ──
        //
        // PESO CDO, 2026-10-28: usa ka card kada bakante nagpalagpot sa
        // bisita sa unom ka kahon nga managsama ug hitsura, ug ang poster nga
        // gipagawas sa opisina sa Facebook wala gyud managsama niana. Didto,
        // ang kompanya ang ulohan ug ang mga posisyon ang bullet sa ilalom —
        // mabasa sa usa ka pagtan-aw kinsa ang nagkuha ug tawo ug para sa
        // unsa.
        //
        // Ang mga detalye — lokasyon, slots, deadline, deskripsyon — naa
        // gihapon sa luyo sa login, diin ang kada posting naa sa iyang
        // kaugalingon nga panid. Kini nga panid dili na mosulay pag-ilis
        // niadto.
        $hiringBoard = $jobs
            ->groupBy(fn (Job $job) => $job->company->company_name ?? 'Company')
            ->map(fn ($ofCompany) => $ofCompany
                ->unique('title')
                ->map(fn (Job $job) => [
                    'title'       => $job->title,
                    'company'     => $job->company->company_name ?? 'Company',
                    'location'    => $job->location,
                    'type'        => ucfirst(str_replace('_', ' ', $job->type)),
                    'slots'       => $job->slots,
                    'deadline'    => $job->deadline ? $job->deadline->format('M d, Y') : '',
                    'description' => strip_tags($job->description ?? ''),
                    'poster'      => $job->poster_image ? asset('storage/' . $job->poster_image) : '',
                ])
                ->values()
                ->all())
            ->sortKeys();

        return view('landing', compact(
            'jobs', 'openJobsCount', 'jobType', 'heroImages', 'jobFairs', 'hiringBoard'
        ));
    }

    /**
     * The job fairs the public may see, each one with its list of hiring.
     *
     * PESO CDO, 2026-10-28: the office prints a poster for every fair — the
     * event on one page, then "LIST OF JOB HIRING", a block per company with
     * its openings under it. The landing page showed the same vacancies as
     * loose cards, so a visitor could read six of them without ever learning
     * they belonged to one day in one hall.
     *
     * The poster is uploaded. The list is not: it is built here from the
     * postings themselves, so it cannot disagree with what the employers
     * actually offered, and nobody has to redraw it when one of them adds a
     * position.
     *
     * Only Job::active() postings are read, which is what already decides
     * what the public may see. A fair whose cutoff has not arrived has no
     * open postings yet and simply lists none.
     */
    private function jobFairsOnShow(): \Illuminate\Support\Collection
    {
        // Ang pinakasunod ra, usa.
        //
        // PESO CDO, 2026-10-29: usa ka fair kada bulan sa tibuok tuig, mao
        // nga ang paglista sa tanan nga umaabot maghatag ug duha o tulo ka
        // poster nga nagtapad, ug dili maila sa bisita kung asa kanila ang
        // sunod. Ang poster sa opisina usa ra gyud ka adlaw ang gipakita.
        //
        // Ang sala dili "kini nga bulan". Ang fair niini nga bulan mahimong
        // nahuman na — sa Oktubre 29, ang Oktubre nga fair milabay na sa
        // ika-14 — ug ang panid mahimong walay ikapakita samtang duna pa
        // diay umaabot sa sunod bulan.
        $events = \App\Models\JobFairEvent::whereIn('status', ['upcoming', 'ongoing'])
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->limit(1)
            ->get();

        if ($events->isEmpty()) {
            return collect();
        }

        // Which posting belongs to which fair is recorded on the employment
        // request, the same table JobFairAudience::eventJobs() reads. Asking
        // the Job table directly would need a column that is not there.
        $requests = \App\Models\JobFairEmploymentRequest::whereIn(
            'job_fair_id', $events->pluck('job_fair_events_id')
        )->get(['job_fair_id', 'job_id']);

        $jobs = $requests->isEmpty()
            ? collect()
            : Job::with('company')
                ->active()
                ->whereIn('job_qualifications_id', $requests->pluck('job_id'))
                ->get()
                ->keyBy('job_qualifications_id');

        return $events->map(function (\App\Models\JobFairEvent $event) use ($requests, $jobs) {
            $ofThisFair = $requests
                ->where('job_fair_id', $event->job_fair_events_id)
                ->map(fn ($r) => $jobs->get($r->job_id))
                ->filter();

            // One block per company, its openings listed under it — and a
            // title offered twice is still one line on a poster.
            $markets = $ofThisFair
                ->groupBy(fn (Job $job) => $job->company?->is_overseas ? 'overseas' : 'local')
                ->map(fn ($rows) => $rows
                    ->groupBy(fn (Job $job) => $job->company->company_name ?? 'Company')
                    ->map(fn ($ofCompany) => $ofCompany->pluck('title')->unique()->values()->all())
                    ->sortKeys());

            return [
                'event'     => $event,
                'markets'   => $markets,
                'companies' => $markets->sum(fn ($byCompany) => $byCompany->count()),
            ];
        })->filter(fn ($fair) => $fair['companies'] > 0 || $fair['event']->poster_image)->values();
    }

    /**
     * Every image dropped into public/images/hero, sorted by filename.
     * Falls back to the original city hall photo so the hero never renders empty.
     */
    private function heroImages(): array
    {
        $dir = public_path('images/hero');
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $files = [];

        if (is_dir($dir)) {
            foreach (glob($dir . '/*') as $path) {
                if (is_file($path) && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $allowed, true)) {
                    $files[] = basename($path);
                }
            }
        }

        if (empty($files)) {
            return [asset('images/cityhall.png')];
        }

        natcasesort($files);

        return array_map(fn ($name) => asset('images/hero/' . $name), array_values($files));
    }

    public function allJobs(Request $request)
    {
        $jobType = $request->input('job_type', 'all'); // all | local | overseas | job_fair

        $filters = $this->jobTypeFilter($jobType, $request->search);

        // Parehas nga lagda sa landing page: usa ka card kada posisyon. Kini
        // ang "View All Jobs" nga page sa parehas nga listahan, mao nga kung
        // dinhi ra ang gi-ayo, mobalik dayon ang doble pag-klik sa bisita.
        $query = Job::with('company')->active()->tap($filters)->onePerGroup($filters);

        $jobs = $query->latest()->paginate(9)->withQueryString();

        return view('jobs-all', compact('jobs', 'jobType'));
    }

    // ── Usa ra ka kopya sa sala sa publiko nga listahan. Gigamit kaduha kada
    // ── request: kausa sa gipakita nga listahan, ug kausa sulod sa
    // ── onePerGroup() aron ang representante nga row sa kada posting group
    // ── kuhaon gikan sa parehas nga sinala nga set. Kung magkalahi sila,
    // ── mahimong mapili ang row nga masala ra man diay pagawas, ug mawala
    // ── ang tibuok posisyon sa listahan. ──
    private function jobTypeFilter(string $jobType, ?string $search = null): \Closure
    {
        return function ($q) use ($jobType, $search) {
            $q->when($jobType === 'local', fn($x) => $x->whereHas('company', fn($c) => $c->where('is_overseas', false)))
              ->when($jobType === 'overseas', fn($x) => $x->whereHas('company', fn($c) => $c->where('is_overseas', true)))
              ->when($jobType === 'job_fair', fn($x) => $x->where('schedule_type', 'job_fair'));

            if ($search) {
                $q->where(function ($x) use ($search) {
                    $x->where('title', 'like', '%' . $search . '%')
                      ->orWhere('location', 'like', '%' . $search . '%');
                });
            }
        };
    }
}   