<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerRequirement extends Model
{
    protected $primaryKey = 'employer_requirements_id';

    protected $fillable = [
        'user_id',
        'reviewed_by',
        'company_logo',
        'business_permit',
        'business_permit_year',
        'business_permit_expires_at',
        'business_permit_expiry_notified_at',
        'business_permit_grace_notified_at',
        'sec_dti',
        'sec_dti_expires_at',
        'sec_dti_expiry_notified_at',
        'company_profile',
        'company_profile_expires_at',
        'company_profile_expiry_notified_at',
        'nsrp_establishment_form',
        'no_pending_case_certificate',
        'no_pending_case_certificate_expires_at',
        'no_pending_case_certificate_expiry_notified_at',
        'vacancy_posting',
        'vacancy_posting_expires_at',
        'vacancy_posting_expiry_notified_at',
        'status',
        'remarks',
        'rejected_fields',
    ];

    protected $casts = [
        'rejected_fields' => 'array',
        'business_permit_expires_at' => 'date',
        'sec_dti_expires_at' => 'date',
        'company_profile_expires_at' => 'date',
        'no_pending_case_certificate_expires_at' => 'date',
        'vacancy_posting_expires_at' => 'date',
        'business_permit_expiry_notified_at' => 'datetime',
        'business_permit_grace_notified_at' => 'datetime',
        'sec_dti_expiry_notified_at' => 'datetime',
        'company_profile_expiry_notified_at' => 'datetime',
        'no_pending_case_certificate_expiry_notified_at' => 'datetime',
        'vacancy_posting_expiry_notified_at' => 'datetime',
    ];

    // ── Kada usa sa 5 ka requirement document field, tugma sa DB column ug sa form field names ──
    public const DOCUMENT_LABELS = [
        'business_permit'             => 'CDO Business Permit',
        'sec_dti'                     => 'SEC / DTI',
        'company_profile'             => 'Company Profile',
        'no_pending_case_certificate' => 'Certificate of No Pending Case',
        'vacancy_posting'             => 'Vacancy Posting',
    ];

    /**
     * The business permit label with the year it covers.
     *
     * The year used to be typed into the label as a literal, which meant every
     * January the page named the wrong permit until somebody edited the code.
     */
    public function businessPermitLabel(): string
    {
        return self::DOCUMENT_LABELS['business_permit']
            . ($this->business_permit_year ? ' ' . $this->business_permit_year : '');
    }

    /**
     * The last day the account may run on the permit currently on file.
     *
     * A permit covers one calendar year and lapses with it, but the city does
     * not issue the next one on New Year's Day — renewal season runs into the
     * first quarter. The office allows for that: a 2026 permit carries the
     * account to the 31st of March 2027, and the account is restricted on the
     * 1st of April.
     */
    public function businessPermitGraceEndsAt(): ?\Carbon\Carbon
    {
        if (!$this->business_permit_year) {
            return null;
        }

        $months = (int) config('peso.employer.business_permit_grace_months', 3);

        return \Carbon\Carbon::create($this->business_permit_year + 1, 1, 1)
            ->addMonths($months)
            ->subDay()
            ->endOfDay();
    }

    /** True once the grace has run out and the permit on file is too old. */
    public function isBusinessPermitOverdue(): bool
    {
        $deadline = $this->businessPermitGraceEndsAt();

        return $deadline !== null && now()->greaterThan($deadline);
    }

    /**
     * True while the permit's own year has passed but the grace has not.
     *
     * This is the window where the employer keeps working normally and the
     * office keeps reminding them to bring the new permit in.
     */
    public function isBusinessPermitInGrace(): bool
    {
        if (!$this->business_permit_year) {
            return false;
        }

        return $this->business_permit_year < now()->year && !$this->isBusinessPermitOverdue();
    }

    public function isFieldExpired(string $field): bool
    {
        $date = $this->{"{$field}_expires_at"};
        return $date && $date->isPast();
    }

    public function isFieldExpiringSoon(string $field, int $days = 7): bool
    {
        $date = $this->{"{$field}_expires_at"};
        return $date && !$date->isPast() && $date->lte(now()->addDays($days));
    }

    /**
     * Documents that lapse within the next week, none of them lapsed yet.
     *
     * The five expiry columns run in parallel on one row, so "expiring soon"
     * is true when any one of them falls in the window — the office chases the
     * employer once and asks for whichever paper is closest to running out.
     *
     * Seven days is the same window SendEmployerRequirementExpiryWarnings uses
     * to email the employer, so what the desk sees on screen is exactly the
     * set that has been warned.
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where(function ($q) use ($days) {
            foreach (array_keys(self::DOCUMENT_LABELS) as $field) {
                $q->orWhere(function ($f) use ($field, $days) {
                    $f->whereNotNull("{$field}_expires_at")
                      ->whereDate("{$field}_expires_at", '>=', today())
                      ->whereDate("{$field}_expires_at", '<=', today()->addDays($days));
                });
            }
        });
    }

    // ── Relationships ──
    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Staff::class, 'reviewed_by');
    }
}
