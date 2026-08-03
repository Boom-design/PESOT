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

    // ── HELPER — force redirect sa Requirements kung wala pa naka-submit ──
    private function requireRequirements($company)
    {
        $nsrp = $company->employerNsrp;
        $hasSubmitted = $nsrp && \App\Models\EmployerRequirement::where('user_id', $nsrp->id)->exists();
        if (!$hasSubmitted) {
            return redirect()->route('company.requirements')
                ->with('info', 'Please submit your requirements first before accessing other features.');
        }
        return null;
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

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->role !== 'company') {
            return back()->withErrors(['email' => 'No company account found.']);
        }

        if ($user->status === 'deactivated') {
            return back()->withErrors(['email' => 'Your account has been deactivated. Please contact PESO.']);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->route('company.dashboard');
        }

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
        return redirect()->route('company.login');
    }

    // ───────────────────────────────
    // DASHBOARD
    // ───────────────────────────────
    public function dashboard()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        $nsrpId = $company->employerNsrp->id;

        $totalJobs = Job::where('company_id', $nsrpId)->count();
        $totalApplicants = Application::whereHas('job', function ($q) use ($nsrpId) {
            $q->where('company_id', $nsrpId);
        })->count();
        $hired = Application::whereHas('job', function ($q) use ($nsrpId) {
            $q->where('company_id', $nsrpId);
        })->where('status', 'hired')->count();

        $recentJobs = Job::where('company_id', $nsrpId)
            ->latest()
            ->take(5)
            ->get();

        // Today's Activities
        $todayJobFairs = \App\Models\JobFairEvent::whereDate('event_date', today())
            ->where('status', '!=', 'completed')
            ->get();

        $todayInhouse = \App\Models\InhouseSchedule::where('employer_id', $company->employerNsrp->id)
            ->whereDate('confirmed_date', today())
            ->where('status', 'accepted')
            ->get();

        return view('company.dashboard', compact(
            'company',
            'totalJobs',
            'totalApplicants',
            'hired',
            'recentJobs',
            'todayJobFairs',
            'todayInhouse'
        ));
    }

    // ───────────────────────────────
    // JOB POSTS — INDEX
    // ───────────────────────────────
    public function jobs()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');
        if ($guard = $this->requireRequirements($company)) return $guard;

        // ── Closed ra (pending o rejected) ang makita diri; pag-approve/pag-open, mabalhin na sa "Schedule Job Vacancy" tab ──
        $jobs = Job::where('company_id', $company->employerNsrp->id)
            ->where('status', 'closed')
            ->latest()->get();

        // Check kung naa nay approved requirements + wala pa na-confirm ang initial vacancy gikan sa registration
        $requirement       = \App\Models\EmployerRequirement::where('user_id', $company->employerNsrp->id)->where('status', 'approved')->first();
        $employerNsrp      = $company->employerNsrp;
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
        if (!$company) return redirect()->route('company.login');

        $employerNsrp = $company->employerNsrp;
        if (!$employerNsrp || $employerNsrp->initial_vacancy_confirmed) {
            return redirect()->route('company.jobs');
        }

        $request->validate([
            'schedule_type'  => 'required|in:inhouse,office_based,job_fair',
            'preferred_date' => 'required_if:schedule_type,inhouse|required_if:schedule_type,office_based|nullable|date|after_or_equal:today',
            'venue_type'     => 'required_if:schedule_type,inhouse|nullable|in:peso_office,other',
            'venue_address'  => 'required_if:venue_type,other|nullable|string|max:255',
            'positions'                          => 'required|array|min:1',
            'positions.*.title'                 => 'required|string|max:255',
            'positions.*.description'           => 'required|string',
            'positions.*.type'                  => 'required|in:permanent,contractual,project_based,internship,part_time,work_from_home',
            'positions.*.location'              => 'required|string|max:255',
            'positions.*.salary'                => 'nullable|string|max:100',
            'positions.*.slots'                 => 'required|integer|min:1',
            'positions.*.deadline'               => 'nullable|date',
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
        ]);

        // ── Kung Inhouse + PESO Office, i-check kung puno na (max 3 companies) sa gipili nga petsa ──
        if ($request->schedule_type === 'inhouse' && $request->venue_type === 'peso_office') {
            $scheduleCompanies = \App\Models\InhouseSchedule::where('venue_type', 'peso_office')
                ->whereDate('preferred_date', $request->preferred_date)
                ->whereIn('status', ['pending', 'accepted'])
                ->distinct('employer_id')
                ->count('employer_id');

            $jobCompanies = Job::where('schedule_type', 'inhouse')
                ->where('venue_type', 'peso_office')
                ->whereDate('preferred_date', $request->preferred_date)
                ->whereIn('posting_status', ['pending', 'approved'])
                ->distinct('company_id')
                ->count('company_id');

            if (($scheduleCompanies + $jobCompanies) >= 3) {
                return back()->withInput()
                    ->with('error', 'This date at PESO Office is fully booked (3/3 companies). Please choose another date or venue.');
            }
        }

        $nsrpId = $company->employerNsrp->id;

        foreach ($request->positions as $pos) {
            Job::create([
                'company_id'          => $nsrpId,
                'title'               => $pos['title'],
                'description'         => $pos['description'],
                'location'            => $pos['location'],
                'type'                => $pos['type'],
                'industry_group'      => $company->employerNsrp->industry_group,
                'slots'               => $pos['slots'],
                'salary'              => $pos['salary'] ?? 'Negotiable',
                'deadline'            => $pos['deadline'] ?? null,
                'status'              => 'closed',
                'posting_status'      => 'pending',
                'posting_type'        => 'direct',
                'schedule_type'       => $request->schedule_type,
                'preferred_date'      => $request->schedule_type !== 'job_fair' ? $request->preferred_date : null,
                'venue_type'          => $request->schedule_type === 'inhouse' ? $request->venue_type : null,
                'venue_address'       => $request->schedule_type === 'inhouse' && $request->venue_type === 'other' ? $request->venue_address : null,
                'experience_months'   => $pos['experience_months'] ?? null,
                'religion'            => $pos['religion'] ?? null,
                'sex_preference'      => $pos['sex_preference'] ?? 'Any',
                'civil_status'        => $pos['civil_status'] ?? 'Any',
                'other_qualifications'=> $pos['other_qualifications'] ?? null,
                'accepts_disability'  => $pos['accepts_disability'] ?? 'no',
                'disability_types'    => $pos['disability_types'] ?? null,
                'education_required'  => $pos['education_required'] ?? null,
                'course_major'        => $pos['course_major'] ?? null,
                'license'             => $pos['license'] ?? null,
                'eligibility'         => $pos['eligibility'] ?? null,
                'certification'       => $pos['certification'] ?? null,
                'language'            => $pos['language'] ?? null,
                'preferred_residence' => $pos['preferred_residence'] ?? null,
                'accepts_programs'    => $pos['accepts_programs'] ?? null,
            ]);
        }

        $employerNsrp->update([
            'initial_vacancy_confirmed' => true,
            'initial_vacancy_data'      => $request->positions,
        ]);

        // ── I-route ang notification base sa schedule_type ──
        // ── Job Fair postings: SRA (overseas) o Job Vacancy staff (local) ang mo-approve — dili si Job Fair staff, sila ra ang mag-post/open human ma-create og event ──
        if ($request->schedule_type === 'inhouse') {
            $inhouseStaffRole = $employerNsrp->is_overseas ? 'sra' : 'lra';
            $staffIds   = \App\Models\Staff::where('staff_role', $inhouseStaffRole)->pluck('id');
            $venueLabel = $request->venue_type === 'other' ? $request->venue_address : 'PESO Office';
            $title      = 'New In-house Job Posting Request 📅';
            $message    = $company->employerNsrp->company_name . ' confirmed their initial job vacancy posting(s) for an in-house interview on ' . \Carbon\Carbon::parse($request->preferred_date)->format('M d, Y') . ' at ' . $venueLabel . '. Please review and approve/reject.';
        } elseif ($request->schedule_type === 'job_fair') {
            $jobFairApproverRole = $employerNsrp->is_overseas ? 'sra' : 'job_vacancy';
            $staffIds = \App\Models\Staff::where('staff_role', $jobFairApproverRole)->pluck('id');
            $title    = 'New Job Fair Posting Request 🎪';
            $message  = $company->employerNsrp->company_name . ' confirmed their initial job vacancy posting(s) for job fair use. Please review and approve.';
        } else {
            $staffIds = \App\Models\Staff::where('staff_role', 'job_vacancy')->pluck('id');
            $title    = 'New Job Posting Request 💼';
            $message  = $company->employerNsrp->company_name . ' confirmed their initial job vacancy posting(s). Please review and approve.';
        }

        \App\Models\Announcement::sendToStaff([
            'type'           => 'job_request',
            'title'          => $title,
            'message'        => $message,
            'reference_type' => 'job',
            'reference_id'   => null,
        ], $staffIds);

        return redirect()->route('company.jobs')
            ->with('success', 'Job posting confirmed! Waiting for PESO staff approval.');
    }

    // ───────────────────────────────
    // JOB POSTS — CREATE
    // ───────────────────────────────
    public function createJob()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        return view('company.jobs.create', compact('company'));
    }

    public function storeJob(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'required|string',
            'location'       => 'required|string|max:255',
            'salary'         => 'nullable|string|max:100',
            'job_type'       => 'required|in:full_time,part_time,contractual',
            'industry_group' => 'required|string|max:255',
            'slots'          => 'required|integer|min:1',
            'deadline'       => 'nullable|date',
        ]);

        Job::create([
            'company_id'     => $company->employerNsrp->id,
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

        return redirect()->route('company.jobs')->with('success', 'Job posted successfully!');
    }

    // ───────────────────────────────
    // JOB POSTS — EDIT
    // ───────────────────────────────
    public function editJob($id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        $job = Job::where('id', $id)->where('company_id', $company->employerNsrp->id)->firstOrFail();

        return view('company.jobs.edit', compact('company', 'job'));
    }

   public function updateJob(Request $request, $id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        $job = Job::where('id', $id)->where('company_id', $company->employerNsrp->id)->firstOrFail();

        $request->validate([
            'title'                => 'required|string|max:255',
            'description'          => 'required|string',
            'type'                 => 'required|in:permanent,contractual,project_based,internship,part_time,work_from_home',
            'location'             => 'required|string|max:255',
            'salary'               => 'nullable|string|max:100',
            'slots'                => 'required|integer|min:1',
            'deadline'             => 'nullable|date',
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
        ]);

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
        ]);

        return redirect()->route('company.jobs')->with('success', 'Job updated successfully!');
    }

    // ───────────────────────────────
    // JOB POSTS — DELETE
    // ───────────────────────────────
    public function deleteJob($id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        $job = Job::where('id', $id)->where('company_id', $company->employerNsrp->id)->firstOrFail();
        $job->delete();

        return back()->with('success', 'Job post deleted.');
    }

    // ───────────────────────────────
    // APPLICANTS — per job
    // ───────────────────────────────
    public function applicants($jobId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        $job = Job::where('id', $jobId)->where('company_id', $company->employerNsrp->id)->firstOrFail();
        $applicants = Application::with('jobseeker')
            ->where('job_id', $jobId)
            ->latest()
            ->get();

        $qualifiedApplicants       = $applicants->filter(fn($a) => ($a->match_percentage ?? 0) >= 50 && ($a->match_percentage ?? 0) < 75)->values();
        $highlyQualifiedApplicants = $applicants->filter(fn($a) => ($a->match_percentage ?? 0) >= 75)->values();

        return view('company.applicants', compact('company', 'job', 'applicants', 'qualifiedApplicants', 'highlyQualifiedApplicants'));
    }

    // ───────────────────────────────
    // UPDATE APPLICANT STATUS
    // ───────────────────────────────
    public function updateApplicantStatus(Request $request, $applicationId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        $request->validate([
            'status' => 'required|in:pending,reviewed,qualified,hired,waiting,rejected',
        ]);

        $application = Application::with('job')->whereHas('job', function ($q) use ($company) {
            $q->where('company_id', $company->employerNsrp->id);
        })->findOrFail($applicationId);

        // Lock — dili na mausab ang final decision
        if (in_array($application->status, ['hired', 'rejected'])) {
            return back()->with('error', 'This application already has a final decision and cannot be changed.');
        }

        $application->update(['status' => $request->status]);

        $messages = [
            'hired'    => ['title' => 'Application Update — Hired! 🎉', 'text' => 'Congratulations! You have been hired for the position "' . $application->job->title . '" at ' . $company->employerNsrp->company_name . '.'],
            'waiting'  => ['title' => 'Application Update — Waiting List ⏳', 'text' => 'Your application for "' . $application->job->title . '" at ' . $company->employerNsrp->company_name . ' has been placed on the waiting list.'],
            'rejected' => ['title' => 'Application Update ❌', 'text' => 'Your application for "' . $application->job->title . '" at ' . $company->employerNsrp->company_name . ' was not selected this time.'],
            'reviewed' => ['title' => 'Application Update — Under Review 👀', 'text' => 'Your application for "' . $application->job->title . '" at ' . $company->employerNsrp->company_name . ' is now being reviewed.'],
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
        if (!$company) return redirect()->route('company.login');

        return view('company.profile', compact('company'));
    }

    public function updateProfile(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        $request->validate([
            'company_name'   => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'position_title' => 'required|string|max:255',
            'mobile_number'  => 'required|string|max:20',
            'email'          => 'required|email|unique:users,email,' . $company->id,
        ]);

        $company->update([
            'email' => $request->email,
            'name'  => $request->company_name,
        ]);

        $company->employerNsrp->update([
            'company_name'   => $request->company_name,
            'contact_person' => $request->contact_person,
            'position_title' => $request->position_title,
            'mobile_number'  => $request->mobile_number,
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

        $date      = $request->query('date');
        $venueType = $request->query('venue_type', 'peso_office');

        if ($venueType !== 'peso_office') {
            return response()->json(['occupied' => false, 'count' => 0]);
        }

        $scheduleCompanies = \App\Models\InhouseSchedule::where('venue_type', 'peso_office')
            ->whereDate('preferred_date', $date)
            ->whereIn('status', ['pending', 'accepted'])
            ->distinct('employer_id')
            ->count('employer_id');

        $jobCompanies = \App\Models\Job::where('schedule_type', 'inhouse')
            ->where('venue_type', 'peso_office')
            ->whereDate('preferred_date', $date)
            ->whereIn('posting_status', ['pending', 'approved'])
            ->distinct('company_id')
            ->count('company_id');

        $total = $scheduleCompanies + $jobCompanies;

        return response()->json(['occupied' => $total >= 3, 'count' => $total]);
    }

    // ───────────────────────────────
    // REQUEST JOB POSTING (multi-position, usa ka poster image per request)
    // ───────────────────────────────
    public function requestJob(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        // Check kung approved ang requirements
        $requirement = \App\Models\EmployerRequirement::where('user_id', $company->employerNsrp->id)
            ->where('status', 'approved')
            ->first();

        if (!$requirement) {
            return redirect()->route('company.dashboard')
                ->with('error', 'Your requirements must be approved before requesting a job posting.');
        }

        $request->validate([
            'schedule_type'  => 'required|in:inhouse,office_based,job_fair',
            'preferred_date' => 'required_if:schedule_type,inhouse|nullable|date|after_or_equal:today',
            'venue_type'     => 'required_if:schedule_type,inhouse|nullable|in:peso_office,other',
            'venue_address'  => 'required_if:venue_type,other|nullable|string|max:255',
            'poster_image'   => 'nullable|file|mimes:jpg,jpeg,png|max:5120',

            'positions'                          => 'required|array|min:1',
            'positions.*.title'                 => 'required|string|max:255',
            'positions.*.description'           => 'required|string',
            'positions.*.type'                  => 'required|in:permanent,contractual,project_based,internship,part_time,work_from_home',
            'positions.*.location'              => 'required|string|max:255',
            'positions.*.salary'                => 'nullable|string|max:100',
            'positions.*.slots'                 => 'required|integer|min:1',
            'positions.*.deadline'              => 'nullable|date',
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
        ]);

        // ── Kung Inhouse + PESO Office, i-check kung puno na (max 3 companies) sa gipili nga petsa ──
        if ($request->schedule_type === 'inhouse' && $request->venue_type === 'peso_office') {
            $scheduleCompanies = \App\Models\InhouseSchedule::where('venue_type', 'peso_office')
                ->whereDate('preferred_date', $request->preferred_date)
                ->whereIn('status', ['pending', 'accepted'])
                ->distinct('employer_id')
                ->count('employer_id');

            $jobCompanies = \App\Models\Job::where('schedule_type', 'inhouse')
                ->where('venue_type', 'peso_office')
                ->whereDate('preferred_date', $request->preferred_date)
                ->whereIn('posting_status', ['pending', 'approved'])
                ->distinct('company_id')
                ->count('company_id');

            if (($scheduleCompanies + $jobCompanies) >= 3) {
                return back()->withInput()
                    ->with('error', 'This date at PESO Office is fully booked (3/3 companies). Please choose another date or venue.');
            }
        }

        // ── Usa ra ka poster image para sa TIBUOK request, i-share sa tanan positions ──
        $posterPath = null;
        if ($request->hasFile('poster_image')) {
            $posterPath = $request->file('poster_image')->store('job_posters', 'public');
        }

        $companyId = $company->employerNsrp->id;
        $createdJobs = [];

        foreach ($request->positions as $pos) {
            $createdJobs[] = Job::create([
                'company_id'          => $companyId,
                'title'               => $pos['title'],
                'description'         => $pos['description'] ?? '',
                'location'            => $pos['location'],
                'type'                => $pos['type'],
                'industry_group'      => $company->employerNsrp->industry_group,
                'slots'               => $pos['slots'],
                'deadline'            => $pos['deadline'] ?? null,
                'salary'              => $pos['salary'] ?? 'Negotiable',
                'status'              => 'closed',
                'posting_status'      => 'pending',
                'posting_type'        => 'direct',
                'schedule_type'       => $request->schedule_type,
                'preferred_date'      => $request->preferred_date,
                'venue_type'          => $request->venue_type,
                'venue_address'       => $request->venue_type === 'other' ? $request->venue_address : null,
                'poster_image'        => $posterPath,
                // Qualification fields
                'sex_preference'      => $pos['sex_preference'] ?? 'Any',
                'education_required'  => $pos['education_required'] ?? null,
                'religion'            => $pos['religion'] ?? null,
                'civil_status'        => $pos['civil_status'] ?? 'Any',
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
                'experience_months'   => $pos['experience_months'] ?? null,
            ]);
        }

        $firstJob = $createdJobs[0];
        $positionCount = count($createdJobs);
        $titleSummary = $positionCount > 1
            ? $firstJob->title . ' (+' . ($positionCount - 1) . ' more)'
            : $firstJob->title;

        // ── I-route ang notification base sa schedule_type ──
        // ── Job Fair postings: SRA (overseas) o Job Vacancy staff (local) ang mo-approve — dili si Job Fair staff, sila ra ang mag-post/open human ma-create og event ──
        if ($request->schedule_type === 'inhouse') {
            $inhouseStaffRole = ($company->employerNsrp && $company->employerNsrp->is_overseas) ? 'sra' : 'lra';
            $staffIds   = \App\Models\Staff::where('staff_role', $inhouseStaffRole)->pluck('id');
            $venueLabel = $request->venue_type === 'other' ? $request->venue_address : 'PESO Office';
            $title      = 'New In-house Job Posting Request 📅';
            $message    = $company->employerNsrp->company_name . ' requested an in-house interview for "' . $titleSummary . '" on ' . \Carbon\Carbon::parse($request->preferred_date)->format('M d, Y') . ' at ' . $venueLabel . '. Please review and approve/reject the job posting.';
        } elseif ($request->schedule_type === 'job_fair') {
            $jobFairApproverRole = ($company->employerNsrp && $company->employerNsrp->is_overseas) ? 'sra' : 'job_vacancy';
            $staffIds = \App\Models\Staff::where('staff_role', $jobFairApproverRole)->pluck('id');
            $title    = 'New Job Fair Posting Request 🎪';
            $message  = $company->employerNsrp->company_name . ' requested to post "' . $titleSummary . '" for job fair use. Please review and approve.';
        } else {
            $staffIds = \App\Models\Staff::where('staff_role', 'job_vacancy')->pluck('id');
            $title    = 'New Job Posting Request 💼';
            $message  = $company->employerNsrp->company_name . ' requested to post "' . $titleSummary . '". Please review and approve.';
        }

        \App\Models\Announcement::sendToStaff([
            'type'           => 'job_request',
            'title'          => $title,
            'message'        => $message,
            'reference_type' => 'job',
            'reference_id'   => $firstJob->id,
        ], $staffIds);

        return redirect()->route('company.dashboard')
            ->with('success', $positionCount > 1
                ? $positionCount . ' job postings request submitted! Waiting for PESO staff approval.'
                : 'Job posting request submitted! Waiting for PESO staff approval.');
    }

    // ───────────────────────────────
    // CLEAR ALL NOTIFICATIONS
    // ───────────────────────────────
    public function clearAllNotifications()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        \App\Models\Announcement::where('employer_id', $company->employerNsrp->id)->delete();

        return back()->with('success', 'All notifications cleared.');
    }

    // ───────────────────────────────
    // MARK NOTIFICATION READ
    // ───────────────────────────────
    public function markNotificationRead($id)
    {
        $company = $this->authCompany();
        if (!$company) return response()->json(['error' => 'Unauthorized'], 401);

        \App\Models\Announcement::where('id', $id)
            ->where('employer_id', $company->employerNsrp->id)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
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
            ->where('employer_id', $company->employerNsrp->id)
            ->latest()
            ->paginate(10);

        return view('company.inhouse.index', compact('schedules'));
    }

    public function createInhouse()
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        // Check kung approved ang requirements
        $requirement = \App\Models\EmployerRequirement::where('user_id', $company->employerNsrp->id)
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
            'preferred_date'  => 'required|date|after_or_equal:today',
            'preferred_time'  => 'required',
            'num_applicants'  => 'required|integer|min:1',
            'venue_type'      => 'required|in:peso_office,office_based,custom',
            'venue_address'   => 'required_if:venue_type,custom|nullable|string|max:255',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $schedule = \App\Models\InhouseSchedule::create([
            'employer_id'    => $company->employerNsrp->id,
            'preferred_date' => $request->preferred_date,
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
            'office_based' => $company->employerNsrp->company_name . "'s Office",
            'custom'       => $request->venue_address,
        };

        $staffRole = ($company->employerNsrp && $company->employerNsrp->is_overseas) ? 'sra' : 'lra';
        $staffIds = \App\Models\Staff::where('staff_role', $staffRole)->pluck('id');

        \App\Models\Announcement::sendToStaff([
            'type'           => 'inhouse_request',
            'title'          => 'New In-house Interview Request 📅',
            'message'        => $company->employerNsrp->company_name . ' has requested an in-house interview on ' . \Carbon\Carbon::parse($request->preferred_date)->format('M d, Y') . ' at ' . \Carbon\Carbon::parse($request->preferred_time)->format('h:i A') . ' (' . $venueLabel . ').',
            'reference_type' => 'inhouse_schedule',
            'reference_id'   => $schedule->id,
        ], $staffIds);

        return redirect()->route('company.inhouse')
            ->with('success', 'In-house interview request submitted successfully!');
    }

    // ───────────────────────────────
    // JOB FAIR INVITATIONS
    // ───────────────────────────────
    public function jobFairInvitations(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');
        if ($guard = $this->requireRequirements($company)) return $guard;

        $invitations = \App\Models\JobFairParticipant::with('jobFair')
            ->where('employer_id', $company->employerNsrp->id)
            ->latest()
            ->get();

        // Get applicants kung confirmed ang employer sa bisan unsang job fair
        $isConfirmed = \App\Models\JobFairParticipant::where('employer_id', $company->employerNsrp->id)
            ->where('confirmation_status', 'confirmed')
            ->exists();

        // ── Actions (Hired/Waiting/Rejected) naka-lock hangtod moabot ang event date sa bisan unsang confirmed job fair ──
        $earliestConfirmedEventDate = \App\Models\JobFairParticipant::with('jobFair')
            ->where('employer_id', $company->employerNsrp->id)
            ->where('confirmation_status', 'confirmed')
            ->get()
            ->pluck('jobFair.event_date')
            ->filter()
            ->sort()
            ->first();

        $actionsUnlocked = $earliestConfirmedEventDate
            ? now()->toDateString() >= \Carbon\Carbon::parse($earliestConfirmedEventDate)->toDateString()
            : false;

        $search = $request->input('search');

        $applicants = collect();
        if ($isConfirmed) {
            $applicants = \App\Models\Application::with(['jobseeker', 'jobseeker.nsrp', 'jobseeker.user', 'job'])
                ->whereHas('job', fn($q) => $q->where('company_id', $company->employerNsrp->id))
                ->whereIn('status', ['pending', 'reviewed', 'qualified', 'waiting', 'hired', 'rejected'])
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->whereHas('jobseeker', function ($jq) use ($search) {
                            $jq->where('first_name', 'like', "%{$search}%")
                               ->orWhere('surname', 'like', "%{$search}%");
                        })->orWhereHas('job', function ($jq) use ($search) {
                            $jq->where('title', 'like', "%{$search}%");
                        });
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString();
        }

        return view('company.jobfair.index', compact('invitations', 'applicants', 'isConfirmed', 'search', 'actionsUnlocked', 'earliestConfirmedEventDate'));
    }

    public function respondJobFair(Request $request, $id)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $request->validate([
            'response' => 'required|in:confirmed,declined',
        ]);

        $participant = \App\Models\JobFairParticipant::where('id', $id)
            ->where('employer_id', $company->employerNsrp->id)
            ->firstOrFail();

        $participant->update(['confirmation_status' => $request->response]);

        if ($request->response === 'confirmed') {
            $userIds = \App\Models\Application::whereHas('job', fn($q) =>
                $q->where('company_id', $company->employerNsrp->id)
            )->pluck('jobseeker_id')->unique();

            \App\Models\Announcement::sendToJobseekers([
                'type'           => 'job_fair_invitation',
                'title'          => 'Job Fair Invitation 🎉',
                'message'        => $company->employerNsrp->company_name . ' has confirmed participation in ' . $participant->jobFair->title . ' on ' . $participant->jobFair->event_date->format('M d, Y') . ' at ' . $participant->jobFair->venue . '. You are invited to attend!',
                'reference_type' => 'job_fair',
                'reference_id'   => $participant->job_fair_id,
            ], $userIds);
        }

        $msg = $request->response === 'confirmed'
            ? 'You have confirmed the job fair invitation! Please select which job posts you will bring to this event.'
            : 'You have declined the job fair invitation.';

        if ($request->response === 'confirmed') {
            return redirect()->route('company.jobfair.selectJobs', $participant->job_fair_id)
                ->with('success', $msg);
        }

        return back()->with('success', $msg);
    }

    // ───────────────────────────────
    // JOB FAIR — SELECT JOBS TO BRING
    // ───────────────────────────────
    public function showJobFairJobSelect($jobFairId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $jobFair = \App\Models\JobFairEvent::findOrFail($jobFairId);

        $myJobs = \App\Models\Job::where('company_id', $company->employerNsrp->id)
            ->where('status', 'open')
            ->get();

        $selectedJobIds = \App\Models\JobFairEmploymentRequest::where('job_fair_id', $jobFairId)
            ->where('employer_id', $company->employerNsrp->id)
            ->pluck('job_id')
            ->toArray();

        return view('company.jobfair.select_jobs', compact('jobFair', 'myJobs', 'selectedJobIds'));
    }

    public function storeJobFairJobSelect(Request $request, $jobFairId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $request->validate([
            'job_ids'   => 'nullable|array',
            'job_ids.*' => 'exists:job_qualifications,id',
        ]);

        $employerId = $company->employerNsrp->id;

        \App\Models\JobFairEmploymentRequest::where('job_fair_id', $jobFairId)
            ->where('employer_id', $employerId)
            ->whereNotIn('job_id', $request->job_ids ?? [])
            ->delete();

        foreach (($request->job_ids ?? []) as $jobId) {
            \App\Models\JobFairEmploymentRequest::firstOrCreate([
                'job_fair_id' => $jobFairId,
                'employer_id' => $employerId,
                'job_id'      => $jobId,
            ]);
        }

        return redirect()->route('company.jobfair')
            ->with('success', 'Job offers updated for this job fair!');
    }

    // ───────────────────────────────
    // QUALIFIED APPLICANTS — per job (Company)
    // ───────────────────────────────
    public function qualifiedApplicants($jobId)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('login');

        $job = \App\Models\Job::where('id', $jobId)
            ->where('company_id', $company->employerNsrp->id)
            ->firstOrFail();

        $applicants = \App\Models\Application::with(['jobseeker', 'jobseeker.nsrp'])
            ->where('job_id', $jobId)
            ->orderByDesc('match_percentage')
            ->get();

        $isInhouse = $job->schedule_type === 'inhouse';
        $isOfficeBased = $job->schedule_type === 'office_based';

        // ── Para sa In-house nga jobs, ang Qualified/Highly Qualified/Not Qualified tabs — ONLY kadtong ni-ACCEPT sa participation ──
        // ── Para sa Office Based, parehas nga rule gamit ang office_participation — kadtong nag-DECLINE (No) magpabilin ra sa All Jobseekers tab, walay action buttons ──
        // ── Para sa Job Fair, walay restriction — parehas ra sa daan (dayon classified base sa match%) ──
        if ($isInhouse) {
            $eligibleForTabs = $applicants->where('inhouse_participation', 'accepted');
        } elseif ($isOfficeBased) {
            $eligibleForTabs = $applicants->where('office_participation', 'accepted');
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

        // ── Hire/Reject/Waiting actions naka-lock hangtod moabot ang In-house preferred_date; para sa Office Based, naka-lock hangtod mo-accept ang jobseeker ──
        $actionsUnlocked = !$isInhouse || ($job->preferred_date && now()->toDateString() >= \Carbon\Carbon::parse($job->preferred_date)->toDateString());

        return view('company.jobs.qualified', compact(
            'job', 'applicants', 'company', 'isInhouse', 'isOfficeBased', 'actionsUnlocked',
            'highlyQualified', 'qualified', 'notQualified',
            'totalAll', 'totalHighly', 'totalQualified', 'totalNotQualified'
        ));
    }

    // ───────────────────────────────
    // CHANGE PASSWORD
    // ───────────────────────────────
    public function changePassword(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
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
        if (!$company) return redirect()->route('company.login');
        if ($guard = $this->requireRequirements($company)) return $guard;

        $search = $request->input('search');

        // ── Open ra (na-approve na sa PESO staff) ang makita diri ──
        $jobs = Job::where('company_id', $company->employerNsrp->id)
            ->where('status', 'open')
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('company.jobseeker.index', compact('jobs', 'search'));
    }

    // ───────────────────────────────
    // REPORTS — Hired applicants only
    // ───────────────────────────────
    public function reports(Request $request)
    {
        $company = $this->authCompany();
        if (!$company) return redirect()->route('company.login');
        if ($guard = $this->requireRequirements($company)) return $guard;

        $search = $request->input('search');

        $hired = Application::with(['jobseeker.user', 'job'])
            ->whereHas('job', fn($q) => $q->where('company_id', $company->employerNsrp->id))
            ->where('status', 'hired')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('jobseeker', function ($jq) use ($search) {
                        $jq->where('first_name', 'like', "%{$search}%")
                           ->orWhere('surname', 'like', "%{$search}%")
                           ->orWhereHas('user', fn($uq) => $uq->where('email', 'like', "%{$search}%"));
                    })->orWhereHas('job', function ($jq) use ($search) {
                        $jq->where('title', 'like', "%{$search}%");
                    });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('company.reports.index', compact('hired', 'search'));
    }
}