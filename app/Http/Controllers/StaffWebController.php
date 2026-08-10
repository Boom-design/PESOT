<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\JobseekerRegistration;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
    $query = JobseekerRegistration::with(['user', 'nsrp'])
        ->whereHas('nsrp', function($q) {
            $q->whereIn('type', ['overseas', 'both']);
        });
            $total  = $query->count();
            $recent = $query->latest()->take(5)->get();
            return view('staff.sra.dashboard', compact(
                'total', 'recent', 'todayJobFair', 'todayInhouse'
            ));

        } elseif ($staffRole === 'lra') {
    $query = JobseekerRegistration::with(['user', 'nsrp'])
        ->whereHas('nsrp', function($q) {
            $q->whereIn('type', ['local', 'both']);
        });
            $total  = $query->count();
            $recent = $query->latest()->take(5)->get();
            return view('staff.lra.dashboard', compact(
                'total', 'recent', 'todayJobFair', 'todayInhouse'
            ));

        } elseif ($staffRole === 'job_fair') {
            $totalSent    = \App\Models\Announcement::where('type', 'job_fair')->count();
            $recentNotifs = \App\Models\Announcement::where('type', 'job_fair')->latest()->take(5)->get();
            return view('staff.job_fair.dashboard', compact(
                'totalSent', 'recentNotifs', 'todayJobFair', 'todayInhouse'
            ));

        } elseif ($staffRole === 'job_vacancy') {
            $pendingCount  = \App\Models\EmployerRequirement::where('status', 'pending')->count();
            $approvedCount = \App\Models\EmployerRequirement::where('status', 'approved')->count();
            $rejectedCount = \App\Models\EmployerRequirement::where('status', 'rejected')->count();
            return view('staff.job_vacancy.dashboard', compact(
                'pendingCount', 'approvedCount', 'rejectedCount',
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
        $search = request('search');

       if ($staffRole === 'sra') {
    $registrations = JobseekerRegistration::with(['user', 'nsrp'])
        ->whereHas('nsrp', function($q) {
            $q->whereIn('type', ['overseas', 'both']);
        })
                ->when($search, function($q) use ($search) {
                    $q->whereHas('user', function($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10);
            return view('staff.sra.registrations.index', compact('registrations'));

        } elseif ($staffRole === 'lra') {
    $registrations = JobseekerRegistration::with(['user', 'applications', 'nsrp'])
        ->whereHas('nsrp', function($q) {
            $q->whereIn('type', ['local', 'both']);
        })
                ->when($search, function($q) use ($search) {
                    $q->whereHas('user', function($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10);
            return view('staff.lra.registrations.index', compact('registrations'));
        }

        return redirect()->route('staff.dashboard');
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

        $isOverseas = $staffRole === 'sra';
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

        if ($staffRole === 'sra') {
    return view('staff.sra.registrations.show', compact('registration', 'nsrp', 'openJobs'));
} elseif ($staffRole === 'lra') {
    return view('staff.lra.registrations.show', compact('registration', 'nsrp', 'openJobs'));
}

        return redirect()->route('staff.dashboard');
    }

    // ───────────────────────────────
    // JOB FAIR — VIEW ONLY (LRA/SRA)
    // ───────────────────────────────
    public function jobFairViewOnly()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if (!in_array($staff->staff_role, ['lra', 'sra', 'job_vacancy'])) return redirect()->route('staff.dashboard');

        $staffRole = $staff->staff_role;
        $events = \App\Models\JobFairEvent::orderByDesc('event_date')->paginate(3, ['*'], 'event_page');

        $jobs = null;
        $jobStatus = request('job_status', 'pending');
        $totalPendingJobs = $totalApprovedJobs = $totalRejectedJobs = 0;

        // ── LRA dili mo-approve og Job Fair postings (SRA/Job Vacancy ra) — pero pwede gihapon makakita sa events monitoring ──
        if (in_array($staffRole, ['job_vacancy', 'sra'])) {
            $isOverseas = $staffRole === 'sra';

            $baseJobQuery = \App\Models\Job::where('schedule_type', 'job_fair')
                ->whereHas('company', fn($n) => $n->where('is_overseas', $isOverseas));

            $totalPendingJobs  = (clone $baseJobQuery)->where('posting_status', 'pending')->count();
            $totalApprovedJobs = (clone $baseJobQuery)->where('posting_status', 'approved')->count();
            $totalRejectedJobs = (clone $baseJobQuery)->where('posting_status', 'rejected')->count();

            $jobs = (clone $baseJobQuery)->with('company')
                ->when($jobStatus !== 'all', fn($q) => $q->where('posting_status', $jobStatus))
                ->latest()
                ->paginate(3, ['*'], 'job_page');
        }

        return view('staff.inhouse.jobfair', compact(
            'events', 'staffRole', 'jobs', 'jobStatus',
            'totalPendingJobs', 'totalApprovedJobs', 'totalRejectedJobs'
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
            ->paginate(10);

        $totalUpcoming  = \App\Models\JobFairEvent::where('status', 'upcoming')->count();
        $totalOngoing   = \App\Models\JobFairEvent::where('status', 'ongoing')->count();
        $totalCompleted = \App\Models\JobFairEvent::where('status', 'completed')->count();

        $participants        = null;
        $confirmedCount      = 0;
        $pendingCount        = 0;
        $declinedCount       = 0;
        $employmentRequests  = collect();

        if ($view === 'participants' && request('event_id')) {
            $participants = \App\Models\JobFairParticipant::with('employer')
                ->where('job_fair_id', request('event_id'))
                ->latest()
                ->paginate(10);

            $employmentRequests = \App\Models\JobFairEmploymentRequest::with('job')
                ->where('job_fair_id', request('event_id'))
                ->get()
                ->groupBy('employer_id');

            $confirmedCount = \App\Models\JobFairParticipant::where('job_fair_id', request('event_id'))
                ->where('confirmation_status', 'confirmed')->count();
            $pendingCount   = \App\Models\JobFairParticipant::where('job_fair_id', request('event_id'))
                ->where('confirmation_status', 'pending')->count();
            $declinedCount  = \App\Models\JobFairParticipant::where('job_fair_id', request('event_id'))
                ->where('confirmation_status', 'declined')->count();
        }

        // ── ATTENDANCE VIEW ──
        $registrations       = null;
        $attendanceFilter    = request('attendance_filter', 'all');
        $totalRegistered     = 0;
        $totalAttended       = 0;
        $totalAbsent         = 0;
        $totalLocal          = 0;
        $totalOverseas       = 0;

        $attendanceSearch = request('attendance_search');

        $event = request('event_id') ? \App\Models\JobFairEvent::find(request('event_id')) : null;

        if ($view === 'attendance' && request('event_id')) {
            // ── Dili pa gi-confirm (is_attended NULL) DILI mabutang sa listahan — mabutang lang human ma-confirm sa jobseeker sa event day ──
            $regQuery = \App\Models\JobFairRegistration::with(['jobseeker.nsrp', 'jobseeker.user'])
                ->where('job_fair_id', request('event_id'))
                ->whereNotNull('is_attended')
                ->when($attendanceFilter !== 'all', function ($q) use ($attendanceFilter) {
                    $q->whereHas('jobseeker.nsrp', function ($n) use ($attendanceFilter) {
                        $n->whereIn('type', [$attendanceFilter, 'both']);
                    });
                })
                ->when($attendanceSearch, function ($q) use ($attendanceSearch) {
                    $q->where(function ($sub) use ($attendanceSearch) {
                        $sub->where('slip_number', 'like', "%{$attendanceSearch}%")
                            ->orWhereHas('jobseeker', function ($jq) use ($attendanceSearch) {
                                $jq->where('first_name', 'like', "%{$attendanceSearch}%")
                                   ->orWhere('surname', 'like', "%{$attendanceSearch}%");
                            });
                    });
                });

            $registrations = $regQuery->latest()->paginate(10)->withQueryString();

            $baseCountQuery = \App\Models\JobFairRegistration::where('job_fair_id', request('event_id'));

            $totalRegistered = (clone $baseCountQuery)->count();
            $totalAttended   = (clone $baseCountQuery)->where('is_attended', true)->count();
            $totalAbsent     = (clone $baseCountQuery)->where('is_attended', false)->count();

            $totalLocal = (clone $baseCountQuery)->whereNotNull('is_attended')->whereHas('jobseeker.nsrp', fn($n) =>
                $n->whereIn('type', ['local', 'both'])
            )->count();

            $totalOverseas = (clone $baseCountQuery)->whereNotNull('is_attended')->whereHas('jobseeker.nsrp', fn($n) =>
                $n->whereIn('type', ['overseas', 'both'])
            )->count();
        }

        return view('staff.job_fair.events.index', compact(
            'events', 'status', 'totalUpcoming', 'totalOngoing', 'totalCompleted',
            'allEvents', 'participants', 'confirmedCount', 'pendingCount', 'declinedCount',
            'employmentRequests', 'registrations', 'attendanceFilter', 'attendanceSearch',
            'totalRegistered', 'totalAttended', 'totalAbsent', 'totalLocal', 'totalOverseas', 'event'
        ));
    }

    public function createJobFairEvent()
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');
        return view('staff.job_fair.events.create');
    }

    public function storeJobFairEvent(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $request->validate([
            'title'             => 'required|string|max:255',
            'event_date'        => 'required|date|after_or_equal:' . now()->addDays(10)->format('Y-m-d'),
            'event_time'        => 'required|date_format:H:i|after_or_equal:09:00|before_or_equal:17:00',
            'venue'             => 'required|string|max:255',
            'employer_capacity' => 'required|integer|min:1',
            'cater'             => 'required|array|min:1',
            'cater.*'           => 'in:local,overseas',
        ], [
            'event_date.after_or_equal' => 'Event date must be at least 10 days from today (earliest: ' . now()->addDays(10)->format('M d, Y') . ').',
            'event_time.after_or_equal' => 'Event time must be between 9:00 AM and 5:00 PM.',
            'event_time.before_or_equal' => 'Event time must be between 9:00 AM and 5:00 PM.',
            'cater.required'            => 'Please check at least one employer type (Local or Overseas) to invite.',
        ]);

        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();

        $event = \App\Models\JobFairEvent::create([
            'created_by'        => $staffRecord->id,
            'title'             => $request->title,
            'event_date'        => $request->event_date,
            'event_time'        => $request->event_time,
            'venue'             => $request->venue,
            'employer_capacity' => $request->employer_capacity,
            'status'            => 'upcoming',
        ]);

        // ── Auto-notify approved employers, na-filter base sa gi-check nga Local/Overseas ──
        $caterTo = $request->cater;
        $employers = User::where('role', 'company')
            ->where('status', 'approved')
            ->whereHas('employerRequirement', fn($q) => $q->where('status', 'approved'))
            ->whereHas('employerNsrp', function ($q) use ($caterTo) {
                $q->where(function ($q2) use ($caterTo) {
                    if (in_array('local', $caterTo)) {
                        $q2->orWhere('is_overseas', false);
                    }
                    if (in_array('overseas', $caterTo)) {
                        $q2->orWhere('is_overseas', true);
                    }
                });
            })
            ->get();

        foreach ($employers as $employer) {
            \App\Models\JobFairParticipant::create([
                'job_fair_id'         => $event->id,
                'employer_id'         => $employer->employerNsrp->employer_nsrp_registrations_id,
                'confirmation_status' => 'pending',
            ]);
        }

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'job_fair_invitation',
            'title'          => 'Job Fair Invitation 🎉',
            'message'        => 'You are invited to join ' . $event->title . ' on ' . $event->event_date->format('M d, Y') . ' at ' . $event->venue . '. Please respond to confirm your participation.',
            'reference_type' => 'job_fair',
            'reference_id'   => $event->id,
        ], $employers->map(fn($e) => $e->employerNsrp->employer_nsrp_registrations_id));

        // ── AUTO-OPEN: tanan Job Fair postings nga approved na pero magpabilin CLOSED (nag-hulat sa bag-ong event) mabalhin karon nga OPEN, mapost na sa landing page ──
        $closedJobFairJobs = \App\Models\Job::with('company')
            ->where('schedule_type', 'job_fair')
            ->where('posting_status', 'approved')
            ->where('status', 'closed')
            ->get();

        foreach ($closedJobFairJobs as $job) {
            $job->update(['status' => 'open']);

            \App\Models\Announcement::sendToEmployers([
                'type'           => 'job_fair_posting_opened',
                'title'          => 'Job Fair Posting Now Live 🎪',
                'message'        => 'Your job fair posting "' . $job->title . '" is now open and visible to jobseekers, following the creation of ' . $event->title . '.',
                'reference_type' => 'job',
                'reference_id'   => $job->job_qualifications_id,
            ], $job->company_id);
        }

        if ($closedJobFairJobs->isNotEmpty()) {
            $allJobseekerRegs = \App\Models\JobseekerRegistration::whereHas('user', fn($q) => $q->where('status', 'approved'))
                ->with('nsrp')
                ->get();

            foreach ($closedJobFairJobs as $job) {
                $matchedIds = collect();
                $unmatchedIds = collect();
                $jobTitleLower = strtolower($job->title);

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

                if ($matchedIds->isNotEmpty()) {
                    \App\Models\Announcement::sendToJobseekers([
                        'type'           => 'job_match',
                        'title'          => 'Matching Job Vacancy Found! 💼',
                        'message'        => 'A job vacancy matching your preferred position "' . $job->title . '" from ' . ($job->company->company_name ?? 'an employer') . ' is now available. Would you like to apply?',
                        'reference_type' => 'job',
                        'reference_id'   => $job->job_qualifications_id,
                    ], $matchedIds);
                }

                if ($unmatchedIds->isNotEmpty()) {
                    \App\Models\Announcement::sendToJobseekers([
                        'type'           => 'job_posted',
                        'title'          => 'New Job Vacancy Posted 💼',
                        'message'        => 'A new job vacancy "' . $job->title . '" from ' . ($job->company->company_name ?? 'an employer') . ' is now available!',
                        'reference_type' => 'job',
                        'reference_id'   => $job->job_qualifications_id,
                    ], $unmatchedIds);
                }
            }
        }

        return redirect()->route('staff.jobfair.events')
            ->with('success', 'Job Fair event created and employers notified!' . ($closedJobFairJobs->isNotEmpty() ? ' ' . $closedJobFairJobs->count() . ' job fair posting(s) are now live.' : ''));
    }

    public function editJobFairEvent($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $event = \App\Models\JobFairEvent::findOrFail($id);
        return view('staff.job_fair.events.edit', compact('event'));
    }

    public function updateJobFairEvent(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $request->validate([
            'title'      => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'venue'      => 'required|string|max:255',
            'status'     => 'required|in:upcoming,ongoing,completed',
        ]);

        $event = \App\Models\JobFairEvent::findOrFail($id);
        $event->update([
            'title'      => $request->title,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'venue'      => $request->venue,
            'status'     => $request->status,
        ]);

        return redirect()->route('staff.jobfair.events')
            ->with('success', 'Job Fair event updated successfully!');
    }

    public function deleteJobFairEvent($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        \App\Models\JobFairEvent::findOrFail($id)->delete();

        return back()->with('success', 'Job Fair event deleted.');
    }

    // ───────────────────────────────
    // AUTO-CHECK & NOTIFY JOB FAIR
    // ───────────────────────────────
    public function checkAndNotifyJobFair($id)
    {
        $event = \App\Models\JobFairEvent::findOrFail($id);

        $confirmedCount = \App\Models\JobFairParticipant::where('job_fair_id', $id)
            ->where('confirmation_status', 'confirmed')
            ->count();

        if ($confirmedCount >= 3) {
            $jobseekers = User::where('role', 'jobseeker')
                ->where('status', 'approved')
                ->get();

            $alreadyNotifiedIds = \App\Models\AnnouncementJobseeker::whereHas('announcement', function ($q) use ($event) {
                $q->where('reference_type', 'job_fair')
                  ->where('reference_id', $event->id)
                  ->where('type', 'job_fair_open');
            })->pluck('jobseeker_id');

            $regIds = \App\Models\JobseekerRegistration::whereIn('user_id', $jobseekers->pluck('users_id'))
                ->whereNotIn('jobseeker_registrations_id', $alreadyNotifiedIds)
                ->pluck('jobseeker_registrations_id');

            if ($regIds->isNotEmpty()) {
                \App\Models\Announcement::sendToJobseekers([
                    'type'           => 'job_fair_open',
                    'title'          => 'Job Fair Event Available! 🎉',
                    'message'        => $event->title . ' on ' . $event->event_date->format('M d, Y') . ' at ' . $event->venue . ' is now open!',
                    'reference_type' => 'job_fair',
                    'reference_id'   => $event->id,
                ], $regIds);
            }
            return back()->with('success', 'Jobseekers notified successfully!');
        }

        $daysRemaining = now()->diffInDays($event->event_date, false);
        if ($confirmedCount < 3 && $daysRemaining <= 5 && $daysRemaining > 0) {
            $pendingEmployers = \App\Models\JobFairParticipant::with('employer')
                ->where('job_fair_id', $id)
                ->where('confirmation_status', 'pending')
                ->get();

            \App\Models\Announcement::sendToEmployers([
                'type'           => 'job_fair_reminder',
                'title'          => 'Job Fair Reminder ⏰',
                'message'        => 'Only ' . $daysRemaining . ' days left! Please confirm your participation for ' . $event->title . ' on ' . $event->event_date->format('M d, Y') . '.',
                'reference_type' => 'job_fair',
                'reference_id'   => $event->id,
            ], $pendingEmployers->pluck('employer_id'));
            return back()->with('success', 'Reminder sent to pending employers!');
        }

        return back()->with('info', 'No notifications sent. Confirmed: ' . $confirmedCount . ', Days remaining: ' . $daysRemaining);
    }

    public function sendJobFairInvitation($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $event = \App\Models\JobFairEvent::findOrFail($id);

        $employers = User::where('role', 'company')
            ->where('status', 'approved')
            ->whereHas('employerRequirement', fn($q) => $q->where('status', 'approved'))
            ->get();

        $sent = 0;
        $newEmployerIds = collect();
        foreach ($employers as $employer) {
            $exists = \App\Models\JobFairParticipant::where('job_fair_id', $event->id)
                ->where('employer_id', $employer->employerNsrp->employer_nsrp_registrations_id)
                ->exists();

            if (!$exists) {
                \App\Models\JobFairParticipant::create([
                    'job_fair_id'         => $event->id,
                    'employer_id'         => $employer->employerNsrp->employer_nsrp_registrations_id,
                    'confirmation_status' => 'pending',
                ]);

                $newEmployerIds->push($employer->employerNsrp->employer_nsrp_registrations_id);
                $sent++;
            }
        }

        if ($newEmployerIds->isNotEmpty()) {
            \App\Models\Announcement::sendToEmployers([
                'type'           => 'job_fair_invitation',
                'title'          => 'Job Fair Invitation 🎉',
                'message'        => 'You are invited to join ' . $event->title . ' on ' . $event->event_date->format('M d, Y') . ' at ' . $event->venue . '. Please respond to confirm your participation.',
                'reference_type' => 'job_fair',
                'reference_id'   => $event->id,
            ], $newEmployerIds);
        }

        return back()->with('success', "Invitations sent to {$sent} employers!");
    }

    public function jobFairParticipants($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $event        = \App\Models\JobFairEvent::findOrFail($id);
        $participants = \App\Models\JobFairParticipant::with('employer')
            ->where('job_fair_id', $id)
            ->latest()
            ->paginate(10);

        $totalPending  = \App\Models\JobFairParticipant::where('job_fair_id', $id)->where('confirmation_status', 'pending')->count();
        $totalAccepted = \App\Models\JobFairParticipant::where('job_fair_id', $id)->where('confirmation_status', 'accepted')->count();
        $totalDeclined = \App\Models\JobFairParticipant::where('job_fair_id', $id)->where('confirmation_status', 'declined')->count();

        return view('staff.job_fair.events.participants', compact(
            'event', 'participants', 'totalPending', 'totalAccepted', 'totalDeclined'
        ));
    }

    // ───────────────────────────────
    // JOB FAIR — SHOW SEND PAGE
    // ───────────────────────────────
    public function showJobFairSend()
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');
        return view('staff.job_fair.send');
    }

    // ───────────────────────────────
    // JOB FAIR — SEND NOTIFICATION
    // ───────────────────────────────
    public function sendJobFairNotification(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $jobseekerRegIds = \App\Models\JobseekerRegistration::whereHas('user', fn($q) => $q->where('status', 'approved'))->pluck('jobseeker_registrations_id');
        \App\Models\Announcement::sendToJobseekers([
            'type'           => 'job_fair',
            'title'          => $request->title,
            'message'        => $request->message,
            'reference_type' => 'job_fair',
            'reference_id'   => null,
        ], $jobseekerRegIds);

        $employerIds = \App\Models\EmployerNsrpRegistration::whereHas('user', fn($q) => $q->where('status', 'approved'))->pluck('employer_nsrp_registrations_id');
        \App\Models\Announcement::sendToEmployers([
            'type'           => 'job_fair',
            'title'          => $request->title,
            'message'        => $request->message,
            'reference_type' => 'job_fair',
            'reference_id'   => null,
        ], $employerIds);

        $total = $jobseekerRegIds->count() + $employerIds->count();
        return back()->with('success', 'Job Fair notification sent to ' . $total . ' users!');
    }

    // ───────────────────────────────
    // JOB FAIR — JOB POSTINGS (Closed = naghulat pa sa event; Open = na-post na)
    // ───────────────────────────────
    public function jobFairPostings()
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $status = request('status', 'closed'); // closed | open | all
        $search = request('search');

        $query = \App\Models\Job::with('company')
            ->where('schedule_type', 'job_fair')
            ->whereIn('posting_status', ['pending', 'approved'])
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('company', fn($u) => $u->where('company_name', 'like', "%{$search}%"))
            );

        $totalClosed = \App\Models\Job::where('schedule_type', 'job_fair')
            ->whereIn('posting_status', ['pending', 'approved'])
            ->where('status', 'closed')->count();
        $totalOpen = \App\Models\Job::where('schedule_type', 'job_fair')
            ->where('posting_status', 'approved')
            ->where('status', 'open')->count();

        $jobs = $query->latest()->paginate(10)->withQueryString();

        return view('staff.job_fair.postings', compact('jobs', 'status', 'totalClosed', 'totalOpen'));
    }

    // ───────────────────────────────
    // JOB FAIR — APPROVE JOB POSTING
    // ───────────────────────────────
    public function approveJobFairJob($id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $job = \App\Models\Job::with('company')->findOrFail($id);

        if ($job->schedule_type !== 'job_fair') {
            return back()->with('error', 'This job is not a job fair posting.');
        }

        if ($job->posting_status !== 'pending') {
            return back()->with('error', 'Only pending job postings can be approved.');
        }

        $job->update([
            'posting_status' => 'approved',
            'status'         => 'closed',
            'remarks'        => null,
        ]);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'job_approved',
            'title'          => 'Job Posting Approved ✅',
            'message'        => 'Your job posting "' . $job->title . '" has been approved for job fair use. It will go live once PESO opens a job fair event.',
            'reference_type' => 'job',
            'reference_id'   => $job->job_qualifications_id,
        ], $job->company_id);

        return back()->with('success', 'Job posting approved. It will go live once a job fair event is created.');
    }

    // ───────────────────────────────
    // JOB FAIR — REJECT JOB POSTING
    // ───────────────────────────────
    public function rejectJobFairJob(Request $request, $id)
    {
        $staff = $this->authStaff();
        if (!$staff || $staff->staff_role !== 'job_fair') return redirect()->route('login');

        $request->validate(['remarks' => 'required|string|max:500']);

        $job = \App\Models\Job::with('company')->findOrFail($id);

        if ($job->schedule_type !== 'job_fair') {
            return back()->with('error', 'This job is not a job fair posting.');
        }

        $job->update([
            'posting_status' => 'rejected',
            'status'         => 'closed',
            'remarks'        => $request->remarks,
        ]);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'job_rejected',
            'title'          => 'Job Posting Rejected ❌',
            'message'        => 'Your job posting "' . $job->title . '" was rejected for job fair use. Reason: ' . $request->remarks,
            'reference_type' => 'job',
            'reference_id'   => $job->job_qualifications_id,
        ], $job->company_id);

        return back()->with('success', 'Job posting rejected.');
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
                ->where('status', 'approved')
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
        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
        $requirement->update([
            'status'      => 'approved',
            'reviewed_by' => $staffRecord->id ?? null,
            'remarks'     => null,
        ]);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'requirements_approved',
            'title'          => 'Requirements Approved ✅',
            'message'        => 'Your submitted requirements have been approved by PESO staff. You can now request in-house interviews and post job vacancies.',
            'reference_type' => 'employer_requirement',
            'reference_id'   => $requirement->id,
        ], $requirement->user_id);

        return redirect()->route('staff.requirements')
            ->with('success', 'Requirements approved successfully.');
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
                'reviewed_by' => $staffRecord->id ?? null,
                'remarks'     => $request->remarks,
            ]);

            \App\Models\Announcement::sendToEmployers([
                'type'           => 'requirements_rejected',
                'title'          => 'Requirements Rejected ❌',
                'message'        => 'Please resubmit your requirements. Reason: ' . $request->remarks,
                'reference_type' => 'employer_requirement',
                'reference_id'   => $requirement->id,
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
        $requirement->update([
            'status'          => 'rejected',
            'reviewed_by'     => $staffRecord->id ?? null,
            'remarks'         => $request->remarks,
            'rejected_fields' => $request->rejected_fields,
        ]);

        $labels = [
            'business_permit'             => 'CDO Business Permit',
            'sec_dti'                     => 'SEC / DTI',
            'company_profile'             => 'Company Profile',
            'no_pending_case_certificate' => 'Certificate of No Pending Case',
            'vacancy_posting'             => 'Vacancy Posting',
        ];
        $rejectedLabels = collect($request->rejected_fields)->map(fn($f) => $labels[$f] ?? $f)->implode(', ');

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'requirements_rejected',
            'title'          => 'Requirements Rejected ❌',
            'message'        => 'Please resubmit the following document(s): ' . $rejectedLabels . '. Reason: ' . $request->remarks,
            'reference_type' => 'employer_requirement',
            'reference_id'   => $requirement->id,
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
        $tab       = $staffRole === 'lra' ? 'approved' : request('tab', 'pre');
        $search    = request('search');

        $baseQuery = User::where('role', 'company')
    ->when($staffRole === 'lra' || $staffRole === 'job_vacancy', fn($q) =>
        $q->whereHas('employerNsrp', fn($n) => $n->where('is_overseas', false))
    )
    ->when($staffRole === 'sra', fn($q) =>
        $q->whereHas('employerNsrp', fn($n) => $n->where('is_overseas', true))
    )
            ->when($search, fn($q) =>
                $q->whereHas('employerNsrp', fn($n) => $n->where('company_name', 'like', "%{$search}%"))
                  ->orWhere('email', 'like', "%{$search}%")
            );

        if ($tab === 'pre') {
            $employers = (clone $baseQuery)
                ->where(fn($q) =>
                    $q->whereDoesntHave('employerRequirement')
                      ->orWhereHas('employerRequirement', fn($q2) =>
                          $q2->where('status', 'pending')
                      )
                )
                ->latest()
                ->paginate(10);
        } else {
            $employers = (clone $baseQuery)
                ->whereHas('employerRequirement', fn($q) =>
                    $q->where('status', 'approved')
                )
                ->with(['jobs.applications' => fn($q) =>
                    $q->where('status', 'hired')
                ])
                ->latest()
                ->paginate(10);
        }

        $totalPre = (clone $baseQuery)
            ->where(fn($q) =>
                $q->whereDoesntHave('employerRequirement')
                  ->orWhereHas('employerRequirement', fn($q2) =>
                      $q2->where('status', 'pending')
                  )
            )->count();

        $totalApproved = (clone $baseQuery)
            ->whereHas('employerRequirement', fn($q) =>
                $q->where('status', 'approved')
            )->count();

        return view('staff.employers.index', compact(
            'employers', 'staffRole', 'tab',
            'totalPre', 'totalApproved'
        ));
    }

    // ───────────────────────────────
    // VIEW EMPLOYER MODAL DATA
    // ───────────────────────────────
    public function viewEmployer($id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $employer = User::with([
    'employerRequirement',
    'employerNsrp',
    'jobs.applications' => fn($q) => $q->where('status', 'hired'),
    'jobs.applications.jobseeker',
])->findOrFail($id);

        $staffRole  = $staff->staff_role;
        $totalHired = $employer->jobs->flatMap->applications->count();
        $hiredApps  = $employer->jobs->flatMap->applications;

        return view('staff.employers.show', compact(
            'employer', 'staffRole', 'totalHired', 'hiredApps'
        ));
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

        // ── SOURCE 2: Job (job posting + inhouse schedule combined) — pending ra, human ma-approve mabalhin sa Job Vacancies tab ──
        $jobItems = \App\Models\Job::with('company')
            ->where('schedule_type', 'inhouse')
            ->where('posting_status', 'pending')
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

        // ── MERGE + SORT (latest first) ──
        $merged = $scheduleItems->concat($jobItems)->sortByDesc('sort_date')->values();

        // ── STAT CARDS (Total = tanan In-house ever gikan sa duha ka sources; Pending = parehas sa Total, kay pending ra man permanente ang gipakita dinhi) ──
        $totalAll = \App\Models\InhouseSchedule::where($employerRoleFilter)->count()
            + \App\Models\Job::where('schedule_type', 'inhouse')->where($roleFilter)->count();

        $totalPending = $merged->count();

        // ── MANUAL PAGINATION (kay gi-merge nato ang duha ka sources) ──
        $perPage   = 10;
        $schedules = new \Illuminate\Pagination\LengthAwarePaginator(
            $merged->forPage($page, $perPage)->values(),
            $merged->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        return view('staff.inhouse.index', compact(
            'schedules', 'staffRole',
            'totalAll', 'totalPending'
        ));
    }

    // ───────────────────────────────
    // IN-HOUSE CALENDAR DATA — employers naka-schedule per date (LRA/SRA dashboard calendar)
    // ───────────────────────────────
    public function inhouseCalendarData()
    {
        $staff = $this->authStaff();
        if (!$staff) return response()->json(['error' => 'Unauthorized'], 401);

        $staffRole = $staff->staff_role;
        if (!in_array($staffRole, ['lra', 'sra'])) return response()->json(['error' => 'Unauthorized'], 401);

        $isOverseas = $staffRole === 'sra';

        $scheduleItems = \App\Models\InhouseSchedule::with('employer')
            ->where('status', 'accepted')
            ->whereHas('employer', fn($n) => $n->where('is_overseas', $isOverseas))
            ->get()
            ->map(fn($s) => [
                'date'    => optional($s->confirmed_date)->format('Y-m-d'),
                'company' => $s->employer->company_name ?? '—',
                'time'    => $s->confirmed_time ? \Carbon\Carbon::parse($s->confirmed_time)->format('h:i A') : null,
            ])
            ->filter(fn($s) => $s['date']);

        $jobItems = \App\Models\Job::with('company')
            ->where('schedule_type', 'inhouse')
            ->where('posting_status', 'approved')
            ->whereHas('company', fn($n) => $n->where('is_overseas', $isOverseas))
            ->get()
            ->map(fn($j) => [
                'date'    => optional($j->preferred_date)->format('Y-m-d'),
                'company' => $j->company->company_name ?? '—',
                'time'    => null,
            ])
            ->filter(fn($j) => $j['date']);

        $grouped = $scheduleItems->concat($jobItems)->groupBy('date');

        $result = $grouped->map(fn($items) => $items->values())->toArray();

        return response()->json(['dates' => $result]);
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
            'confirmed_date' => 'required|date',
            'confirmed_time' => 'required',
        ]);

        $schedule = \App\Models\InhouseSchedule::with('employer')->findOrFail($id);
        $staffRecord = \App\Models\Staff::where('user_id', $staff->users_id)->first();
        $schedule->update([
            'status'           => 'accepted',
            'reviewed_by'      => $staffRecord->id ?? null,
            'confirmed_date'   => $request->confirmed_date,
            'confirmed_time'   => $request->confirmed_time,
            'rejection_reason' => null,
        ]);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'inhouse_accepted',
            'title'          => 'In-house Schedule Accepted ✅',
            'message'        => 'Your in-house interview request has been accepted. Confirmed date: ' . \Carbon\Carbon::parse($request->confirmed_date)->format('M d, Y') . ' at ' . \Carbon\Carbon::parse($request->confirmed_time)->format('h:i A') . ' (' . ($schedule->venue_type === 'custom' ? $schedule->venue_address : 'PESO Office') . ').',
            'reference_type' => 'inhouse_schedule',
            'reference_id'   => $schedule->id,
        ], $schedule->employer_id);

        // Notify ang tanan jobseekers nga naka-apply sa maong employer
        $venueLabel = match($schedule->venue_type) {
            'peso_office'  => 'PESO Office',
            'office_based' => 'the employer\'s office',
            'custom'       => $schedule->venue_address,
            default        => 'PESO Office',
        };

        $userIds = \App\Models\Application::whereHas('job', function ($q) use ($schedule) {
            $q->where('company_id', $schedule->employer_id);
        })->pluck('jobseeker_id')->unique();

        \App\Models\Announcement::sendToJobseekers([
            'type'           => 'inhouse_schedule_confirmed',
            'title'          => 'In-house Interview Schedule 📅',
            'message'        => ($schedule->employer->company_name ?? 'The employer') . ' has scheduled an in-house interview on ' . \Carbon\Carbon::parse($request->confirmed_date)->format('M d, Y') . ' at ' . \Carbon\Carbon::parse($request->confirmed_time)->format('h:i A') . ' (' . $venueLabel . '). Check your Schedules for details.',
            'reference_type' => 'inhouse_schedule',
            'reference_id'   => $schedule->id,
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
            'reviewed_by'      => $staffRecord->id ?? null,
            'rejection_reason' => $request->rejection_reason,
        ]);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'inhouse_rejected',
            'title'          => 'In-house Schedule Rejected ❌',
            'message'        => 'Your in-house interview request was rejected. Reason: ' . $request->rejection_reason,
            'reference_type' => 'inhouse_schedule',
            'reference_id'   => $schedule->id,
        ], $schedule->employer_id);

        return redirect()->route('staff.inhouse')
            ->with('success', 'In-house schedule rejected.');
    }

    

    // ───────────────────────────────
    // JOB VACANCIES
    // ───────────────────────────────
    public function jobVacancies()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        $search    = request('search');
        $status    = request('status', 'all');

        $query = \App\Models\Job::with('company')
            ->when($staffRole === 'lra', function($q) {
                // LRA: local, In-house ra nga NA-APPROVE na (mabalhin diri gikan sa In-house tab)
                $q->whereHas('company', fn($n) => $n->where('is_overseas', false))
                  ->where('schedule_type', 'inhouse')
                  ->where('posting_status', 'approved');
            })
            ->when($staffRole === 'job_vacancy', function($q) {
                // Job Vacancy staff: local, Office Based ra (dili In-house — LRA na ang naga-handle; dili Job Fair — naa na sa Job Fair tab)
                $q->whereHas('company', fn($n) => $n->where('is_overseas', false))
                  ->where(function ($q2) {
                      $q2->whereNull('schedule_type')->orWhere('schedule_type', 'office_based');
                  });
            })
            ->when($staffRole === 'sra', function($q) {
                // SRA: separate nga tab para sa Office Based ug In-house (query param 'type')
                $sraType = request('type', 'inhouse');
                $q->whereHas('company', fn($n) => $n->where('is_overseas', true));
                if ($sraType === 'inhouse') {
                    $q->where('schedule_type', 'inhouse')->where('posting_status', 'approved');
                } else {
                    $q->where(function ($q2) {
                        $q2->whereNull('schedule_type')->orWhere('schedule_type', 'office_based');
                    });
                }
            })
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('company', fn($u) =>
                    $u->where('company_name', 'like', "%{$search}%")
                )
            );

        $totalAll    = (clone $query)->count();
        $totalOpen   = (clone $query)->where('status', 'open')->count();
        $totalClosed = (clone $query)->where('status', 'closed')->count();

        $jobs = $query->latest()->paginate(3);

        return view('staff.jobs.index', compact(
            'jobs', 'status', 'staffRole',
            'totalAll', 'totalOpen', 'totalClosed'
        ));
    }

    // ───────────────────────────────
    // ALL JOB POSTINGS (In-house + Office Based, tanan status) — Job Vacancy staff ra, para makita/ma-delete ang expired
    // ───────────────────────────────
    public function allJobPostings()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');
        if ($staff->staff_role !== 'job_vacancy') return redirect()->route('staff.dashboard');

        $search = request('search');
        $status = request('status', 'all');

        $query = \App\Models\Job::with('company')
            ->whereHas('company', fn($n) => $n->where('is_overseas', false))
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('company', fn($u) => $u->where('company_name', 'like', "%{$search}%"))
            );

        $totalAll    = (clone $query)->count();
        $totalOpen   = (clone $query)->where('status', 'open')->count();
        $totalClosed = (clone $query)->where('status', 'closed')->count();

        $jobs = $query->latest()->paginate(3);

        return view('staff.jobs.all', compact(
            'jobs', 'status',
            'totalAll', 'totalOpen', 'totalClosed'
        ));
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

    public function deleteJobVacancy($id)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        \App\Models\Job::findOrFail($id)->delete();
        return back()->with('success', 'Job vacancy deleted.');
    }

    // ───────────────────────────────
    // JOBSEEKERS — Applications (LRA/SRA)
    // ───────────────────────────────
    public function jobseekers()
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $staffRole = $staff->staff_role;
        $search    = request('search');

        $query = \App\Models\Application::with(['jobseeker', 'job'])
            ->when($staffRole === 'lra', function($q) {
    $q->whereHas('jobseeker.nsrp', fn($r) =>
          $r->whereIn('type', ['local', 'both'])
      );
})
            ->when($staffRole === 'sra', function($q) {
    $q->whereHas('jobseeker.nsrp', fn($r) =>
          $r->whereIn('type', ['overseas', 'both'])
      );
})
            ->where('status', 'qualified')
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            ));

        $totalQualified = (clone $query)->count();
        $applications   = $query->latest()->paginate(10);

        return view('staff.jobseeker.index', compact(
            'applications', 'staffRole', 'totalQualified'
        ));
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
            'nsrp_image' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $image    = $request->file('nsrp_image');
        $path     = $image->store('nsrp_scans', 'public');
        $fullPath = storage_path('app/public/' . $path);

        // ── Pre-process image (grayscale + contrast boost) para mo-improve gamay ang OCR readability ──
        $processedPath = $this->preprocessNsrpImage($fullPath);

        try {
            $text = (new \thiagoalessio\TesseractOCR\TesseractOCR($processedPath))->run();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'OCR processing failed. Please fill out the form manually. (' . $e->getMessage() . ')',
            ], 500);
        }

        $parsed = $this->parseWalkinNsrpText($text);

        return response()->json([
            'success'  => true,
            'raw_text' => $text,
            'data'     => $parsed,
        ]);
    }

    // ── Pre-process image: grayscale + contrast boost para mas klaro ang text sa OCR ──
    private function preprocessNsrpImage($fullPath)
    {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        $src = match($ext) {
            'png'          => imagecreatefrompng($fullPath),
            'jpg', 'jpeg'  => imagecreatefromjpeg($fullPath),
            default        => null,
        };

        if (!$src) {
            return $fullPath; // fallback sa original kung dili ma-open
        }

        // Grayscale
        imagefilter($src, IMG_FILTER_GRAYSCALE);
        // Contrast boost (negative value = mas taas ang contrast sa GD)
        imagefilter($src, IMG_FILTER_CONTRAST, -25);
        // Slight sharpen (convolution)
        $sharpen = [
            [0, -1, 0],
            [-1, 5, -1],
            [0, -1, 0],
        ];
        imageconvolution($src, $sharpen, array_sum(array_map('array_sum', $sharpen)) ?: 1, 0);

        $processedPath = storage_path('app/public/nsrp_scans/processed_' . basename($fullPath));
        imagejpeg($src, $processedPath, 95);
        imagedestroy($src);

        return $processedPath;
    }

    private function parseWalkinNsrpText($text)
    {
        $data = [];

        $patterns = [
            'surname'        => '/Surname\s*[:\-]?\s*([A-Za-z]+(?:\s[A-Za-z]+){0,3})(?=\s|$|\n|FIRST|DATE|SEX|CIVIL)/i',
            'first_name'     => '/First\s*Name\s*[:\-]?\s*([A-Za-z]+(?:\s[A-Za-z]+){0,3})(?=\s|$|\n|MIDDLE|SUFFIX|DATE)/i',
            'middle_name'    => '/Middle\s*Name\s*[:\-]?\s*([A-Za-z]+(?:\s[A-Za-z]+){0,3})(?=\s|$|\n|SUFFIX|DATE)/i',
            'contact_number' => '/Contact\s*Number\/?s?\s*[:\-]?\s*([0-9]{7,15})/i',
            'religion'       => '/Religion\s*[:\-]?\s*([A-Za-z]+(?:\s[A-Za-z]+){0,2})(?=\s|$|\n|CIVIL|STATUS)/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $data[$key] = trim(preg_replace('/\s+/', ' ', $matches[1]));
            }
        }

        return $data;
    }

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
            'contact_number'    => 'required|string|max:20',
            'reg_email'         => 'nullable|email|max:255',
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
                    $path = $file->store('certificates', 'public');
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
                    'jobseeker_nsrp_registration_id' => $nsrp->id,
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
                    'jobseeker_nsrp_registration_id' => $nsrp->id,
                    'category'    => 'eligibility',
                    'name'        => $e['name'],
                    'date_taken'  => $safeParseDate($e['date_taken'] ?? null),
                ]);
            }
        }

        foreach ($request->licenses ?? [] as $l) {
            if (!empty($l['name'])) {
                \App\Models\JobseekerCertification::create([
                    'jobseeker_nsrp_registration_id' => $nsrp->id,
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
    public function approveJob($id)
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
        // Local Office Based / Job Fair: Job Vacancy staff ra
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

        // ── Job Fair postings: magpabilin nga CLOSED human ma-approve — si Job Fair staff pa ang mo-open niini pag-create og event ──
        $job->update([
            'posting_status' => 'approved',
            'status'         => $isJobFair ? 'closed' : 'open',
            'remarks'        => null,
        ]);

        \App\Models\Announcement::sendToEmployers([
            'type'           => 'job_approved',
            'title'          => 'Job Posting Approved ✅',
            'message'        => $isJobFair
                ? 'Your job posting "' . $job->title . '" has been approved for job fair use. It will go live once PESO opens a job fair event.'
                : 'Your job posting "' . $job->title . '" has been approved and is now live!',
            'reference_type' => 'job',
            'reference_id'   => $job->job_qualifications_id,
        ], $job->company_id);

        // ── Job Fair postings dili pa i-post sa jobseekers hangtod ma-open sa Job Fair staff ──
        if (!$isJobFair) {
            // ── Find jobseekers whose preferred_occupations match the job title ──
            $allJobseekerRegs = \App\Models\JobseekerRegistration::whereHas('user', fn($q) => $q->where('status', 'approved'))
                ->with('nsrp')
                ->get();

            $matchedIds = collect();
            $unmatchedIds = collect();
            $jobTitleLower = strtolower($job->title);

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
                    'message'        => 'A job vacancy matching your preferred position "' . $job->title . '" from ' . ($job->company->company_name ?? 'an employer') . ' is now available. Would you like to apply?',
                    'reference_type' => 'job',
                    'reference_id'   => $job->job_qualifications_id,
                ], $matchedIds);
            }

            // ── Generic notification for non-matched jobseekers ──
            if ($unmatchedIds->isNotEmpty()) {
                \App\Models\Announcement::sendToJobseekers([
                    'type'           => 'job_posted',
                    'title'          => 'New Job Vacancy Posted 💼',
                    'message'        => 'A new job vacancy "' . $job->title . '" from ' . ($job->company->company_name ?? 'an employer') . ' is now available!',
                    'reference_type' => 'job',
                    'reference_id'   => $job->job_qualifications_id,
                ], $unmatchedIds);
            }
        }

        return back()->with('success', $isJobFair
            ? 'Job fair posting approved. It will be posted once a job fair event is opened.'
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
        ));
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
            $tab      = request('tab', 'summary');
            if ($isSraJobFairView && $tab === 'placement') $tab = 'summary';
            $eventId  = request('event_id');
            $allEvents = \App\Models\JobFairEvent::orderByDesc('event_date')->get();
            $event     = $eventId ? \App\Models\JobFairEvent::find($eventId) : null;

            // Job IDs nga gi-"bring" sa mga employer niini nga event
            $eventJobIds = $eventId
                ? \App\Models\JobFairEmploymentRequest::where('job_fair_id', $eventId)->pluck('job_id')
                : collect();

            // ── TAB 1: ATTENDANCE ──
            $attendanceFilter = request('attendance_filter', 'all');
            $registrations = null;
            $totalRegistered = $totalAttended = $totalLocalAttendance = $totalOverseasAttendance = 0;

            if ($tab === 'attendance' && $eventId) {
                if ($isSraJobFairView) $attendanceFilter = 'overseas';
                $regQuery = \App\Models\JobFairRegistration::with(['jobseeker.nsrp', 'jobseeker.user'])
                    ->where('job_fair_id', $eventId)
                    ->when($attendanceFilter !== 'all', function ($q) use ($attendanceFilter) {
                        return $q->whereHas('jobseeker.nsrp', fn($n) => $n->whereIn('type', [$attendanceFilter, 'both']));
                    });
                $registrations = $regQuery->latest()->paginate(10);

                $baseQ = \App\Models\JobFairRegistration::where('job_fair_id', $eventId);
                $totalRegistered = (clone $baseQ)->count();
                $totalAttended   = (clone $baseQ)->where('is_attended', true)->count();
                $totalLocalAttendance    = (clone $baseQ)->whereHas('jobseeker.nsrp', fn($n) => $n->whereIn('type', ['local','both']))->count();
                $totalOverseasAttendance = (clone $baseQ)->whereHas('jobseeker.nsrp', fn($n) => $n->whereIn('type', ['overseas','both']))->count();
            }

            // ── TAB 2: LIST OF LOCAL/OVERSEAS COMPANIES FOR JOB FAIR ──
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

            // ── TAB 3: LIST OF FURTHER INTERVIEW (waiting status) ──
            $furtherInterview = null;
            if ($tab === 'further_interview' && $eventId) {
                $furtherInterview = \App\Models\Application::with(['jobseeker.nsrp', 'job.company'])
                    ->whereIn('job_id', $eventJobIds)
                    ->where('status', 'waiting')
                    ->when($isSraJobFairView, fn($q) => $q->whereHas('job.company', fn($c) => $c->where('is_overseas', true)))
                    ->latest()
                    ->paginate(10);
            }

            // ── TAB 4: HOTS — hired for a job brought to this event (dili na i-match ang eksaktong petsa, kay ang job mismo naka-scope na sa event via eventJobIds) ──
            $hots = null;
            if ($tab === 'hots' && $eventId && $event) {
                $hots = \App\Models\Application::with(['jobseeker', 'job.company'])
                    ->whereIn('job_id', $eventJobIds)
                    ->where('status', 'hired')
                    ->when($isSraJobFairView, fn($q) => $q->whereHas('job.company', fn($c) => $c->where('is_overseas', true)))
                    ->latest()
                    ->paginate(10);
            }

            // ── TAB 5: POST JOB FAIR SUMMARY REPORT ──
            $summaryParticipants = collect();
            $summaryTotals = ['vacancies' => 0, 'interviewed' => 0, 'male' => 0, 'female' => 0, 'qualified' => 0, 'hired' => 0];

            if ($tab === 'summary' && $eventId) {
                $participants = \App\Models\JobFairParticipant::with('employer')
                    ->where('job_fair_id', $eventId)
                    ->where('confirmation_status', 'confirmed')
                    ->when($isSraJobFairView, fn($q) => $q->whereHas('employer', fn($e) => $e->where('is_overseas', true)))
                    ->get();

                $summaryParticipants = $participants->map(function ($p) use ($eventId) {
                    $jobIds = \App\Models\JobFairEmploymentRequest::where('job_fair_id', $eventId)
                        ->where('employer_id', $p->employer_id)
                        ->pluck('job_id');

                    $apps = \App\Models\Application::with('jobseeker')->whereIn('job_id', $jobIds)->get();

                    $p->vacancies   = \App\Models\Job::whereIn('job_qualifications_id', $jobIds)->sum('slots');
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

            // ── TAB 6: TOTAL COMPANIES WITH VACANCIES (per Industry Group) ──
            $industryLocal    = collect();
            $industryOverseas = collect();

            if ($tab === 'industry' && $eventId) {
                $jobs = \App\Models\Job::with('company')->whereIn('job_qualifications_id', $eventJobIds)->get();

                $industryLocal = $jobs->filter(fn($j) => !($j->company->is_overseas ?? false))
                    ->groupBy('industry_group')
                    ->map(fn($group) => $group->sum('slots'));

                $industryOverseas = $jobs->filter(fn($j) => $j->company->is_overseas ?? false)
                    ->groupBy('industry_group')
                    ->map(fn($group) => $group->sum('slots'));
            }

            // ── TAB: TOP EMPLOYERS (job fair participation count, per employer) — placeholder logic, i-refine pa sa ulahi ──
            $topEmployersFilter = request('top_employers_filter', 'monthly');
            $topEmployersMonth  = request('top_employers_month');
            $topEmployersYear   = request('top_employers_year');
            $topEmployersByOfficeBasedInterviews = collect();

            if ($tab === 'top_employers') {
                $participantQuery = \App\Models\JobFairParticipant::with('employer')
                    ->where('confirmation_status', 'confirmed')
                    ->when($isSraJobFairView, fn($q) => $q->whereHas('employer', fn($e) => $e->where('is_overseas', true)));

                if ($topEmployersFilter === 'yearly') {
                    $selectedYear = $topEmployersYear ?: now()->year;
                    $participantQuery->whereYear('created_at', $selectedYear);
                } else {
                    $selectedMonth = $topEmployersMonth ?: now()->format('Y-m');
                    [$selYear, $selMon] = array_pad(explode('-', $selectedMonth), 2, now()->month);
                    $participantQuery->whereYear('created_at', $selYear)->whereMonth('created_at', $selMon);
                }

                $topEmployersByOfficeBasedInterviews = $participantQuery->get()
                    ->groupBy('employer_id')
                    ->map(fn($group) => [
                        'employer'             => $group->first()->employer,
                        'participation_count'  => $group->count(),
                    ])
                    ->sortByDesc('participation_count')
                    ->take(5)
                    ->values();
            }

            // ── TAB 7: COMPANY PLACEMENT REPORT (local only, hired AFTER event date) ──
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
                'placementReport', 'isSraJobFairView', 'reportView',
                'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear', 'topEmployersByOfficeBasedInterviews'
            ));
        }

        if (!in_array($staffRole, ['lra', 'sra'])) return redirect()->route('staff.dashboard');

        $search      = request('search');
        $tab         = request('tab', 'registered');
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
            }));

        $totalRegisteredAll = (clone $registeredAllQuery)->count();

        // ── TAB 1b: jobseekers nga ni-Accept/Join sa in-house interview (Application.inhouse_participation) ──
        $registeredQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->where('inhouse_participation', 'accepted')
            ->whereHas('job', fn($q) => $q->where('schedule_type', 'inhouse'))
            ->whereHas('job.company', fn($q) => $q->where('is_overseas', $isOverseas))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
            ));

        $totalRegistered = (clone $registeredQuery)->count();

        // ── TAB 2: PLACED APPLICANTS — status = hired ──
        $placedQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->where('status', 'hired')
            ->whereHas('jobseeker.nsrp', fn($r) => $r->whereIn('type', $jobseekerType))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            ));

        $totalPlaced = (clone $placedQuery)->count();

        // ── TAB 3: JOB APPLICANTS REFERRED — status = waiting OR rejected ──
        $referredQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->whereIn('status', ['waiting', 'rejected'])
            ->whereHas('jobseeker.nsrp', fn($r) => $r->whereIn('type', $jobseekerType))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            ));

        $totalReferred = (clone $referredQuery)->count();

        $vacancyMonth = request('vacancy_month', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-\d{2}$/', $vacancyMonth)) {
            $vacancyMonth = now()->format('Y-m');
        }
        [$vacancyYear, $vacancyMonthNumber] = array_pad(explode('-', $vacancyMonth), 2, now()->format('m'));

        $solicitedJobs = collect();
        $totalVacanciesSolicited = 0;
        if ($staffRole === 'sra') {
            $vacancyQuery = \App\Models\Job::with('company')
                ->whereHas('company', fn($q) => $q->where('is_overseas', true))
                ->where('posting_status', 'approved')
                ->whereYear('updated_at', $vacancyYear)
                ->whereMonth('updated_at', $vacancyMonthNumber)
                ->where(function ($q) {
                    $q->whereNull('schedule_type')
                      ->orWhere('schedule_type', 'office_based')
                      ->orWhere(function ($q2) {
                          $q2->where('schedule_type', 'inhouse')
                             ->where('posting_status', 'approved');
                      });
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

        // ── TAB: TOP EMPLOYERS (in-house interview participation count, per employer) — placeholder logic, i-refine pa sa ulahi ──
        $topEmployersFilter = request('top_employers_filter', 'monthly');
        $topEmployersMonth  = request('top_employers_month');
        $topEmployersYear   = request('top_employers_year');
        $topEmployersByOfficeBasedInterviews = collect();

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

            $topEmployersByOfficeBasedInterviews = $inhouseQuery->get()
                ->groupBy('company_id')
                ->map(fn($group) => [
                    'employer'        => $group->first()->company,
                    'interview_count' => $group->count(),
                ])
                ->sortByDesc('interview_count')
                ->take(5)
                ->values();
        }

        return view('staff.reports.index', compact(
            'staffRole', 'tab', 'registeredView', 'reportView',
            'registeredParticipants', 'registeredAll', 'placedApplications', 'referredApplications',
            'totalRegistered', 'totalRegisteredAll', 'totalPlaced', 'totalReferred',
            'vacancyMonth', 'solicitedJobs', 'totalVacanciesSolicited',
            'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear', 'topEmployersByOfficeBasedInterviews'
        ));
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
            'phone' => 'nullable|string|max:20',
        ]);

        $staff->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $staff = $this->authStaff();
        if (!$staff) return redirect()->route('login');

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
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

        \App\Models\Announcement::where('id', $id)
            ->where('staff_id', $staffRecord->id)
            ->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead()
    {
        $staffRecord = \App\Models\Staff::where('user_id', Auth::id())->first();
        if (!$staffRecord) return back();

        \App\Models\Announcement::where('staff_id', $staffRecord->id)
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
            ? \App\Models\Announcement::where('staff_id', $staffRecord->id)->latest()->get()
            : collect();

        return view('staff.notifications.index', compact('notifications'));
    }
}