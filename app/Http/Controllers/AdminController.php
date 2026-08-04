<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JobseekerRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ───────────────────────────────
    // LOGIN (Admin) — redirect sa unified
    // ───────────────────────────────
    public function showLogin()
    {
        return redirect()->route('login');
    }

    public function login(Request $request)
    {
        return redirect()->route('login');
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
        $totalUsers        = User::whereIn('role', ['jobseeker', 'company'])->count();
        $activeUsers       = User::whereIn('role', ['jobseeker', 'company'])->where('status', 'approved')->count();
        $deactivatedUsers  = User::whereIn('role', ['jobseeker', 'company'])->where('status', 'deactivated')->count();
        $jobseekerCount    = User::where('role', 'jobseeker')->count();
        $companyCount      = User::where('role', 'company')->count();
        $registrationCount = JobseekerRegistration::count();

        $monthlyJobFairCount = \App\Models\JobFairEvent::whereMonth('event_date', now()->month)
            ->whereYear('event_date', now()->year)
            ->count();

        // Today's Activities
        $todayJobFairs = \App\Models\JobFairEvent::whereDate('event_date', today())
            ->where('status', '!=', 'completed')
            ->get();

        $todayInhouse = \App\Models\InhouseSchedule::whereDate('confirmed_date', today())
            ->where('status', 'accepted')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'deactivatedUsers',
            'jobseekerCount',
            'companyCount',
            'registrationCount',
            'monthlyJobFairCount',
            'todayJobFairs',
            'todayInhouse'
        ));
    }

    // ───────────────────────────────
    // MANAGE USERS
    // ───────────────────────────────
    public function manageUsers()
    {
        $users = User::whereIn('role', ['jobseeker', 'company', 'staff'])
                     ->with(['registration.nsrp', 'employerNsrp', 'staff'])
                     ->latest()
                     ->get();

        $occupiedStaffRoles = \App\Models\Staff::query()
            ->whereHas('user', fn($q) => $q->where('status', '!=', 'deactivated'))
            ->pluck('staff_role')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return view('admin.users.manage', compact('users', 'occupiedStaffRoles'));
    }

    // ───────────────────────────────
    // STORE STAFF USER
    // ───────────────────────────────
    public function storeUser(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:6',
            'staff_role' => 'required|in:sra,lra,job_fair,job_vacancy',
        ]);

        $newStaff = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'phone'          => $request->phone,
            'role'           => 'staff',
            'status'         => 'approved',
        ]);

        $nameParts = explode(' ', $request->name, 2);

        \App\Models\Staff::create([
            'user_id'     => $newStaff->id,
            'staff_role'  => $request->staff_role,
            'first_name'  => $nameParts[0] ?? $request->name,
            'last_name'   => $nameParts[1] ?? '',
            'middle_name' => null,
            'phone'       => $request->phone,
        ]);

        return back()->with('success', 'Staff account created successfully!');
    }

    // ───────────────────────────────
    // UPDATE USER (Status + Password)
    // ───────────────────────────────
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Admin makahilabot lang sa Staff accounts — Jobseeker/Company dili niya ma-update
        if ($user->role !== 'staff') {
            abort(403, 'Admin is not authorized to update Jobseeker or Company accounts. This is managed by PESO Staff.');
        }

        $request->validate([
            'status'           => 'required|in:approved,deactivated',
            'password'         => 'nullable|string|min:6',
            'update_staff_role'=> 'required|in:sra,lra,job_fair,job_vacancy',
        ]);

        $staffRecord = \App\Models\Staff::where('user_id', $user->id)->first();

        // Check duplicate role kung lahi ang gipili gikan sa current role
        if ($staffRecord && $staffRecord->staff_role !== $request->update_staff_role) {
            $taken = \App\Models\Staff::where('staff_role', $request->update_staff_role)
                ->where('user_id', '!=', $user->id)
                ->whereHas('user', fn($q) => $q->where('status', '!=', 'deactivated'))
                ->exists();

            if ($taken) {
                $labels = ['sra' => 'SRA', 'lra' => 'LRA', 'job_fair' => 'Job Fair', 'job_vacancy' => 'Job Vacancy'];
                return back()
                    ->withErrors(['update_staff_role' => 'Another active staff member is already assigned to the ' . ($labels[$request->update_staff_role] ?? $request->update_staff_role) . ' role. Please deactivate or change that staff member\'s role first.'])
                    ->withInput(['update_user_id' => $user->id]);
            }
        }

        $data = ['status' => $request->status];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($staffRecord) {
            $staffRecord->update(['staff_role' => $request->update_staff_role]);
        }

        return back()->with('success', $user->name . ' account updated successfully.');
    }

    // ───────────────────────────────
    // PROFILE
    // ───────────────────────────────
    public function showProfile()
    {
        $admin = Auth::user();
        return view('admin.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $admin->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        $admin = Auth::user();

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $admin->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    // ───────────────────────────────
    // REGISTERED JOBSEEKERS — LIST
    // ───────────────────────────────
    public function registrations()
{
    $search = request('search');
    $type   = request('type', 'local');

    $baseQuery = JobseekerRegistration::with(['user', 'nsrp'])
        ->when($search, function($q) use ($search) {
            $q->where(function($sub) use ($search) {
                $sub->where('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('reg_email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        });

    $totalLocal    = (clone $baseQuery)->whereHas('nsrp', function($q) {
        $q->where('type', 'local');
    })->count();
    $totalOverseas = (clone $baseQuery)->whereHas('nsrp', function($q) {
        $q->where('type', 'overseas');
    })->count();
    $totalBoth     = (clone $baseQuery)->whereHas('nsrp', function($q) {
        $q->where('type', 'both');
    })->count();

if ($type === 'local') {
    $baseQuery->whereHas('nsrp', function($q) {
        $q->where('type', 'local');
    });
} elseif ($type === 'overseas') {
    $baseQuery->whereHas('nsrp', function($q) {
        $q->where('type', 'overseas');
    });
} elseif ($type === 'both') {
    $baseQuery->whereHas('nsrp', function($q) {
        $q->where('type', 'both');
    });
}
    $registrations = $baseQuery->latest()->paginate(10);

    return view('admin.registrations.index', compact(
        'registrations', 'totalLocal', 'totalOverseas', 'totalBoth'
    ));
}

    // ───────────────────────────────
    // REGISTERED JOBSEEKERS — VIEW ONE
    // ───────────────────────────────
    public function viewRegistration($id)
{
    $registration = JobseekerRegistration::with(['user', 'nsrp.workExperiences'])->findOrFail($id);
    $nsrp = $registration->nsrp;
    return view('admin.registrations.show', compact('registration', 'nsrp'));
}

    // ───────────────────────────────
    // NOTIFICATIONS
    // ───────────────────────────────
    public function markNotificationRead($id)
    {
        \App\Models\Announcement::find($id)?->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function notifications()
    {
        $notifications = \App\Models\Announcement::latest()->get();
        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAllNotificationsRead()
    {
        \App\Models\Announcement::where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function clearAllNotifications()
    {
        \App\Models\Announcement::query()->delete();
        return back();
    }

    // ───────────────────────────────
    // MANAGE JOB ACTIVITIES
    // ───────────────────────────────
    public function jobActivities()
    {
        // Tab 1 — In-house interviews (LRA/SRA)
        $inhouseSchedules = \App\Models\InhouseSchedule::with('employer')
            ->latest()
            ->get();

        foreach ($inhouseSchedules as $schedule) {
            $schedule->job_offers = \Illuminate\Support\Facades\DB::table('inhouse_participants')
                ->where('inhouse_schedule_id', $schedule->id)
                ->whereIn('jobseeker_id', function ($q) use ($schedule) {
                    $q->select('jobseeker_id')->from('job_matching')
                      ->where('status', 'hired')
                      ->whereIn('job_id', function ($jq) use ($schedule) {
                          $jq->select('id')->from('job_qualifications')
                             ->where('company_id', $schedule->employer_id);
                      });
                })->count();
        }

        // Tab 2 — Job Solicitation (direct to company) job postings
        $classification = request('classification', 'all');

        $officeBasedJobsQuery = \App\Models\Job::with('company')
            ->where('posting_type', 'direct');

        if ($classification === 'lra') {
            $officeBasedJobsQuery->whereHas('company', fn($q) => $q->where('is_overseas', false));
        } elseif ($classification === 'sra') {
            $officeBasedJobsQuery->whereHas('company', fn($q) => $q->where('is_overseas', true));
        }

        $officeBasedJobs = $officeBasedJobsQuery->latest()->get();

        // Tab 3 — Job Fair participants
        $jobFairParticipants = \App\Models\JobFairParticipant::with(['employer', 'jobFair'])
            ->latest()
            ->get();

        // Tab 4 — Company Interview (Office Based schedule type)
        $companyInterviewJobs = \App\Models\Job::with('company')
            ->where('schedule_type', 'office_based')
            ->latest()
            ->get();

        return view('admin.job_activities.index', compact(
            'inhouseSchedules',
            'officeBasedJobs',
            'jobFairParticipants',
            'companyInterviewJobs',
            'classification'
        ));
    }

    // ───────────────────────────────
    // STAFF REPORTS (Admin master view — LRA/SRA/Job Fair)
    // ───────────────────────────────
    public function staffReports()
    {
        $staffRole = request('role', 'lra');

        if ($staffRole === 'job_fair') {
            $tab      = request('tab', 'summary');
            $eventId  = request('event_id');
            $allEvents = \App\Models\JobFairEvent::orderByDesc('event_date')->get();
            $event     = $eventId ? \App\Models\JobFairEvent::find($eventId) : null;

            $eventJobIds = $eventId
                ? \App\Models\JobFairEmploymentRequest::where('job_fair_id', $eventId)->pluck('job_id')
                : collect();

            $attendanceFilter = request('attendance_filter', 'all');
            $registrations = null;
            $totalRegistered = $totalAttended = $totalLocalAttendance = $totalOverseasAttendance = 0;

            if ($tab === 'attendance' && $eventId) {
                $regQuery = \App\Models\JobFairRegistration::with(['jobseeker.nsrp', 'jobseeker.user'])
                    ->where('job_fair_id', $eventId)
                    ->when($attendanceFilter !== 'all', fn($q) =>
                        $q->whereHas('jobseeker.nsrp', fn($n) => $n->whereIn('type', [$attendanceFilter, 'both']))
                    );
                $registrations = $regQuery->latest()->paginate(10);

                $baseQ = \App\Models\JobFairRegistration::where('job_fair_id', $eventId);
                $totalRegistered = (clone $baseQ)->count();
                $totalAttended   = (clone $baseQ)->where('is_attended', true)->count();
                $totalLocalAttendance    = (clone $baseQ)->whereHas('jobseeker.nsrp', fn($n) => $n->whereIn('type', ['local','both']))->count();
                $totalOverseasAttendance = (clone $baseQ)->whereHas('jobseeker.nsrp', fn($n) => $n->whereIn('type', ['overseas','both']))->count();
            }

            $companiesLocal    = collect();
            $companiesOverseas = collect();

            if ($tab === 'companies' && $eventId) {
                $confirmed = \App\Models\JobFairParticipant::with('employer')
                    ->where('job_fair_id', $eventId)
                    ->where('confirmation_status', 'confirmed')
                    ->get();

                $companiesLocal    = $confirmed->filter(fn($p) => !($p->employer->is_overseas ?? false))->values();
                $companiesOverseas = $confirmed->filter(fn($p) => $p->employer->is_overseas ?? false)->values();
            }

            $furtherInterview = null;
            if ($tab === 'further_interview' && $eventId) {
                $furtherInterview = \App\Models\Application::with(['jobseeker.nsrp', 'job.company'])
                    ->whereIn('job_id', $eventJobIds)
                    ->where('status', 'waiting')
                    ->latest()
                    ->paginate(10);
            }

            $hots = null;
            if ($tab === 'hots' && $eventId && $event) {
                $hots = \App\Models\Application::with(['jobseeker', 'job.company'])
                    ->whereIn('job_id', $eventJobIds)
                    ->where('status', 'hired')
                    ->whereDate('updated_at', $event->event_date)
                    ->latest()
                    ->paginate(10);
            }

            $summaryParticipants = collect();
            $summaryTotals = ['vacancies' => 0, 'interviewed' => 0, 'male' => 0, 'female' => 0, 'qualified' => 0, 'hired' => 0];
            $topEmployersFilter = request('top_employers_filter', 'monthly');
            $topEmployersMonth = request('top_employers_month');
            $topEmployersYear = request('top_employers_year');

            if ($tab === 'summary' && $eventId) {
                $participants = \App\Models\JobFairParticipant::with('employer')
                    ->where('job_fair_id', $eventId)
                    ->where('confirmation_status', 'confirmed')
                    ->get();

                $summaryParticipants = $participants->map(function ($p) use ($eventId) {
                    $jobIds = \App\Models\JobFairEmploymentRequest::where('job_fair_id', $eventId)
                        ->where('employer_id', $p->employer_id)
                        ->pluck('job_id');

                    $apps = \App\Models\Application::with('jobseeker')->whereIn('job_id', $jobIds)->get();

                    $p->vacancies   = \App\Models\Job::whereIn('id', $jobIds)->sum('slots');
                    $p->interviewed = $apps->count();
                    $p->male        = $apps->filter(fn($a) => strtolower($a->jobseeker->sex ?? '') === 'male')->count();
                    $p->female      = $apps->filter(fn($a) => strtolower($a->jobseeker->sex ?? '') === 'female')->count();
                    $p->qualified   = $apps->where('status', 'qualified')->count();
                    $p->hired       = $apps->where('status', 'hired')->count();
                    return $p;
                });

                foreach (['vacancies', 'interviewed', 'male', 'female', 'qualified', 'hired'] as $key) {
                    $summaryTotals[$key] = $summaryParticipants->sum($key);
                }
            }

            $industryLocal    = collect();
            $industryOverseas = collect();

            if ($tab === 'industry' && $eventId) {
                $jobs = \App\Models\Job::with('company')->whereIn('id', $eventJobIds)->get();

                $industryLocal = $jobs->filter(fn($j) => !($j->company->is_overseas ?? false))
                    ->groupBy('industry_group')
                    ->map(fn($group) => $group->sum('slots'));

                $industryOverseas = $jobs->filter(fn($j) => $j->company->is_overseas ?? false)
                    ->groupBy('industry_group')
                    ->map(fn($group) => $group->sum('slots'));
            }

            $topEmployersByInhouseInterviews = collect();
            if ($tab === 'top_employers' && $eventId) {
                $topEmployersQuery = \App\Models\JobFairParticipant::with('employer')
                    ->where('job_fair_id', $eventId)
                    ->where('confirmation_status', 'confirmed');

                if ($topEmployersFilter === 'yearly') {
                    $selectedYear = $topEmployersYear ?: now()->year;
                    $topEmployersQuery->whereYear('created_at', $selectedYear);
                } else {
                    $selectedMonth = $topEmployersMonth ?: now()->format('Y-m');
                    [$year, $month] = array_pad(explode('-', $selectedMonth), 2, now()->month);
                    $topEmployersQuery->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month);
                }

                $topEmployersByInhouseInterviews = $topEmployersQuery->get()
                    ->groupBy('employer_id')
                    ->map(function ($participants) {
                        return [
                            'employer' => $participants->first()->employer,
                            'participation_count' => $participants->count(),
                        ];
                    })
                    ->sortByDesc('participation_count')
                    ->take(5)
                    ->values();
            }

            $placementReport = null;
            if ($tab === 'placement' && $eventId && $event) {
                $placementReport = \App\Models\Application::with(['jobseeker', 'job.company'])
                    ->whereIn('job_id', $eventJobIds)
                    ->where('status', 'hired')
                    ->whereHas('job.company', fn($q) => $q->where('is_overseas', false))
                    ->whereDate('updated_at', '>', $event->event_date)
                    ->latest()
                    ->paginate(15);
            }

            return view('staff.reports.index', compact(
                'staffRole', 'tab', 'allEvents', 'event', 'eventId',
                'registrations', 'attendanceFilter', 'totalRegistered', 'totalAttended',
                'totalLocalAttendance', 'totalOverseasAttendance',
                'companiesLocal', 'companiesOverseas',
                'furtherInterview', 'hots',
                'summaryParticipants', 'summaryTotals',
                'industryLocal', 'industryOverseas',
                'placementReport', 'topEmployersByInhouseInterviews',
                'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear'
            ) + ['layout' => 'admin.layouts.app', 'reportRouteName' => 'admin.reports.staff']);
        }

        $search      = request('search');
        $tab         = request('tab', 'registered');
        $jobseekerType = $staffRole === 'lra' ? ['local', 'both'] : ['overseas', 'both'];
        $isOverseas    = $staffRole === 'sra';
        $topEmployersFilter = request('top_employers_filter', 'monthly');
        $topEmployersMonth = request('top_employers_month');
        $topEmployersYear = request('top_employers_year');

        $topEmployersQuery = \App\Models\InhouseSchedule::with('employer')
            ->where('status', 'accepted')
            ->whereHas('employer', fn($q) => $q->where('is_overseas', $isOverseas));

        if ($topEmployersFilter === 'yearly') {
            if ($topEmployersYear) {
                $topEmployersQuery->where(function ($q) use ($topEmployersYear) {
                    $q->where(function ($inner) use ($topEmployersYear) {
                        $inner->whereNotNull('confirmed_date')->whereYear('confirmed_date', $topEmployersYear);
                    })->orWhere(function ($inner) use ($topEmployersYear) {
                        $inner->whereNull('confirmed_date')->whereYear('preferred_date', $topEmployersYear);
                    });
                });
            } else {
                $topEmployersQuery->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->whereNotNull('confirmed_date')->whereYear('confirmed_date', now()->year);
                    })->orWhere(function ($inner) {
                        $inner->whereNull('confirmed_date')->whereYear('preferred_date', now()->year);
                    });
                });
            }
        } else {
            $selectedMonth = $topEmployersMonth ?: now()->format('Y-m');
            [$year, $month] = array_pad(explode('-', $selectedMonth), 2, now()->month);
            $topEmployersQuery->where(function ($q) use ($year, $month) {
                $q->where(function ($inner) use ($year, $month) {
                    $inner->whereNotNull('confirmed_date')
                        ->whereYear('confirmed_date', $year)
                        ->whereMonth('confirmed_date', $month);
                })->orWhere(function ($inner) use ($year, $month) {
                    $inner->whereNull('confirmed_date')
                        ->whereYear('preferred_date', $year)
                        ->whereMonth('preferred_date', $month);
                });
            });
        }

        $topEmployersByInhouseInterviews = $topEmployersQuery
            ->get()
            ->groupBy('employer_id')
            ->map(function ($schedules) {
                return [
                    'employer' => $schedules->first()->employer,
                    'interview_count' => $schedules->count(),
                ];
            })
            ->sortByDesc('interview_count')
            ->take(5)
            ->values();

        $solicitationStats = [
            'lra' => \App\Models\Job::where('posting_type', 'direct')
                ->whereHas('company', fn($q) => $q->where('is_overseas', false))
                ->count(),
            'sra' => \App\Models\Job::where('posting_type', 'direct')
                ->whereHas('company', fn($q) => $q->where('is_overseas', true))
                ->count(),
            'overall' => \App\Models\Job::where('posting_type', 'direct')->count(),
        ];

        $registeredQuery = \App\Models\InhouseParticipant::with(['jobseeker', 'schedule.employer'])
            ->whereHas('schedule.employer', fn($q) =>
                $q->where('is_overseas', $isOverseas)
            )
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            ));

        $totalRegistered = (clone $registeredQuery)->count();

        $placedQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->where('status', 'hired')
            ->whereHas('jobseeker.nsrp', fn($r) => $r->whereIn('type', $jobseekerType))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            ));

        $totalPlaced = (clone $placedQuery)->count();

        $referredQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->whereIn('status', ['waiting', 'rejected'])
            ->whereHas('jobseeker.nsrp', fn($r) => $r->whereIn('type', $jobseekerType))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            ));

        $totalReferred = (clone $referredQuery)->count();

        $registeredParticipants = $tab === 'registered' ? $registeredQuery->latest()->paginate(10) : null;
        $placedApplications     = $tab === 'placed'     ? $placedQuery->latest()->paginate(10)     : null;
        $referredApplications   = $tab === 'referred'   ? $referredQuery->latest()->paginate(10)   : null;

        return view('staff.reports.index', compact(
            'staffRole', 'tab',
            'registeredParticipants', 'placedApplications', 'referredApplications',
            'totalRegistered', 'totalPlaced', 'totalReferred', 'solicitationStats',
            'topEmployersByInhouseInterviews', 'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear'
        ) + ['layout' => 'admin.layouts.app', 'reportRouteName' => 'admin.reports.staff']);
    }

    // ───────────────────────────────
    // STAFF REPORTS — JOB VACANCY (Admin master view)
    // ───────────────────────────────
    public function staffJobVacancyReports()
    {
        $search = request('search');
        $tab    = request('tab', 'vacancies');
        $month  = request('month', now()->format('Y-m'));
        $topEmployersFilter = request('top_employers_filter', 'monthly');
        $topEmployersMonth = request('top_employers_month');
        $topEmployersYear = request('top_employers_year');

        [$year, $mon] = explode('-', $month);

        $query = \App\Models\Job::with('company')
            ->where('posting_status', 'approved')
            ->whereHas('company', fn($q) => $q->where('is_overseas', false))
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $mon)
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('company', fn($u) => $u->where('company_name', 'like', "%{$search}%"))
            );

        $jobs         = $query->orderBy('title')->get();
        $totalVacancies = $jobs->sum('slots');

        $topEmployersByOfficeBasedInterviews = collect();
        if ($tab === 'top_employers') {
            $officeBasedQuery = \App\Models\Job::with('company')
                ->where('schedule_type', 'office_based')
                ->where('posting_status', 'approved')
                ->whereHas('company', fn($q) => $q->where('is_overseas', false));

            if ($topEmployersFilter === 'yearly') {
                $selectedYear = $topEmployersYear ?: now()->year;
                $officeBasedQuery->whereYear('updated_at', $selectedYear);
            } else {
                $selectedMonth = $topEmployersMonth ?: now()->format('Y-m');
                [$selectedYear, $selectedMonth] = array_pad(explode('-', $selectedMonth), 2, now()->month);
                $officeBasedQuery->whereYear('updated_at', $selectedYear)
                    ->whereMonth('updated_at', $selectedMonth);
            }

            $topEmployersByOfficeBasedInterviews = $officeBasedQuery->get()
                ->groupBy('company_id')
                ->map(function ($jobs) {
                    return [
                        'employer' => $jobs->first()->company,
                        'participation_count' => $jobs->count(),
                    ];
                })
                ->sortByDesc('participation_count')
                ->take(5)
                ->values();
        }

        return view('staff.job_vacancy.reports', compact(
            'jobs', 'month', 'totalVacancies', 'search', 'tab',
            'topEmployersByOfficeBasedInterviews', 'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear'
        ) + ['layout' => 'admin.layouts.app', 'reportRouteName' => 'admin.reports.staffJobVacancy']);
    }

    // ───────────────────────────────
    // REPORTS
    // ───────────────────────────────
    public function reports()
    {
        return redirect()->route('admin.reports.staff', ['role' => 'lra']);
    }
}