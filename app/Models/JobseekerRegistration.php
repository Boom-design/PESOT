<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobseekerRegistration extends Model
{
    protected $table = 'jobseeker_registrations';

    protected $primaryKey = 'jobseeker_registrations_id';

    protected $fillable = [
        'user_id',
        'surname', 'first_name', 'middle_name', 'suffix',
        'date_of_birth', 'age', 'sex', 'religion', 'civil_status',
        'house_street', 'barangay', 'municipality_city', 'province',
        'perm_house_street', 'perm_barangay', 'perm_municipality_city', 'perm_province',
        'same_as_permanent',
        'tin', 'disabilities', 'disability_other',
        'height', 'weight', 'contact_number', 'reg_email',
        'sms_opt_in',
        // Ang is_walk_in gipasa sa storeWalkinNsrp ug sa pag-link sa account,
        // apan wala siya diri kaniadto — mao nga hilom siyang gilabay sa mass
        // assignment ug ang matag walk-in natala nga 0. Duha ang naguba niini:
        // ang badge nga "Walk-in" sa listahan sa LRA/SRA wala gyud mogawas, ug
        // ang auto-link sa UnifiedAuthController — nga mangita ug
        // where('is_walk_in', true) — dili gyud makakita bisan usa, mao nga ang
        // walk-in nga mohimo ug account ugma mapugos ug sulat pag-usab sa
        // tibuok NSRP, ug duha na ang rekord sa usa ka tawo.
        'is_walk_in',
    ];

    protected $casts = [
        'disabilities'       => 'array',
        'same_as_permanent'  => 'boolean',
        'date_of_birth'      => 'date',
        'sms_opt_in'         => 'boolean',
    ];

    // ── RELATIONSHIPS ──
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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