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
        // ── Ang sumada sa duha ka card sa ilawom niini.
        // ──
        // ── PESO admin, 2026-10-13: ang card nag-ihap ug ACCOUNT samtang ang
        // ── Jobseekers nga card sa ilawom niini nag-ihap ug REGISTRATION. Ang
        // ── kalainan mao ang 14 ka walk-in nga gi-encode sa opisina gikan sa
        // ── papel ug walay account, mao nga 235 + 45 = 280 ug ang Total Users
        // ── nag-ingon ug 266. Usa ka panid nga dili mo-sibo sa kaugalingon
        // ── niyang aritmetika.
        // ──
        // ── Ang tulo ka card nag-ihap na ug TAWO karon: ang jobseeker nga
        // ── nakapuno sa NSRP, bisan asa gikan ang porma, ug ang establisemento.
        // ── Ang card mismo nagsulti kung unsa ang sulod niini. ──
        // ── Ang BUHI ra.
        // ──
        // ── PESO admin, 2026-10-13: ang card nag-ihap sa tanan, apil ang
        // ── gipatay. Ang account nga na-deactivate dili na makasulod, ug ang
        // ── dormant nga employer usa ra ka pahina ang maabot — wala silay
        // ── gigamit nga sistema, mao nga ang pag-ihap kanila sa "Total Users"
        // ── naghimo sa numero nga ihap sa rekord, dili sa tawo.
        // ──
        // ── Ang walk-in walay account, mao nga wala siyay status nga mapatay:
        // ── tinuod siya nga jobseeker nga gi-encode sa opisina, ug giihap. ──
        $live = fn($q) => $q->whereNotIn('status', ['deactivated', 'dormant']);

        $jobseekerTotal    = JobseekerRegistration::whereHas('nsrp')
                                 ->where(fn($q) => $q->whereNull('user_id')
                                                     ->orWhereHas('user', $live))
                                 ->count();
        $companyTotal      = \App\Models\EmployerNsrpRegistration::whereHas('employer', $live)
                                 ->whereNull('dormant_at')
                                 ->count();
        // Ang staff tawo pud nga naggamit sa sistema, ug ang Manage Users —
        // nga mao ang giablihan niini nga card — naglista kanila uban sa
        // jobseeker ug sa kompanya. Apil ang na-deactivate: naa pa sila didto,
        // sa Inactive nga tab.
        $staffTotal        = User::where('role', 'staff')->where($live)->count();
        $totalUsers        = $jobseekerTotal + $companyTotal + $staffTotal;
        $activeUsers       = User::whereIn('role', ['jobseeker', 'company'])->where('status', 'approved')->count();
        $deactivatedUsers  = User::whereIn('role', ['jobseeker', 'company'])->where('status', 'deactivated')->count();
        // ── Ang giihap kay ang nakahuman sa NSRP, dili ang naka-create ug
        // ── account.
        // ──
        // ── PESO admin, 2026-10-13: ang account matawo dayon pag-sign up, apan
        // ── ang NSRP form mosunod pa — ug naay dili na gyud mobalik. Ang card
        // ── gi-klik aron adto sa Registrations, nga naglista sa NSRP; mao nga
        // ── ang numero nga dili mosibo sa gilista nagpasabot ug rekord nga
        // ── nawala.
        // ──
        // ── Ang wala pa nakahuman makita gihapon sa Manage Users, naay amber
        // ── nga badge, ug ang admin masultihan sa bell inig-create sa account. ──
        // ── Giihap ang REGISTRATION, dili ang account. Ang gi-encode sa opisina
        // ── gikan sa papel walay account — dose sila — ug jobseeker gihapon sila
        // ── sa listahan nga giablihan sa card. ──
        $jobseekerCount    = $jobseekerTotal;
        $jobseekerNoNsrp   = User::where('role', 'jobseeker')
                                 ->whereDoesntHave('registration.nsrp')
                                 ->count();
        // Ang ESTABLISEMENTO, dili ang account: usa ka HR mahimong maghupot ug
        // duha ka kompanya sa usa ka email, ug ang mo-adto sa fair, ang
        // nagpost ug bakante ug ang naay kaugalingong papel kay ang kompanya.
        // Mao pud ni ang giihap sa Registered Employer nga listahan.
        $companyCount      = $companyTotal;
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
            'jobseekerNoNsrp',
            'staffTotal',
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

        \App\Support\AdminAudit::record(
            'calendar_added',
            'Put "' . $event->title . '" on the office calendar for '
                . $event->date_range_label . '.',
            'calendar', $event->office_calendar_events_id
        );

        return back()->with('success', $this->officeEventFlash($event, 'added'));
    }

    public function updateOfficeEvent(Request $request, $id)
    {
        $event = \App\Models\OfficeCalendarEvent::findOrFail($id);
        $event->update($this->validateOfficeEvent($request));

        $this->notifyStaffOfficeEvent($event, 'updated');

        \App\Support\AdminAudit::record(
            'calendar_updated',
            'Changed "' . $event->title . '" on the office calendar — now '
                . $event->date_range_label . '.',
            'calendar', $event->office_calendar_events_id
        );

        return back()->with('success', $this->officeEventFlash($event, 'updated'));
    }

    public function deleteOfficeEvent($id)
    {
        $event = \App\Models\OfficeCalendarEvent::findOrFail($id);
        $title = $event->title;
        $when  = $event->date_range_label;

        $this->notifyStaffOfficeEvent($event, 'cancelled');

        // Before the delete: afterwards there is no id left to record.
        \App\Support\AdminAudit::record(
            'calendar_deleted',
            'Took "' . $title . '" off the office calendar. ' . $when . ' is open for booking again.',
            'calendar', $event->office_calendar_events_id
        );

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

        // ── Ang gi-encode sa opisina walay account, mao nga wala gyud siya dinhi.
        // ──
        // ── PESO admin, 2026-10-13: kining panid naglista ug `users` rows, ug ang
        // ── walk-in nga jobseeker walay usa — gi-type siya sa staff gikan sa
        // ── papel nga porma, ug ang `user_id` niya magpabilin nga null hangtod
        // ── siya mismo mo-register ug mo-angkon niini. Dose sila, ug ang admin
        // ── nangita sa usa kanila sa Jobseekers nga tab nga wala gyud sila didto.
        // ──
        // ── Gidala sila isip registration, dili isip account, kay kana gyud ang
        // ── naa — ug ang laray nagsulti niini. Ang na-angkon na nga rekord dili
        // ── na walk-in (gi-set sa linkWalkInRecord ang is_walk_in ngadto sa
        // ── false), mao nga dili siya madoble dinhi. ──
        $walkinJobseekers = \App\Models\JobseekerRegistration::with('nsrp')
            ->where('is_walk_in', true)
            ->whereNull('user_id')
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

        return view('admin.users.manage', compact('users', 'walkinJobseekers', 'staffRoleCounts'));
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

        // `+` sa array dili mo-overwrite sa key nga naa na, mao nga ang
        // temp_password gi-assign gyud — kung idugang ra siya sa tuo nga kilid,
        // ang luag nga rule gikan sa rules() maoy magpabilin ug ang gikinahanglan
        // nga pag-check dili gyud modagan.
        $rules = \App\Support\EmployerAccountRecovery::rules($employerUser->users_id);
        $rules['reset_password'] = 'nullable|boolean';

        // Gikinahanglan sa duha ka agianan nga naghatag ug password: ang reset
        // lang, ug ang handover nga gipili ang temp_password.
        $rules['temp_password'] = 'required_if:method,temp_password'
                                  . '|required_with:reset_password'
                                  . '|nullable|string|min:4|max:64';

        $validated = $request->validate(
            $rules,
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
                $employerUser, $validated['reason'], $admin, $validated['temp_password']
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

        $parts = self::splitStaffName($request->name);

        \App\Models\Staff::create([
            'user_id'     => $newStaff->users_id,
            'staff_role'  => $request->staff_role,
            'first_name'  => $parts['first'],
            'last_name'   => $parts['last'],
            'middle_name' => $parts['middle'],
            'phone'       => $request->phone,
        ]);

        // ── Ang Staff nga tab, dili ang gigikanan.
        // ──
        // ── PESO admin, 2026-10-13: ang buton makita sa tanang tab, mao nga ang
        // ── admin nga naa sa Jobseekers mohimo ug staff account ug mobalik sa
        // ── Jobseekers — ug ang account nga iyang bag-ong gibuhat wala gyud sa
        // ── iyang atubangan. Ang highlight magtudlo kaniya sa laray mismo. ──
        \App\Support\AdminAudit::record(
            'staff_created',
            'Created a staff account for ' . $request->name . ' on the '
                . (['sra' => 'SRA', 'lra' => 'LRA', 'job_fair' => 'Job Fair', 'job_vacancy' => 'Job Vacancy'][$request->staff_role] ?? $request->staff_role)
                . ' desk.',
            'staff', $newStaff->users_id
        );

        return redirect()
            ->route('admin.users.manage', ['tab' => 'staff', 'highlight' => $newStaff->users_id])
            ->with('recovery_temp_password', $starterPassword)
            ->with('recovery_contact', $request->name)
            ->with('success', 'Staff account created for ' . $request->name
                . '. Read the starter password below to them — they will be asked to'
                . ' replace it the first time they log in.');
    }

    /**
     * One typed line into a given name, a middle initial and a surname.
     *
     * The form asks for "First name, M.I., Last name", because that is how the
     * office writes a name and because `staff` keeps the three apart. This used
     * to be `explode(' ', $name, 2)`, which filed everything after the first
     * word as the surname — "Juan C. Dela Cruz" was surnamed "C. Dela Cruz",
     * and middle_name was hardcoded null for everybody.
     *
     * The middle name is only taken when the second word is an initial: one
     * letter, with or without a period. A spelled-out middle name is
     * indistinguishable from the first word of a two-word surname — "Juan Dela
     * Cruz" would lose "Dela" — so those stay with the surname, which is where
     * the whole name used to go in any case.
     */
    private static function splitStaffName(string $name): array
    {
        $words = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $first  = array_shift($words) ?? $name;
        $middle = null;

        if (count($words) > 1 && preg_match('/^\p{L}\.?$/u', $words[0])) {
            $middle = array_shift($words);
        }

        return [
            'first'  => $first,
            'middle' => $middle,
            'last'   => implode(' ', $words),
        ];
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

        $wasStatus = $user->status;
        $wasRole   = $staffRecord?->staff_role;

        $data = ['status' => $request->status];

        if ($request->filled('password')) {
            $data['password']             = Hash::make($request->password);
            $data['must_change_password'] = true;
        }

        $user->update($data);

        if ($staffRecord) {
            $staffRecord->update(['staff_role' => $request->update_staff_role]);
        }

        // ── Three separate things, logged separately.
        // ──
        // ── One line saying "updated the account" tells the other two
        // ── administrators that something happened and not what, which is the
        // ── question they opened the page to answer. --
        $roleNames = ['sra' => 'SRA', 'lra' => 'LRA', 'job_fair' => 'Job Fair', 'job_vacancy' => 'Job Vacancy'];

        if ($wasStatus !== $request->status) {
            \App\Support\AdminAudit::record(
                'staff_status_changed',
                'Set ' . $user->name . ' to '
                    . ($request->status === 'deactivated' ? 'Inactive' : 'Active') . '.',
                'staff', $user->users_id
            );
        }

        if ($staffRecord && $wasRole !== $request->update_staff_role) {
            \App\Support\AdminAudit::record(
                'staff_role_changed',
                'Moved ' . $user->name . ' from the ' . ($roleNames[$wasRole] ?? $wasRole)
                    . ' desk to ' . ($roleNames[$request->update_staff_role] ?? $request->update_staff_role) . '.',
                'staff', $user->users_id
            );
        }

        if ($request->filled('password')) {
            \App\Support\AdminAudit::record(
                'staff_password_reset',
                'Reset the password for ' . $user->name . '. They must set their own at the next login.',
                'staff', $user->users_id
            );
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

        // ── UNSA ANG IYAHA, UG UNSA ANG SA OPISINA.
        // ──
        // ── PESO, 2026-10-14: ang contact number ug ang litrato iya sa PESO,
        // ── dili sa tawo nga naka-login — usa ra ang opisina, ug usa ra ang
        // ── numero nga iyang gipatawag. Ang pag-usab sa usa ka admin niini
        // ── mo-usab sa tulo.
        // ──
        // ── Ang NGALAN kaugalingon: ang Activity Logs gisulat niini, ug ang
        // ── tibuok tuyo sa tulo ka account mao nga mailhan kinsa ang nagbuhat.
        // ──
        // ── Ang EMAIL kaugalingon pud, ug wala ni mapilian: siya ang login.
        // ── Kung parehas siya sa tulo, usa ra ka account ang ilang maabot, nga
        // ── mao ra gihapon ang problema nga gisulbad niini.
        // ──
        // ── Gisulat sa TANANG admin nga laray imbes sa kaugalingong lamesa:
        // ── ang topbar, ang sidebar ug ang profile page nagbasa na sa
        // ── Auth::user()->profile_photo, ug ang pag-ilis niana nga agianan
        // ── magdala ug risgo nga mas dako kaysa sa gisulbad niini. ──
        $own = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        $office = ['phone' => $request->phone];

        if ($request->hasFile('profile_photo')) {
            // Ang daan nga litrato gi-ambit sa tulo, mao nga kausa ra siya
            // gilimpyohan — dili kausa kada admin.
            if ($admin->profile_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($admin->profile_photo);
            }
            $office['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // Ang label nagdala sa tag-iya, aron ang usa ka linya makasulti kung
        // kang kinsa ang nausab — ug ang laing duha masayod dayon kung sila
        // ba apil o dili.
        $labels = [
            'name'          => 'their own name',
            'email'         => 'their own email',
            'phone'         => "the office's contact number",
            'profile_photo' => "the office's photo",
        ];

        $changed = collect($own + $office)
            ->filter(fn($value, $field) => (string) $admin->getOriginal($field) !== (string) $value)
            ->keys()
            ->map(fn($field) => $labels[$field] ?? $field);

        $admin->update($own + $office);

        // Ang ubang administrator — ang litrato ug ang numero ra. Ang ilang
        // ngalan ug email dili gyud matandog.
        User::where('role', 'admin')
            ->where('users_id', '!=', $admin->users_id)
            ->update($office);

        if ($changed->isNotEmpty()) {
            \App\Support\AdminAudit::record(
                'profile_updated',
                'Updated ' . $changed->join(', ', ' and ') . '.',
                'admin', $admin->users_id
            );
        }

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

        // The password itself is never written here, nor is any hint of it.
        // The other two need to know the account was re-secured, not by what.
        \App\Support\AdminAudit::record(
            'password_changed',
            'Changed their own password.',
            'admin', $admin->users_id
        );

        return back()->with('success', 'Password changed successfully.');
    }

    // ───────────────────────────────
    // ACTIVITY LOGS — kinsa sa tulo ka admin ang nagbuhat, ug kanus-a
    // ───────────────────────────────
    //
    // Tulo ka administrator ang naa sa opisina ug usa ra ka login ang ilang
    // gi-ambitan, mao nga ang tanan nga nahitabo buhat sa "PESO Admin". Tulo na
    // ka account karon, ug kini ang panid nga ilang gi-ambitan puli niini.
    public function activityLogs()
    {
        $actorId = (int) request('actor') ?: null;
        $action  = request('action');

        $actions = [
            'staff_created'        => 'Staff account created',
            'staff_status_changed' => 'Staff switched on or off',
            'staff_role_changed'   => 'Staff moved to another desk',
            'staff_password_reset' => 'Staff password reset',
            'calendar_added'       => 'Office calendar — added',
            'calendar_updated'     => 'Office calendar — changed',
            'calendar_deleted'     => 'Office calendar — removed',
            'employer_recovered'   => 'Employer account recovered',
            'profile_updated'      => 'Own profile updated',
            'password_changed'     => 'Own password changed',
        ];

        if (!array_key_exists((string) $action, $actions)) {
            $action = null;
        }

        $logs = \App\Models\AdminActivityLog::query()
            ->when($actorId, fn($q) => $q->where('actor_user_id', $actorId))
            ->when($action, fn($q) => $q->where('action', $action))
            ->latest()
            // Lima, parehas sa ubang lista sa admin.
            ->paginate(5)
            ->withQueryString();

        // Ang admin nga wala pa nagbuhat ug bisan unsa naa gihapon sa
        // dropdown: ang "wala siyay gibuhat" usa ka tubag nga gipangita.
        $admins = User::where('role', 'admin')->orderBy('name')->get(['users_id', 'name']);

        return view('admin.activity.index', compact('logs', 'admins', 'actions', 'actorId', 'action'));
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
    // Lima, parehas sa ubang lista sa admin. Napulo ang gibuhat niini nga
    // panid nga doble ang gitas-on sa bisan unsa nga iyang katapad, ug ang
    // pagination gitulod ngadto sa ubos sa screen.
    $registrations = $baseQuery->latest()->paginate(5)->withQueryString();

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
        // `admin_seen_at`, not `is_read` — the same correction markAllRead and
        // clearAll already carry. The admin clicking one notice in their bell
        // was clearing the red dot in a jobseeker's, employer's or staff
        // member's own bell for a notice that person had never opened.
        \App\Models\Announcement::whereKey($id)
            ->whereNull('admin_seen_at')
            ->update(['admin_seen_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function notifications()
    {
        $notifications = \App\Models\Announcement::latest()->get();

        // Which ones were new when the page was opened. Marking them seen first
        // and then asking would highlight nothing at all: markAllSeen() stamps
        // every row, so by render time none of them is new any more and the page
        // could not show the admin what had just come in.
        $newIds = $notifications->whereNull('admin_seen_at')
            ->pluck('announcements_id')
            ->all();

        // Opening the full list is the admin saying they have seen everything.
        \App\Support\AdminInbox::markAllSeen();

        return view('admin.notifications.index', compact('notifications', 'newIds'));
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
        // Both doors into the PESO Office: the schedule-only request and the
        // job posting that books its own days. This page used to read the
        // first table alone, so an interview LRA had already confirmed left
        // the tab empty — see App\Support\AdminInhouseRows.
        $inhouseSchedules = \App\Support\AdminInhouseRows::all();

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

        // The SRA's page carries a selector between its own Overseas reports
        // and the job fair ones. The desk's copy reads it; this one did not, so
        // choosing Job Fair Reports here fell through to the code below and
        // came back on Overseas — the selector moved and the page did not.
        $reportView = $staffRole === 'sra' ? request('report_view', 'staff') : 'staff';

        if ($staffRole === 'job_fair' || ($staffRole === 'sra' && $reportView === 'jobfair')) {
            // The desk's own report data, built by the same code, so this copy
            // never lacks a variable the page reads.
            return view('staff.reports.index',
                \App\Support\JobFairReportPage::data($staffRole, $staffRole === 'sra', $reportView)
                + ['layout' => 'admin.layouts.app', 'reportRouteName' => 'admin.reports.staff']);
        }

        $search      = request('search');
        $tab         = request('tab', 'registered');
        $jobseekerType = $staffRole === 'lra' ? ['local', 'both'] : ['overseas', 'both'];
        $isOverseas    = $staffRole === 'sra';
        $registeredView = request('registered_view', 'all');
        $topEmployersFilter = request('top_employers_filter', 'monthly');
        $topEmployersMonth = request('top_employers_month');
        $topEmployersYear = request('top_employers_year');

        // ── Giihap ang NA-HIRE, dili ang gidaghanon sa interview.
        // ──
        // ── PESO LRA, 2026-10-14: ang kompanya nga nag-interview ug napulo ug
        // ── walay gikuha nilabaw kaniadto sa kompanya nga nag-interview ug
        // ── duha ug gikuha silang duha. Ang gipangita sa opisina kay kinsa ang
        // ── naghatag ug trabaho.
        // ──
        // ── Giilisan pud ang ngalan sa variable. Ang panid nagbasa ug
        // ── $topEmployersByCompanyInterviews ug kini nga kopya nagtukod ug
        // ── $topEmployersByInhouseInterviews, mao nga ang Top Employers sa
        // ── admin blangko sukad pa sa sinugdanan — ang @forelse nahulog sa
        // ── iyang @empty kada higayon. Parehas na sila nga ngalan karon.
        $hireQuery = \App\Models\Application::with('job.company')
            ->where('status', 'hired')
            ->whereHas('job', fn($q) => $q
                ->whereHas('company', fn($c) => $c->where('is_overseas', $isOverseas)));

        // Ang daan nga hire walay hired_at, mao nga ang updated_at ang puli.
        $hiredOn = 'COALESCE(job_matching.hired_at, job_matching.updated_at)';

        if ($topEmployersFilter === 'yearly') {
            $selectedYear = $topEmployersYear ?: now()->year;
            $hireQuery->whereRaw("YEAR({$hiredOn}) = ?", [$selectedYear]);
        } else {
            $selectedMonth = $topEmployersMonth ?: now()->format('Y-m');
            [$year, $month] = array_pad(explode('-', $selectedMonth), 2, now()->month);
            $hireQuery->whereRaw("YEAR({$hiredOn}) = ?", [$year])
                      ->whereRaw("MONTH({$hiredOn}) = ?", [$month]);
        }

        $topEmployersByCompanyInterviews = $hireQuery->get()
            ->filter(fn($application) => $application->job?->company)
            ->groupBy(fn($application) => $application->job->company_id)
            ->map(fn($group) => [
                'employer'   => $group->first()->job->company,
                'hire_count' => $group->count(),
            ])
            ->sortByDesc('hire_count')
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

        // Same filter as the desk's page. It was added there and not here, so
        // the dropdown on the admin's copy rewrote the URL and changed nothing.
        $referredStatus = request('referred_status', 'all');
        if (!in_array($referredStatus, ['waiting', 'rejected'], true)) {
            $referredStatus = 'all';
        }

        $referredQuery = \App\Models\Application::with(['jobseeker', 'job.company'])
            ->whereIn('status', ['waiting', 'rejected'])
            ->when($referredStatus !== 'all', fn($q) => $q->where('status', $referredStatus))
            ->whereHas('jobseeker.nsrp', fn($r) => $r->whereIn('type', $jobseekerType))
            ->when($search, fn($q) => $q->whereHas('jobseeker', fn($u) =>
                $u->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            ));

        $totalReferred = (clone $referredQuery)->count();

        // Lima ka laray, parehas sa ubang tab sa panid. Napulo ang gihimo
        // niini nga lista nga mas taas kaysa sa bisan unsa nga naa sa tapad
        // niya, ug ang pagination gitulod ngadto sa ubos sa screen.
        $registeredParticipants = ($tab === 'registered' && $registeredView === 'inhouse') ? $registeredQuery->latest()->paginate(5)->withQueryString() : null;
        $registeredAll          = ($tab === 'registered' && $registeredView === 'all') ? $registeredAllQuery->latest()->paginate(5)->withQueryString() : null;
        $placedApplications     = $tab === 'placed'     ? $placedQuery->latest()->paginate(5)->withQueryString()     : null;
        $referredApplications   = $tab === 'referred'   ? $referredQuery->latest()->paginate(5)->withQueryString()   : null;

        // The two lists the desks were missing: who was given a room and who
        // was declined, and (SRA only) the interviews employers ran themselves.
        // Both desks answer for in-house; company interviews overseas are the
        // SRA's, the local ones belong to the Job Vacancy desk's own report.
        // Ang duha ka tubag nga gihuptan niini nga report. Ang desk nangita
        // sa gibalibaran sa milabay nga bulan ug nagbasa sa tibuok lista.
        $scheduleStatus = \App\Support\InhouseScheduleReport::status(request('schedule_status'));

        $inhouseReport = $tab === 'schedules'
            ? \App\Support\InhouseScheduleReport::paginate($isOverseas, $search, $scheduleStatus)
            : null;

        $companyInterviews = ($tab === 'company_interview' && $isOverseas)
            ? \App\Support\CompanyInterviewReport::paginate(null, null, $search, true)
            : null;

        // ── TAB: EMPLOYER REPORT ──
        //
        // Ang parehas nga tab nga nakita sa LRA. Gikan sa parehas nga helper,
        // mao nga ang numero ug ang paging managsama gyud — ang Admin nga
        // master view mao gyuy tan-awonan sa trabaho sa desk, ug ang report
        // nga magbasa ug lahi dinhi maghimo sa duha nga dili masaligan.
        //
        // LRA ra, parehas sa desk: ang in-house nga interview sa overseas wala
        // pa gitukod. Ang Download nga buton wala dinhi — ang desk ang tag-iya
        // sa file, parehas sa laing export niini nga panid.
        // ── The two SRA tabs the admin opens through this same view: Job
        // ── Vacancies Solicited and Archived Job Postings. The view reads
        // ── these on every render of those tabs, and the admin copy of the
        // ── report never built them, so both tabs failed outright. Built the
        // ── way the SRA's own report builds them, so the admin reads the
        // ── same list the desk does. ──
        $vacancyMonth = request('vacancy_month', now()->format('Y-m'));
        if ($vacancyMonth !== 'all' && !preg_match('/^\d{4}-\d{2}$/', $vacancyMonth)) {
            $vacancyMonth = now()->format('Y-m');
        }

        $solicitedJobs = collect();
        $totalVacanciesSolicited = 0;
        if ($staffRole === 'sra') {
            $vacancyQuery = \App\Models\Job::query()->overseasSolicited()
                ->with('company')
                ->when($vacancyMonth !== 'all', function ($q) use ($vacancyMonth) {
                    [$y, $m] = explode('-', $vacancyMonth);
                    $q->whereYear('updated_at', $y)->whereMonth('updated_at', $m);
                })
                ->when($search, fn($q) => $q->where(fn($w) => $w->where('title', 'like', "%{$search}%")
                    ->orWhereHas('company', fn($u) => $u->where('company_name', 'like', "%{$search}%"))
                ));

            $totalVacanciesSolicited = (clone $vacancyQuery)->count();
            $solicitedJobs = $tab === 'vacancies' ? $vacancyQuery->latest()->paginate(5)->withQueryString() : collect();
        }

        // The same query the SRA's own screen runs; the LRA has none there.
        $archivedJobs = $staffRole === 'sra'
            ? \App\Models\Job::with('company')
                ->inactive()
                ->withGroupHiredCount()
                ->withCount([
                    'applications as hired_count' => fn($q) => $q->where('status', 'hired'),
                ])
                ->latest()
                ->paginate(5, ['*'], 'archived_page')
                ->withQueryString()
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5, 1, ['path' => request()->url()]);

        // The same Employer Report the desk reads, built by the same helper.
        $employerHires   = null;
        // The Total Hired number on Registered Employer opens this tab with one
        // company named. The desk's page has always read it; this copy did not,
        // so the same link landed on the whole list.
        $employerFocusId = (int) request('employer') ?: null;

        if ($tab === 'employer_report') {
            $employerHires = \App\Support\InhouseEmployerReport::byEmployer($isOverseas, $search);
        }

        return view('staff.reports.index', compact(
            'staffRole', 'tab', 'registeredView', 'reportView',
            'registeredParticipants', 'registeredAll', 'placedApplications', 'referredApplications',
            'totalRegistered', 'totalRegisteredAll', 'totalPlaced', 'totalReferred', 'referredStatus', 'solicitationStats',
            'topEmployersByCompanyInterviews', 'topEmployersFilter', 'topEmployersMonth', 'topEmployersYear',
            'inhouseReport', 'companyInterviews', 'scheduleStatus',
            'employerHires', 'employerFocusId',
            'vacancyMonth', 'solicitedJobs', 'totalVacanciesSolicited', 'archivedJobs'
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