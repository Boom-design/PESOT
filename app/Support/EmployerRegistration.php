<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\EmployerNsrpRegistration;
use App\Models\EmployerRequirement;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Putting one establishment on the books.
 *
 * PESO IT, 2026-08-26: the same HR officer can be the authorised contact for
 * two companies, and asked for one e-mail to cover both. The e-mail is how a
 * person signs in, so the account stayed one and the companies became many.
 * That leaves two doors into the same room:
 *
 *   1. public sign-up  — a new person, their first company;
 *   2. Add a company   — someone already signed in, their second.
 *
 * Everything after the account exists is identical, and it lives here. Two
 * copies would drift, and the half that drifts is always the second door: the
 * industry group would be saved on one path and dropped on the other, and the
 * desk would never be told about the company registered through it.
 */
class EmployerRegistration
{
    /** The establishment itself. Nothing here is about signing in. */
    public static function companyRules(): array
    {
        return [
            'company_name'   => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'position_title' => 'required|string|max:255',
            'mobile_number'  => ['required', 'string', new \App\Rules\MobileNumber],
            'employer_type'  => 'required|string',

            // ── I. Establishment Details ──
            'trade_name'       => 'nullable|string|max:255',
            'tin'              => 'required|string|max:50',
            'tin_type'         => 'nullable|in:main,branch',
            'total_workforce'  => 'nullable|in:micro,small,medium,large',
            'line_of_business' => 'nullable|string|max:255',
            // A job fair invitation is aimed at an industry, so a blank one
            // means this company is never matched to a fair.
            'industry_group'   => ['required', Rule::in(EmployerNsrpRegistration::INDUSTRY_GROUPS)],
            'est_barangay'          => 'nullable|string|max:255',
            'est_city_municipality' => 'nullable|string|max:255',
            'est_province'          => 'nullable|string|max:255',

            // ── II. Contact ──
            'contact_title' => 'nullable|string|max:50',
            'telephone_no'  => 'nullable|string|max:50',
            'fax_no'        => 'nullable|string|max:50',

            'certification_agreed' => 'required|accepted',
            'certification_date'   => 'nullable|date',
        ];
    }

    public static function messages(): array
    {
        return [
            'certification_agreed.accepted' => 'You must agree to the certification and authorization.',
            'industry_group.required'       => 'Choose the industry group this establishment belongs to.',
        ];
    }

    /**
     * Write the establishment, its documents, and tell the desk.
     *
     * The account is passed in rather than created here: door 1 has just made
     * it, door 2 has had it for a year.
     */
    public static function create(User $user, Request $request): EmployerNsrpRegistration
    {
        $isOverseas = $request->employer_type === 'Overseas Recruitment Agency';

        $company = EmployerNsrpRegistration::create([
            'user_id'               => $user->users_id,
            'company_name'          => $request->company_name,
            'contact_person'        => $request->contact_person,
            'position_title'        => $request->position_title,
            'mobile_number'         => $request->mobile_number,
            'employer_type'         => $request->employer_type,
            'is_overseas'           => $isOverseas,
            'trade_name'            => $request->trade_name,
            'tin'                   => $request->tin,
            'tin_type'              => $request->tin_type,
            'total_workforce'       => $request->total_workforce,
            'line_of_business'      => $request->line_of_business,
            'industry_group'        => $request->industry_group,
            'est_barangay'          => $request->est_barangay,
            'est_city_municipality' => $request->est_city_municipality,
            'est_province'          => $request->est_province,
            'contact_title'         => $request->contact_title,
            'telephone_no'          => $request->telephone_no,
            'fax_no'                => $request->fax_no,
            'certification_agreed'  => true,
            'certification_date'    => $request->certification_date,
            // Job vacancies are no longer collected during registration — the
            // employer posts them from the dashboard modal after logging in.
            'initial_vacancy_data'      => [],
            'initial_vacancy_confirmed' => true,
        ]);

        self::attachRequirements($company, $request);
        self::tellTheDesk($company);

        return $company;
    }

    /** The six documents, optional at this point, with their expiry dates. */
    private static function attachRequirements(EmployerNsrpRegistration $company, Request $request): void
    {
        $fields = [
            'business_permit', 'sec_dti', 'company_profile',
            'nsrp_establishment_form', 'no_pending_case_certificate', 'vacancy_posting',
        ];

        $uploaded = [];

        foreach ($fields as $field) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $uploaded[$field] = $request->file($field)->store('employer_requirements', 'local');

            if ($field === 'business_permit' && $request->filled('business_permit_year')) {
                // Ang petsa gikan sa tuig, dili gikan sa gi-type — dili gyud
                // sila magkalahi kung usa ra ang tinubdan.
                $year = (int) $request->input('business_permit_year');
                $uploaded['business_permit_year']       = $year;
                $uploaded['business_permit_expires_at'] = \Carbon\Carbon::create($year, 12, 31)->toDateString();
                continue;
            }

            if ($request->filled("{$field}_expires_at")) {
                $uploaded["{$field}_expires_at"] = $request->input("{$field}_expires_at");
            }
        }

        if ($request->hasFile('company_logo')) {
            $uploaded['company_logo'] = $request->file('company_logo')->store('employer_requirements', 'local');
        }

        if (empty($uploaded)) {
            return;
        }

        EmployerRequirement::create(array_merge(
            ['user_id' => $company->employer_nsrp_registrations_id, 'status' => 'pending'],
            $uploaded
        ));
    }

    /**
     * Local goes to LRA and Job Vacancy, overseas to SRA.
     *
     * Job Vacancy is told as well as LRA for a local establishment, because
     * Job Vacancy is the desk that approves its requirements.
     */
    private static function tellTheDesk(EmployerNsrpRegistration $company): void
    {
        $roles = $company->is_overseas ? ['sra'] : ['lra', 'job_vacancy'];

        $staffIds = Staff::whereIn('staff_role', $roles)->pluck('staff_id');

        if ($staffIds->isEmpty()) {
            return;
        }

        Announcement::sendToStaff([
            'type'           => 'employer_registered',
            'title'          => 'New Employer Registration 🏢',
            'message'        => $company->company_name . ' has registered as a '
                                . ($company->is_overseas ? 'overseas' : 'local')
                                . ' employer. Please review their profile.',
            'reference_type' => 'employer_registration',
            'reference_id'   => $company->user_id,
        ], $staffIds);
    }
}
