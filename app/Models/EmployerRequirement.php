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

        // Ang papel sa ahensya sa gawas. Tulo ra kanila ang naay petsa.
        'agency_profile',
        'cert_of_renewal_license',
        'cert_of_renewal_license_expires_at',
        'cert_of_renewal_license_expiry_notified_at',
        'jo_balance_dmw',
        'branch_org_chart',
        'authority_to_operate',
        'authority_to_operate_expires_at',
        'authority_to_operate_expiry_notified_at',

        'status',
        'remarks',
        'rejected_fields',
        'rejection_notes',
        'approved_fields',
    ];

    protected $casts = [
        'rejected_fields' => 'array',
        'rejection_notes' => 'array',
        'approved_fields' => 'array',
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
        'cert_of_renewal_license_expires_at' => 'date',
        'authority_to_operate_expires_at' => 'date',
        'cert_of_renewal_license_expiry_notified_at' => 'datetime',
        'authority_to_operate_expiry_notified_at' => 'datetime',
    ];

    // ── ANG PAPEL SA MATAG MERKADO ──
    //
    // PESO SRA, 2026-10-21: lahi ang gipangayo sa ahensya sa gawas kaysa sa
    // lokal nga establisemento, ug naay kaugalingon silang papel sa opisina.
    // Kaniadto ang tanan gipangayoan sa lokal nga lima, mao nga ang ahensya
    // nag-upload ug SEC/DTI ug Vacancy Posting nga wala gyud gibasa sa SRA,
    // ug wala gyud gipangayoan sa renewal sa lisensya o sa J.O balance.
    //
    // Ang business permit mao ra ang usa nga gidala sa duha.

    /** Ang lima ka papel sa lokal nga establisemento. */
    public const LOCAL_DOCUMENTS = [
        'business_permit'             => 'CDO Business Permit',
        'sec_dti'                     => 'SEC / DTI',
        'company_profile'             => 'Company Profile',
        'no_pending_case_certificate' => 'Certificate of No Pending Case',
        'vacancy_posting'             => 'Vacancy Posting',
    ];

    /**
     * Ang unom ka papel sa ahensya sa gawas, sa han-ay sa papel sa opisina.
     *
     * Ang papel naglista ug walo. Wala dinhi ang PhilJobNet registration, nga
     * gitangtang sa opisina, ug ang filled-up NSRP form, nga nakuha na sa
     * sistema sa pag-register.
     */
    public const OVERSEAS_DOCUMENTS = [
        'agency_profile'          => 'Agency Profile',
        'business_permit'         => 'CDO Business Permit',
        'cert_of_renewal_license' => 'Certificate of Renewal of License',
        'jo_balance_dmw'          => 'J.O Balance Approved by DMW (latest)',
        'branch_org_chart'        => 'Branch Organizational Chart / Office Staff ID',
        'authority_to_operate'    => 'Authority to Operate a Branch Office',
    ];

    /**
     * Ang ngalan sa tanan nga papel sa duha ka merkado.
     *
     * Para ni sa paghubad sa usa ka field ngadto sa ngalan niya — kanunay
     * siya nga husto, bisan kinsa ang employer. Ang pangutana nga "unsa ang
     * gipangayo niining employer" NATUBAG sa documentsFor(), dili dinhi.
     */
    public const DOCUMENT_LABELS = [
        ...self::LOCAL_DOCUMENTS,
        ...self::OVERSEAS_DOCUMENTS,
    ];

    /**
     * Ang papel nga naay petsa nga mahuman.
     *
     * Dili tanan. Ang Agency Profile, ang J.O Balance ug ang Branch
     * Organizational Chart walay petsa nga naka-print, mao nga ang pagpangayo
     * ug expiry nagpugos sa employer sa pag-imbento.
     */
    public const EXPIRY_FIELDS = [
        'business_permit',
        'sec_dti',
        'company_profile',
        'no_pending_case_certificate',
        'vacancy_posting',
        'cert_of_renewal_license',
        'authority_to_operate',
    ];

    /** Ang papel nga gipangayo sa usa ka merkado. */
    public static function documentsFor(bool $isOverseas): array
    {
        return $isOverseas ? self::OVERSEAS_DOCUMENTS : self::LOCAL_DOCUMENTS;
    }

    /** Naay expiry ba kini nga papel? */
    public static function fieldExpires(string $field): bool
    {
        return in_array($field, self::EXPIRY_FIELDS, true);
    }

    /**
     * The business permit label with the year it covers.
     *
     * The year used to be typed into the label as a literal, which meant every
     * January the page named the wrong permit until somebody edited the code.
     */
    /**
     * Every paper the desk may be asked to judge, both markets together.
     *
     * This is an allow-list, not a checklist: it answers "may the desk accept
     * or reject this field at all", which is the question the accept and
     * reject routes ask before they touch anything. Whether a particular
     * employer owes a particular paper is reviewedDocuments(), below.
     *
     * nsrp_establishment_form and company_logo are not here on purpose: the
     * first comes from the registration and the desk never judged it, and the
     * second is a picture so the desk can tell one company from another.
     */
    public const REVIEWED_DOCUMENTS = [
        'business_permit',
        'sec_dti',
        'company_profile',
        'no_pending_case_certificate',
        'vacancy_posting',
        'agency_profile',
        'cert_of_renewal_license',
        'jo_balance_dmw',
        'branch_org_chart',
        'authority_to_operate',
    ];

    /**
     * The papers THIS employer owes, in the order the office reads them.
     *
     * Everything that counts a folder complete goes through here, so an
     * agency is never held up for a Vacancy Posting and a local establishment
     * is never asked for a DMW job order balance.
     */
    public function reviewedDocuments(): array
    {
        return array_keys(self::documentsFor((bool) $this->employer?->is_overseas));
    }

    /** Nabasa na ba sa desk kini nga papel ug gidawat? */
    public function isFieldAccepted(string $field): bool
    {
        return in_array($field, (array) $this->approved_fields, true);
    }

    /** Pila ka papel ang nadawat na. */
    public function acceptedDocumentCount(): int
    {
        return count(array_intersect((array) $this->approved_fields, $this->reviewedDocuments()));
    }

    /**
     * Ang papel nga wala pa gidawat — apil ang wala gi-upload.
     *
     * Ang wala gi-submit maapil dinhi tinuyo: dili siya madawat, mao nga dili
     * siya mahurot, mao nga dili ma-approve ang folder. Kana husto — ang
     * pagbasa ug upat ka papel dili pag-aprubar sa lima.
     */
    public function documentsNotYetAccepted(): array
    {
        return array_values(array_diff($this->reviewedDocuments(), (array) $this->approved_fields));
    }

    /** Kompleto na ba ang tanan, andam na ba i-approve? */
    public function allDocumentsAccepted(): bool
    {
        return $this->documentsNotYetAccepted() === [];
    }

    /** Gibalibaran na ba sa desk kini nga papel? */
    public function isFieldRejected(string $field): bool
    {
        return in_array($field, (array) $this->rejected_fields, true);
    }

    /** Ang hinungdan nga gisulat sa desk para niining usa ka papel. */
    public function fieldRejectionNote(string $field): ?string
    {
        return ((array) $this->rejection_notes)[$field] ?? null;
    }

    /** Nahukman na ba kini nga papel — gidawat o gibalibaran? */
    public function isFieldDecided(string $field): bool
    {
        return $this->isFieldAccepted($field) || $this->isFieldRejected($field);
    }

    /**
     * Ang papel nga wala pa nahukman — wala pa gidawat, wala pa gibalibaran.
     *
     * Ang wala gi-upload maapil dinhi tinuyo: dili siya madawat, mao nga dili
     * mahuman ang folder hangtod ipadala siya sa employer.
     */
    public function documentsNotYetDecided(): array
    {
        return array_values(array_filter(
            $this->reviewedDocuments(),
            fn($f) => !$this->isFieldDecided($f)
        ));
    }

    /** Nahukman na ang tanan, bisan pila ang gibalibaran. */
    public function allDocumentsDecided(): bool
    {
        return $this->documentsNotYetDecided() === [];
    }

    /** Naay papel nga gibalibaran, mao nga ang folder mobalik sa employer. */
    public function hasRejectedDocuments(): bool
    {
        return count(array_intersect((array) $this->rejected_fields, $this->reviewedDocuments())) > 0;
    }

    /** Pila ka papel ang nahukman na, gidawat man o gibalibaran. */
    public function decidedDocumentCount(): int
    {
        return count($this->reviewedDocuments()) - count($this->documentsNotYetDecided());
    }

    /** Ang tawhanong ngalan sa papel, para sa mensahe ug sa listahan. */
    public static function documentLabel(string $field): string
    {
        return self::DOCUMENT_LABELS[$field] ?? $field;
    }

    public function businessPermitLabel(): string
    {
        return self::DOCUMENT_LABELS['business_permit']
            . ($this->business_permit_year ? ' ' . $this->business_permit_year : '');
    }

    /**
     * The last day the account may run on the permit currently on file.
     *
     * A permit lapses on the date printed on it, but the city does not issue
     * the next one the following morning — renewal season runs into the first
     * quarter. The office allows for that: a permit expiring 31 December 2026
     * carries the account to the 31st of March 2027, and the account is
     * restricted on the 1st of April.
     *
     * The grace is counted from the expiry the employer read off the paper, so
     * a permit that runs out mid-year is not quietly given until March.
     */
    public function businessPermitGraceEndsAt(): ?\Carbon\Carbon
    {
        if (!$this->business_permit_expires_at) {
            return null;
        }

        $months = (int) config('peso.employer.business_permit_grace_months', 3);

        return $this->business_permit_expires_at->copy()
            ->addMonths($months)
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
        if (!$this->business_permit_expires_at) {
            return false;
        }

        return $this->business_permit_expires_at->isPast() && !$this->isBusinessPermitOverdue();
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
            foreach (self::EXPIRY_FIELDS as $field) {
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
