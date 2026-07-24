<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    protected $table = 'job_qualifications';

    protected $fillable = [
    'company_id',
    'title',
    'description',
    'location',
    'type',
    'industry_group',
    'slots',
    'status',
    'posting_status',
    'posting_type',
    'remarks',
    'salary',
    'deadline',            // ← bug fix: gi-pasa na sa controller pero wala diri sauna
    'accepts_programs',    // ← bug fix: gi-pasa na sa controller pero wala diri sauna
    'religion',
    'civil_status',
    'other_qualifications',
    'accepts_disability',
    'disability_types',
    'course_major',
    'license',
    'eligibility',
    'certification',
    'language',
    'preferred_residence',
    'experience_months',
    'experience_required',  // ← para sa Smart Matching (age/height/exp/skills criteria)
    'experience_years',
    'age_required',
    'age_min',
    'age_max',
    'height_required',
    'height_minimum',
    'skills_required',
    'sex_preference',
    'education_required',
    'schedule_type',
    'preferred_date',
    'preferred_time',
    'poster_image',
    'venue_type',
    'venue_address',
];
    
    protected $casts = [
        'disability_types'  => 'array',
        'accepts_programs'  => 'array',
        'skills_required'   => 'array',
        'deadline'          => 'date',
        'preferred_date'    => 'date',
    ];

    // Relationship: Job belongs to a Company (EmployerNsrpRegistration)
    public function company()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'company_id');
    }

    // Relationship: Job has many Applications
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    

    
}