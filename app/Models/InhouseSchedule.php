<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InhouseSchedule extends Model
{
    protected $primaryKey = 'inhouse_schedules_id';

    protected $fillable = [
        'employer_id',
        'reviewed_by',
        'preferred_date',
        'preferred_time',
        'num_applicants',
        'venue_type',
        'venue_address',
        'notes',
        'status',
        'rejection_reason',
        'confirmed_date',
        'confirmed_time',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'confirmed_date' => 'date',
        'job_positions'  => 'array',
    ];

    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'employer_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Staff::class, 'reviewed_by');
    }
}