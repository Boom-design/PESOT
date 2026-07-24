<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerNsrpRegistration extends Model
{
    protected $table = 'employer_nsrp_registrations';

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_person',
        'position_title',
        'mobile_number',
        'employer_type',
        'is_overseas',
        'trade_name', 'tin', 'tin_type', 'total_workforce', 'line_of_business',
        'est_barangay', 'est_city_municipality', 'est_province',
        'contact_title', 'telephone_no', 'fax_no',
        'certification_agreed', 'certification_date',
        'initial_vacancy_data',
        'initial_vacancy_confirmed',
    ];

    protected $casts = [
        'is_overseas'               => 'boolean',
        'certification_agreed'      => 'boolean',
        'certification_date'        => 'date',
        'initial_vacancy_data'      => 'array',
        'initial_vacancy_confirmed' => 'boolean',
    ];

    public function employer()
    {
        return $this->belongsTo(User::class, 'user_id');
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