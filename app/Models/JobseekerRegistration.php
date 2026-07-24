<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobseekerRegistration extends Model
{
    protected $table = 'jobseeker_registrations';

    protected $fillable = [
        'user_id',
        'surname', 'first_name', 'middle_name', 'suffix',
        'date_of_birth', 'age', 'sex', 'religion', 'civil_status',
        'house_street', 'barangay', 'municipality_city', 'province',
        'perm_house_street', 'perm_barangay', 'perm_municipality_city', 'perm_province',
        'same_as_permanent',
        'tin', 'disabilities', 'disability_other',
        'height', 'contact_number', 'reg_email',
    ];

    protected $casts = [
        'disabilities'       => 'array',
        'same_as_permanent'  => 'boolean',
        'date_of_birth'      => 'date',
    ];

    // ── RELATIONSHIPS ──
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nsrp()
{
    return $this->hasOne(JobseekerNsrpRegistration::class, 'jobseeker_registration_id');
}

    public function applications()
    {
        return $this->hasMany(Application::class, 'jobseeker_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'jobseeker_id');
    }
}