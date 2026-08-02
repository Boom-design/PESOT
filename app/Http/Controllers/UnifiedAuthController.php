<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetCodeMail;

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

        $user = User::where('email', $request->email)->first();

        // Role check — jobseekers cannot login here
       if (!$user) {
    return back()->withErrors(['email' => 'No account found.'])->withInput();
}

        if ($user->status === 'deactivated') {
            return back()->withErrors(['email' => 'Your account has been deactivated. Please contact PESO.'])->withInput();
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

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

   private function redirectByRole(string $role)
    {
        if ($role === 'jobseeker') {
            $user = Auth::user();
            $hasNsrp = \App\Models\JobseekerRegistration::where('user_id', $user->id)->exists();
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
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'This email address is already registered. Please use a different email or sign in instead.',
        ]);

        $user = User::create([
            'name'           => $request->first_name . ' ' . $request->last_name,
            'email'          => $request->email,
            'password'       => \Illuminate\Support\Facades\Hash::make($request->password),
            'phone'          => $request->phone ?? null,
            'role'           => 'jobseeker',
            'status'         => 'approved',
        ]);

        // ── Auto-link walk-in NSRP record (kung naa) — email match una, tapos fallback Name + Contact ──
        $walkIn = \App\Models\JobseekerRegistration::where('is_walk_in', true)
            ->whereNull('user_id')
            ->where('reg_email', $request->email)
            ->first();

        if (!$walkIn) {
            $walkIn = \App\Models\JobseekerRegistration::where('is_walk_in', true)
                ->whereNull('user_id')
                ->whereRaw('LOWER(first_name) = ?', [strtolower($request->first_name)])
                ->whereRaw('LOWER(surname) = ?', [strtolower($request->last_name)])
                ->where('contact_number', $request->phone)
                ->first();
        }

        if ($walkIn) {
            $walkIn->update([
                'user_id'    => $user->id,
                'is_walk_in' => false,
            ]);
        }

        return redirect()->route('login')
            ->with('success', 'Account created successfully! Please login.');
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
    $request->validate([
        'company_name'   => 'required|string|max:255',
        'contact_person' => 'required|string|max:255',
        'position_title' => 'required|string|max:255',
        'email'          => 'required|email|unique:users,email',
        'mobile_number'  => 'required|string|max:20',
        'employer_type'  => 'required|string',
        'password'       => 'required|string|min:6|confirmed',
        // ── I. Establishment Details ──
        'trade_name'         => 'nullable|string|max:255',
        'tin'                => 'nullable|string|max:50',
        'tin_type'           => 'nullable|in:main,branch',
        'total_workforce'    => 'nullable|in:micro,small,medium,large',
        'line_of_business'   => 'nullable|string|max:255',
        'est_barangay'       => 'nullable|string|max:255',
        'est_city_municipality' => 'nullable|string|max:255',
        'est_province'       => 'nullable|string|max:255',
        // ── II. Establishment Contact Details ──
        'contact_title'      => 'nullable|string|max:20',
        'telephone_no'       => 'nullable|string|max:20',
        'fax_no'             => 'nullable|string|max:20',
        // ── Certification/Authorization ──
        'certification_agreed' => 'required|accepted',
        'certification_date'   => 'required|date',
        // ── OPTIONAL — 6 Requirements (attach na dayon panahon sa registration) ──
        'business_permit'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        'sec_dti'                     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        'company_profile'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        'nsrp_establishment_form'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        'no_pending_case_certificate' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        'vacancy_posting'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        // ── III/IV. Vacancy Details + Qualification Requirements (OPTIONAL) ──
        'positions'                          => 'nullable|array',
        'positions.*.title'                 => 'required|string|max:255',
        'positions.*.description'           => 'required|string',
        'positions.*.type'                  => 'required|in:permanent,contractual,project_based,internship,part_time,work_from_home',
        'positions.*.industry_group'        => 'required|string|max:255',
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
    ], [
        'email.unique' => 'This email address is already registered. Please use a different email or sign in instead.',
        'certification_agreed.accepted' => 'You must agree to the certification and authorization.',
    ]);

    $isOverseas = $request->employer_type === 'Overseas Recruitment Agency';

    $user = User::create([
        'name'           => $request->company_name,
        'email'          => $request->email,
        'phone'          => $request->mobile_number,
        'password'       => \Illuminate\Support\Facades\Hash::make($request->password),
        'role'           => 'company',
        'status'         => 'approved',
    ]);

    $hasPositions = !empty($request->positions);

    \App\Models\EmployerNsrpRegistration::create([
        'user_id'                   => $user->id,
        'company_name'              => $request->company_name,
        'contact_person'            => $request->contact_person,
        'position_title'            => $request->position_title,
        'mobile_number'             => $request->mobile_number,
        'employer_type'             => $request->employer_type,
        'is_overseas'               => $isOverseas,
        'trade_name'                => $request->trade_name,
        'tin'                       => $request->tin,
        'tin_type'                  => $request->tin_type,
        'total_workforce'           => $request->total_workforce,
        'line_of_business'          => $request->line_of_business,
        'est_barangay'              => $request->est_barangay,
        'est_city_municipality'     => $request->est_city_municipality,
        'est_province'              => $request->est_province,
        'contact_title'             => $request->contact_title,
        'telephone_no'              => $request->telephone_no,
        'fax_no'                    => $request->fax_no,
        'certification_agreed'      => true,
        'certification_date'        => $request->certification_date,
        // Kung walay gi-fill up nga positions, i-set nga "confirmed" na dayon
        // (walay laing gikinahanglan i-confirm), aron dili mo-gawas ang modal
        'initial_vacancy_data'      => $hasPositions ? $request->positions : [],
        'initial_vacancy_confirmed' => !$hasPositions,
    ]);

    // ── OPTIONAL — 6 Requirements attach na dayon panahon sa registration ──
    $requirementFields = [
        'business_permit', 'sec_dti', 'company_profile',
        'nsrp_establishment_form', 'no_pending_case_certificate', 'vacancy_posting',
    ];

    $uploadedDocs = [];
    foreach ($requirementFields as $field) {
        if ($request->hasFile($field)) {
            $uploadedDocs[$field] = $request->file($field)->store('employer_requirements', 'public');
        }
    }

    if (!empty($uploadedDocs)) {
        $employerNsrp = \App\Models\EmployerNsrpRegistration::where('user_id', $user->id)->first();

        \App\Models\EmployerRequirement::create(array_merge(
            ['user_id' => $employerNsrp->id, 'status' => 'pending'],
            $uploadedDocs
        ));
    }

    // ── Notify LRA (local) o SRA (overseas) staff — Admin automatic na makakita (walay filter ang admin's notification query) ──
    $staffRoleToNotify = $isOverseas ? 'sra' : 'lra';
    $staffIds = \App\Models\Staff::where('staff_role', $staffRoleToNotify)->pluck('id');

    if ($staffIds->isNotEmpty()) {
        \App\Models\Announcement::sendToStaff([
            'type'           => 'employer_registered',
            'title'          => 'New Employer Registration 🏢',
            'message'        => $request->company_name . ' has registered as a ' . ($isOverseas ? 'overseas' : 'local') . ' employer. Please review their profile.',
            'reference_type' => 'employer_registration',
            'reference_id'   => $user->id,
        ], $staffIds);
    }

    // ── Dugang notify pud sa Job Vacancy staff (local employers ra, kay sila ang mag-approve sa Office Based job postings) ──
    if (!$isOverseas) {
        $jobVacancyStaffIds = \App\Models\Staff::where('staff_role', 'job_vacancy')->pluck('id');

        if ($jobVacancyStaffIds->isNotEmpty()) {
            \App\Models\Announcement::sendToStaff([
                'type'           => 'employer_registered',
                'title'          => 'New Employer Registration 🏢',
                'message'        => $request->company_name . ' has registered as a local employer. Please review their profile.',
                'reference_type' => 'employer_registration',
                'reference_id'   => $user->id,
            ], $jobVacancyStaffIds);
        }
    }

    return redirect()->route('login')
        ->with('success', 'Company account created! Please login.');
}
// ───────────────────────────────
    // FORGOT PASSWORD
    // ───────────────────────────────
    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
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

        Mail::to($request->email)->send(new ResetCodeMail($code, $user->name));

        return redirect()->route('password.verify', ['email' => $request->email])
            ->with('success', 'A 6-digit verification code has been sent to your email.');
    }

    public function showResetForm(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return redirect()->route('password.request');
        }
        return view('auth.reset_password', compact('email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_codes')->where('email', $request->email)->first();

        if (!$record || $record->code !== $request->code) {
            return back()->withErrors(['code' => 'Invalid verification code. Please check and try again.'])->withInput();
        }

        if (now()->greaterThan($record->expires_at)) {
            return back()->withErrors(['code' => 'This code has expired. Please request a new one.'])->withInput();
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No account found.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_codes')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password reset successfully! Please login with your new password.');
    }

    // ───────────────────────────────
    // REAL-TIME CHECKS
    // ───────────────────────────────
    public function checkName(Request $request)
    {
        $taken = User::whereRaw('LOWER(first_name) = ?', [strtolower($request->first_name)])
                     ->whereRaw('LOWER(last_name) = ?', [strtolower($request->last_name)])
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
}