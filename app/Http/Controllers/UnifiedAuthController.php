<?php

namespace App\Http\Controllers;

use App\Models\EmployerNsrpRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Mail\ResetCodeMail;
use App\Rules\PasswordPolicy;
use App\Support\LoginThrottle;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UnifiedAuthController extends Controller
{
    public function showLogin()
{
    if (Auth::check()) {
        return $this->redirectByRole(Auth::user()->role);
    }
    return response()->view('auth.login')->withHeaders([
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma'        => 'no-cache',
        'Expires'       => '0',
    ]);
}

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // ── Brute-force guard — check before touching the database at all ──
        if (LoginThrottle::tooManyAttempts($request)) {
            return back()
                ->withErrors(['email' => LoginThrottle::message(LoginThrottle::secondsRemaining($request))])
                ->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (!$user && strtolower($request->email) === 'admin@peso.gov.ph') {
            $user = $this->ensureAdminAccount();
        }

        // Role check — jobseekers cannot login here
        if (!$user) {
            LoginThrottle::recordFailure($request);
            return back()->withErrors(['email' => 'No account found.'])->withInput();
        }

        if ($user->status === 'deactivated') {
            LoginThrottle::recordFailure($request);
            return back()->withErrors(['email' => 'Your account has been deactivated. Please contact PESO.'])->withInput();
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            LoginThrottle::recordFailure($request);
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        LoginThrottle::clear($request);
        $request->session()->regenerate();

        return $this->redirectByRole($user->role);
    }

    public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login')->withHeaders([
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma'        => 'no-cache',
        'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
    ]);
}

    private function ensureAdminAccount(): ?User
    {
        $admin = User::where('email', 'admin@peso.gov.ph')->first();

        if ($admin) {
            $needsUpdate = $admin->name !== 'PESO Admin'
                || $admin->role !== 'admin'
                || $admin->status !== 'approved';

            if ($needsUpdate) {
                // Repair the profile fields only. The password is deliberately
                // left alone: this method runs from an unauthenticated login
                // request, so resetting it here would let anyone who knows the
                // admin email force the account back to a known password.
                $admin->fill([
                    'name'   => 'PESO Admin',
                    'role'   => 'admin',
                    'status' => 'approved',
                ]);
                $admin->save();
            }

            return $admin;
        }

        // Bootstrap account for a fresh install. The password is random and is
        // never displayed, so it cannot be logged into until an administrator
        // sets a real one via `php artisan peso:admin-password`.
        return User::create([
            'name'     => 'PESO Admin',
            'email'    => 'admin@peso.gov.ph',
            'password' => Hash::make(Str::random(48)),
            'role'     => 'admin',
            'status'   => 'approved',
            'phone'    => null,
        ]);
    }

    private function redirectByRole(string $role)
    {
        if ($role === 'jobseeker') {
            $user = Auth::user();
            $hasNsrp = \App\Models\JobseekerRegistration::where('user_id', $user->users_id)->exists();
            if (!$hasNsrp) {
                return redirect()->route('jobseeker.nsrp');
            }
            return redirect()->route('jobseeker.dashboard');
        }

        return match($role) {
            'admin'   => redirect()->route('admin.dashboard'),
            'company' => redirect()->route('company.dashboard'),
            'staff'   => redirect()->route('staff.dashboard'),
            default   => redirect()->route('login'),
        };
    }

    // ───────────────────────────────
    // REGISTER — JOBSEEKER
    // ───────────────────────────────
    public function showJobseekerRegister()
    {
        return view('auth.register_jobseeker');
    }

    public function registerJobseeker(Request $request)
    {
        $request->validate([
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => PasswordPolicy::required(),
        ], [
            'email.unique' => 'This email address is already registered. Please use a different email or sign in instead.',
        ]);

        $user = User::create([
            'name'           => $request->first_name . ' ' . $request->last_name,
            // Kept out of `name` on purpose: `name` is what the whole system
            // displays. The middle name is stored beside it so the NSRP form
            // can fill itself in without widening every greeting and table cell.
            'middle_name'    => trim((string) $request->middle_name) ?: null,
            'email'          => $request->email,
            'password'       => \Illuminate\Support\Facades\Hash::make($request->password),
            'phone'          => $request->phone ?? null,
            'role'           => 'jobseeker',
            'status'         => 'approved',
        ]);

        $this->linkWalkInRecord($user, $request);

        return redirect()->route('login')
            ->with('success', 'Account created successfully! Please login.');
    }

    /**
     * Hand a walk-in NSRP record over to the account its owner just made.
     *
     * What is being handed over is large: the whole NSRP form, the work history,
     * and every job application PESO staff filed on that person's behalf. So it
     * takes TWO matching facts, never one — the name, plus either the email or
     * the contact number.
     *
     * The old rule linked on the email alone. Anyone who knew a walk-in's email
     * address — a relative, a former employer, somebody who read it off the
     * paper form — could register with it and inherit that person's identity
     * inside the system, pending applications included.
     *
     * Nothing is said to the registrant when no record matches. Telling them
     * "a record was found but the name does not match" would confirm to a
     * stranger that a given email or number is in PESO's files, which is the
     * leak this exists to close.
     */
    private function linkWalkInRecord(User $user, Request $request): void
    {
        $email = trim((string) $request->email);
        $phone = trim((string) $request->phone);

        $candidates = \App\Models\JobseekerRegistration::where('is_walk_in', true)
            ->whereNull('user_id')
            ->where(function ($q) use ($email, $phone) {
                $q->where('reg_email', $email);
                // Optional on the sign-up form. Without this guard the clause
                // becomes `contact_number = ''` and matches nothing useful.
                if ($phone !== '') {
                    $q->orWhere('contact_number', $phone);
                }
            })
            ->get();

        $matches = $candidates->filter(fn($record) =>
            $this->sameName($record->first_name, $request->first_name)
            && $this->sameName($record->surname, $request->last_name)
        );

        // Exactly one, or none at all. Two survivors means the identifiers point
        // at different people who happen to share a name — linking either one
        // would hand somebody another person's record, which is worse than
        // asking them to fill the form again.
        if ($matches->count() !== 1) {
            return;
        }

        $matches->first()->update([
            'user_id'    => $user->users_id,
            'is_walk_in' => false,
        ]);
    }

    /**
     * Are these the same name, allowing for how people actually type them?
     *
     * Case, stray spacing and the periods Filipino names carry are all ignored:
     * "Ma. Rowena" and "ma rowena" are the same person. mb_strtolower rather
     * than strtolower so Ñ folds to ñ — the byte-wise version leaves it alone,
     * and the surname is common enough here to matter.
     */
    private function sameName(?string $a, ?string $b): bool
    {
        $normalise = function (?string $name): string {
            $name = mb_strtolower(trim((string) $name));
            $name = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $name);
            return trim(preg_replace('/\s+/u', ' ', $name));
        };

        $a = $normalise($a);

        return $a !== '' && $a === $normalise($b);
    }

    // ───────────────────────────────
    // REGISTER — COMPANY
    // ───────────────────────────────
    public function showCompanyRegister()
    {
        return view('auth.register_company');
    }

    public function registerCompany(Request $request)
    {
        // ── Ang account ug ang establisemento ──
        //
        // Ang email ug ang password ra ang bahin sa TAWO. Ang tanan sa ubos
        // niini bahin sa KOMPANYA, ug parehas gyud sa gigamit sa "Add another
        // company" nga porma, mao nga usa ra ka lugar ang naghubad niini.
        $request->validate(
            array_merge(
                [
                    'email'    => 'required|email|unique:users,email',
                    'password' => PasswordPolicy::required(),
                ],
                \App\Support\EmployerRegistration::companyRules(),
                self::requirementFileRules()
            ),
            array_merge(\App\Support\EmployerRegistration::messages(), [
                'email.unique' => 'This email address is already registered. Please use a different email or sign in instead.',
                'business_permit_year.required_with' => 'Say which year this business permit covers.',
            ])
        );

        $user = User::create([
            'name'     => $request->company_name,
            'email'    => $request->email,
            'phone'    => $request->mobile_number,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role'     => 'company',
            'status'   => 'approved',
        ]);

        \App\Support\EmployerRegistration::create($user, $request);

        return redirect()->route('login')
            ->with('success', 'Company account created! Please login.');
    }

    /**
     * The six documents, optional at this point.
     *
     * Public sign-up and "Add another company" both offer them, so the rules
     * are written once here and read by both.
     */
    public static function requirementFileRules(): array
    {
        return [
            'business_permit'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'sec_dti'                     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'company_profile'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'nsrp_establishment_form'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'no_pending_case_certificate' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'vacancy_posting'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'company_logo'                => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // ── Ang business permit gitimbang-timbang sa tuig nga iyang gisakop,
            // ── dili sa petsa nga gi-type: usa ra ka tinubdan, walay magkalahi. ──
            'business_permit_year' => 'nullable|required_with:business_permit|integer|min:2000|max:2100',

            'sec_dti_expires_at'                     => 'nullable|required_with:sec_dti|date|after:today',
            'company_profile_expires_at'             => 'nullable|required_with:company_profile|date|after:today',
            'no_pending_case_certificate_expires_at' => 'nullable|required_with:no_pending_case_certificate|date|after:today',
            'vacancy_posting_expires_at'             => 'nullable|required_with:vacancy_posting|date|after:today',
        ];
    }

