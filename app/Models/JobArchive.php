<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobArchive extends Model
{
    protected $primaryKey = 'job_archives_id';

    protected $fillable = [
        'company_id',
        'company_name',
        'job_title',
        'schedule_type',
        'slots',
        'hired_count',
        'applicants_count',
        'posted_month',
        'archived_at',
    ];

    protected $casts = [
        'posted_month' => 'date',
        'archived_at'  => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'company_id');
    }
}
