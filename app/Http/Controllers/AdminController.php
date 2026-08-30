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

        // Ang nangagi nga meeting wala gitangtang sa lamesa — kasaysayan man
        // siya — apan ang lista sa dashboard mao ang gamiton nga trabahoan,
        // mao nga ang umaabot ra ang gilista.
        $officeEvents = \App\Models\OfficeCalendarEvent::query()
            ->where(function ($q) {
                $q->whereDate('start_date', '>=', today())
                  ->orWhereDate('end_date', '>=', today());
            })
            ->orderBy('start_date')
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
            'todayInhouse',
            'officeEvents'
        ));
    }

    // ───────────────────────────────
    // OFFICE CALENDAR
    //
    // Dinhi mag-marka ang admin sa adlaw nga puliki ang opisina — meeting,
    // training, aktibidad, sarado. Ang upat ka staff calendar mobasa niini, ug
    // dili na ma-book ang maong adlaw: anaa sa miting ang staff nga mo-atiman
    // sa in-house o job fair.
    //
    // Ang admin ra ang makasulat dinhi. Ang tanang entry makita sa upat ka
    // staff — usa ra ka opisina, dili siya ma-libre para sa usa ug puliki para
    // sa uban.
    // ───────────────────────────────

    /** Feed sa kalendaryo sa admin. Makita niya ang tanan: office, job fair, in-house. */
    public function officeCalendarData()
    {
        return response()->json([
            'dates'    => \App\Support\StaffCalendar::forRole('admin'),
            'holidays' => \App\Support\Holidays::aroundNow(),
            'blocked'  => \App\Support\OfficeCalendar::blockedDates(),
            'legend'   => \App\Support\StaffCalendar::TYPES,
        ]);
    }

    public function storeOfficeEvent(Request $request)
    {
        $data = $this->validateOfficeEvent($request);

        $event = \App\Models\OfficeCalendarEvent::create($data + [
            'created_by' => Auth::user()->users_id,
        ]);

        $this->notifyStaffOfficeEvent($event, 'added');

        return back()->with('success', $this->officeEventFlash($event, 'added'));
    }

    public function updateOfficeEvent(Request $request, $id)
    {
        $event = \App\Models\OfficeCalendarEvent::findOrFail($id);
        $event->update($this->validateOfficeEvent($request));

        $this->notifyStaffOfficeEvent($event, 'updated');

        return back()->with('success', $this->officeEventFlash($event, 'updated'));
    }

    public function deleteOfficeEvent($id)
    {
        $event = \App\Models\OfficeCalendarEvent::findOrFail($id);
        $title = $event->title;
        $when  = $event->date_range_label;

        $this->notifyStaffOfficeEvent($event, 'cancelled');
        $event->delete();

        return back()->with('success', '"' . $title . '" on ' . $when . ' removed. The date is open for booking again.');
    }

    private function validateOfficeEvent(Request $request): array
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'type'       => 'required|in:' . implode(',', array_keys(\App\Models\OfficeCalendarEvent::TYPES)),
            'start_date' => 'required|date',
            // Usa ka adlaw ra: biyai nga blangko ang end_date.
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i|after:start_time',
            'location'   => 'nullable|string|max:255',
            'notes'      => 'nullable|string|max:2000',
        ], [
            'end_date.after_or_equal' => 'The end date cannot be before the start date.',
            'end_time.after'          => 'The end time must be later than the start time.',
        ]);

        // Ang usa ka adlaw nga entry walay end_date. Kung parehas ra sila,
        // himoon nato nga null aron usa ra ang porma sa datos.
        if (!empty($validated['end_date']) && $validated['end_date'] === $validated['start_date']) {
            $validated['end_date'] = null;
        }

        return $validated;
    }

    /**
     * Ang flash message mosulti kung naa'y na-book na sulod sa gimarkahan nga
     * adlaw. Wala kanselaha ang naabtan — desisyon na sa opisina kung unsaon
     * — apan kinahanglan mahibaw-an dayon sa admin nga naa'y banggaan.
     */
    private function officeEventFlash(\App\Models\OfficeCalendarEvent $event, string $verb): string
    {
        $message = '"' . $event->title . '" ' . $verb . ' for ' . $event->date_range_label
                 . '. Staff have been notified and the date is now blocked for booking.';

        $clashes = $this->officeEventClashes($event);

        if ($clashes > 0) {
            $message .= ' Note: ' . $clashes . ' activity(ies) were already booked in that span — '
                      . 'they were kept, not cancelled. Review them on the calendar.';
        }

        return $message;
    }

    /** Pila ka in-house o job fair ang naa na sulod sa gimarkahan nga adlaw. */
    private function officeEventClashes(\App\Models\OfficeCalendarEvent $event): int
    {
        $days     = \App\Support\OfficeCalendar::daysOf($event);
        $calendar = \App\Support\StaffCalendar::forRole('admin');
        $count    = 0;

        foreach ($days as $iso) {
            foreach ($calendar[$iso] ?? [] as $item) {
                if ($item['type'] !== 'office') $count++;
            }
        }

        return $count;
    }

    private function notifyStaffOfficeEvent(\App\Models\OfficeCalendarEvent $event, string $verb): void
    {
        $staffIds = \App\Models\Staff::whereIn('staff_role', ['lra', 'sra', 'job_fair', 'job_vacancy'])
            ->pluck('staff_id');

        if ($staffIds->isEmpty()) return;

        $when = $event->date_range_label . ($event->time_label ? ', ' . $event->time_label : '');

        $message = match ($verb) {
            'cancelled' => $event->type_label . ' "' . $event->title . '" on ' . $when
                           . ' has been cancelled. The date is open for booking again.',
            'updated'   => $event->type_label . ' "' . $event->title . '" has been moved or changed — it is now on '
                           . $when . ($event->location ? ' at ' . $event->location : '') . '.',
            default     => $event->type_label . ' "' . $event->title . '" is scheduled on ' . $when
                           . ($event->location ? ' at ' . $event->location : '')
                           . '. In-house interviews and job fairs cannot be booked on that date.',
        };

        \App\Models\Announcement::sendToStaff([
            'type'           => 'office_schedule',
            'title'          => $verb === 'cancelled' ? 'Office Schedule Cancelled 🗓️' : 'Office Schedule 🗓️',
            'message'        => $message,
            'reference_type' => 'office_calendar_event',
            'reference_id'   => $event->office_calendar_events_id,
        ], $staffIds);
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

        // ── Pila ang naghupot sa matag desk karon.
        // ──
        // ── PESO, 2026-08-26: usa ka desk mahimong duha o tulo ka tawo. Ang
        // ── SRA nga naay katabang dili ikaduhang desk — ikaduha siya ka
        // ── tawo sa parehas nga desk, ug pareho silang makadawat sa bell ug
        // ── pareho silang makabuhat sa trabaho.
        // ──
        // ── Ihap ni, dili utlanan. Kaniadto ang rol nga naay tawo gipatay sa
        // ── porma, mao nga ang admin dili gyud makadugang ug ikaduha bisan
        // ── pila pa ang trabaho sa maong desk. ──
        $staffRoleCounts = \App\Models\Staff::query()
            ->whereHas('user', fn($q) => $q->where('status', '!=', 'deactivated'))
            ->get()
            ->groupBy('staff_role')
            ->map->count()
            ->all();

        return view('admin.users.manage', compact('users', 'staffRoleCounts'));
    }

    // ───────────────────────────────
    // EMPLOYER ACCOUNT RECOVERY — backup ra kung wala ang LRA/SRA
    // ───────────────────────────────
    // Ang updateUser nag-abort gihapon ug 403 para sa company account, ug kana
    // magpabilin: ang Admin walay katungod sa kinatibuk-ang pag-edit sa
    // employer. Kini nga route usa ka piho ug gi-rekord nga buhat — ang bag-ong
    // HR nitawag, walay LRA/SRA nga makatabang, ug kinahanglan siya makasulod.
    //
    // Parehas gyud nga code sa staff (EmployerAccountRecovery), mao nga parehas
    // ang audit ug ang epekto bisan kinsa ang nag-buhat.
    public function recoverEmployerAccount(Request $request, $id)
    {
        $admin = Auth::user();
        if (!$admin || $admin->role !== 'admin') {
            return redirect()->route('login');
        }

        $employerUser = User::with('employerNsrp')->where('role', 'company')->findOrFail($id);

        if (!$employerUser->employerNsrp) {
            return back()->with('error', 'This employer has no NSRP registration record yet.');
        }

        $validated = $request->validate(
            \App\Support\EmployerAccountRecovery::rules($employerUser->users_id) + [
                'reset_password' => 'nullable|boolean',
            ],
            \App\Support\EmployerAccountRecovery::messages()
        );

        // — ANG PASSWORD RA —
        // Parehas nga tawo, dili makasulod: nakalimot sa password, o wala
        // moabot ang reset e-mail. Ang handover sa ubos mo-usab sa contact ug
        // sa email; kung kana ang gamiton dinhi, isulat pag-usab ang mga bili
        // nga naa na sila ug mag-file ug audit nga nagsulti nga nabalhin ang
        // account nga walay nabalhin.
        $contactChanged = \App\Support\EmployerAccountRecovery::contactChanged($employerUser, $validated);

        if (!$contactChanged) {
            if (!$request->boolean('reset_password')) {
                return back()->with('info',
                    'Nothing was changed. Edit the contact details, or tick "Reset their password too".');
            }

            $tempPassword = \App\Support\EmployerAccountRecovery::resetPassword(
                $employerUser, $validated['reason'], $admin
            );

            return back()
                ->with('recovery_temp_password', $tempPassword)
                ->with('recovery_contact', $employerUser->employerNsrp->contact_person ?? $employerUser->name)
                ->with('success',
                    'Password reset for ' . ($employerUser->employerNsrp->company_name ?? $employerUser->name)
                    . '. Read the temporary password below to them now — it is shown only once, '
                    . 'and they must change it the moment they log in.');
        }

        $result = \App\Support\EmployerAccountRecovery::perform($employerUser, $validated, $admin);

        if ($result['method'] === \App\Support\EmployerAccountRecovery::METHOD_TEMP_PASSWORD) {
            return back()
                ->with('recovery_temp_password', $result['temp_password'])
                ->with('recovery_contact', $validated['new_contact_person'])
                ->with('success',
                    'Account recovered for ' . $validated['new_contact_person']
                    . '. Read the temporary password below to them now — it is shown only once, '
                    . 'and they must change it the moment they log in.');
        }

        if ($result['mail_failed']) {
            return back()->with('error',
                'The account was recovered for ' . $validated['new_contact_person']
                . ', but the verification email could not be sent. Ask them to use "Forgot Password" on the login page.');
        }

        return back()->with('success',
            'Account recovered for ' . $validated['new_contact_person']
            . '. A 6-digit verification code was emailed to ' . $validated['new_email'] . '.');
    }

    // ───────────────────────────────
    // STORE STAFF USER
    // ───────────────────────────────
    public function storeUser(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => ['nullable', 'string', new \App\Rules\MobileNumber],
            // The full policy does not apply to what the admin types here.
            // Nobody keeps this password: `must_change_password` below stops
            // the account at the door until the staff member sets their own,
            // and THAT one is held to PasswordPolicy in full.
            'password'   => ['nullable', 'string', 'max:255'],
            'staff_role' => 'required|in:sra,lra,job_fair,job_vacancy',
        ]);

        // Left blank on purpose most of the time: the first name in small
        // letters is what the admin reads out, so there is nothing to invent.
        $starterPassword = $request->filled('password')
            ? $request->password
            : \App\Support\StarterPassword::fromName($request->name);

        $newStaff = User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'password'             => Hash::make($starterPassword),
            'phone'                => $request->phone,
            'role'                 => 'staff',
            'status'               => 'approved',
            'must_change_password' => true,
        ]);

        $nameParts = explode(' ', $request->name, 2);

        \App\Models\Staff::create([
            'user_id'     => $newStaff->users_id,
            'staff_role'  => $request->staff_role,
            'first_name'  => $nameParts[0] ?? $request->name,
            'last_name'   => $nameParts[1] ?? '',
            'middle_name' => null,
            'phone'       => $request->phone,
        ]);

        return back()
            ->with('recovery_temp_password', $starterPassword)
            ->with('recovery_contact', $request->name)
            ->with('success', 'Staff account created for ' . $request->name
                . '. Read the starter password below to them — they will be asked to'
                . ' replace it the first time they log in.');
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
            // Same reasoning as storeUser: blank means keep the current one,
            // and anything typed here is a starter the staff member is forced
            // to replace at their next login.
            'password'         => ['nullable', 'string', 'max:255'],
            'update_staff_role'=> 'required|in:sra,lra,job_fair,job_vacancy',
        ]);

        $staffRecord = \App\Models\Staff::where('user_id', $user->users_id)->first();

        // ── Walay pagsusi ug doble nga rol dinhi.
        // ──
        // ── Naa siya kaniadto: ang pagbalhin sa usa ka tawo ngadto sa desk nga
        // ── naay tawo gisalikway. Apan ang desk dili puwesto nga usa ra —
        // ── duha ka tawo sa Job Vacancy managsama ang mahimo, ug pareho silang
        // ── makadawat sa bell. Ang tanan nga nagpadala ug notipikasyon nag-
        // ── pluck sa TANAN nga naghupot sa rol, mao nga walay mahulog. ──

        $data = ['status' => $request->status];

        if ($request->filled('password')) {
            $data['password']             = Hash::make($request->password);
            $data['must_change_password'] = true;
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
            'email' => 'required|email|unique:users,email,' . $admin->users_id . ',users_id',
            'phone' => ['nullable', 'string', new \App\Rules\MobileNumber],
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->hasFile('profile_photo')) {
            if ($admin->profile_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($admin->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $admin->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => \App\Rules\PasswordPolicy::required(),
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
    // Same rule as the Job Activities tabs: looking at the page is what
    // clears its counter in the sidebar.
    \App\Support\AdminInbox::markRegistrationsSeen();

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

    // ── MONTHLY/YEARLY BREAKDOWN — Local/Overseas/Both counts para sa gipili nga period ──
    $periodFilter = request('period_filter', 'monthly');
    $periodMonth  = request('period_month');
    $periodYear   = request('period_year');

    $periodQuery = JobseekerRegistration::query();

    if ($periodFilter === 'yearly') {
        $selectedYear = $periodYear ?: now()->year;
        $periodQuery->whereYear('created_at', $selectedYear);
    } else {
        $selectedMonth = $periodMonth ?: now()->format('Y-m');
        [$py, $pm] = array_pad(explode('-', $selectedMonth), 2, now()->month);
        $periodQuery->whereYear('created_at', $py)->whereMonth('created_at', $pm);
    }

    $periodLocal    = (clone $periodQuery)->whereHas('nsrp', fn($q) => $q->where('type', 'local'))->count();
    $periodOverseas = (clone $periodQuery)->whereHas('nsrp', fn($q) => $q->where('type', 'overseas'))->count();
    $periodBoth     = (clone $periodQuery)->whereHas('nsrp', fn($q) => $q->where('type', 'both'))->count();

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
        'registrations',
        'periodFilter', 'periodMonth', 'periodYear', 'periodLocal', 'periodOverseas', 'periodBoth'
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

        // Opening the full list is the admin saying they have seen everything.
        \App\Support\AdminInbox::markAllSeen();

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAllNotificationsRead()
    {
        // `is_read` belongs to the person the notice was addressed to. Setting
        // it from here used to clear the red dot in a jobseeker's, employer's
        // or staff member's own bell for a notice they had never opened. The
        // admin marks their own column instead.
        \App\Support\AdminInbox::markAllSeen();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function clearAllNotifications()
    {
        // This used to be `Announcement::query()->delete()` — every notice in
        // the office, for every account, gone. The admin dismissing their own
        // list is not a reason to empty somebody else's bell, so the rows stay
        // and are simply marked seen.
        \App\Support\AdminInbox::markAllSeen();

        return back()->with('success', 'Notifications cleared from your list.');
    }

    // ───────────────────────────────
    // MANAGE JOB ACTIVITIES
    // ───────────────────────────────
    public function jobActivities()
    {
        $tab = request('tab', 'inhouse');

        // A tab that no longer exists renders an empty page, and the Job
        // Solicitation tab was linked from bookmarks and from the sidebar for
        // months. Send anything unrecognised to the first tab instead.
        if (!in_array($tab, ['inhouse', 'jobfair', 'companyinterview'], true)) {
            return redirect()->route('admin.job.activities');
        }

        // Looking at a tab is what clears its counter — not a button the admin
        // has to remember to press.
        \App\Support\AdminInbox::markTabSeen($tab);

        // Tab 1 — In-house interviews (LRA/SRA)
        //
        // The NSRP row calls its account `employer`, not `user`; it is loaded
        // for the e-mail address on the detail modal, the only company field
        // that does not live on the NSRP row itself.
        $inhouseSchedules = \App\Models\InhouseSchedule::with('employer.employer')
            ->latest()
            ->get();

        // The employer's own postings, so the modal can put slots and pay
        // beside each position they asked to interview for. One query for the
        // whole page rather than one per schedule.
        $inhousePostings = \App\Models\Job::whereIn('company_id', $inhouseSchedules->pluck('employer_id')->unique())
            ->get()
            ->groupBy('company_id');

        foreach ($inhouseSchedules as $schedule) {
            $schedule->postings = $inhousePostings[$schedule->employer_id] ?? collect();

            $schedule->job_offers = \Illuminate\Support\Facades\DB::table('inhouse_participants')
                ->where('inhouse_schedule_id', $schedule->inhouse_schedules_id)
                ->whereIn('jobseeker_id', function ($q) use ($schedule) {
                    $q->select('jobseeker_id')->from('job_matching')
                      ->where('status', 'hired')
                      ->whereIn('job_id', function ($jq) use ($schedule) {
                          $jq->select('job_qualifications_id')->from('job_qualifications')
                             ->where('company_id', $schedule->employer_id);
                      });
                })->count();
        }

        // The Job Solicitation tab is gone (2026-08-28). Every posting in the
        // system carries posting_type = 'direct', so the tab listed all of
        // them — the in-house ones, the company interviews and the job fair
        // ones — under a fourth name, and the same row appeared twice on the
        // page. The three remaining tabs each answer for one kind.

        // Tab 3 — Job Fair, one row per event.
        //
        // The table used to be one row per invitation, so a fair with twelve
        // employers filled twelve rows and repeated its own title, date and
        // venue on every one of them. The event is the thing the admin is
        // looking at; the employers on it are the detail, and the detail opens
        // in a modal.
        $jobFairEvents = \App\Models\JobFairEvent::with(['participants.employer'])
            ->orderByDesc('event_date')
            ->get()
            ->each(function ($event) {
                // Alphabetical, because the row number in the modal is a
                // position on the list and not something to quote back.
                $event->roster = $event->participants
                    ->sortBy(fn($p) => strtolower($p->employer->company_name ?? ''))
                    ->values();

                // When the invitations for this fair went out. They are sent in
                // one batch, so the earliest is the date of the batch; a fair
                // nobody has been invited to yet has none.
                $event->invited_on = $event->participants->min('invited_at');
            });

        // Tab 4 — Company Interview postings.
        //
        // Earliest interview first: the admin reads this to see what is coming
        // up, and `latest()` put the furthest-away month at the top. A posting
        // with no date sits at the bottom rather than pretending to be next.
        $companyInterviewJobs = \App\Models\Job::with('company')
            ->where('schedule_type', 'company_interview')
            ->orderByRaw('preferred_date IS NULL')
            ->orderBy('preferred_date')
            ->get();

        return view('admin.job_activities.index', compact(
            'inhouseSchedules',
            'jobFairEvents',
            'companyInterviewJobs'
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
        $registeredView = request('registered_view', 'all');
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

        $registeredAllQuery = \App\Models\JobseekerRegistration::with(['user', 'nsrp'])
            ->whereHas('nsrp', fn($q) => $q->whereIn('type', $jobseekerType))
            ->when($search, fn($q) => $q->where(function ($sub) use ($search) {
                $sub->where('first_name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            }));

        $totalRegisteredAll = (clone $registeredAllQuery)->count();

        $registeredQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->where('inhouse_participation', 'accepted')
            ->whereHas('job', fn($q) => $q->where('schedule_type', 'inhouse'))
            ->whereHas('job.company', fn($q) => $q->where('is_overseas', $isOverseas))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('first_name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
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

        $registeredParticipants = ($tab === 'registered' && $registeredView === 'inhouse') ? $registeredQuery->latest()->paginate(10) : null;
        $registeredAll          = ($tab === 'registered' && $registeredView === 'all') ? $registeredAllQuery->latest()->paginate(10) : null;
        $placedApplications     = $tab === 'placed'     ? $placedQuery->latest()->paginate(10)     : null;
        $referredApplications   = $tab === 'referred'   ? $referredQuery->latest()->paginate(10)   : null;

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

        return view('staff.reports.index', compact(
            'staffRole', 'tab', 'registeredView',
            'registeredParticipants', 'registeredAll', 'placedApplications', 'referredApplications',
            'totalRegistered', 'totalRegisteredAll', 'totalPlaced', 'totalReferred', 'solicitationStats',
            'topEmployersByInhouseInterviews', 'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear',
            'inhouseReport', 'companyInterviews'
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

        // ── Total kay sa TIBUOK filtered set (dili lang sa current page), mao nga clone una ang query ──
        // ── groupLeaders: ang usa ka position nga gi-post sa daghang schedule
        // ── type kay usa ra gihapon ka set sa bakante, dili tag-usa kada row. ──
        $totalVacancies = (clone $query)->groupLeaders()->sum('slots');
        $jobs = $query->orderBy('title')->paginate(5)->withQueryString();

        // ── Parehas nga ranking sa Job Vacancy staff: pila ka bakante ang
        // ── gihatag, dili pila ka higayon nga miapil. Ang groupLeaders()
        // ── nagpugong sa doble-ihap sa posisyon nga gi-post sa daghang channel.
        // ── Kung mausab kini, usba pud ang StaffWebController::jobVacancyReports. ──
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

        // The desk's other half — see App\Support\CompanyInterviewReport.
        $companyInterviews = $tab === 'company_interview'
            ? \App\Support\CompanyInterviewReport::paginate($year, $mon, $search)
            : null;

        return view('staff.job_vacancy.reports', compact(
            'jobs', 'month', 'totalVacancies', 'search', 'tab',
            'topEmployers', 'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear',
            'companyInterviews'
        ) + ['layout' => 'admin.layouts.app', 'reportRouteName' => 'admin.reports.staffJobVacancy']);
    }

    // ───────────────────────────────
    // REPORTS
    // ───────────────────────────────
    // ───────────────────────────────
    // REPORTS — OVERVIEW
    // ───────────────────────────────
    /**
     * What the office did in a stretch of time.
     *
     * PESO, 2026-08-26: the Admin wants three numbers for a month — how many
     * were hired, how many jobseekers applied, how many employers registered —
     * and wants to move the dates. The desk reports underneath answer "how is
     * this desk doing"; this one answers "how did the office do".
     *
     * Defaults to the current month, because that is the question actually
     * asked. The range is inclusive on both ends: a report "for August" that
     * silently drops the 31st is worse than no report.
     */
    public function reports()
    {
        [$from, $to] = $this->reportRange();

        $inRange = fn($query, string $column) => $query
            ->whereDate($column, '>=', $from)
            ->whereDate($column, '<=', $to);

        // Ang na-hire: gikan sa hired_at, dili sa updated_at. Ang updated_at
        // mausab sa bisan unsa nga mohikap sa row, ug ang report sa bulan
        // molukso-lukso kada higayon nga adunay mag-edit ug daan nga rekord.
        $hired = $inRange(
            \App\Models\Application::where('status', 'hired'), 'hired_at'
        )->count();

        $applied         = $inRange(\App\Models\Application::query(), 'created_at')->count();
        $employersJoined = $inRange(\App\Models\EmployerNsrpRegistration::query(), 'created_at')->count();
        $jobseekersJoined = $inRange(\App\Models\JobseekerRegistration::query(), 'created_at')->count();
        $vacanciesPosted = $inRange(\App\Models\Job::query(), 'created_at')->count();

        // Ang bahin sa lokal ug overseas: ang parehas nga numero, gibahin sa
        // duha ka desk, aron makita kung asa gikan ang kalihokan.
        $hiredLocal = $inRange(
            \App\Models\Application::where('status', 'hired')
                ->whereHas('job.company', fn($q) => $q->where('is_overseas', false)),
            'hired_at'
        )->count();

        return view('admin.reports.overview', [
            'from'             => $from,
            'to'               => $to,
            'hired'            => $hired,
            'hiredLocal'       => $hiredLocal,
            'hiredOverseas'    => $hired - $hiredLocal,
            'applied'          => $applied,
            'employersJoined'  => $employersJoined,
            'jobseekersJoined' => $jobseekersJoined,
            'vacanciesPosted'  => $vacanciesPosted,
        ]);
    }

    /**
     * The dates the report covers, as [from, to].
     *
     * Anything unreadable falls back to this month rather than erroring: a
     * report page that will not open because of a typed date is no use to
     * anyone. A backwards range is swapped rather than returning nothing.
     */
    private function reportRange(): array
    {
        $parse = function (?string $value) {
            if (blank($value)) return null;
            try { return \Carbon\Carbon::parse($value)->startOfDay(); }
            catch (\Throwable $e) { return null; }
        };

        $from = $parse(request('from')) ?: now()->startOfMonth();
        $to   = $parse(request('to'))   ?: now()->endOfMonth();

        return $from->greaterThan($to) ? [$to, $from] : [$from, $to];
    }
}