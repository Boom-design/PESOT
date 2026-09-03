<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CompanyWebController extends Controller
{
    // ── HELPER — ensure logged in user is a company ──────────
    private function authCompany()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'company') {
            Auth::logout();
            return null;
        }
        return $user;
    }

    /**
     * Feed for the dashboard calendar.
     *
     * Narrower than the office feed on purpose: the employer sees the fairs
     * they were invited to and their own interview days, and nothing that
     * belongs to PESO or to another employer. `blocked` is empty because the
     * dashboard calendar only shows what is on — the booking calendar inside
     * the posting form is the one that has to say what is unavailable.
     */
    public function calendarData()
    {
        $company = $this->authCompany();
        if (!$company) return response()->json(['error' => 'Unauthorized'], 401);

        $active = $company->activeCompany();
        if (!$active) return response()->json(['dates' => [], 'holidays' => [], 'blocked' => [], 'legend' => []]);

        return response()->json([
            'dates'    => \App\Support\StaffCalendar::forEmployer($active->employer_nsrp_registrations_id),
            'holidays' => \App\Support\Holidays::aroundNow(),
            'blocked'  => [],
            'legend'   => \App\Support\StaffCalendar::TYPES,
        ]);
    }

    // ───────────────────────────────
    // MANY COMPANIES UNDER ONE ACCOUNT
    // ───────────────────────────────
    //
    // PESO IT, 2026-08-26: the same HR officer can be the authorised contact
    // for two companies, and asked for one e-mail to cover both. The e-mail is
    // how a person signs in, so the account stayed one and the companies
    // became many. Which one they are working on is a property of the sitting,
    // not of the account, so it is held in the session.

    /** Work on a different company from now until they switch again. */
    public function switchCompany(Request $request, $id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        // Gisusi batok sa iyang kaugalingong kompanya. Ang gi-paste nga id
        // gikan sa laing account walay mahimo dinhi.
        $chosen = $company->employerCompanies()
            ->where('employer_nsrp_registrations_id', $id)
            ->first();

        if (!$chosen) {
            return back()->with('error', 'That company is not on your account.');
        }

        $request->session()->put('active_company_id', $chosen->employer_nsrp_registrations_id);

        return redirect()->route('company.dashboard')
            ->with('success', 'You are now working on ' . $chosen->company_name . '.');
    }

    /** The registration form again, minus the parts about the account. */
    public function showAddCompany()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        return view('auth.register_company', ['addingCompany' => true]);
    }

    /**
     * Put a second establishment on this account.
     *
     * Runs through the same App\Support\EmployerRegistration as public
     * sign-up: same fields, same documents, same notice to the desk. The only
     * difference is that the account already exists, so nothing here touches
     * the e-mail or the password.
     */
    public function storeAddCompany(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $request->validate(
            array_merge(
                \App\Support\EmployerRegistration::companyRules(),
                \App\Http\Controllers\UnifiedAuthController::requirementFileRules()
            ),
            array_merge(\App\Support\EmployerRegistration::messages(), [
                'business_permit_year.required_with' => 'Say which year this business permit covers.',
            ])
        );

        $new = \App\Support\EmployerRegistration::create($company, $request);

        // Ang bag-o mao dayon ang gitrabahoan: mao man ang gikan niya, ug ang
        // requirements niini ang sunod niyang atimanon.
        $request->session()->put('active_company_id', $new->employer_nsrp_registrations_id);

        return redirect()->route('company.dashboard')->with('success',
            $new->company_name . ' has been added to your account, and you are now working on it. '
            . 'PESO staff will review its requirements.');
    }

    // ───────────────────────────────
    // ACCOUNT DISABLED — ang bugtong pahina nga maabot sa dormant nga employer
    // ───────────────────────────────
    //
    // Ang opisina nangutana kung nagpangita pa ba sila ug tawo; walay mitubag,
    // mao nga na-disable ang account ug natago ang iyang mga posting. Dinhi
    // niya isulti kung unsa ang nahitabo. Ang staff ang mo-abli pag-usab —
    // dili ni automatic, kay ang tumong sa tibuok butang mao nga adunay tawo
    // sa opisina nga nakabasa sa tubag.
    public function showDormantNotice()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        // Ang dili dormant walay labot dinhi — kung wala ni, ang usa ka link
        // gikan sa daan nga email modala kanila sa pahina nga walay kahulogan.
        if ($company->status !== 'dormant') {
            return redirect()->route('company.dashboard');
        }

        $employer   = $company->activeCompany();
        $lastPosted = $employer?->jobs()->max('created_at');

        return view('company.dormant', [
            'employer'    => $employer,
            'lastPosted'  => $lastPosted ? \Carbon\Carbon::parse($lastPosted) : null,
            'hiddenCount' => $employer
                ? \App\Models\Job::where('company_id', $employer->employer_nsrp_registrations_id)
                    ->whereNotNull('dormant_closed_at')->count()
                : 0,
        ]);
    }

    public function submitDormantNotice(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        if ($company->status !== 'dormant') {
            return redirect()->route('company.dashboard');
        }

        $request->validate([
            'inactivity_status'   => 'required|in:still_hiring,paused,closed',
            'inactivity_response' => 'required|string|max:2000',
        ], [
            'inactivity_status.required'   => 'Please pick the line that describes your company right now.',
            'inactivity_response.required' => 'Please tell PESO what happened, in your own words.',
        ]);

        $employer = $company->activeCompany();
        if (!$employer) {
            return back()->with('error', 'Your company record is missing. Please contact PESO.');
        }

        $employer->update([
            'inactivity_responded_at' => now(),
            'inactivity_status'       => $request->inactivity_status,
            'inactivity_response'     => $request->inactivity_response,
        ]);

        // Ang lokal kay sa Job Vacancy, ang overseas kay sa SRA.
        $staffRole = $employer->is_overseas ? 'sra' : 'job_vacancy';
        $staffIds  = \App\Models\Staff::where('staff_role', $staffRole)->pluck('staff_id');

        if ($staffIds->isNotEmpty()) {
            \App\Models\Announcement::sendToStaff([
                'type'           => 'employer_inactivity_reply',
                'title'          => 'Employer answered the status check 📨',
                'message'        => ($employer->company_name ?? 'An employer')
                                    . ' has explained why they stopped posting. Read it on the Inactive Employer Account tab'
                                    . ' and switch their account back on if it should be reopened.',
                'reference_type' => 'employer_inactivity',
                'reference_id'   => $employer->employer_nsrp_registrations_id,
            ], $staffIds);
        }

        return back()->with('success',
            'Thank you — your answer has been sent to PESO. Your account will be reopened once staff have read it.');
    }

    // ── HELPER — naa bay adlaw nga puliki ang opisina sulod sa gipili?
    //
    // BISAN USA ka adlaw igo na sa pagbalibad sa TIBUOK range. Ang range dili
    // "mga adlaw nga mahimong pilion" — reserbasyon siya: ang employer mo-
    // okupar sa kada adlaw sulod niini ug siya ang mopili kung asa siya mo-
    // interview. Kung ang Aug 25 naay meeting, ang range nga Aug 24–26 mo-
    // reserba gihapon sa Aug 25 — mao nga dili siya mahimo.
    //
    // (Kaniadto ang usa ka adlaw dili igo, kay ang PESO pa ang mopili ug adlaw
    // sulod sa window. Nausab kadto: ang employer na ang mopili.) ──
    private function officeBlockedWindowError(?string $start, ?string $end): ?string
    {
        return \App\Support\InhouseBooking::blockedWindowError($start, $end);
    }

    // ── Ang limit sa PESO Office. Ang tinuod nga lagda naa sa
    // ── App\Support\InhouseBooking — gigamit pud siya sa walk-in nga counter,
    // ── mao nga usa ra ang tubag bisan asa gikan ang pangayo. ──
    private function pesoOfficeCapacityError(?string $start, ?string $end): ?string
    {
        return \App\Support\InhouseBooking::capacityError($start, $end);
    }

    // ── HELPER — force redirect sa Requirements kung wala pa naka-submit ──
    private function requireRequirements($company)
    {
        $nsrp = $company->activeCompany();
        $hasSubmitted = $nsrp && \App\Models\EmployerRequirement::where('user_id', $nsrp->employer_nsrp_registrations_id)->exists();
        if (!$hasSubmitted) {
            return redirect()->route('company.requirements')
                ->with('info', 'Please submit your requirements first before accessing other features.');
        }
        return null;
    }

    // ── HELPER — force redirect kung requirements not yet approved ──
    // ── Lahi ang mensahe kung na-expire ang Business Permit: dili kadto "wala pa
    // ── na-approve", na-approve na kaniadto ug nahurot lang ang bili. Ang employer
    // ── kinahanglan mahibalo nga renewal ang solusyon, dili bag-ong submission. ──
    private function requireApprovedRequirements($company)
    {
        $nsrp = $company->activeCompany();
        $requirement = $nsrp
            ? \App\Models\EmployerRequirement::where('user_id', $nsrp->employer_nsrp_registrations_id)->latest('updated_at')->first()
            : null;

        if ($requirement && $requirement->status === 'approved') {
            return null;
        }

        $message = $requirement && $requirement->status === 'expired'
            ? 'Your ' . \App\Models\EmployerRequirement::DOCUMENT_LABELS['business_permit']
                . ' has expired, so job posting is paused. Upload a renewed copy below to resume.'
            : 'Your requirements must be approved before accessing this feature.';

        return redirect()->route('company.requirements')->with('info', $message);
    }

    // ── HELPER — block ra ang employer nga na-restrict (expired nga Business
    // ── Permit). Lahi ni sa requireApprovedRequirements: ang bag-ong employer
    // ── nga nag-hulat pa sa unang approval makapadayon gihapon sa pag-request,
    // ── kay closed/pending man gihapon ang posting ug ang staff maoy mo-abli.
    // ── Ang na-expire kay dili — kaniadto na-approve, karon nahurot na. ──
    private function blockIfRestricted($company)
    {
        if (($company->status ?? null) !== 'restricted') {
            return null;
        }

        return redirect()->route('company.requirements')->with('error',
            'Your ' . \App\Models\EmployerRequirement::DOCUMENT_LABELS['business_permit']
            . ' has expired. Job posting and job fair invitations are paused until PESO staff approve a renewed copy.');
    }

    // ───────────────────────────────
    // LOGIN
    // ───────────────────────────────
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'company') {
            return redirect()->route('company.dashboard');
        }
        return view('company.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // ── Brute-force guard — check before touching the database at all ──
        if (\App\Support\LoginThrottle::tooManyAttempts($request)) {
            return back()->withErrors([
                'email' => \App\Support\LoginThrottle::message(\App\Support\LoginThrottle::secondsRemaining($request)),
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->role !== 'company') {
            \App\Support\LoginThrottle::recordFailure($request);
            return back()->withErrors(['email' => 'No company account found.']);
        }

        if ($user->status === 'deactivated') {
            \App\Support\LoginThrottle::recordFailure($request);
            return back()->withErrors(['email' => 'Your account has been deactivated. Please contact PESO.']);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            \App\Support\LoginThrottle::clear($request);
            $request->session()->regenerate();
            return redirect()->route('company.dashboard');
        }

        \App\Support\LoginThrottle::recordFailure($request);
        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    // ───────────────────────────────
    // LOGOUT
    // ───────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // ───────────────────────────────
    // DASHBOARD
    // ───────────────────────────────
    public function dashboard()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $nsrpId = $company->activeCompany()->employer_nsrp_registrations_id;

        // ── Ang tulo ka stat card static na: usa ka numero, walay pagpili.
        // ── Naa kaniadto'y year/month/day nga filter dinhi, apan ang dashboard
        // ── mao ang unang makita sa employer ug ang tulo ka dropdown didto
        // ── nagpangutana kaniya sa dili pa siya makakita ug numero. Ang
        // ── pagbahin-bahin sa petsa naa sa Reports, diin siya gipangita. ──

        // ── Active ra ang tulo ka stat card (PESO interview, 2026-08-12). Ang
        // ── nahomana nga job post mahanaw sa tulo — apil ang applicants ug
        // ── hired niini — kay dili na man to active nga vacancy. Ang tibuok
        // ── kasaysayan naa sa Reports, mao nga walay mawala. ──
        // alive(), dili active(): parehas ang gi-ihap dinhi ug ang gilista sa
        // Active Job Vacancy page — apil ang Job Fair nga naghulat pa ug event.
        // Kung magkalahi, mag-duda ang employer kung asa ang tinuod.
        $activeJobs = function ($q) use ($nsrpId) {
            $q->where('company_id', $nsrpId)->alive();
        };

        $totalJobs = Job::where('company_id', $nsrpId)->alive()->count();

        // Ang hired dili na maapil sa "active applicants" — mabalhin sila
        // ngadto sa Hired card, dili gi-ihap kaduha.
        $totalApplicants = Application::whereHas('job', $activeJobs)
            ->where('status', '!=', 'hired')
            ->count();

        $hired = Application::whereHas('job', $activeJobs)
            ->where('status', 'hired')
            ->count();

        $recentJobs = Job::where('company_id', $nsrpId)
            ->latest()
            ->take(5)
            ->get();

        // Job vacancies are no longer collected at registration, so prompt the
        // employer to post one as soon as they log in — even while their
        // requirements are still pending. Stops once they have posted any job.
        // Gi-ihap tanan nga job, dili ang active ra: ang prompt para sa
        // employer nga wala pa gyud ka-post bisan kausa. Kung $totalJobs ang
        // gamiton, mobalik ang prompt matag mahomana ang tanan nilang posting.
        $showJobPostingPrompt = Job::where('company_id', $nsrpId)->doesntExist();

        return view('company.dashboard', compact(
            'company',
            'totalJobs',
            'totalApplicants',
            'hired',
            'recentJobs',
            'showJobPostingPrompt'
        ));
    }

    // ───────────────────────────────
    // JOB POSTS — INDEX
    // ───────────────────────────────
    public function jobs()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        // No approved-requirements guard here: an employer may submit a job
        // posting while their requirements are still under review, so they also
        // need to see the pending requests they submitted.

        // ── Ang nag-hulat pa ug desisyon sa staff ra ang makita diri: pending
        // ── ug rejected. Pag-approve, mabalhin sa "Active Job Vacancy"; pag-
        // ── expire o pagkapuno, mabalhin sa Reports → Archived Job Postings.
        // ──
        // ── Ang basihan kay posting_status, DILI status='closed'. Ang
        // ── status='closed' duha ka lahi ug kahulogan: ang bag-ong request
        // ── mag-sugod nga closed samtang nag-hulat, ug ang nahuman nga posting
        // ── closed pud. Kung status ang basihan, ang nahuman nga posting
        // ── mobalik diri nga daw bag-ong request — ug ma-edit pa gyud. ──
        $jobs = Job::where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->whereIn('posting_status', ['pending', 'rejected'])
            ->latest()->get();

        // Check kung naa nay approved requirements + wala pa na-confirm ang initial vacancy gikan sa registration
        $requirement       = \App\Models\EmployerRequirement::where('user_id', $company->activeCompany()->employer_nsrp_registrations_id)->where('status', 'approved')->first();
        $employerNsrp      = $company->activeCompany();
        $showInitialVacancy = $requirement
            && $employerNsrp
            && !$employerNsrp->initial_vacancy_confirmed
            && !empty($employerNsrp->initial_vacancy_data);

        return view('company.jobs.index', compact('company', 'jobs', 'showInitialVacancy', 'employerNsrp'));
    }

    // ───────────────────────────────
    // CONFIRM INITIAL VACANCY (gikan sa registration data)
    // ───────────────────────────────
   public function confirmInitialVacancy(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');
        if ($guard = $this->blockIfRestricted($company)) return $guard;
        // Parehas sa requestJob: ang posting mogawas dayon, mao nga ang
        // requirements kinahanglan na-approve na.
        if ($guard = $this->requireApprovedRequirements($company)) return $guard;

        $employerNsrp = $company->activeCompany();
        if (!$employerNsrp || $employerNsrp->initial_vacancy_confirmed) {
            return redirect()->route('company.jobseekers');
        }

        $request->validate([
            'schedule_type'  => 'required|in:inhouse,company_interview,job_fair',
            // Dili karong adlawa: naay preparasyon ang PESO. Tan-awa ang
            // OfficeCalendar::earliestBookable().
            'preferred_date' => 'required_if:schedule_type,inhouse|required_if:schedule_type,company_interview|nullable|date|after_or_equal:'
                                . \App\Support\OfficeCalendar::earliestBookableDate(),
            // Ang in-house kay window: ang employer moingon kung kanus-a sila
            // libre, ang LRA/SRA mopili sa aktwal nga adlaw. Blangko = usa ra
            // ka adlaw.
            'preferred_date_end' => 'nullable|date|after_or_equal:preferred_date',
            'venue_type'     => 'required_if:schedule_type,inhouse|nullable|in:peso_office,other',
            'venue_address'  => 'required_if:venue_type,other|nullable|string|max:255',
            'positions'                          => 'required|array|min:1',
            'positions.*.title'                 => 'required|string|max:255',
            'positions.*.description'           => 'required|string',
            'positions.*.type'                  => 'required|in:permanent,contractual,project_based,internship,part_time,work_from_home',
            'positions.*.location'              => 'required|string|max:255',
            'positions.*.salary'                => 'nullable|string|max:100',
            'positions.*.slots'                 => 'required|integer|min:1',
            'positions.*.deadline'               => 'nullable|date|after_or_equal:today|before_or_equal:' . now()->addYear()->toDateString(),
            'positions.*.experience_months'     => 'nullable|integer|min:0',
            'positions.*.religion'              => 'nullable|string|max:100',
            'positions.*.sex_preference'        => 'nullable|in:Male,Female,Any',
            'positions.*.civil_status'          => 'nullable|in:Single,Married,Any',
            'positions.*.other_qualifications'  => 'nullable|string',
            'positions.*.accepts_disability'    => 'nullable|in:yes,no',
            'positions.*.disability_types'      => 'nullable|array',
            'positions.*.education_required'    => 'nullable|string|max:100',
            'positions.*.course_major'          => 'nullable|string|max:255',
            'positions.*.license'               => 'nullable|string|max:255',
            'positions.*.eligibility'           => 'nullable|string|max:255',
            'positions.*.certification'         => 'nullable|string|max:255',
            'positions.*.language'              => 'nullable|string|max:255',
            'positions.*.preferred_residence'   => 'nullable|string|max:255',
            'positions.*.accepts_programs'      => 'nullable|array',
            'positions.*.job_image'             => 'nullable|string',
        ], [
            'preferred_date.after_or_equal'     => \App\Support\OfficeCalendar::leadTimeMessage(),
            'preferred_date_end.after_or_equal' => 'The last available date cannot be before the first.',
        ]);

        // ── Ang tanang adlaw sa window puliki ang opisina? Kung naa pa'y bisan
        // ── usa ka adlaw nga libre, padayon — ang staff na ang mopili didto. ──
        if ($request->schedule_type === 'inhouse') {
            if ($why = $this->officeBlockedWindowError($request->preferred_date, $request->preferred_date_end)) {
                return back()->withInput()->with('error', $why);
            }
        }

        if ($request->schedule_type === 'inhouse' && $request->venue_type === 'peso_office') {
            if ($why = $this->pesoOfficeCapacityError($request->preferred_date, $request->preferred_date_end)) {
                return back()->withInput()->with('error', $why);
            }
        }

        $nsrpId       = $company->activeCompany()->employer_nsrp_registrations_id;
        $createdJobs  = [];

        foreach ($request->positions as $pos) {
            $createdJobs[] = Job::create([
                'company_id'          => $nsrpId,
                'title'               => $pos['title'],
                'description'         => $pos['description'],
                'location'            => $pos['location'],
                'type'                => $pos['type'],
                'industry_group'      => $company->activeCompany()->industry_group,
                'slots'               => $pos['slots'],
                'salary'              => $pos['salary'] ?? 'Negotiable',
                // ── Deadline = kataposang adlaw sa schedule (In-house/Company
                // ── Based), auto-fill dili na separate input. Sa window, ang
                // ── kataposan sa window — dili mosira ang posting samtang
                // ── mahimo pa ang interview. ──
                'deadline'            => in_array($request->schedule_type, ['inhouse', 'company_interview'])
                                          ? ($request->preferred_date_end ?: $request->preferred_date)
                                          : ($pos['deadline'] ?? null),
                // Buhi dayon — wala nay pag-approve sa staff. Tan-awa ang
                // JobPostingNotice::initialState().
                ...\App\Support\JobPostingNotice::initialState($request->schedule_type),
                'posting_type'        => 'direct',
                'schedule_type'       => $request->schedule_type,
                'preferred_date'      => $request->schedule_type !== 'job_fair' ? $request->preferred_date : null,
                'preferred_date_end'  => $request->schedule_type === 'inhouse'
                                          ? ($request->preferred_date_end ?: $request->preferred_date)
                                          : null,
                'venue_type'          => $request->schedule_type === 'inhouse' ? $request->venue_type : null,
                'venue_address'       => $request->schedule_type === 'inhouse' && $request->venue_type === 'other' ? $request->venue_address : null,
                'experience_months'   => $pos['experience_months'] ?: 0,
                'religion'            => $pos['religion'] ?: 'Any',
                'sex_preference'      => $pos['sex_preference'] ?: 'Any',
                'civil_status'        => $pos['civil_status'] ?: 'Any',
                'other_qualifications'=> $pos['other_qualifications'] ?? null,
                'accepts_disability'  => $pos['accepts_disability'] ?? 'no',
                'disability_types'    => $pos['disability_types'] ?? null,
                'education_required'  => $pos['education_required'] ?: 'Any',
                'course_major'        => $pos['course_major'] ?? null,
                'license'             => $pos['license'] ?? null,
                'eligibility'         => $pos['eligibility'] ?? null,
                'certification'       => $pos['certification'] ?? null,
                'language'            => $pos['language'] ?? null,
                'preferred_residence' => $pos['preferred_residence'] ?? null,
                'accepts_programs'    => $pos['accepts_programs'] ?? null,
                'poster_image'       => $this->resolveJobImage($pos),
            ]);
        }

        $employerNsrp->update([
            'initial_vacancy_confirmed' => true,
            'initial_vacancy_data'      => $request->positions,
        ]);

        // ── Ang staff wala na mag-approve sa ordinaryo nga posting;
        // ── gipahibalo ra sila nga buhi na. Ang in-house lahi: naghulat siya
        // ── sa ilang desisyon, mao nga ang mensahe usa ka hangyo, dili
        // ── kasayoran. ──
        $goesLive = \App\Support\JobPostingNotice::goesLive($request->schedule_type);

        if ($request->schedule_type === 'inhouse') {
            $staffRole  = $employerNsrp->is_overseas ? 'sra' : 'lra';
            $venueLabel = $request->venue_type === 'other' ? $request->venue_address : 'PESO Office';
            $window     = ($createdJobs[0] ?? null)?->schedule_window_label ?? '—';
            $title      = 'In-house Schedule Needs Approval 📅';
            $message    = $employerNsrp->company_name . ' requested an in-house interview, '
                          . $window . ' at ' . $venueLabel . '. Those dates are held while you decide. '
                          . 'The vacancy stays hidden from jobseekers until you accept.';
        } elseif ($request->schedule_type === 'job_fair') {
            $staffRole = $employerNsrp->is_overseas ? 'sra' : 'job_vacancy';
            $title     = 'New Job Fair Posting 🎪';
            $message   = $employerNsrp->company_name . ' posted their initial job vacancy for job fair use. '
                         . \App\Support\JobFairPostingWindow::liveNote();
        } else {
            $staffRole = $employerNsrp->is_overseas ? 'sra' : 'job_vacancy';
            $title     = 'New Job Posting 💼';
            $message   = $employerNsrp->company_name . ' posted their initial job vacancy. It is live now.';
        }

        \App\Models\Announcement::sendToStaff([
            'type'           => 'job_posted_notice',
            'title'          => $title,
            'message'        => $message,
            // Ang in-house gidesisyunan sa In-house Schedule nga tab, dili sa
            // In-house Job Vacancy — didto dad-on sa bell.
            'reference_type' => $request->schedule_type === 'inhouse' ? 'inhouse_schedule' : 'job',
            'reference_id'   => null,
        ], \App\Models\Staff::where('staff_role', $staffRole)->pluck('staff_id'));

        // ── Ang jobseeker masultihan ra kung tinuod nga makita na niya ang
        // ── bakante — ang job fair magpabilin nga sirado hangtod haduol na. ──
        if ($goesLive) {
            foreach ($createdJobs as $created) {
                \App\Support\JobPostingNotice::announce($created);
            }
        }

        return redirect()->route('company.jobseekers')->with('success', $goesLive
            ? 'Job posting confirmed and is now live!'
            : 'Job posting confirmed. '
              . \App\Support\JobPostingNotice::pendingNote($request->schedule_type));
    }

    // ───────────────────────────────
    // JOB POSTS — CREATE
    // ───────────────────────────────
    public function createJob()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        return view('company.jobs.create', compact('company'));
    }

    public function storeJob(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');
        if ($guard = $this->blockIfRestricted($company)) return $guard;

        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'required|string',
            'location'       => 'required|string|max:255',
            'salary'         => 'nullable|string|max:100',
            'job_type'       => 'required|in:full_time,part_time,contractual',
            'industry_group' => 'required|string|max:255',
            'slots'          => 'required|integer|min:1',
            'deadline'       => 'nullable|date|after_or_equal:today|before_or_equal:' . now()->addYear()->toDateString(),
        ]);

        Job::create([
            'company_id'     => $company->activeCompany()->employer_nsrp_registrations_id,
            'title'          => $request->title,
            'description'    => $request->description,
            'location'       => $request->location,
            'salary'         => $request->salary ?? 'Negotiable',
            'job_type'       => $request->job_type,
            'industry_group' => $request->industry_group,
            'slots'          => $request->slots,
            'deadline'       => $request->deadline,
            'status'         => 'open',
        ]);

        return redirect()->route('company.jobseekers')->with('success', 'Job posted successfully!');
    }

    // ───────────────────────────────
    // JOB POSTS — EDIT
    // ───────────────────────────────
    // ── PESO interview 2026-08-13: "Mahimo kini i-update basta active pa ang
    // ── vacancy... Pero kung closed o expired na, dili na dapat ma-update."
    // ── Ang lifecycle_status maoy usa ka basihan sa duha ka method sa ubos. ──
    private function requireEditableJob($job)
    {
        if ($job->is_editable) {
            return null;
        }

        return redirect()->route('company.jobseekers')
            ->with('error', $job->lifecycle_block_reason);
    }

    // ───────────────────────────────
    // OFF-SYSTEM HIRES — gi-hire nga wala miagi sa PESO
    // ───────────────────────────────
    // PESO pilot testing 2026-08-13: nag-post ug 5 ka Welder ang employer,
    // dayon gi-hire niya ang iyang ig-agaw nga wala gyud mi-apply sa system.
    // Upat na lang ang tinuod nga bakante, apan ang tanan nga slot count gikan
    // sa job_matching — mao nga magpadayon ang posting sa pag-anunsyo ug 5.
    //
    // Numero ra ang gi-record, walay pangalan (desisyon sa opisina). DILI ni
    // maihap nga PESO placement — tan-awa ang Reports.
    public function recordExternalHires(Request $request, $id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');
        if ($guard = $this->blockIfRestricted($company)) return $guard;

        $job = Job::where('job_qualifications_id', $id)
            ->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->firstOrFail();

        $request->validate([
            'external_hires' => 'required|integer|min:0|max:9999',
        ], [
            'external_hires.required' => 'Enter how many people you hired outside PESO.',
        ]);

        $external = (int) $request->external_hires;
        $peso     = $job->group_peso_hires;
        $slots    = (int) $job->slots;

        // Ang PESO nga hire dili mabakwit — kung dili na mohaum, ang Close ang
        // sakto nga buhaton, dili ang pagpataas sa numero.
        if ($peso + $external > $slots) {
            return back()->with('error',
                'That would be ' . ($peso + $external) . ' hires for ' . $slots . ' slot(s). '
                . $peso . ' already hired through PESO, so you can record at most '
                . max(0, $slots - $peso) . ' more. Use Close instead if the position is full.');
        }

        $previousExternal = (int) $job->group_external_hires;

        // Sa group leader gitipigan aron usa ra ka lugar — ang SQL nag-sum man
        // sa tibuok group, mao nga doble ang maihap kung magkatag.
        Job::whereIn('job_qualifications_id', $job->groupJobIds())
            ->update(['external_hires' => 0]);
        Job::where('job_qualifications_id', $job->group_key)
            ->update(['external_hires' => $external]);

        // Slots that filled without an applicant on the list need a reason
        // beside them, or the arithmetic on the page cannot be checked.
        \App\Support\JobChangeLog::recordExternalHires($job, $previousExternal, $external, $company);

        $fresh = Job::withHireBreakdown()->find($job->job_qualifications_id);

        return back()->with('success', $external === 0
            ? 'Cleared the outside-PESO hires for "' . $job->title . '".'
            : $external . ' outside-PESO hire(s) recorded for "' . $job->title . '". '
              . 'Now ' . $fresh->group_hired_count . ' of ' . $slots . ' slot(s) filled.');
    }

    // ── Walay bulag nga "mark as filled" nga buton. Block 8: "Kung ideklara sa
    // ── kompanya nga filled na ang position, dili na usab kini i-post" —
    // ── natuman na kini sa pag-record sa hire: pag-abot sa slots, mo-sira ang
    // ── tanan nga channel mag-isa. Usa ka click, dili duha. ──

    public function editJob($id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $job = Job::where('job_qualifications_id', $id)->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)->firstOrFail();
        if ($guard = $this->requireEditableJob($job)) return $guard;

        return view('company.jobs.edit', compact('company', 'job'));
    }

   public function updateJob(Request $request, $id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $job = Job::where('job_qualifications_id', $id)->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)->firstOrFail();
        if ($guard = $this->requireEditableJob($job)) return $guard;

        $request->validate([
            'title'                => 'required|string|max:255',
            'description'          => 'required|string',
            'type'                 => 'required|in:permanent,contractual,project_based,internship,part_time,work_from_home',
            'location'             => 'required|string|max:255',
            'salary'               => 'nullable|string|max:100',
            'slots'                => 'required|integer|min:1',
            // ── Max usa ka tuig (PESO interview: employer nga 100 ka vacancy
            // ── mahimong hatagan ug hangtod usa ka tuig, dili usa ka bulan). ──
            'deadline'             => 'nullable|date|after_or_equal:today|before_or_equal:' . now()->addYear()->toDateString(),
            'industry_group'       => 'required|string|max:255',
            'experience_months'    => 'nullable|integer|min:0',
            'religion'             => 'nullable|string|max:100',
            'sex_preference'       => 'nullable|in:Male,Female,Any',
            'civil_status'         => 'nullable|in:Single,Married,Any',
            'other_qualifications' => 'nullable|string',
            'education_required'   => 'nullable|string|max:100',
            'course_major'         => 'nullable|string|max:255',
            'license'              => 'nullable|string|max:255',
            'eligibility'          => 'nullable|string|max:255',
            'certification'        => 'nullable|string|max:255',
            'language'             => 'nullable|string|max:255',
            'preferred_residence'  => 'nullable|string|max:255',
            'accepts_disability'   => 'nullable|in:yes,no',
            'disability_types'     => 'nullable|array',
            'disability_types.*'   => 'string|max:50',
        ]);

        // Read before the write, so the history can say what moved. See
        // App\Support\JobChangeLog.
        $beforeEdit = \App\Support\JobChangeLog::snapshot($job);

        // ── Ang tipo sa disability walay kahulogan kung "No" ang tubag. ──
        $acceptsDisability = $request->accepts_disability ?? 'no';
        $disabilityTypes   = $acceptsDisability === 'yes'
            ? array_values((array) $request->input('disability_types', []))
            : null;

        // ── Status (Open/Closed) is controlled by PESO staff via Approve/Reject, not editable by the employer ──
        $job->update([
            'title'                => $request->title,
            'description'          => $request->description,
            'type'                 => $request->type,
            'location'             => $request->location,
            'salary'               => $request->salary ?? 'Negotiable',
            'slots'                => $request->slots,
            'deadline'             => $request->deadline,
            'industry_group'       => $request->industry_group,
            'experience_months'    => $request->experience_months,
            'religion'             => $request->religion,
            'sex_preference'       => $request->sex_preference ?? 'Any',
            'civil_status'         => $request->civil_status ?? 'Any',
            'other_qualifications' => $request->other_qualifications,
            'education_required'   => $request->education_required,
            'course_major'         => $request->course_major,
            'license'              => $request->license,
            'eligibility'          => $request->eligibility,
            'certification'        => $request->certification,
            'language'             => $request->language,
            'preferred_residence'  => $request->preferred_residence,
            'accepts_disability'   => $acceptsDisability,
            'disability_types'     => $disabilityTypes,
        ]);

        // ── Ang slots gi-ambitan sa TIBUOK posting group — usa ra ka bakante
        // ── ang gi-post sa Company Interview, In-house ug Job Fair. Kung ang usa ka
        // ── row ra ang mausab, magkalahi na ang mga row: kada usa mo-tandi sa
        // ── parehas nga hired count batok sa kaugalingon niyang slots, mao nga
        // ── ang usa mo-undang samtang ang uban buhi pa. ──
        // ── Ang pagdawat sa PWD kabtangan sa BAKANTE, dili sa channel. Usa ra
        // ── ka bakante ang gi-post sa Company Interview, In-house ug Job Fair,
        // ── mao nga dili siya modawat ug PWD sa usa ug mosalikway sa lain. Kung
        // ── ang row nga gi-edit ra ang mausab, ang Job Fair desk mobasa gihapon
        // ── sa daan nga tubag sa row nga wala niya makita. ──
        $groupIds = $job->groupJobIds();
        if (count($groupIds) > 1) {
            Job::whereIn('job_qualifications_id', $groupIds)
                ->update([
                    'slots'              => (int) $request->slots,
                    'accepts_disability' => $acceptsDisability,
                    'disability_types'   => $disabilityTypes ? json_encode($disabilityTypes) : null,
                ]);
        }

        // Written after the group update, so a change pushed to the sibling
        // channels is in the snapshot too.
        \App\Support\JobChangeLog::recordEdit($job, $beforeEdit, $company);

        return redirect()->route('company.jobseekers')->with('success', 'Job updated successfully!');
    }

    // ───────────────────────────────
    // JOB POSTS — DELETE
    // ───────────────────────────────
    public function deleteJob($id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $job = Job::where('job_qualifications_id', $id)->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)->firstOrFail();

        // ── Kuhaa ang detalye samtang buhi pa ang row. Human sa delete, wala
        // ── nay mabasa — ug ang notipikasyon walay pulos kung dili niya
        // ── manganlan kung unsang posting ang nawala. ──
        $nsrp        = $company->activeCompany();
        $isOverseas  = (bool) $nsrp->is_overseas;
        $jobTitle    = $job->title;
        $slots       = $job->slots;
        $companyName = $nsrp->company_name;

        $job->delete();

        // ── PESO, 2026-08-26: ang posting iya sa employer, ug siya ra ang
        // ── makatangtang niini. Apan ang desk nga nag-atiman kaniya kinahanglan
        // ── masayod — naa nay jobseeker nga nag-plano palibot sa bakante, ug
        // ── ang report sa opisina magpangutana asa siya nawala. Ang LRA ang
        // ── makadawat para sa lokal, ang SRA para sa overseas. ──
        $deskRole = $isOverseas ? 'sra' : 'lra';
        $staffIds = \App\Models\Staff::where('staff_role', $deskRole)->pluck('staff_id');

        if ($staffIds->isNotEmpty()) {
            \App\Models\Announcement::sendToStaff([
                'type'           => 'job_posting_deleted',
                'title'          => 'Job Posting Deleted 🗑️',
                'message'        => $companyName . ' deleted their job posting "' . $jobTitle . '"'
                    . ($slots ? ' (' . $slots . ' slot(s))' : '') . '. '
                    . 'The vacancy and its applications are gone — it will not appear in the vacancy list any more.',
                // 'job', dili 'employer_registration': ang bell mo-abli sa lista
                // sa bakante, diin makita sa desk ang nahibiling posting sa
                // maong employer. Ang reference_id sa gi-delete nga row wala
                // nay abtan — ang link wala magagamit niini.
                'reference_type' => 'job',
                'reference_id'   => $nsrp->employer_nsrp_registrations_id,
            ], $staffIds);
        }

        return back()->with('success', 'Job post deleted.');
    }

    // ───────────────────────────────
    // APPLICANTS — per job
    // ───────────────────────────────
    public function applicants($jobId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $job = Job::where('job_qualifications_id', $jobId)->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)->firstOrFail();

        // ── Job Fair jobs naay lahi/locked nga hiring flow — didto ra i-manage, dili dinhi ──
        if ($job->schedule_type === 'job_fair') {
            return redirect()->route('company.jobseekers', ['tab' => 'applicants'])
                ->with('info', 'Job fair applicants are managed from the Job Fair Applicants tab.');
        }

        $applicants = Application::with('jobseeker')
            ->where('job_id', $jobId)
            ->latest()
            ->get();

        $qualifiedApplicants       = $applicants->filter(fn($a) => ($a->match_percentage ?? 0) >= 50 && ($a->match_percentage ?? 0) < 75)->values();
        $highlyQualifiedApplicants = $applicants->filter(fn($a) => ($a->match_percentage ?? 0) >= 75)->values();

        // Parehas nga pugong sa Qualified Applicants nga pahina: walay final nga
        // desisyon hangtod moabot ang adlaw sa interbyu.
        $interviewDate   = $job->interview_date;
        $actionsUnlocked = !$this->needsInterviewDate($job)
            || !$interviewDate
            || now()->toDateString() >= $interviewDate->toDateString();

        return view('company.applicants', compact('company', 'job', 'applicants',
            'qualifiedApplicants', 'highlyQualifiedApplicants', 'actionsUnlocked'));
    }

    /**
     * Does this posting interview its applicants on a scheduled day?
     *
     * In-house and Company Interview both do, and both make the employer name the
     * day up front, so a decision before that day is a decision made without
     * the interview it is supposed to come from. Job Fair is scheduled too but
     * keeps its own check further down — its date lives on the event, not the
     * posting.
     */
    private function needsInterviewDate($job): bool
    {
        return in_array($job->schedule_type ?? null, ['inhouse', 'company_interview'], true);
    }

    // ───────────────────────────────
    // UPDATE APPLICANT STATUS
    // ───────────────────────────────
    public function updateApplicantStatus(Request $request, $applicationId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $request->validate([
            'status' => 'required|in:pending,reviewed,qualified,hired,waiting,rejected',
        ]);

        $application = Application::with('job')->whereHas('job', function ($q) use ($company) {
            $q->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id);
        })->findOrFail($applicationId);

        // ── Walay permanenteng lock sa hired ug rejected.
        // ──
        // ── PESO: ang gi-hire mahimong dili motunga sa trabaho, o mo-undang sa
        // ── unang semana. Kung dili na mausab ang rekord, magpabilin siyang
        // ── "hired" sa report bisan wala gyud siya nagtrabaho, ug ang slot nga
        // ── iyang giluk-an dili na maablihan para sa sunod nga aplikante. Ang
        // ── desisyon sa employer maoy nag-una — dili ang una niyang gipindot.
        // ──
        // ── Ang pagpugong sa petsa sa interbyu sa ubos nagpabilin: kana kay
        // ── mahitungod sa KANUS-A mo-desisyon, dili kung mausab ba.

        // ── In-house ug Company Interview — dili pwede i-Hire/Waiting/Reject hangtod
        // ── moabot ang adlaw sa interbyu. Gitago na kini sa UI, apan ang pagtago
        // ── sa buton dili mopugong sa POST; diri gyud ang tinuod nga pugong. ──
        if ($application->job && $this->needsInterviewDate($application->job)
            && in_array($request->status, ['hired', 'waiting', 'rejected'])) {
            $interviewDate = $application->job->interview_date;

            if ($interviewDate && now()->toDateString() < $interviewDate->toDateString()) {
                $label = $application->job->schedule_type === 'inhouse'
                    ? 'in-house interview'
                    : 'company interview';

                return back()->with('error', 'You can only finalize this applicant once the ' . $label
                    . ' date (' . $interviewDate->format('M d, Y') . ') arrives.');
            }
        }

        // ── Job Fair applicants — dili pwede i-Hire/Waiting/Reject hangtod moabot ang event date (server-side, dili lang UI hide) ──
        if ($application->job && $application->job->schedule_type === 'job_fair'
            && in_array($request->status, ['hired', 'waiting', 'rejected'])) {
            $jobFairEvent = \App\Models\JobFairEmploymentRequest::where('job_id', $application->job_id)
                ->with('jobFair')
                ->first()?->jobFair;

            if ($jobFairEvent && now()->toDateString() < \Carbon\Carbon::parse($jobFairEvent->event_date)->toDateString()) {
                return back()->with('error', 'You can only finalize job fair applicants once the event date (' . $jobFairEvent->event_date->format('M d, Y') . ') arrives.');
            }
        }

        // ── Ang adlaw sa pag-hire gitiman-an dinhi, dili gikan sa updated_at.
        // ── Ang updated_at mausab sa bisan unsa nga mohikap sa row; ang
        // ── jobseeker nagbasa niini isip "Employed since", ug ang opisina
        // ── nag-ihap ug hire kada bulan gikan niini. Kung mobalhin ang
        // ── status palayo sa hired, ang petsa mawala pud — dili siya
        // ── na-hire. ──
        $application->update([
            'status'   => $request->status,
            'hired_at' => $request->status === 'hired'
                ? ($application->hired_at ?: now())
                : null,
        ]);

        $messages = [
            'hired'    => ['title' => 'Application Update — Hired! 🎉', 'text' => 'Congratulations! You have been hired for the position "' . $application->job->title . '" at ' . $company->activeCompany()->company_name . '.'],
            'waiting'  => ['title' => 'Application Update — Waiting List ⏳', 'text' => 'Your application for "' . $application->job->title . '" at ' . $company->activeCompany()->company_name . ' has been placed on the waiting list.'],
            'rejected' => ['title' => 'Application Update ❌', 'text' => 'Your application for "' . $application->job->title . '" at ' . $company->activeCompany()->company_name . ' was not selected this time.'],
            'reviewed' => ['title' => 'Application Update — Under Review 👀', 'text' => 'Your application for "' . $application->job->title . '" at ' . $company->activeCompany()->company_name . ' is now being reviewed.'],
        ];

        if (isset($messages[$request->status])) {
            \App\Models\Announcement::sendToJobseekers([
                'type'           => 'application_status',
                'title'          => $messages[$request->status]['title'],
                'message'        => $messages[$request->status]['text'],
                'reference_type' => 'job',
                'reference_id'   => $application->job_id,
            ], $application->jobseeker_id);
        }

        return back()->with('success', 'Applicant status updated.');
    }

    // ───────────────────────────────
    // PROFILE
    // ───────────────────────────────
    public function showProfile()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        return view('company.profile', compact('company'));
    }

    public function updateProfile(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $request->validate([
            'company_name'   => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'position_title' => 'required|string|max:255',
            'mobile_number'  => ['required', 'string', new \App\Rules\MobileNumber],
            'email'          => 'required|email|unique:users,email,' . $company->users_id . ',users_id',
            'profile_photo'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'sms_opt_in'     => 'nullable|boolean',
            // PESO aims a job fair invitation at an industry, so an employer
            // with no industry group is never matched to a fair. The ones who
            // registered before the field was saved can fill it in here.
            'industry_group' => ['required', \Illuminate\Validation\Rule::in(
                \App\Models\EmployerNsrpRegistration::INDUSTRY_GROUPS
            )],
        ]);

        $data = [
            'email' => $request->email,
            'name'  => $request->company_name,
        ];

        if ($request->hasFile('profile_photo')) {
            if ($company->profile_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($company->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $company->update($data);

        $company->activeCompany()->update([
            'company_name'   => $request->company_name,
            'contact_person' => $request->contact_person,
            'position_title' => $request->position_title,
            'mobile_number'  => $request->mobile_number,
            'industry_group' => $request->industry_group,
            // Unchecked boxes are absent from the request, so this reads as
            // "off" rather than "unchanged" — which is what the form means.
            'sms_opt_in'     => $request->boolean('sms_opt_in'),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    // ───────────────────────────────
    // CHECK INHOUSE DATE AVAILABILITY (AJAX)
    // ───────────────────────────────
    public function checkInhouseDateAvailability(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return response()->json(['error' => 'Unauthorized'], 401);

        return response()->json(\App\Support\InhouseBooking::availability(
            $request->query('date'),
            $request->query('date_end'),
            $request->query('venue_type', 'peso_office')
        ));
    }

    // ───────────────────────────────
    // GET BOOKED DATES (PESO Office, fully-booked/3-3) — para sa calendar picker
    // ───────────────────────────────
    public function getBookedDates()
    {
        $company = $this->authCompany();
        if (!$company) return response()->json(['error' => 'Unauthorized'], 401);

        return response()->json(['booked_dates' => \App\Support\InhouseBooking::bookedDates()]);
    }

    // ───────────────────────────────
    // REQUEST JOB POSTING (multi-position, usa ka poster image per request)
    // ───────────────────────────────
    public function requestJob(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

    
        if ($guard = $this->blockIfRestricted($company)) return $guard;
        if ($guard = $this->requireApprovedRequirements($company)) return $guard;

        
        $requestedTypes = array_values(array_intersect(
            ['company_interview', 'inhouse', 'job_fair'],
            array_map('strval', (array) $request->input('schedule_types', []))
        ));

        
        if ($request->filled('job_fair_id')) {
            $requestedTypes = ['job_fair'];
        }

        $wantsInhouse = in_array('inhouse', $requestedTypes, true);
        $wantsCompanyInterview  = in_array('company_interview', $requestedTypes, true);

        $request->validate([
            'schedule_types'   => 'required|array|min:1',
            'schedule_types.*' => 'in:inhouse,company_interview,job_fair',
            'job_fair_id'      => 'nullable|exists:job_fair_events,job_fair_events_id',
            
           
            'inhouse_date'   => [\Illuminate\Validation\Rule::requiredIf($wantsInhouse), 'nullable', 'date', 'after_or_equal:' . \App\Support\OfficeCalendar::earliestBookableDate()],
            
            'inhouse_date_end' => 'nullable|date|after_or_equal:inhouse_date',
            'company_interview_date'    => [\Illuminate\Validation\Rule::requiredIf($wantsCompanyInterview),  'nullable', 'date', 'after_or_equal:' . \App\Support\OfficeCalendar::earliestBookableDate()],
            'venue_type'     => [\Illuminate\Validation\Rule::requiredIf($wantsInhouse), 'nullable', 'in:peso_office,other'],
            'venue_address'  => 'required_if:venue_type,other|nullable|string|max:255',
            'poster_image'   => 'nullable|file|mimes:jpg,jpeg,png|max:5120',

           
            'existing_job_ids'   => 'nullable|array',
            'existing_job_ids.*' => 'integer',

            'positions'                          => 'required_without:existing_job_ids|array',
            'positions.*.title'                 => 'required|string|max:255',
            'positions.*.description'           => 'required|string',
            'positions.*.type'                  => 'required|in:permanent,contractual,project_based,internship,part_time,work_from_home',
            'positions.*.location'              => 'required|string|max:255',
            'positions.*.salary'                => 'nullable|string|max:100',
            'positions.*.slots'                 => 'required|integer|min:1',
            
            'positions.*.deadline'              => 'nullable|date|after_or_equal:today|before_or_equal:' . now()->addYear()->toDateString(),
            'positions.*.experience_months'     => 'nullable|integer|min:0',
            'positions.*.religion'              => 'nullable|string|max:100',
            'positions.*.sex_preference'        => 'nullable|in:Male,Female,Any',
            'positions.*.civil_status'          => 'nullable|in:Single,Married,Any',
            'positions.*.other_qualifications'  => 'nullable|string',
            'positions.*.accepts_disability'    => 'nullable|in:yes,no',
            'positions.*.disability_types'      => 'nullable|array',
            'positions.*.education_required'    => 'nullable|string|max:100',
            'positions.*.course_major'          => 'nullable|string|max:255',
            'positions.*.license'               => 'nullable|string|max:255',
            'positions.*.eligibility'           => 'nullable|string|max:255',
            'positions.*.certification'         => 'nullable|string|max:255',
            'positions.*.language'              => 'nullable|string|max:255',
            'positions.*.preferred_residence'   => 'nullable|string|max:255',
            'positions.*.accepts_programs'      => 'nullable|array',
            'positions.*.job_image'             => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ], [
            
            'schedule_types.required' => 'Pick at least one schedule type.',
            'schedule_types.*.in'     => 'That schedule type is not one of the choices.',
            'company_interview_date.required'    => 'Pick the preferred date for the Company Interview posting.',
            'inhouse_date.required'   => 'Pick the preferred date for the In-house posting.',
            'company_interview_date.after_or_equal'  => \App\Support\OfficeCalendar::leadTimeMessage(),
            'inhouse_date.after_or_equal' => \App\Support\OfficeCalendar::leadTimeMessage(),
            'inhouse_date_end.after_or_equal' => 'The last available date cannot be before the first.',
            'venue_type.required'     => 'Pick the venue for the In-house interview.',
            'positions.*.deadline.after_or_equal'  => 'The posting deadline cannot be in the past.',
            'positions.*.deadline.before_or_equal' => 'A posting can run for at most one year. Pick a deadline on or before '
                                                      . now()->addYear()->format('M d, Y') . '.',
            'positions.required_without'           => 'Tick a vacancy to bring, or add a new position.',
        ]);

       
        $inhouseWindowIsOneDay = !$request->filled('inhouse_date_end')
            || $request->input('inhouse_date_end') === $request->input('inhouse_date');

        $holidays = \App\Support\Holidays::aroundNow();
        foreach ([
            'company_interview_date'  => $request->input('company_interview_date'),
            'inhouse_date' => $inhouseWindowIsOneDay ? $request->input('inhouse_date') : null,
        ] as $field => $picked) {
            if ($picked && isset($holidays[$picked])) {
                return back()->withInput()->withErrors([
                    $field => 'PESO is closed on ' . $holidays[$picked]
                              . ' (' . \Carbon\Carbon::parse($picked)->format('M d, Y') . '). Please pick another date.',
                ]);
            }
        }

        if ($wantsInhouse) {
            if ($why = $this->officeBlockedWindowError($request->inhouse_date, $request->inhouse_date_end)) {
                return back()->withInput()->with('error', $why);
            }
        }

        if ($wantsInhouse && $request->venue_type === 'peso_office') {
            if ($why = $this->pesoOfficeCapacityError($request->inhouse_date, $request->inhouse_date_end)) {
                return back()->withInput()->with('error', $why);
            }
        }

        
        $posterPath = null;
        if ($request->hasFile('poster_image')) {
            $posterPath = $request->file('poster_image')->store('job_posters', 'public');
        }

        $companyId    = $company->activeCompany()->employer_nsrp_registrations_id;
        $createdJobs  = [];
        $jobFairJobs  = [];

       
        $broughtCount = 0;
        $bringIds = array_filter(array_map('intval', (array) $request->input('existing_job_ids', [])));
        if ($bringIds) {
            
            $alreadyBroughtGroups = Job::whereIn(
                    'job_qualifications_id',
                    \App\Models\JobFairEmploymentRequest::where('job_fair_id', $request->job_fair_id)
                        ->where('employer_id', $companyId)
                        ->pluck('job_id')
                )
                ->get()
                ->map(fn($job) => $job->group_key)
                ->all();

            $sourceJobs = Job::whereIn('job_qualifications_id', $bringIds)
                ->where('company_id', $companyId)          // iyaha ra gyud
                ->where('schedule_type', '!=', 'job_fair') // wala na sa fair
                ->where('posting_status', 'approved')
                ->active()                                  // buhi pa: dili expired, dili puno
                ->get()
                ->reject(fn($job) => in_array($job->group_key, $alreadyBroughtGroups))
               
                ->unique(fn($job) => $job->group_key);

            foreach ($sourceJobs as $source) {
                $clone = $source->replicate([
                    'preferred_date', 'preferred_time', 'venue_type', 'venue_address',
                    'confirmed_date', 'confirmed_time', 'schedule_status', 'schedule_rejection_reason',
                ]);
                $clone->schedule_type    = 'job_fair';
               
                $clone->fill(\App\Support\JobPostingNotice::initialState('job_fair'));
                $clone->posting_group_id = $source->group_key;  // parehas nga bakante
                $clone->remarks          = null;
                $clone->save();

                $createdJobs[] = $clone;
                $jobFairJobs[] = $clone;
                $broughtCount++;
            }
        }

        foreach ((array) $request->input('positions', []) as $pos) {
           
            $groupId = null;

            foreach ($requestedTypes as $type) {
               
                $scheduleDate = match ($type) {
                    'inhouse'      => $request->inhouse_date,
                    'company_interview' => $request->company_interview_date,
                    default        => null,
                };

                
                $scheduleEnd = $type === 'inhouse'
                    ? ($request->inhouse_date_end ?: $request->inhouse_date)
                    : null;

                $job = Job::create([
                    'company_id'          => $companyId,
                    'title'               => $pos['title'],
                    'description'         => $pos['description'] ?? '',
                    'location'            => $pos['location'],
                    'type'                => $pos['type'],
                    'industry_group'      => $company->activeCompany()->industry_group,
                    'slots'               => $pos['slots'],
                    'deadline'            => ($pos['deadline'] ?? null) ?: ($scheduleEnd ?: $scheduleDate),
                    'salary'              => $pos['salary'] ?? 'Negotiable',
                    
                    ...\App\Support\JobPostingNotice::initialState($type),
                    'posting_type'        => 'direct',
                    'schedule_type'       => $type,
                    'preferred_date'      => $scheduleDate,
                    'preferred_date_end'  => $scheduleEnd,
                    'venue_type'          => $type === 'inhouse' ? $request->venue_type : null,
                    'venue_address'       => $type === 'inhouse' && $request->venue_type === 'other' ? $request->venue_address : null,
                    'poster_image'        => $this->resolveJobImage($pos) ?? $posterPath,
                    // Qualification fields — kung blangko ang gi-submit, automatic "Any" (dili null/blank display)
                    'sex_preference'      => $pos['sex_preference'] ?: 'Any',
                    'education_required'  => $pos['education_required'] ?: 'Any',
                    'religion'            => $pos['religion'] ?: 'Any',
                    'civil_status'        => $pos['civil_status'] ?: 'Any',
                    'other_qualifications'=> $pos['other_qualifications'] ?? null,
                    'accepts_disability'  => $pos['accepts_disability'] ?? 'no',
                    'disability_types'    => $pos['disability_types'] ?? null,
                    'accepts_programs'    => $pos['accepts_programs'] ?? null,
                    'course_major'        => $pos['course_major'] ?? null,
                    'license'             => $pos['license'] ?? null,
                    'eligibility'         => $pos['eligibility'] ?? null,
                    'certification'       => $pos['certification'] ?? null,
                    'language'            => $pos['language'] ?? null,
                    'preferred_residence' => $pos['preferred_residence'] ?? null,
                    'experience_months'   => $pos['experience_months'] ?: 0,
                ]);

                
                $groupId = $groupId ?? $job->job_qualifications_id;
                $job->update(['posting_group_id' => $groupId]);

                $createdJobs[] = $job;
                if ($type === 'job_fair') {
                    $jobFairJobs[] = $job;
                }
            }
        }

        
        if ($request->filled('job_fair_id')) {
            $event = \App\Models\JobFairEvent::find($request->job_fair_id);

            foreach ($jobFairJobs as $job) {
               
                if ($event && $job->deadline && $job->deadline->lt($event->event_date)) {
                    $job->update(['deadline' => $event->event_date->toDateString()]);
                }

               
                // report — gisulat na sa pag-approve, dili dinhi.
                $job->update(['requested_job_fair_id' => $request->job_fair_id]);
            }
        }

    
        if (empty($createdJobs)) {
            return back()->withInput()->with('error',
                'Nothing was posted — the vacancies you ticked are no longer open. Add a new position instead.');
        }

        $firstJob = $createdJobs[0];
       
        $positionCount = count((array) $request->input('positions', [])) + $broughtCount;
        $titleSummary  = $positionCount > 1
            ? $firstJob->title . ' (+' . ($positionCount - 1) . ' more)'
            : $firstJob->title;

        
        $isOverseas = $company->activeCompany() && $company->activeCompany()->is_overseas;

        foreach ($requestedTypes as $type) {
            $channelJob = collect($createdJobs)->firstWhere('schedule_type', $type);

            if ($type === 'inhouse') {
                $role       = $isOverseas ? 'sra' : 'lra';
                $venueLabel = $request->venue_type === 'other' ? $request->venue_address : 'PESO Office';
                $title      = 'New In-house Posting 📅';
                $windowLabel = $inhouseWindowIsOneDay
                    ? \Carbon\Carbon::parse($request->inhouse_date)->format('M d, Y')
                    : \Carbon\Carbon::parse($request->inhouse_date)->format('M d') . ' – '
                      . \Carbon\Carbon::parse($request->inhouse_date_end)->format('M d, Y');
                $message    = $company->activeCompany()->company_name . ' posted "' . $titleSummary
                              . '" for an in-house interview, ' . $windowLabel . ' at ' . $venueLabel
                              . '. Those dates are held while you decide, and the vacancy stays'
                              . ' hidden from jobseekers until you accept.';
            } elseif ($type === 'job_fair') {
                $role    = $isOverseas ? 'sra' : 'job_vacancy';
                $title   = 'New Job Fair Posting 🎪';
                $message = $company->activeCompany()->company_name . ' posted "' . $titleSummary
                           . '" for job fair use. ' . \App\Support\JobFairPostingWindow::liveNote();
            } else {
                $role    = $isOverseas ? 'sra' : 'job_vacancy';
                $title   = 'New Job Posting 💼';
                $message = $company->activeCompany()->company_name . ' posted "' . $titleSummary
                           . '" for a company interview on '
                           . \Carbon\Carbon::parse($request->company_interview_date)->format('M d, Y') . '. It is live now.';
            }

            \App\Models\Announcement::sendToStaff([
                'type'           => 'job_posted_notice',
                'title'          => $title,
                'message'        => $message,
                // Parehas sa ibabaw: ang in-house modala sa In-house Schedule.
                'reference_type' => $type === 'inhouse' ? 'inhouse_schedule' : 'job',
                'reference_id'   => $type === 'inhouse' ? null : $channelJob?->job_qualifications_id,
            ], \App\Models\Staff::where('staff_role', $role)->pluck('staff_id'));
        }

       
        $announced = [];

        foreach ($createdJobs as $created) {
            if ($created->status !== 'open') continue;

            $group = $created->group_key;
            if (isset($announced[$group])) continue;

            $announced[$group] = true;
            \App\Support\JobPostingNotice::announce($created);
        }

        $noun = $positionCount > 1 ? $positionCount . ' job postings' : 'Job posting';

       
        $channelText = collect($requestedTypes)
            ->map(fn($t) => \App\Models\Job::scheduleTypeLabel($t))
            ->implode(', ');

        $successMsg = $noun . ' published for ' . $channelText . '!';

        
        if (in_array('job_fair', $requestedTypes, true)) {
            $successMsg .= ' The job fair posting is not on a fair yet — '
                         . lcfirst(\App\Support\JobPostingNotice::pendingNote('job_fair'));
        }

        
        if (in_array('inhouse', $requestedTypes, true)) {
            $successMsg .= ' The in-house posting is not visible yet — '
                         . lcfirst(\App\Support\JobPostingNotice::pendingNote('inhouse'));
        }

        if ($request->filled('job_fair_id')) {
            return redirect()->route('company.jobseekers', ['tab' => 'invitations'])->with('success', $successMsg);
        }

        return redirect()->route('company.dashboard')->with('success', $successMsg);
    }

    // ───────────────────────────────
    // CLEAR ALL NOTIFICATIONS
    // ───────────────────────────────
    public function clearAllNotifications()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        \App\Models\Announcement::where('employer_id', $company->activeCompany()->employer_nsrp_registrations_id)->delete();

        return back()->with('success', 'All notifications cleared.');
    }

    // ───────────────────────────────
    // MARK NOTIFICATION READ
    // ───────────────────────────────
    public function markNotificationRead($id)
    {
        $company = $this->authCompany();
        if (!$company) return response()->json(['error' => 'Unauthorized'], 401);

        \App\Models\Announcement::where('announcements_id', $id)
            ->where('employer_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    // ── Pag-abli sa bell, kita na niya ang tanan — mao nga wala nay pulos ang
    // ── numero nga nagpabilin. Ang tibuok listahan gimarkahan nga nabasa na,
    // ── ug ang tubag mao ang bag-ong ihap aron ang badge dili maglaban sa
    // ── database.
    // ──
    // ── Ang `markNotificationRead` gibilin gihapon: kana usa ra ang gi-klik,
    // ── ug siya ang gigamit sa pahina nga listahan, dili sa bell. ──
    public function markAllNotificationsRead()
    {
        $company = $this->authCompany();
        if (!$company) return response()->json(['error' => 'Unauthorized'], 401);

        $employerNsrp = $company->activeCompany();
        if (!$employerNsrp) return response()->json(['unread_count' => 0]);

        \App\Models\Announcement::where('employer_id', $employerNsrp->employer_nsrp_registrations_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['unread_count' => 0]);
    }

    // ───────────────────────────────
    // ALL NOTIFICATIONS (full history page)
    // ───────────────────────────────
    public function notifications()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $employerNsrp   = $company->activeCompany();
        $notifications  = $employerNsrp
            ? \App\Models\Announcement::where('employer_id', $employerNsrp->employer_nsrp_registrations_id)->latest()->get()
            : collect();

        return view('company.notifications.index', compact('notifications'));
    }

    // ── AJAX — fresh unread count + latest notifications, para live ang bell (dili na kinahanglan i-reload ang page) ──
    public function notificationsFetch()
    {
        $company = $this->authCompany();
        if (!$company) return response()->json(['error' => 'Unauthorized'], 401);

        $employerNsrp = $company->activeCompany();
        if (!$employerNsrp) return response()->json(['unread_count' => 0, 'notifications' => []]);

        $employerId = $employerNsrp->employer_nsrp_registrations_id;

        $unreadCount = \App\Models\Announcement::where('employer_id', $employerId)->where('is_read', false)->count();

        $notifications = \App\Models\Announcement::where('employer_id', $employerId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($notif) {
                $url = null;
                if ($notif->type === 'new_applicant' && $notif->reference_id) {
                    $url = route('company.jobs.qualified', $notif->reference_id);
                } elseif ($notif->reference_type === 'employer_inactivity') {
                    // Ang pagpost ug bag-ong bakante mao ang tubag nga giila sa
                    // sweep; didto siya modala. Parehas sa dropdown sa layout.
                    $url = route('company.jobs.create');
                } else {
                    $url = match ($notif->reference_type) {
                        'job'                  => route('company.jobseekers'),
                        'employer_requirement' => route('company.requirements'),
                        'job_archive'          => route('company.reports'),
                        'job_fair'              => route('company.jobfair'),
                        default                 => null,
                    };
                }

                return [
                    'id'       => $notif->announcements_id,
                    'title'    => $notif->title,
                    'message'  => $notif->message,
                    'time_ago' => $notif->created_at->diffForHumans(),
                    'is_read'  => (bool) $notif->is_read,
                    'url'      => $url,
                ];
            });

        return response()->json(['unread_count' => $unreadCount, 'notifications' => $notifications]);
    }

    // ───────────────────────────────
    // IN-HOUSE SCHEDULES
    // ───────────────────────────────
    public function inhouseSchedules()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');
        if ($guard = $this->requireRequirements($company)) return $guard;

        $schedules = \App\Models\InhouseSchedule::with('reviewer')
            ->where('employer_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->latest()
            ->paginate(10);

        return view('company.inhouse.index', compact('schedules'));
    }

    public function createInhouse()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        // Check kung approved ang requirements
        $requirement = \App\Models\EmployerRequirement::where('user_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->where('status', 'approved')
            ->first();

        if (!$requirement) {
            return redirect()->route('company.dashboard')
                ->with('error', 'Your requirements must be approved before requesting an in-house interview.');
        }

        return view('company.inhouse.create');
    }

    public function storeInhouse(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $request->validate([
            'preferred_date'  => 'required|date|after_or_equal:' . \App\Support\OfficeCalendar::earliestBookableDate(),
            // Window: blangko = usa ra ka adlaw.
            'preferred_date_end' => 'nullable|date|after_or_equal:preferred_date',
            'preferred_time'  => 'required',
            'num_applicants'  => 'required|integer|min:1',
            'venue_type'      => 'required|in:peso_office,company_office,custom',
            'venue_address'   => 'required_if:venue_type,custom|nullable|string|max:255',
            'notes'           => 'nullable|string|max:1000',
        ], [
            'preferred_date.after_or_equal'     => \App\Support\OfficeCalendar::leadTimeMessage(),
            'preferred_date_end.after_or_equal' => 'The last available date cannot be before the first.',
        ]);

        if ($why = $this->officeBlockedWindowError($request->preferred_date, $request->preferred_date_end)) {
            return back()->withInput()->with('error', $why);
        }

        if ($request->venue_type === 'peso_office') {
            if ($why = $this->pesoOfficeCapacityError($request->preferred_date, $request->preferred_date_end)) {
                return back()->withInput()->with('error', $why);
            }
        }

        $schedule = \App\Models\InhouseSchedule::create([
            'employer_id'    => $company->activeCompany()->employer_nsrp_registrations_id,
            'preferred_date' => $request->preferred_date,
            'preferred_date_end' => $request->preferred_date_end ?: $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'num_applicants' => $request->num_applicants,
            'venue_type'     => $request->venue_type,
            'venue_address'  => $request->venue_type === 'custom' ? $request->venue_address : null,
            'notes'          => $request->notes,
            'status'         => 'pending',
        ]);

        // Notify LRA/SRA staff
        $venueLabel = match($request->venue_type) {
            'peso_office'  => 'PESO Office',
            'company_office' => $company->activeCompany()->company_name . "'s Office",
            'custom'       => $request->venue_address,
        };

        $staffRole = ($company->activeCompany() && $company->activeCompany()->is_overseas) ? 'sra' : 'lra';
        $staffIds = \App\Models\Staff::where('staff_role', $staffRole)->pluck('staff_id');

        \App\Models\Announcement::sendToStaff([
            'type'           => 'inhouse_request',
            'title'          => 'New In-house Interview Request 📅',
            'message'        => $company->activeCompany()->company_name . ' has requested an in-house interview, available '
                                . $schedule->schedule_window_label . ' at '
                                . \Carbon\Carbon::parse($request->preferred_time)->format('h:i A')
                                . ' (' . $venueLabel . '). Please pick the interview date and accept or reject.',
            'reference_type' => 'inhouse_schedule',
            'reference_id'   => $schedule->inhouse_schedules_id,
        ], $staffIds);

        return redirect()->route('company.inhouse')
            ->with('success', 'In-house interview request submitted successfully!');
    }

    // ───────────────────────────────
    // JOB FAIR INVITATIONS
    // ───────────────────────────────
    // ── Ang Job Fair wala nay kaugalingon nga page — usa na sila ka tab sa
    // ── Active Job Vacancy. Gibilin ang route ug ang ngalan niini aron
    // ── magpadayon ang tanan nga daan nga link: ang notification nga
    // ── nag-turol sa job_fair, ug ang mga redirect gikan sa ubang flow.
    // ── reflash(): kung dili, mahanaw ang success/info nga mensahe sa pag-agi
    // ── niini nga redirect. ──
    public function jobFairInvitations()
    {
        session()->reflash();

        return redirect()->route('company.jobseekers', ['tab' => 'invitations']);
    }

    public function respondJobFair(Request $request, $id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $request->validate([
            'response' => 'required|in:confirmed,declined',
        ]);

        $participant = \App\Models\JobFairParticipant::with('jobFair')->where('job_fair_participants_id', $id)
            ->where('employer_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->firstOrFail();

        // ── Walay pagsalikway dinhi.
        // ──
        // ── Kaniadto gi-block ang Confirm kung na-abot na ang capacity sa
        // ── event, per-type. PESO Job Fair staff, 2026-08-23: "walay maximum
        // ── sa job fair event kay depende na sa sponsor sa job fair." Ang
        // ── numero nga gisulat sa staff target na ra — dili siya utlanan nga
        // ── ipatuman batok sa employer nga miabot ug usa ka gutlo nga ulahi.
        // ── Ang sponsor mao ang nagsulti kung pila ang mahaom, ug dili kana
        // ── mahibaw-an sa sistema. ──
        // ──
        // ── Ang na-lapse nga imbitasyon dawaton gihapon. Ang usa ka semana usa
        // ── ka signal sa staff nga mangita ug lain, dili pultahan nga sirad-an
        // ── ang employer nga gusto gyud moapil — ang tumong mao ang mapuno ang
        // ── mga slot. Ang milabay na nga fair ra ang tinuod nga sirado. ──
        if ($participant->jobFair->event_date->isPast()) {
            return back()->with('error', 'This job fair has already taken place.');
        }

        $afterSubmission = $participant->jobFair->pastDoleCutoff();

        // ── Ang oo sa overseas nga ahensya usa ka pangayo, dili pa pagsulod.
        // ──
        // ── PESO SRA, 2026-09-01: si SRA ang nagdala niining ahensya sa
        // ── imbitasyon, human siya mangayo ug permiso sa pangulo sa PESO. Kung
        // ── ang oo mismo sa ahensya mao nay makapasulod niya, ang pagpili sa
        // ── desk mahimong walay bili — mananghid siya kinsa ang pangutan-on,
        // ── unya lain ang mutubag kung kinsa ang mosulod. Mao nga ang oo
        // ── mohunong sa 'accepted', ug si SRA ang mopili gikan didto.
        // ──
        // ── Ang lokal wala giusab: walay tawo nga nagpili kaniya — ang lagda
        // ── sa event mismo ang nag-invite — mao nga ang iyang oo kay oo dayon.
        $isOverseas = (bool) $company->activeCompany()->is_overseas;
        $needsSelection = $isOverseas && $request->response === 'confirmed';
        $newStatus = $needsSelection ? 'accepted' : $request->response;

        $participant->update([
            'confirmation_status' => $newStatus,
            'responded_at'        => now(),
        ]);

        // Ang jobseeker giingnan lang kung apil na gyud ang kompanya. Ang
        // naghulat pa sa SRA dili pa siya — ug ang pagsulti sa jobseeker nga
        // moadto siya para sa usa ka kompanya nga wala diay didto mas grabe pa
        // kaysa wala gyoy gisulti.
        if ($newStatus === 'confirmed') {
            $userIds = \App\Models\Application::whereHas('job', fn($q) =>
                $q->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
            )->pluck('jobseeker_id')->unique();

            \App\Models\Announcement::sendToJobseekers([
                'type'           => 'job_fair_invitation',
                'title'          => 'Job Fair Invitation 🎉',
                'message'        => $company->activeCompany()->company_name . ' has confirmed participation in ' . $participant->jobFair->title . ' on ' . $participant->jobFair->event_date->format('M d, Y') . ' at ' . $participant->jobFair->venue . '. You are invited to attend!',
                'reference_type' => 'job_fair',
                'reference_id'   => $participant->job_fair_id,
            ], $userIds);
        }

        // ── Ang gibalibaran nga imbitasyon modala sa bakante niini.
        // ──
        // ── PESO Job Fair staff, 2026-09-01: ang employer nga nagpadala ug
        // ── bakante para niining fair, unya miingon ug dili, wala nay bakante
        // ── nga naghulat — apan nagpabilin siya sa listahan sa desk isip
        // ── naghulat, ug ang usa ka buton nga mo-post sa tanan modala kaniya
        // ── ngadto sa fair nga iyang gibalibaran.
        // ──
        // ── Ang gipangayo niini nga fair ra ang gitangtang. Ang bakante nga
        // ── nangayo ug laing fair, o wala gyud nangayo, wala gihilabti: dili
        // ── siya bahin niining tubaga. ──
        if ($newStatus === 'declined') {
            \App\Models\Job::where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
                ->where('schedule_type', 'job_fair')
                ->where('posting_status', 'pending')
                ->where('requested_job_fair_id', $participant->job_fair_id)
                ->update([
                    'posting_status' => 'rejected',
                    'status'         => 'closed',
                    'remarks'        => 'Withdrawn — the employer declined the invitation to '
                                        . $participant->jobFair->title . '.',
                ]);
        }

        // Kung walay mosulti sa SRA nga naay mitubag, ang ahensya maghulat sa
        // usa ka desisyon nga wala gani nahibaw-i nga naghulat.
        if ($needsSelection) {
            \App\Models\Announcement::sendToStaff([
                'type'           => 'job_fair_selection_pending',
                'title'          => 'Overseas Agency Accepted 📋',
                'message'        => $company->activeCompany()->company_name . ' accepted the invitation to '
                                    . $participant->jobFair->title . ' on '
                                    . $participant->jobFair->event_date->format('M d, Y')
                                    . '. Choose whether to bring them to this fair.',
                'reference_type' => 'job_fair_selection',
                'reference_id'   => $participant->job_fair_id,
            ], \App\Models\Staff::where('staff_role', 'sra')->pluck('staff_id'));
        }

        $msg = match (true) {
            $needsSelection => 'Your acceptance has been sent to PESO. The office will confirm'
                               . ' your slot for this job fair — you will be notified once it does.',
            $request->response === 'confirmed'
                            => 'You have confirmed the job fair invitation! Please post a job vacancy for this event.',
            default         => 'You have declined the job fair invitation.',
        };

        // Ang opisina nagpasa sa listahan sa DOLE napulo ka adlaw sa dili pa ang
        // fair. Ang mi-confirm human niana wala sa gipasa nga listahan, ug mas
        // maayo nga masayod siya karon kaysa sa adlaw sa fair.
        if ($afterSubmission && $newStatus === 'confirmed') {
            $msg .= ' Note: the office already submitted its list of participating'
                  . ' companies to DOLE on '
                  . $participant->jobFair->dole_cutoff_at->format('M d, Y')
                  . ', so please coordinate with PESO about your booth.';
        }

        // Ang modal sa pag-post ug bakante moabli lang kung apil na gyud siya.
        // Ang naghulat pa sa SRA walay ma-post — ug ang pag-abli sa porma kay
        // saad nga wala pa gihatag.
        if ($newStatus === 'confirmed') {
            return redirect()->route('company.jobseekers', ['tab' => 'invitations'])
                ->with('success', $msg)
                ->with('open_job_fair_modal', $participant->job_fair_id);
        }

        return back()->with('success', $msg);
    }

    // ───────────────────────────────
    // QUALIFIED APPLICANTS — per job (Company)
    // ───────────────────────────────
    public function qualifiedApplicants($jobId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $job = \App\Models\Job::where('job_qualifications_id', $jobId)
            ->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->firstOrFail();

        // ── Job Fair jobs naay lahi/locked nga hiring flow — didto ra i-manage, dili dinhi ──
        if ($job->schedule_type === 'job_fair') {
            return redirect()->route('company.jobseekers', ['tab' => 'applicants'])
                ->with('info', 'Job fair applicants are managed from the Job Fair Applicants tab.');
        }

        $applicants = \App\Models\Application::with(['jobseeker', 'jobseeker.nsrp'])
            ->where('job_id', $jobId)
            ->orderByDesc('match_percentage')
            ->get();

        $isInhouse = $job->schedule_type === 'inhouse';
        $isCompanyInterview = $job->schedule_type === 'company_interview';

        // ── Para sa In-house nga jobs, ang Qualified/Highly Qualified/Not Qualified tabs — ONLY kadtong ni-ACCEPT sa participation ──
        // ── Para sa Company Interview, parehas nga rule gamit ang company_interview_participation — kadtong nag-DECLINE (No) magpabilin ra sa All Jobseekers tab, walay action buttons ──
        // ── Para sa Job Fair, walay restriction — parehas ra sa daan (dayon classified base sa match%) ──
        if ($isInhouse) {
            $eligibleForTabs = $applicants->where('inhouse_participation', 'accepted');
        } elseif ($isCompanyInterview) {
            $eligibleForTabs = $applicants->where('company_interview_participation', 'accepted');
        } else {
            $eligibleForTabs = $applicants;
        }

        $highlyQualified = $eligibleForTabs->filter(fn($a) => ($a->match_percentage ?? 0) >= 75)->values();
        $qualified       = $eligibleForTabs->filter(fn($a) => ($a->match_percentage ?? 0) >= 50 && ($a->match_percentage ?? 0) < 75)->values();
        $notQualified    = $eligibleForTabs->filter(fn($a) => ($a->match_percentage ?? 0) < 50)->values();

        $totalAll     = $applicants->count();
        $totalHighly  = $highlyQualified->count();
        $totalQualified = $qualified->count();
        $totalNotQualified = $notQualified->count();

        // ── Hire/Reject/Waiting actions naka-lock hangtod moabot ang adlaw sa
        // ── interbyu — parehas sa In-house ug sa Company Interview. Ang desisyon
        // ── gikan sa interbyu; kung mahatag kini samtang wala pa ang adlaw,
        // ── ang rekord nag-ingon nga nahitabo ang interbyu nga wala pa gyud.
        // ── Ang `interview_date` mao ang gipili sa staff sulod sa window sa
        // ── employer — kung wala pay pinili, ang sinugdanan sa window; para sa
        // ── Company Interview, ang preferred date mismo.
        // ──
        // ── Ang daang posting nga walay petsa dili ma-lock: walay adlaw nga
        // ── basihan, ug ang pag-lock sa wala'y katapusan mas grabe pa.
        $interviewDate   = $job->interview_date;
        $actionsUnlocked = !$this->needsInterviewDate($job)
            || !$interviewDate
            || now()->toDateString() >= $interviewDate->toDateString();

        // ── Ang ubang channel sa parehas nga position (Company Interview/In-house/
        // ── Job Fair nga gi-post nga usa ra ka request). Managsama silag
        // ── bakante, mao nga kinahanglan makita sa employer pila na ang na-hire
        // ── sa tanan, dili sa kini nga listahan ra. ──
        $groupJobs = \App\Models\Job::where(function ($q) use ($job) {
                $q->where('posting_group_id', $job->group_key)
                  ->orWhere('job_qualifications_id', $job->group_key);
            })
            ->withCount(['applications as hired_count' => fn($q) => $q->where('status', 'hired')])
            ->get();

        // A slot is filled whoever filled it. This counted only the PESO
        // hires, so a posting with six slots, one PESO hire and one hire the
        // employer made themselves read "1 of 6 filled" while the badge on the
        // listing said 2 — two numbers for one posting, and the employer had no
        // way to tell which was right. Job::group_hired is the single answer.
        $groupExternal = (int) $groupJobs->sum('external_hires');
        $groupPeso     = (int) $groupJobs->sum('hired_count');
        $groupHired    = $groupPeso + $groupExternal;

        // What has changed on this posting since it went live — see
        // App\Support\JobChangeLog.
        $jobActivity = \App\Support\JobChangeLog::forJob($job);

        return view('company.jobs.qualified', compact(
            'job', 'applicants', 'company', 'isInhouse', 'isCompanyInterview', 'actionsUnlocked',
            'highlyQualified', 'qualified', 'notQualified',
            'totalAll', 'totalHighly', 'totalQualified', 'totalNotQualified',
            'groupJobs', 'groupHired', 'groupPeso', 'groupExternal', 'jobActivity'
        ));
    }

    // ───────────────────────────────
    // CHANGE PASSWORD
    // ───────────────────────────────
    public function changePassword(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => \App\Rules\PasswordPolicy::required(),
        ]);

        if (!Hash::check($request->current_password, $company->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $company->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    // ───────────────────────────────
    // SCHEDULE JOB VACANCY — main table (Job Position / Date / Schedule Type), View → separate qualified applicants page
    // ───────────────────────────────
    public function jobseekers(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');
        if ($guard = $this->requireApprovedRequirements($company)) return $guard;

        $search = $request->input('search');

        // ── Tanan nga buhi nga posting sa employer — apil ang Job Fair nga
        // ── na-approve na apan naghulat pa ug event (closed kana sa DB, apan
        // ── buhi). Kung ihaw-as ang Job Fair dinhi, wala na gyud siyay
        // ── mapakitaan sa employer: dili siya makita sa Job Vacancy Request
        // ── (approved na man) ug dili pud siya angay sa Archived (buhi pa).
        // ── Ang Schedule Type ug Status nga column maoy mag-lain kanila. ──
        // ── Apil ang naghulat pa ug desisyon sa PESO.
        // ──
        // ── Sukad nga ang in-house nga posting nanginahanglan na ug approval sa
        // ── LRA, ang bag-ong gi-post kay posting_status='pending' — ug ang
        // ── alive() nangayo ug 'approved'. Ang sangputanan: nag-post ang
        // ── employer, dayon wala gyud siyay makita bisan asa. Dili siya makita
        // ── diri (dili pa approved), dili sa Archived (approved pud ang
        // ── gikinahanglan), ug ang daan nga Job Vacancy Request nga page wala
        // ── na sa sidebar.
        // ──
        // ── Ang gi-reject wala dinhi: natapos na siya, ug ang Active Job
        // ── Postings para sa buhi. Didto siya sa Reports → Archived Job
        // ── Postings, uban ang rason — dili siya basta mawagtang. ──
        $jobs = Job::where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->where(function ($q) {
                $q->alive()->orWhere('posting_status', 'pending');
            })
            // Group-wide ang hired count: kung ang parehas nga position gi-post
            // sa duha ka channel, usa ra ang bakante nga gi-ambitan nila.
            // withHireBreakdown para makita pud kung pila ang gawas sa PESO.
            ->withHireBreakdown()
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10, ['*'], 'vac_page')
            ->withQueryString();

        // ── Ang Job Fair wala nay kaugalingon nga nav (PESO interview,
        // ── 2026-08-12) — usa ra ang Active Job Vacancy nga page, tab-tab ra
        // ── ang pagbulag. Parehas ra nga data, lahi ra ang plastar. ──
        $jobFair = $this->jobFairPanelData($company, $request);

        $tab = in_array($request->input('tab'), ['vacancies', 'invitations', 'applicants'], true)
            ? $request->input('tab')
            : 'vacancies';

        return view('company.jobseeker.index', array_merge(
            compact('company', 'jobs', 'search', 'tab'),
            $jobFair
        ));
    }

    // ── HELPER — tanan nga data sa Job Fair nga tab sa Active Job Vacancy.
    // ── Gilain aron usa ra ka kopya sa query bisan asa siya gamiton. ──
    private function jobFairPanelData($company, Request $request): array
    {
        $employerId = $company->activeCompany()->employer_nsrp_registrations_id;

        // ── Pending ra ang prominent/actionable list; ang na-resolve (confirmed/declined) kay ──
        // ── paginated ug collapsed sa "Past Invitations", aron dili taason ang tab bisan daghan na ──
        // ──
        // ── Apil ang 'expired' dinhi, dili sa Past. Ang na-lapse nga imbitasyon
        // ── madawat gihapon hangtod sa adlaw sa fair, mao nga aksyonable pa
        // ── siya — ang pagtago niya sa collapsed nga listahan mao ang pagsulti
        // ── nga wala nay mahimo, nga bakak. ──
        // ── Apil ang 'accepted' dinhi, dili sa Past. Mitubag na ang ahensya,
        // ── apan wala pa siya nahibaw-i kung apil ba siya — ug ang pagtago
        // ── niini sa collapsed nga listahan mao ang pagsulti nga tapos na ang
        // ── asunto. Walay buton dinhi para niya, usa ka linya ra nga nagsulti
        // ── nga ang opisina pa ang mopili. ──
        $pendingInvitations = \App\Models\JobFairParticipant::with('jobFair')
            ->where('employer_id', $employerId)
            ->whereIn('confirmation_status', ['pending', 'expired', 'accepted'])
            ->latest()
            ->paginate(5, ['*'], 'pending_page')
            ->withQueryString();

        $pastInvitations = \App\Models\JobFairParticipant::with('jobFair')
            ->where('employer_id', $employerId)
            ->whereIn('confirmation_status', ['confirmed', 'declined', 'not_selected'])
            ->latest()
            ->paginate(5, ['*'], 'past_page')
            ->withQueryString();

        $pendingInvitationsCount = \App\Models\JobFairParticipant::where('employer_id', $employerId)
            ->whereIn('confirmation_status', ['pending', 'expired'])
            ->count();

        // Pila untay makit-an nga tawo kung mo-oo siya. Suhestiyon ra — tan-awa
        // ang jobFairPotentialApplicants para sa kung ngano.
        $potentialApplicants = $this->jobFairPotentialApplicants($employerId, $pendingInvitations);

        $invitations = \App\Models\JobFairParticipant::with('jobFair')
            ->where('employer_id', $employerId)
            ->get();

        // ── Confirmed count per event, para ma-lock ang Accept button kung full na ──
        $confirmedCountsPerEvent = \App\Models\JobFairParticipant::where('confirmation_status', 'confirmed')
            ->whereIn('job_fair_id', $invitations->pluck('job_fair_id'))
            ->selectRaw('job_fair_id, count(*) as cnt')
            ->groupBy('job_fair_id')
            ->pluck('cnt', 'job_fair_id');

        // Get applicants kung confirmed ang employer sa bisan unsang job fair
        $isConfirmed = \App\Models\JobFairParticipant::where('employer_id', $employerId)
            ->where('confirmation_status', 'confirmed')
            ->exists();

        // Kaugalingon nga search param: ang "search" gamit na sa Active
        // Vacancies nga tab, ug dili angay magkuha-kuha ang duha.
        $jfSearch = $request->input('jf_search');

        $applicants     = collect();
        $jobFairByJobId = collect();

        if ($isConfirmed) {
            // ── Job ids nga tinuod nga gi-submit para sa mga event nga confirmed ang company ──
            $confirmedEventIds = \App\Models\JobFairParticipant::where('employer_id', $employerId)
                ->where('confirmation_status', 'confirmed')
                ->pluck('job_fair_id');
            $employmentRequests = \App\Models\JobFairEmploymentRequest::with('jobFair')
                ->where('employer_id', $employerId)
                ->whereIn('job_fair_id', $confirmedEventIds)
                ->get();
            $bringableJobIds = $employmentRequests->pluck('job_id');
            // ── job_id => JobFairEvent, para ipakita sa Applicants table kung unsa nga event ang gikan sa matag applicant ──
            $jobFairByJobId = $employmentRequests->pluck('jobFair', 'job_id');

            $applicants = \App\Models\Application::with(['jobseeker', 'jobseeker.nsrp', 'jobseeker.user', 'job'])
                ->whereIn('job_id', $bringableJobIds)
                ->whereIn('status', ['pending', 'reviewed', 'qualified', 'waiting', 'hired', 'rejected'])
                ->when($jfSearch, function ($q) use ($jfSearch) {
                    $q->where(function ($sub) use ($jfSearch) {
                        $sub->whereHas('jobseeker', function ($jq) use ($jfSearch) {
                            $jq->where('first_name', 'like', "%{$jfSearch}%")
                               ->orWhere('surname', 'like', "%{$jfSearch}%");
                        })->orWhereHas('job', function ($jq) use ($jfSearch) {
                            $jq->where('title', 'like', "%{$jfSearch}%");
                        });
                    });
                })
                ->latest()
                // Five to a page. The employer works down this list one
                // applicant at a time during the fair, and a long page
                // loses their place.
                ->paginate(5, ['*'], 'jf_page')
                ->withQueryString();
        }

        return compact(
            'pendingInvitations', 'pastInvitations', 'pendingInvitationsCount',
            'applicants', 'jobFairByJobId', 'isConfirmed', 'jfSearch', 'confirmedCountsPerEvent',
            'potentialApplicants'
        );
    }

    /**
     * How many registered jobseekers this employer's vacancies would match,
     * per fair they have been invited to.
     *
     * PESO Job Fair staff, 2026-09-02: an employer asked to join a fair has
     * nothing to weigh the invitation against. This is that: the number of
     * jobseekers registered today whose NSRP form lines up with the vacancies
     * they would bring.
     *
     * It is a suggestion, and it is written as one on screen. No names, no
     * promise anyone turns up, and no promise anyone is hired — the real
     * applicant list only exists after they confirm and the vacancy goes live.
     * The tiers are the same 75 / 50 boundaries every other screen uses, so a
     * jobseeker sits in the same band wherever they are counted.
     */
    private function jobFairPotentialApplicants(int $employerId, $invitations): array
    {
        if ($invitations->isEmpty()) {
            return [];
        }

        $fairIds = $invitations->pluck('job_fair_id')->unique();

        $jobs = Job::withCount([
                'applications as highly_count'    => fn($q) => $q->where('match_percentage', '>=', 75),
                'applications as qualified_count' => fn($q) => $q->whereBetween('match_percentage', [50, 74.99]),
            ])
            ->where('schedule_type', 'job_fair')
            ->where('company_id', $employerId)
            ->get()
            ->keyBy('job_qualifications_id');

        // Gipili na ba niya kung asa nga bakante ang dad-on sa maong fair? Kung
        // wala pa, ang tanan niyang job fair nga bakante ang gibasehan — mao
        // gyud kana ang gipangutana: pila untay madala niya.
        $attached = \App\Models\JobFairEmploymentRequest::where('employer_id', $employerId)
            ->whereIn('job_fair_id', $fairIds)
            ->get()
            ->groupBy('job_fair_id');

        $out = [];
        foreach ($invitations as $invitation) {
            $fairId = $invitation->job_fair_id;

            $rows = isset($attached[$fairId])
                ? $attached[$fairId]->map(fn($request) => $jobs->get($request->job_id))->filter()
                : $jobs;

            $out[$fairId] = [
                'vacancies' => $rows->count(),
                'highly'    => $rows->sum('highly_count'),
                'qualified' => $rows->sum('qualified_count'),
            ];
        }

        return $out;
    }

    // ───────────────────────────────
    // REPORTS — job list nga naay bisan usa ka hired applicant
    // ───────────────────────────────
    // ── Ang duha ka listahan sa Reports, gikan sa usa ka lugar, aron ang
    // ── screen, ang CSV ug ang printed nga kopya dili gyud magkalahi. ──
    private function reportQueries($company, \App\Support\DateRange $range, ?string $search = null): array
    {
        $companyId = $company->activeCompany()->employer_nsrp_registrations_id;

        // Ang petsa nga gi-filter kay ang adlaw sa pag-hire, dili ang adlaw
        // sa pag-post: "kung January 1 hangtod January 31 ang gipili nga date
        // range, makita ang total applicants sulod ana nga period."
        $hiredInRange = function ($q) use ($range) {
            $q->where('status', 'hired');
            $range->apply($q, 'job_matching.updated_at');
        };

        $hired = Job::where('company_id', $companyId)
            ->whereHas('applications', $hiredInRange)
            ->withCount(['applications as hired_count' => $hiredInRange])
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%"))
            ->latest();

        // Walay date range sa archive — walay kontrol sa screen para niini, ug
        // ang listahan nga hilom nga na-filter sa usa ka URL parameter nga dili
        // makita kay bakak nga listahan.
        // Ang gi-reject usa ka natapos nga posting sama sa nag-expire ug sa
        // napuno — apan ang inactive() nangayo ug 'approved', mao nga dili gyud
        // siya makasulod niini. Gidugang siya dinhi aron adunay lugar nga
        // makit-an ang rason human mawala ang notification sa bell.
        $archived = Job::where('company_id', $companyId)
            ->where(fn($q) => $q->inactive()->orWhere('posting_status', 'rejected'))
            ->withHireBreakdown()
            ->withCount(['applications as hired_count' => fn($q) => $q->where('status', 'hired')])
            ->latest();

        return ['hired' => $hired, 'archived' => $archived];
    }

    public function reports(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');
        if ($guard = $this->requireApprovedRequirements($company)) return $guard;

        $search = $request->input('search');
        $range  = \App\Support\DateRange::fromRequest($request);
        $queries = $this->reportQueries($company, $range, $search);

        $jobs = $queries['hired']->paginate(10)->withQueryString();

        // ── Ang nahuman na nga posting — milabay ang deadline o napuno ang
        // ── slots. Rekord sa posting ra ni: unsa ang trabaho ug unsa ang
        // ── requirements. Ang hired nga jobseeker naa sa Hired Applicants
        // ── tab — kung diri pud, doble ra ang parehas nga data.
        // ── Ang hired_count gamiton ra sa pag-ila kung nganong nasira. ──
        // ── Ang group_hired_count gi-load para sa status badge: kung dili,
        // ── usa ka query kada row ang buhaton sa Job::getGroupHiredAttribute. ──
        $archivedJobs = $queries['archived']->paginate(5, ['*'], 'archived_page')->withQueryString();

        // ── Chart: pila ka hired kada bulan sulod sa gipili nga range. Ang
        // ── pag-group sa PHP, dili sa SQL, kay ang MySQL ug SQLite lahi ug
        // ── date function ug gamay ra man ang row nga giagian. ──
        $hiredCounts = \App\Models\Application::whereIn('job_id',
                Job::where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
                    ->pluck('job_qualifications_id'))
            ->where('status', 'hired')
            ->tap(fn($q) => $range->apply($q, 'job_matching.updated_at'))
            ->get(['updated_at'])
            ->groupBy(fn($a) => $a->updated_at->format('M Y'))
            ->map->count();

        // ── Ang tuig kompleto, January hangtod December, bisan zero.
        // ──
        // ── Kaniadto ang bulan nga walay hire wala gyud sa chart, mao nga usa
        // ── ka employer nga nag-hire kaduha ka higayon nakakita ug duha ka
        // ── bar nga magkilid ug murag padayon ang trabaho. Ang gap mao gyud
        // ── ang tubag: ang zero nga bulan bahin sa istorya.
        // ──
        // ── Kung naay gipili nga range, ang range ang gisunod; kung wala,
        // ── ang tibuok karon nga tuig. ──
        $chartStart = ($range->from ?? now()->startOfYear())->copy()->startOfMonth();
        $chartEnd   = ($range->to   ?? now()->endOfYear())->copy()->startOfMonth();

        $hiredChart = collect();
        for ($month = $chartStart->copy(); $month->lessThanOrEqualTo($chartEnd); $month->addMonth()) {
            $key = $month->format('M Y');
            $hiredChart[$key] = (int) ($hiredCounts[$key] ?? 0);
        }

        // ── Ang duha ka numero, bulag. Ang PESO placements maoy gi-report sa
        // ── opisina; ang gawas sa PESO gipakita ra aron klaro sa employer
        // ── nganong nahurot ang iyang slots. ──
        $companyJobIds     = Job::where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
                                ->pluck('job_qualifications_id');
        $pesoHiresTotal    = \App\Models\Application::whereIn('job_id', $companyJobIds)
                                ->where('status', 'hired')->count();
        $outsideHiresTotal = (int) Job::where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
                                ->groupLeaders()->sum('external_hires');

        return view('company.reports.index', compact(
            'jobs', 'search', 'archivedJobs', 'range', 'hiredChart',
            'pesoHiresTotal', 'outsideHiresTotal'
        ));
    }

    // ───────────────────────────────
    // REPORTS — CSV download ug printable nga kopya
    // ───────────────────────────────
    // Parehas nga query sa screen (reportQueries), mao nga ang gi-download
    // ug ang gi-print pareho gyud sa gi-tan-aw.
    public function exportReports(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');
        if ($guard = $this->requireApprovedRequirements($company)) return $guard;

        $view    = $request->input('view') === 'archived' ? 'archived' : 'hired';
        $range   = \App\Support\DateRange::fromRequest($request);
        $queries = $this->reportQueries($company, $range, $request->input('search'));
        $records = $queries[$view]->get();

        $scheduleLabel = fn($type) => \App\Models\Job::scheduleTypeLabel($type);

        // ── Bulag gyud ang duha ka numero. Ang gi-hire nga wala miagi sa PESO
        // ── mo-hurot ug slot, apan DILI siya PESO placement — kung sagolon,
        // ── mataas ang numero nga isumite sa Mayor's Office ug DOLE nga dili
        // ── tinuod nga trabaho sa opisina. ──
        if ($view === 'archived') {
            $columns = ['Job Position', 'Location', 'Schedule Type', 'Posted', 'Status', 'Slots',
                        'Hired through PESO', 'Hired outside PESO', 'Deadline'];
            $rows = $records->map(fn($job) => [
                $job->title,
                $job->location ?? '',
                $scheduleLabel($job->schedule_type),
                $job->created_at->format('Y-m-d'),
                \App\Models\Job::LIFECYCLE_LABELS[$job->lifecycle_status],
                $job->slots,
                $job->group_peso_hires,
                $job->group_external_hires,
                $job->deadline?->format('Y-m-d') ?? '',
            ]);
            $title = 'Archived Job Postings';

            $pesoHires     = $records->sum(fn($job) => $job->group_peso_hires);
            $externalHires = $records->sum(fn($job) => $job->group_external_hires);
        } else {
            // ── Usa ka linya kada TAWO, dili kada posting.
            // ──
            // ── Ang screen magpakita ug ihap ("2 / 2") kay ang pangalan naa
            // ── man sa View nga pahina, usa ka pag-klik ang gilay-on. Ang CSV
            // ── walay View nga pahina — kung ihap ra ang naa niya, walay
            // ── pangalan nga makuha ang opisina sa dokumentasyon nga ilang
            // ── ipasa. Mao nga ang gi-download mao ang sulod sa duha ka
            // ── pahina, gitakdo, ug ang posisyon gibalik-balik kada linya
            // ── aron mabalhin ug ma-sort ang file bisan asa. ──
            $columns = ['Job Position', 'Schedule Type', 'Location', 'Jobseeker',
                        'Email', 'Contact Number', 'Match %', 'Date Hired'];

            // Parehas nga sala sa reportQueries: hired, sulod sa gipiling
            // petsa, ug ang petsa nga gi-ihap kay ang adlaw sa pag-hire.
            $hiredApplications = Application::with('jobseeker.user')
                ->whereIn('job_id', $records->pluck('job_qualifications_id'))
                ->where('status', 'hired')
                ->tap(fn($q) => $range->apply($q, 'job_matching.updated_at'))
                ->orderBy('updated_at')
                ->get()
                ->groupBy('job_id');

            $rows = $records->flatMap(function ($job) use ($hiredApplications, $scheduleLabel) {
                return ($hiredApplications[$job->job_qualifications_id] ?? collect())
                    ->map(function ($app) use ($job, $scheduleLabel) {
                        $reg = $app->jobseeker;

                        return [
                            $job->title,
                            $scheduleLabel($job->schedule_type),
                            $job->location ?? '',
                            trim(($reg->first_name ?? '') . ' ' . ($reg->surname ?? '')),
                            $reg->reg_email ?? ($reg->user->email ?? ''),
                            $reg->contact_number ?? '',
                            $app->match_percentage,
                            $app->updated_at?->format('Y-m-d') ?? '',
                        ];
                    });
            });

            $title = 'Hired Applicants';

            $pesoHires     = $records->sum('hired_count');
            $externalHires = null;   // ang tab na kini kay PESO placements ra
        }

        $companyName = $company->activeCompany()->company_name;

        if ($request->input('format') === 'print') {
            $totals = [
                'Total postings listed' => $records->count(),
                'Hired through PESO'    => $pesoHires,
            ];
            if ($externalHires !== null) {
                $totals['Hired outside PESO (not a PESO placement)'] = $externalHires;
                $totals['Total slots filled'] = $pesoHires + $externalHires;
            }

            return view('reports.print', [
                'title'      => $title,
                'subtitle'   => $companyName,
                'range'      => $range,
                'columns'    => $columns,
                'rows'       => $rows,
                'totals'     => $totals,
                'preparedBy' => $company->name,
            ]);
        }

        return \App\Support\ExcelExport::stream(
            'peso-' . str($title)->slug() . '-' . now()->format('Ymd') . '.xlsx',
            $columns,
            $rows,
            array_values(array_filter([
                // Hyphen, dili em-dash. Kung ang mag-abli sa file mag-basa
                // niini isip Windows-1252, ang em-dash mahimong "â€”" — ug
                // ang unang linya sa report mao ang labing dili maayo nga
                // lugar nga makita kana.
                ['PESO Cagayan de Oro - ' . $title],
                ['Employer', $companyName],
                ['Covering period', $range->label()],
                ['Hired through PESO', $pesoHires],
                $externalHires !== null
                    ? ['Hired outside PESO (not a PESO placement)', $externalHires]
                    : null,
                ['Generated', now()->format('Y-m-d H:i')],
            ]))
        );
    }

    // ───────────────────────────────
    // JOB — full details sa usa ka posting (walay applicant data)
    // ───────────────────────────────
    public function jobDetails($jobId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $job = Job::where('job_qualifications_id', $jobId)
            ->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->firstOrFail();

        return view('company.jobs.details', compact('job'));
    }

    // ───────────────────────────────
    // REPORTS — hired jobseekers per specific job
    // ───────────────────────────────
    public function reportsByJob(Request $request, $jobId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $job = Job::where('job_qualifications_id', $jobId)
            ->where('company_id', $company->activeCompany()->employer_nsrp_registrations_id)
            ->firstOrFail();

        $search = $request->input('search');

        $hired = Application::with(['jobseeker.user'])
            ->where('job_id', $jobId)
            ->where('status', 'hired')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('jobseeker', function ($jq) use ($search) {
                    $jq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('surname', 'like', "%{$search}%");
                });
            })
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return view('company.reports.show', compact('job', 'hired', 'search'));
    }

    // ───────────────────────────────
    // Resolve job image: upload if file, return path if string, null otherwise
    // ───────────────────────────────
    private function resolveJobImage($pos): ?string
    {
        if (isset($pos['job_image']) && $pos['job_image'] instanceof \Illuminate\Http\UploadedFile) {
            return $pos['job_image']->store('job_images', 'public');
        }
        return $pos['poster_image'] ?? $pos['job_image'] ?? null;
    }
}