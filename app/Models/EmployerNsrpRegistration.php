<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerNsrpRegistration extends Model
{
    protected $table = 'employer_nsrp_registrations';

    protected $primaryKey = 'employer_nsrp_registrations_id';

    /**
     * The PSIC industry groups an establishment can belong to.
     *
     * The employer picks one when they register, a job fair says which of them
     * it is looking for, and the two are matched to decide who gets invited.
     * The list used to be typed out inside each Blade that needed it; it lives
     * here now so those three copies cannot drift apart.
     *
     * ── PSIC REVISION 5, SECTION LEVEL ──
     *
     * The PESO manager, 2026-10-20: the office classifies an establishment by
     * the Philippine Standard Industrial Classification, and an answer that
     * does not come from it is an answer the office cannot defend. The list
     * here used to be the older PSIC wording carried over from the NSRP paper
     * form, which no longer matches what the PSA publishes: it merged
     * Transportation with Telecommunications, called Section I "Hotel and
     * Restaurants", and had no entry at all for Electricity and Gas, Water
     * Supply, Publishing, Telecommunications and IT, Professional and
     * Technical Activities, Administrative and Support, Arts and Sports, or
     * Households as Employers.
     *
     * These are the 22 sections of PSIC Revision 5, worded exactly as the PSA
     * detailed structure of 30 July 2026 words them. Section level, not
     * division: the NSRP form asks for the industry group, and the job fair
     * matches an employer against a fair's target industries on the same
     * level.
     *
     * Nothing is added to it. "Overseas Manpower Services" used to sit at the
     * bottom as an entry of ours, and it is gone: the PSA already classifies
     * the work, as sub-class 78102, Overseas employment recruitment and
     * placement activities, under Division 78 of Section O. An entry we wrote
     * ourselves beside twenty-two the PSA wrote is the one line in the list
     * the office could not point at a source for.
     *
     * An overseas agency is still told apart from a local employer, by
     * is_overseas on this record. That is the column the whole system already
     * reads, and the one the DOLE report's overseas heading reads now.
     */
    public const INDUSTRY_GROUPS = [
        'Agriculture, Forestry and Fishing',
        'Mining and Quarrying',
        'Manufacturing',
        'Electricity, Gas, Steam and Air Conditioning Supply',
        'Water Supply; Sewerage, Waste Management and Remediation',
        'Construction',
        'Wholesale and Retail Trade',
        'Transportation and Storage',
        'Accommodation and Food Service Activities',
        'Publishing, Broadcasting, and Content Production and Distribution Activities',
        'Telecommunications, Computer Programming, Consultancy, Computing Infrastructure, and Other Information Service Activities',
        'Financial and Insurance Activities',
        'Real Estate Activities',
        'Professional, Scientific, and Technical Activities',
        'Administrative and Support Service Activities',
        'Public Administration and Defense; Compulsory Social Security',
        'Education',
        'Human Health and Social Work Activities',
        'Arts, Sports and Recreation',
        'Other Service Activities',
        'Activities of Households as Employers; Undifferentiated Goods- and Services-Producing Activities of Households for own use',
        'Activities of Extraterritorial Organizations and Bodies',
    ];

    /**
     * What each retired industry group became under PSIC Revision 5.
     *
     * The migration that rewrote the stored answers reads this, and it stays
     * afterwards: an employer's old answer may still arrive from a bookmarked
     * filter or an imported report, and the one place that says what it means
     * now is here.
     */
    public const RETIRED_INDUSTRY_GROUPS = [
        'Agriculture, Hunting and Forestry, Fishing'                     => 'Agriculture, Forestry and Fishing',
        'Wholesale, Retail Trade, Repair of Motor Vehicles, Motorcycles, & Personal and Household Goods'
                                                                         => 'Wholesale and Retail Trade',
        'Hotel and Restaurants'                                          => 'Accommodation and Food Service Activities',
        // Telecommunications left Section H for Section K under Revision 5.
        // An establishment that answered this was asked for its transport
        // activity, so Transportation and Storage is where it belongs; a
        // telecoms company re-picks its own section.
        'Transport, Storage and Communications'                          => 'Transportation and Storage',
        'Financial Intermediation'                                       => 'Financial and Insurance Activities',
        // Renting and business activities split off into Sections N and O.
        'Real Estate, Renting and Business Activities'                   => 'Real Estate Activities',
        'Public Administration and Defense, Compulsory Social Security'  => 'Public Administration and Defense; Compulsory Social Security',
        'Health and Social Work'                                         => 'Human Health and Social Work Activities',
        'Other Community, Social and Personal Activities'                => 'Other Service Activities',
        'Extra-territorial Organization and Bodies'                      => 'Activities of Extraterritorial Organizations and Bodies',
        // Never a PSIC group — ours, for the overseas agencies. The PSA puts
        // the work at sub-class 78102, under Division 78 of Section O.
        'Overseas Manpower Services'                                     => 'Administrative and Support Service Activities',
    ];

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_person',
        'position_title',
        'mobile_number',
        'employer_type',
        'is_overseas',

        // Job Vacancy staff encoded this company at the counter. The
        // account is real, so this is only a marker on the record — but
        // it has to be fillable because the walk-in form mass-assigns
        // everything in one create().
        'is_walk_in',

        // ── Ang sweep sa employer nga hunong na sa pagpasa ug bakante. Ang
        // ── inactivity_notified_at mao pud ang marka nga na-warn na siya. ──
        'inactivity_notified_at',
        'inactivity_second_notified_at',
        'inactivity_disable_prompted_at',
        'inactivity_responded_at',
        'inactivity_status',
        'inactivity_response',
        'dormant_at',

        'trade_name', 'tin', 'tin_type', 'total_workforce', 'line_of_business',
        'industry_group',
        'est_barangay', 'est_city_municipality', 'est_province',
        'contact_title', 'telephone_no', 'fax_no',
        'certification_agreed', 'certification_date',
        'initial_vacancy_data',
        'initial_vacancy_confirmed',
        'sms_opt_in',
    ];

    protected $casts = [
        'sms_opt_in'                => 'boolean',
        'is_overseas'               => 'boolean',
        'is_walk_in'                => 'boolean',
        'inactivity_notified_at'         => 'datetime',
        'inactivity_second_notified_at'  => 'datetime',
        'inactivity_disable_prompted_at' => 'datetime',
        'inactivity_responded_at'   => 'datetime',
        'dormant_at'                => 'datetime',
        'certification_agreed'      => 'boolean',
        'certification_date'        => 'date',
        'initial_vacancy_data'      => 'array',
        'initial_vacancy_confirmed' => 'boolean',
    ];

    public function employer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Where this company stands, in the words the desk uses.
     *
     * PESO, 2026-08-30. Two different things can put a company on the desk's
     * list, and they are not treated the same:
     *
     *   the papers ran out   the account is on an expired document and cannot
     *                        be trusted until a new one is uploaded
     *   the posting stopped  the papers are fine, but no vacancy has come in
     *                        for a month, then two, and the office starts
     *                        asking whether the company is still hiring
     *
     * The papers win when both are true. An expired permit is the harder
     * problem — a company with no permit on file should not be chased for a
     * vacancy it is not allowed to post.
     *
     * Returns the label, the colour to draw it in, and a second line of detail
     * where there is one. Never null: a company with nothing wrong is Active,
     * and the column is never blank.
     */
    public function companyStatus(): array
    {
        // Switched off already — nothing below is worth saying.
        if ($this->dormant_at || optional($this->employer)->status === 'dormant') {
            return [
                'label'  => 'Inactive',
                'color'  => 'var(--n-500)',
                'detail' => $this->dormant_at
                    ? 'since ' . $this->dormant_at->format('M d, Y')
                    : null,
            ];
        }

        $requirement = $this->requirement;

        if ($requirement) {
            // The business permit is the one document that gates the account,
            // and it lapses on its own calendar rather than on a date column.
            if ($requirement->isBusinessPermitOverdue()) {
                return [
                    'label'  => 'Requirements expired',
                    'color'  => 'var(--danger)',
                    'detail' => 'business permit ' . $requirement->business_permit_year,
                ];
            }

            foreach (array_keys(EmployerRequirement::DOCUMENT_LABELS) as $field) {
                if ($requirement->isFieldExpired($field)) {
                    return [
                        'label'  => 'Requirements expired',
                        'color'  => 'var(--danger)',
                        'detail' => strtolower(EmployerRequirement::DOCUMENT_LABELS[$field]),
                    ];
                }
            }

            // ── About to run out, and not yet run out.
            // ──
            // ── PESO Job Vacancy staff, 2026-10-13: the dashboard counted these
            // ── and the list could be narrowed to them, but no row said WHICH
            // ── paper was about to lapse — so the desk had the companies and
            // ── still had to open each one to learn what to ask for. The same
            // ── seven days the warning email uses, so the screen and the letter
            // ── name the same document.
            // ──
            // ── The soonest one, when more than one is in the window: that is
            // ── the one the next phone call is about.
            $soonest = null;
            foreach (array_keys(EmployerRequirement::DOCUMENT_LABELS) as $field) {
                if (!$requirement->isFieldExpiringSoon($field)) {
                    continue;
                }
                $date = $requirement->{"{$field}_expires_at"};
                if (!$soonest || $date->lt($soonest['date'])) {
                    $soonest = ['field' => $field, 'date' => $date];
                }
            }

            if ($soonest) {
                return [
                    'label'  => 'Requirements expiring',
                    'color'  => 'var(--warn)',
                    'detail' => strtolower(EmployerRequirement::DOCUMENT_LABELS[$soonest['field']])
                                . ' — ' . $soonest['date']->format('M d, Y'),
                ];
            }
        }

        // How long the office has been waiting for a vacancy. A company that
        // has never posted is measured from the day it registered, so a new
        // employer gets the same month everybody else gets.
        $lastPosted = $this->jobs()->max('created_at');
        $since      = $lastPosted ? \Carbon\Carbon::parse($lastPosted) : $this->created_at;

        if (!$since) {
            return ['label' => 'Active', 'color' => 'var(--g-700)', 'detail' => null];
        }

        $months = (int) $since->diffInMonths(now());
        $first  = (int) config('peso.employer.inactive_months', 1);
        $second = (int) config('peso.employer.inactive_second_months', 2);

        if ($months < $first) {
            return ['label' => 'Active', 'color' => 'var(--g-700)', 'detail' => null];
        }

        $detail = 'last posted ' . ($lastPosted ? $since->format('M d, Y') : 'never');

        // The week after the second letter, with no answer, is the desk's to
        // act on — say so rather than repeating the month count.
        if ($this->inactivity_disable_prompted_at && !$this->inactivity_responded_at) {
            return [
                'label'  => 'For disabling',
                'color'  => 'var(--danger)',
                'detail' => 'no answer since ' . $this->inactivity_second_notified_at?->format('M d, Y'),
            ];
        }

        if ($this->inactivity_responded_at) {
            return [
                'label'  => 'Answered — awaiting review',
                'color'  => 'var(--warn)',
                'detail' => $detail,
            ];
        }

        return [
            'label'  => 'No posting — ' . $months . ' month' . ($months === 1 ? '' : 's'),
            'color'  => $months >= $second ? 'var(--danger)' : 'var(--warn)',
            'detail' => $detail,
        ];
    }

    /**
     * The six documents belong to the establishment, not to the account.
     *
     * employer_requirements.user_id holds an employer_nsrp_registrations_id
     * despite its name. One HR account holding two companies has two sets of
     * documents, and each is approved on its own.
     */
    public function requirement()
    {
        return $this->hasOne(\App\Models\EmployerRequirement::class, 'user_id', 'employer_nsrp_registrations_id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'company_id');
    }

    public function jobFairEmploymentRequests()
    {
        return $this->hasMany(\App\Models\JobFairEmploymentRequest::class, 'employer_id');
    }

    public function announcements()
    {
        return $this->hasMany(\App\Models\Announcement::class, 'employer_id');
    }
}