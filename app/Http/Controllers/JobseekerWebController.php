<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\JobseekerRegistration;
use App\Models\JobseekerNsrpRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class JobseekerWebController extends Controller
{
    // ── Helper ──
    private function authJobseeker()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'jobseeker') return null;
        return $user;
    }

    // ── NSRP Guard — redirect to NSRP if not yet submitted ──
    private function requireNsrp($jobseeker)
    {
        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();
        if (!$registration || !$registration->nsrp) {
            return redirect()->route('jobseeker.nsrp')
                ->with('info', 'Please complete your NSRP Registration Form first before accessing other features.');
        }
        return null;
    }

    // ───────────────────────────────
    // DASHBOARD
    // ───────────────────────────────
    public function dashboard()
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $registration = JobseekerRegistration::with('nsrp')->where('user_id', $jobseeker->users_id)->first();
        $nsrp = $registration->nsrp ?? null;

        $regId = $registration->jobseeker_registrations_id ?? null;

        $totalApplications   = $regId ? Application::where('jobseeker_id', $regId)->count() : 0;
        $pendingApplications = $regId ? Application::where('jobseeker_id', $regId)->where('status', 'pending')->count() : 0;
        $hiredApplications   = $regId ? Application::where('jobseeker_id', $regId)->where('status', 'hired')->count() : 0;

        $todayJobFairs = \App\Models\JobFairEvent::whereDate('event_date', today())
            ->where('status', '!=', 'completed')
            ->get();

        $todayInhouse = \App\Models\InhouseSchedule::whereDate('confirmed_date', today())
            ->where('status', 'accepted')
            ->get();

        // ── HIGHLY QUALIFIED MATCH — preferred occupation MATCHED + overall match score ≥75% ──
        $highlyQualifiedMatch = null;
        $preferredOccupations = $nsrp->preferred_occupations ?? [];
        if (!empty($preferredOccupations) && $regId) {
            $openJobs = Job::with('company')->where('status', 'open')
                ->where(function ($q) {
                    $q->whereNull('deadline')->orWhereDate('deadline', '>=', now()->toDateString());
                })->get();

            $appliedJobIds = Application::where('jobseeker_id', $regId)->pluck('job_id')->toArray();
            $shownJobIds   = session('highly_qualified_shown', []);

            $appController = new ApplicationController();
            $bestMatch = null;
            $bestPercentage = 0;

            foreach ($openJobs as $job) {
                if (in_array($job->job_qualifications_id, $appliedJobIds) || in_array($job->job_qualifications_id, $shownJobIds)) continue;

                // Preferred occupation check una — dili gyud i-consider kung wala match sa preferred occupation
                $occMatched = false;
                foreach ($preferredOccupations as $occ) {
                    if (strtolower(trim($occ)) === strtolower(trim($job->title))) {
                        $occMatched = true;
                        break;
                    }
                }
                if (!$occMatched) continue;

                $breakdown = $appController->computeMatchBreakdownPublic($jobseeker->users_id, $job);
                if ($breakdown['percentage'] >= 75 && $breakdown['percentage'] > $bestPercentage) {
                    $bestPercentage = $breakdown['percentage'];
                    $bestMatch = $job;
                }
            }

            if ($bestMatch) {
                $highlyQualifiedMatch = [
                    'job'        => $bestMatch,
                    'percentage' => $bestPercentage,
                ];
                session(['highly_qualified_shown' => array_unique(array_merge($shownJobIds, [$bestMatch->job_qualifications_id]))]);
            }
        }

        return view('jobseeker.dashboard', compact(
            'jobseeker', 'registration', 'nsrp',
            'totalApplications', 'pendingApplications', 'hiredApplications',
            'todayJobFairs', 'todayInhouse', 'highlyQualifiedMatch'
        ));
    }

    // ───────────────────────────────
    // NSRP FORM
    // ───────────────────────────────
    public function nsrp()
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $registration = JobseekerRegistration::with('nsrp.workExperiences')
    ->where('user_id', $jobseeker->users_id)->first();
$nsrp = $registration->nsrp ?? null;