// ───────────────────────────────
    // FORGOT PASSWORD
    // ───────────────────────────────
    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    // ───────────────────────────────
    // EMPLOYER RECOVERY — pangita pinaagi sa pangalan sa kompanya
    // ───────────────────────────────
    // Ang HR nga nag-register ni-hawa na, ug personal niyang Gmail ang gigamit.
    // Ang bag-ong HR wala kabalo unsa nga address ang naa sa account. Dinhi
    // siya mangita pinaagi sa pangalan sa kompanya, ug ang tinabunan nga email
    // maoy mag-sulti kung iyaha ba kadto o iya sa ni-hawa.
    //
    // TIMAN-I: enumeration surface kini. Bisan kinsa makatype ug company name
    // ug mahibaw-an nga registered kadto, ug ang porma sa address. Kana gyud
    // ang tumong — kinahanglan mailhan sa bag-ong HR — mao nga ang panagang
    // dili pagtago kondili paghinay: throttle, ug tabon nga igo ra sa pag-ila.
    private function findEmployerByCompanyName(string $companyName)
    {
        $needle = mb_strtolower(trim($companyName));

        // Ang eksaktong tugma mo-una. Kung wala ni, ang "Northline
        // Manufacturing Corporation" mo-tugma gihapon sa "Northline
        // Manufacturing Corporation Branch 2" — ug ang tawo nga nag-type sa
        // BUO nga pangalan masultihan nga "type it in full", nga walay
        // kalingkawasan.
        $exact = \App\Models\EmployerNsrpRegistration::with('employer')
            ->whereRaw('LOWER(TRIM(company_name)) = ?', [$needle])
            ->get();

        if ($exact->isNotEmpty()) {
            return $exact;
        }

        return \App\Models\EmployerNsrpRegistration::with('employer')
            ->whereRaw('LOWER(company_name) LIKE ?', ['%' . $needle . '%'])
            ->get();
    }

    public function lookupCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
        ], [
            'company_name.required' => 'Enter your company name.',
        ]);

        $throttleKey = 'company-lookup:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $wait = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'company_name' => 'Too many searches. Please try again in ' . $wait . ' seconds.',
            ])->withInput();
        }
        RateLimiter::hit($throttleKey, 600);

        $matches = $this->findEmployerByCompanyName($request->company_name)
            ->filter(fn($nsrp) => $nsrp->employer !== null);

        if ($matches->isEmpty()) {
            return back()->withErrors([
                'company_name' => 'No employer account found with that company name. Check the spelling, or call PESO if you are not sure how it was registered.',
            ])->withInput();
        }

        // Dili i-listahan ang tanan: kung daghan ang mo-tugma, ang usa ka
        // hangyo mahimong makuha ug daghang address nga dungan.
        if ($matches->count() > 1) {
            return back()->withErrors([
                'company_name' => 'More than one company matches that name. Please type it in full.',
            ])->withInput();
        }

        $nsrp = $matches->first();

        return redirect()->route('password.request', ['as' => 'employer'])
            ->with('recovery_company', $nsrp->company_name)
            ->with('recovery_masked_email', \App\Support\MaskedEmail::mask($nsrp->employer->email));
    }

    // ── Ang pagpadala mo-pangita pag-usab gikan sa pangalan sa kompanya. Ang
    // ── tinuod nga address wala gyud gi-render ug wala gi-butang sa hidden
    // ── field, mao nga walay makuha bisan tan-awon ang page source. ──
    public function sendCodeToCompany(Request $request)
    {
        $request->validate(['company_name' => 'required|string|max:255']);

        $nsrp = $this->findEmployerByCompanyName($request->company_name)
            ->filter(fn($n) => $n->employer !== null)
            ->first();

        if (!$nsrp) {
            return redirect()->route('password.request', ['as' => 'employer'])
                ->withErrors(['company_name' => 'That company could not be found. Please search again.']);
        }

        $email = $nsrp->employer->email;

        $throttleKey = 'reset-code:' . Str::lower($email) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $wait = RateLimiter::availableIn($throttleKey);
            return redirect()->route('password.request', ['as' => 'employer'])
                ->withErrors(['company_name' => 'Too many reset requests for this company. Please try again in ' . $wait . ' seconds.']);
        }
        RateLimiter::hit($throttleKey, 600);

        if (!$this->issueResetCode($email, $nsrp->employer->name)) {
            return redirect()->route('password.request', ['as' => 'employer'])
                ->withErrors(['company_name' => 'We could not send the email right now. Please try again in a few minutes, or call PESO.']);
        }

        // ── DILI i-butang ang address sa URL. Ang sendResetCode mo-redirect ug
        // ── ?email=... kay ang tawo mismo ang nag-type niini didto — apan dinhi
        // ── wala niya kabalo ang address, ug ang tabon mawad-an ug pulos kung
        // ── mabasa ra ang buo gikan sa address bar. Session ang gamiton. ──
        return redirect()->route('password.verify')
            ->with('reset_email', $email)
            // Gipabilin para sa Resend nga buton — mo-agi siya pag-usab sa
            // pangalan sa kompanya, dili sa address.
            ->with('recovery_company', $nsrp->company_name)
            ->with('success', 'A 6-digit verification code has been sent to the email on file for ' . $nsrp->company_name . '.');
    }

    // ── Ang pag-isyu ug pag-padala sa code, gigamit sa duha ka agianan.
    // ── Mobalik ug false kung napakyas ang mail server — ang code natipigan na,
    // ── ang delivery ra ang nawala. ──
    private function issueResetCode(string $email, string $name): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_codes')->updateOrInsert(
            ['email' => $email],
            [
                'code'       => $code,
                'expires_at' => now()->addMinutes(15),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        try {
            Mail::to($email)->send(new ResetCodeMail($code, $name));
            return true;
        } catch (\Throwable $e) {
            Log::error('Password reset code could not be sent', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Without a limit the form is a free mail sender: anyone could point it
        // at an address and hold the send button down.
        $throttleKey = 'reset-code:' . Str::lower((string) $request->email) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $wait = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => 'Too many reset requests. Please try again in ' . $wait . ' seconds.',
            ])->withInput();
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            RateLimiter::hit($throttleKey, 600);
            return back()->withErrors(['email' => 'No account found with that email address.'])->withInput();
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_codes')->updateOrInsert(
            ['email' => $request->email],
            [
                'code'       => $code,
                'expires_at' => now()->addMinutes(15),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // A mail-server outage must not become a 500 page. The code is already
        // stored, so the only thing lost is the delivery — say so plainly
        // instead of leaving the user on a blank error screen.
        try {
            Mail::to($request->email)->send(new ResetCodeMail($code, $user->name));
        } catch (\Throwable $e) {
            Log::error('Password reset code could not be sent', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors([
                'email' => 'We could not send the email right now. Please try again in a few minutes, or contact PESO.',
            ])->withInput();
        }

        RateLimiter::hit($throttleKey, 600);

        return redirect()->route('password.verify', ['email' => $request->email])
            ->with('success', 'A 6-digit verification code has been sent to your email.');
    }

    // ───────────────────────────────
    // FORCED PASSWORD CHANGE
    // ───────────────────────────────
    // Ang employer nga gihatagan ug temporary password sa telepono. Ang tawo
    // sa opisina nakahibalo ana nga password, mao nga dinhi siya mahutdan ug
    // pulos — walay laing page nga maabot hangtod mo-ilis siya.
    public function showForceChangePassword()
    {
        if (!Auth::check() || !Auth::user()->must_change_password) {
            return redirect()->route('login');
        }

        return view('auth.force_change_password');
    }

    public function forceChangePassword(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->must_change_password) {
            return redirect()->route('login');
        }

        $request->validate([
            'password' => PasswordPolicy::required(),
        ]);

        // Ang temporary nga password wala gipangayo pag-usab: gi-type ra man
        // niya kadto pila ka segundo ang milabay sa login, ug ang pagpangayo
        // kaduha mag-aghat lang nga isulat niya kini sa papel.
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Pick a different password from the temporary one you were given.',
            ]);
        }

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return $this->redirectByRole($user->role)
            ->with('success', 'Password updated. Only you know it now.');
    }

    public function showResetForm(Request $request)
    {
        // Ang employer nga agianan nagpasa niini sa session, dili sa URL: wala
        // niya kabalo ang address, ug ang tabon mawad-an ug pulos kung mabasa
        // ra kini gikan sa address bar. Ang session gi-reflash aron dili
        // mawala kung mo-refresh o mabalik siya gikan sa validation error.
        $sessionEmail = $request->session()->get('reset_email');
        $email        = $request->query('email') ?: $sessionEmail;

        if (!$email) {
            return redirect()->route('password.request');
        }

        // Ang employer nga agianan wala mag-render sa address bisan asa — dili
        // sa URL, dili sa hidden field, ug ang gipakita kay ang tinabunan.
        $maskedOnly = !$request->query('email') && $sessionEmail;

        if ($maskedOnly) {
            $request->session()->keep(['reset_email', 'recovery_company']);
        }

        return view('auth.reset_password', [
            'email'      => $email,
            'maskedOnly' => (bool) $maskedOnly,
            'shownEmail' => $maskedOnly ? \App\Support\MaskedEmail::mask($email) : $email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        // Ang employer nga agianan walay email sa form — naa siya sa session,
        // kay wala man kabalo ang tawo sa address ug dili siya angay makita.
        $email = $request->input('email') ?: $request->session()->get('reset_email');

        $request->merge(['email' => $email]);

        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|string',
            'password' => PasswordPolicy::required(),
        ]);

        $record = DB::table('password_reset_codes')->where('email', $email)->first();

        if (!$record || $record->code !== $request->code) {
            $request->session()->keep(['reset_email']);
            return back()->withErrors(['code' => 'Invalid verification code. Please check and try again.'])->withInput();
        }

        if (now()->greaterThan($record->expires_at)) {
            $request->session()->keep(['reset_email']);
            return back()->withErrors(['code' => 'This code has expired. Please request a new one.'])->withInput();
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No account found.']);
        }

        // Ang tawo mismo ang nagbutang niini, mao nga wala nay pugos nga pag-ilis.
        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        DB::table('password_reset_codes')->where('email', $email)->delete();
        $request->session()->forget('reset_email');

        return redirect()->route('login')
            ->with('success', 'Password reset successfully! Please login with your new password.');
    }

    // ───────────────────────────────
    // REAL-TIME CHECKS
    // ───────────────────────────────
    /**
     * Warn the register form that this name already has an account.
     *
     * `users` stores one `name` column, not first_name / last_name — this used
     * to query the split columns and every blur on the register form answered
     * with a 500 the browser quietly swallowed, so the warning never appeared.
     *
     * Only jobseekers are compared. Two people really do share a name, so this
     * warns and never blocks; the unique check that matters is on the email.
     */
    public function checkName(Request $request)
    {
        $full = trim(preg_replace('/\s+/u', ' ',
            trim((string) $request->first_name) . ' ' . trim((string) $request->last_name)));

        if ($full === '') {
            return response()->json(['taken' => false]);
        }

        $taken = User::where('role', 'jobseeker')
                     ->whereRaw('LOWER(name) = ?', [mb_strtolower($full)])
                     ->exists();

        return response()->json(['taken' => $taken]);
    }

    public function checkEmail(Request $request)
    {
        $taken = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->exists();
        return response()->json(['taken' => $taken]);
    }

    public function checkCompanyName(Request $request)
    {
        $name = trim($request->company_name ?? '');
        if ($name === '') {
            return response()->json(['exists' => false]);
        }

        $exists = \App\Models\EmployerNsrpRegistration::whereRaw('LOWER(company_name) = ?', [strtolower($name)])->exists();

        return response()->json(['exists' => $exists]);
    }

    // ───────────────────────────────
    // PH ADDRESS DATA — gikan sa hardcoded JSON files sa storage/app/ph_address
    // ───────────────────────────────
    public function addressProvinces()
    {
        $path = storage_path('app/ph_address/provinces.json');
        if (!file_exists($path)) {
            return response()->json(['error' => 'Address data not found'], 404);
        }
        return response(file_get_contents($path))->header('Content-Type', 'application/json');
    }

    public function addressCities($code)
    {
        $code = basename($code); // sanitize, para dili ma-abuse ang path traversal
        $path = storage_path("app/ph_address/cities/{$code}.json");
        if (!file_exists($path)) {
            return response()->json([]);
        }
        return response(file_get_contents($path))->header('Content-Type', 'application/json');
    }

    public function addressBarangays($code)
    {
        $code = basename($code);
        $path = storage_path("app/ph_address/barangays/{$code}.json");
        if (!file_exists($path)) {
            return response()->json([]);
        }
        return response(file_get_contents($path))->header('Content-Type', 'application/json');
    }

    // ───────────────────────────────
    // ONE-TIME SEEDER — fetch PSGC data and save as local JSON
    // Visit /seed-address once, then remove the route
    // ───────────────────────────────
    public function seedAddressData()
    {
        set_time_limit(300);
        $PSGC = 'https://psgc.gitlab.io/api';
        $base  = storage_path('app/ph_address');
        $log   = [];

        // 1. Fetch & save provinces
        $provincesJson = @file_get_contents("{$PSGC}/provinces.json");
        if ($provincesJson === false) {
            return response()->json(['error' => 'Cannot reach PSGC API for provinces'], 502);
        }
        $provinces = json_decode($provincesJson, true);
        file_put_contents("{$base}/provinces.json", json_encode($provinces));
        $log[] = 'Provinces saved: ' . count($provinces);

        // 2. For each province → fetch & save cities
        $cityCount = 0;
        foreach ($provinces as $prov) {
            $code = $prov['code'];
            $citiesJson = @file_get_contents("{$PSGC}/provinces/{$code}/cities-municipalities.json");
            if ($citiesJson === false) { $log[] = "SKIP province {$code}"; continue; }
            $cities = json_decode($citiesJson, true);
            file_put_contents("{$base}/cities/{$code}.json", json_encode($cities));
            $cityCount += count($cities);
            usleep(100000); // 100ms delay to be polite to PSGC
        }
        $log[] = 'Cities saved: ' . $cityCount;

        // 3. For each city → fetch & save barangays
        $barangayCount = 0;
        foreach ($provinces as $prov) {
            $provCode = $prov['code'];
            $citiesPath = "{$base}/cities/{$provCode}.json";
            if (!file_exists($citiesPath)) continue;
            $cities = json_decode(file_get_contents($citiesPath), true);
            foreach ($cities as $city) {
                $cityCode = $city['code'];
                $brgyJson = @file_get_contents("{$PSGC}/cities-municipalities/{$cityCode}/barangays.json");
                if ($brgyJson === false) { $log[] = "SKIP city {$cityCode}"; continue; }
                $brgies = json_decode($brgyJson, true);
                // Store as simple array of names (matches frontend expectation)
                $names = array_map(fn($b) => $b['name'], $brgies);
                file_put_contents("{$base}/barangays/{$cityCode}.json", json_encode($names));
                $barangayCount += count($names);
                usleep(100000);
            }
        }
        $log[] = 'Barangays saved: ' . $barangayCount;

        return response()->json(['status' => 'done', 'log' => $log]);
    }

    // ───────────────────────────────
    // Process job image uploads for each position
    // ───────────────────────────────
    private function processPositionImages(array $positions): array
    {
        foreach ($positions as &$pos) {
            if (isset($pos['job_image']) && $pos['job_image'] instanceof \Illuminate\Http\UploadedFile) {
                $pos['job_image'] = $pos['job_image']->store('job_images', 'public');
            } else {
                unset($pos['job_image']);
            }
        }
        unset($pos);
        return $positions;
    }
}