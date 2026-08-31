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

        $jobs = $query->latest()->take(4)->get();

        // Ang ihap kinahanglan mo-uyon sa listahan: Job::active() usa ra ang
        // kahulogan sa "buhi pa" — abli, wala pa malabyi ang deadline, ug wala
        // pa mapuno ang slots sa tibuok posting group. Ug tungod kay ang
        // listahan usa ra ka card kada posisyon, ang ihap kinahanglan mo-ihap
        // ug posisyon usab — kung dili, "2 open jobs" ang isulti niya samtang
        // usa ra ka card ang makita.
        $openJobsCount = Job::active()->onePerGroup()->count();

        $heroImages = $this->heroImages();

        return view('landing', compact('jobs', 'openJobsCount', 'jobType', 'heroImages'));
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