return view('jobseeker.nsrp.index', compact('jobseeker', 'registration', 'nsrp'));
    }

    // ───────────────────────────────
    // NSRP OCR SCAN — Auto-fill from uploaded photo
    // ───────────────────────────────
    public function nsrpScan(Request $request)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return response()->json(['error' => 'Unauthorized'], 401);

        $request->validate([
            'nsrp_image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $image    = $request->file('nsrp_image');
        $path     = $image->store('nsrp_scans', 'public');
        $fullPath = storage_path('app/public/' . $path);

        try {
            $text = (new \thiagoalessio\TesseractOCR\TesseractOCR($fullPath))->run();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'OCR processing failed. Please fill out the form manually. (' . $e->getMessage() . ')',
            ], 500);
        }

        $parsed = $this->parseNsrpText($text);

        return response()->json([
            'success'  => true,
            'raw_text' => $text, // TEMPORARY — for debugging/tuning patterns. Remove once accuracy is confirmed.
            'data'     => $parsed,
        ]);
    }

    // ── Helper: parse raw OCR text into structured fields (basic label-matching) ──
    private function parseNsrpText($text)
    {
        $data = [];

        $patterns = [
            'surname'        => '/Surname\s*[:\-]?\s*([A-Za-z\s]+)/i',
            'first_name'     => '/First\s*Name\s*[:\-]?\s*([A-Za-z\s]+)/i',
            'middle_name'    => '/Middle\s*Name\s*[:\-]?\s*([A-Za-z\s]+)/i',
            'contact_number' => '/Contact\s*Number\/?s?\s*[:\-]?\s*([0-9\-\s]+)/i',
            'religion'       => '/Religion\s*[:\-]?\s*([A-Za-z\s]+)/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $data[$key] = trim(preg_replace('/\s+/', ' ', $matches[1]));
            }
        }

        return $data;
    }

    public function nsrpStore(Request $request)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

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
            'contact_number'    => 'required|string|max:20',
            'classification_type' => 'required|in:local,overseas,both',
            'employment_type'   => 'required|string',
            'work_type'         => 'required|string',
            'preferred_occupations.0' => 'required|string',
            'certification_date'      => 'required|string',
            'certification_agreed'       => 'required',
            'training_certificates.*'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // ══════════════════════════════════════════
        // PART 1: Personal Info → jobseeker_registrations
        // ══════════════════════════════════════════
        $registrationData = [
            'user_id'           => $jobseeker->users_id,
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

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();
        if ($registration) {
            $registration->update($registrationData);
        } else {
            $registration = JobseekerRegistration::create($registrationData);
        }

        // ══════════════════════════════════════════
        // PART 2: NSRP Form Data → jobseeker_nsrp_registrations
        // ══════════════════════════════════════════
        $nsrpData = [
            'jobseeker_registration_id' => $registration->jobseeker_registrations_id,
            'type'               => $request->classification_type ?? 'local',
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

        // Handle certificate file uploads
        $nsrp = JobseekerNsrpRegistration::where('jobseeker_registration_id', $registration->jobseeker_registrations_id)->first();

        $existingCerts = [];
        if ($nsrp && $nsrp->training_certificates) {
            $existingCerts = is_array($nsrp->training_certificates)
                ? $nsrp->training_certificates
                : json_decode($nsrp->training_certificates, true);
        }

        $uploadedCerts = $existingCerts ?? [];
        if ($request->hasFile('training_certificates')) {
            foreach ($request->file('training_certificates') as $idx => $file) {
                if ($file && $file->isValid()) {
                    if (!empty($uploadedCerts[$idx])) {
                        \Illuminate\Support\Facades\Storage::delete($uploadedCerts[$idx]);
                    }
                    $path = $file->store('certificates', 'public');
                    $uploadedCerts[$idx] = $path;
                }
            }
        }
        $nsrpData['training_certificates'] = $uploadedCerts;

        $isNewSubmission = !$nsrp; // true kung una pa ni siya nga submission (dili edit/update)

        if ($nsrp) {
            $nsrp->update($nsrpData);
        } else {
            $nsrp = JobseekerNsrpRegistration::create($nsrpData);
        }

        // ══════════════════════════════════════════
// PART 3: Work Experiences → jobseeker_work_experiences (naka-link na sa NSRP Form)
// ══════════════════════════════════════════
\App\Models\JobseekerWorkExperience::where(
    'jobseeker_nsrp_registration_id', $nsrp->jobseeker_nsrp_registrations_id
)->delete();

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
        // PART 4: Certifications → jobseeker_certifications
        // ══════════════════════════════════════════
        \App\Models\JobseekerCertification::where(
            'jobseeker_nsrp_registration_id', $nsrp->jobseeker_nsrp_registrations_id
        )->delete();

        // ── Helper: safely parse a date string, return null kung dili valid ──
        $safeParseDate = function ($value) {
            if (empty($value)) return null;
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null; // dili ma-parse (e.g. "08/2024" walay adlaw) → null na lang, dili mo-crash
            }
        };

        $eligibilities = $request->eligibilities ?? [];
        foreach ($eligibilities as $e) {
            if (!empty($e['name'])) {
                \App\Models\JobseekerCertification::create([
                    'jobseeker_nsrp_registration_id' => $nsrp->jobseeker_nsrp_registrations_id,
                    'category'    => 'eligibility',
                    'name'        => $e['name'],
                    'date_taken'  => $safeParseDate($e['date_taken'] ?? null),
                ]);
            }
        }

        $licenses = $request->licenses ?? [];
        foreach ($licenses as $l) {
            if (!empty($l['name'])) {
                \App\Models\JobseekerCertification::create([
                    'jobseeker_nsrp_registration_id' => $nsrp->jobseeker_nsrp_registrations_id,
                    'category'     => 'license',
                    'name'         => $l['name'],
                    'valid_until'  => $safeParseDate($l['valid_until'] ?? null),
                ]);
            }
        }

        // ── Notify LRA/SRA staff kung bag-ong submission (dili edit/update) ──
        if ($isNewSubmission) {
            $rolesToNotify = [];
            if (in_array($nsrp->type, ['local', 'both']))    $rolesToNotify[] = 'lra';
            if (in_array($nsrp->type, ['overseas', 'both'])) $rolesToNotify[] = 'sra';

            if (!empty($rolesToNotify)) {
                $staffIds = \App\Models\Staff::whereIn('staff_role', $rolesToNotify)->pluck('staff_id');

                $jobseekerName = trim(($registration->first_name ?? '') . ' ' . ($registration->surname ?? ''));

                \App\Models\Announcement::sendToStaff([
                    'type'           => 'nsrp_submitted',
                    'title'          => 'New Jobseeker Registration 📋',
                    'message'        => ($jobseekerName ?: 'A jobseeker') . ' has submitted their NSRP registration form.',
                    'reference_type' => 'jobseeker_registration',
                    'reference_id'   => $registration->jobseeker_registrations_id,
                ], $staffIds);
            }
        }

        return redirect()->route('jobseeker.nsrp')
            ->with('success', 'NSRP form submitted successfully!');
    }

    // ───────────────────────────────
    // JOB VACANCIES — LIST
    // ───────────────────────────────
    public function jobs(Request $request)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $jobType = $request->input('job_type', 'all'); // all | local | overseas | job_fair

        $query = Job::with('company')->where('status', 'open')
            ->where(function ($q) {
                $q->whereNull('deadline')->orWhereDate('deadline', '>=', now()->toDateString());
            })
            ->when($jobType === 'local', fn($q) => $q->whereHas('company', fn($c) => $c->where('is_overseas', false)))
            ->when($jobType === 'overseas', fn($q) => $q->whereHas('company', fn($c) => $c->where('is_overseas', true)))
            ->when($jobType === 'job_fair', fn($q) => $q->where('schedule_type', 'job_fair'));

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $jobs = $query->latest()->paginate(4)->withQueryString();

        $registration = \App\Models\JobseekerRegistration::with('nsrp')->where('user_id', $jobseeker->users_id)->first();
        $preferredOccupations = $registration->nsrp->preferred_occupations ?? [];

        return view('jobseeker.jobs.index', compact('jobseeker', 'jobs', 'jobType', 'preferredOccupations'));
    }

    // ───────────────────────────────
    // JOB VACANCIES — SHOW
    // ───────────────────────────────
    public function showJob($id)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $job  = Job::with('company')->findOrFail($id);
        $registration = JobseekerRegistration::with('nsrp')->where('user_id', $jobseeker->users_id)->first();
        $nsrp = $registration->nsrp ?? null;

        $application    = $registration ? Application::where('jobseeker_id', $registration->jobseeker_registrations_id)->where('job_id', $id)->first() : null;
        $alreadyApplied = $application !== null;

        // Compute match percentage + breakdown
        $matchPercentage = null;
        $matchCriteria   = [];
        if ($nsrp) {
            $appController = new ApplicationController();
            $breakdown       = $appController->computeMatchBreakdownPublic($jobseeker->users_id, $job);
            $matchPercentage = $breakdown['percentage'];
            $matchCriteria   = $breakdown['criteria'];
        }

        // ── In-house: auto-prompt kung ≤5 days na lang ang nabilin sa preferred_date ug wala pa nagrespond ──
        $showInhouseParticipationPrompt = false;
        if ($application && $job->schedule_type === 'inhouse' && $application->inhouse_participation === 'pending' && $job->preferred_date) {
            $daysUntil = now()->diffInDays(\Carbon\Carbon::parse($job->preferred_date), false);
            $showInhouseParticipationPrompt = $daysUntil >= 0 && $daysUntil <= 5;
        }

        return view('jobseeker.jobs.show', compact(
            'jobseeker', 'job', 'registration', 'nsrp',
            'alreadyApplied', 'application', 'matchPercentage', 'matchCriteria',
            'showInhouseParticipationPrompt'
        ));
    }

    // ───────────────────────────────
    // APPLICATIONS
    // ───────────────────────────────
    public function applications()
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');
        if ($guard = $this->requireNsrp($jobseeker)) return $guard;

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();

        $applications = Application::with('job.company')
            ->where('jobseeker_id', $registration->jobseeker_registrations_id ?? 0)
            ->latest()
            ->get();

        return view('jobseeker.applications.index', compact('jobseeker', 'applications'));
    }

    // ───────────────────────────────
    // PROFILE
    // ───────────────────────────────
    public function showProfile()
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        return view('jobseeker.profile', compact('jobseeker'));
    }

    public function updateProfile(Request $request)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $jobseeker->users_id . ',users_id',
        ]);

        $jobseeker->update([
            'email' => $request->email,
            'phone' => $request->phone,
            'name'  => $request->first_name . ' ' . $request->last_name,
        ]);

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();
        if ($registration) {
            $registration->update([
                'first_name'    => $request->first_name,
                'surname'       => $request->last_name,
                'middle_name'   => $request->middle_name,
                'date_of_birth' => $request->date_of_birth,
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $jobseeker->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $jobseeker->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success', 'Password changed successfully.');
    }

    // ───────────────────────────────
    // SCHEDULES
    // ───────────────────────────────
    public function schedules()
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');
        if ($guard = $this->requireNsrp($jobseeker)) return $guard;

        $type = request('type', 'inhouse');

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();

        // ── In-house interviews nga na-ACCEPT sa jobseeker (gikan sa Job-based in-house postings) ──
        $inhouseApplications = Application::with('job.company')
            ->where('jobseeker_id', $registration->jobseeker_registrations_id ?? 0)
            ->where('inhouse_participation', 'accepted')
            ->whereHas('job', function ($q) {
                $q->where('schedule_type', 'inhouse');
            })
            ->latest()
            ->get();

        $jobFairSchedules = \App\Models\JobFairEvent::with('employmentRequests.job.company')
            ->where('status', '!=', 'completed')
            ->withCount(['participants as confirmed_count' => function ($q) {
                $q->where('confirmation_status', 'confirmed');
            }])
            ->having('confirmed_count', '>=', 3)
            ->latest()
            ->get();

        $joinedJobFairIds = \App\Models\JobFairRegistration::where('user_id', $registration->jobseeker_registrations_id ?? 0)
            ->pluck('job_fair_id')
            ->toArray();

        return view('jobseeker.schedules.index', compact(
            'inhouseApplications', 'jobFairSchedules', 'type', 'joinedJobFairIds'
        ));
    }

    // ───────────────────────────────
    // JOIN IN-HOUSE INTERVIEW
    // ───────────────────────────────
    public function joinInhouse($id)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $schedule = \App\Models\InhouseSchedule::where('inhouse_schedules_id', $id)
            ->where('status', 'accepted')
            ->firstOrFail();

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();

        \App\Models\InhouseParticipant::firstOrCreate([
            'inhouse_schedule_id' => $schedule->inhouse_schedules_id,
            'jobseeker_id'        => $registration->jobseeker_registrations_id ?? 0,
        ], [
            'joined_at' => now(),
        ]);

        return back()->with('success', 'You have successfully joined the in-house interview!');
    }

    // ───────────────────────────────
    // JOIN JOB FAIR
    // ───────────────────────────────
    public function joinJobFair($id)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $jobFair = \App\Models\JobFairEvent::where('job_fair_events_id', $id)
            ->where('status', '!=', 'completed')
            ->firstOrFail();

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();

        $alreadyJoined = \App\Models\JobFairRegistration::where('job_fair_id', $jobFair->job_fair_events_id)
            ->where('user_id', $registration->jobseeker_registrations_id ?? 0)
            ->exists();

        if ($alreadyJoined) {
            return back()->with('info', 'You have already joined this job fair.');
        }

        $totalRegistered = \App\Models\JobFairRegistration::where('job_fair_id', $jobFair->job_fair_events_id)->count();
        $slipNumber = 'JF' . $jobFair->job_fair_events_id . '-' . str_pad($totalRegistered + 1, 4, '0', STR_PAD_LEFT);

        $isEarly = now()->diffInDays($jobFair->event_date, false) >= 3;

        \App\Models\JobFairRegistration::create([
            'job_fair_id' => $jobFair->job_fair_events_id,
            'user_id'     => $registration->jobseeker_registrations_id ?? 0,
            'slip_number' => $slipNumber,
            'is_early'    => $isEarly,
        ]);

        return back()->with('success', 'You have successfully joined the job fair! Your slip number is ' . $slipNumber . '.');
    }

    // ───────────────────────────────
    // RESPOND TO JOB FAIR ATTENDANCE CONFIRMATION (sent on event day)
    // ───────────────────────────────
    public function respondJobFairAttendance(Request $request, $registrationId)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return response()->json(['error' => 'Unauthorized'], 401);

        $request->validate(['response' => 'required|in:yes,no']);

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();

        $reg = \App\Models\JobFairRegistration::where('job_fair_registrations_id', $registrationId)
            ->where('user_id', $registration->jobseeker_registrations_id ?? 0)
            ->firstOrFail();

        $reg->update([
            'is_attended' => $request->response === 'yes',
            'attended_at' => $request->response === 'yes' ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }

    // ───────────────────────────────
    // HISTORY — Hired applications only
    // ───────────────────────────────
    public function history(Request $request)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');
        if ($guard = $this->requireNsrp($jobseeker)) return $guard;

        $search = $request->input('search');

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();

        $hired = Application::with('job.company')
            ->where('jobseeker_id', $registration->jobseeker_registrations_id ?? 0)
            ->where('status', 'hired')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('job', function ($jq) use ($search) {
                    $jq->where('title', 'like', "%{$search}%")
                       ->orWhereHas('company', fn($c) => $c->where('company_name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('jobseeker.history.index', compact('hired', 'search'));
    }

    // ───────────────────────────────
    // NOTIFICATIONS
    // ───────────────────────────────
    public function markNotificationRead($id)
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return response()->json(['error' => 'Unauthorized'], 401);

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();
        if (!$registration) return response()->json(['error' => 'Not found'], 404);

        \App\Models\Announcement::where('id', $id)
            ->where('jobseeker_id', $registration->jobseeker_registrations_id)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead()
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return response()->json(['error' => 'Unauthorized'], 401);

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();
        if (!$registration) return response()->json(['error' => 'Not found'], 404);

        \App\Models\Announcement::where('jobseeker_id', $registration->jobseeker_registrations_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function notifications()
    {
        $jobseeker = $this->authJobseeker();
        if (!$jobseeker) return redirect()->route('login');

        $registration = JobseekerRegistration::where('user_id', $jobseeker->users_id)->first();

        $notifications = $registration
            ? \App\Models\Announcement::where('jobseeker_id', $registration->jobseeker_registrations_id)->latest()->get()
            : collect();

        return view('jobseeker.notifications.index', compact('notifications'));
    }
}