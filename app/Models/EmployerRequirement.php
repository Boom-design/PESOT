<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerRequirement extends Model
{
    protected $fillable = [
        'user_id',
        'reviewed_by',
        'business_permit',
        'sec_dti',
        'company_profile',
        'nsrp_establishment_form',
        'no_pending_case_certificate',
        'vacancy_posting',
        'status',
        'remarks',
        'rejected_fields',
    ];

    protected $casts = [
        'rejected_fields' => 'array',
    ];

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