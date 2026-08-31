<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;  // ← ADDED

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;  // ← ADDED HasApiTokens

    protected $primaryKey = 'users_id';

    protected $fillable = [
    'name',
    // Asked for at registration and carried into the NSRP form so nobody types
    // it twice. Optional — plenty of people have no middle name.
    'middle_name',
    'email',
    'password',
    // Gi-set kung ang PESO staff o Admin nagbutang ug temporary password para
    // sa employer nga nawad-an ug access. Walay laing page nga maabot niya
    // hangtod mo-ilis siya — tan-awa ang EnsurePasswordChanged nga middleware.
    'must_change_password',
    'role',
    'status',
    'phone',
    'profile_photo',
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    // Relationship: User (company) has many Jobs — via employer_nsrp_registrations
    public function jobs()
    {
        return $this->hasManyThrough(
            Job::class,
            EmployerNsrpRegistration::class,
            'user_id',      // FK sa employer_nsrp_registrations nga naka-point sa users
            'company_id',   // FK sa jobs nga naka-point sa employer_nsrp_registrations
            'users_id',                       // local key sa users
            'employer_nsrp_registrations_id'  // local key sa employer_nsrp_registrations
        );
    }

    // Relationship: User (jobseeker) has many Applications — via jobseeker_registrations
    public function applications()
    {
        return $this->hasManyThrough(
            Application::class,
            JobseekerRegistration::class,
            'user_id',
            'jobseeker_id',
            'users_id',
            'jobseeker_registrations_id'
        );
    }

    public function employerRequirement()
{
    return $this->hasOneThrough(
        \App\Models\EmployerRequirement::class,
        \App\Models\EmployerNsrpRegistration::class,
        'user_id',   // FK sa employer_nsrp_registrations → users
        'user_id',   // FK sa employer_requirements → employer_nsrp_registrations
        'users_id',
        'employer_nsrp_registrations_id'
    );
}

/**
 * Every establishment this account holds.
 *
 * PESO IT, 2026-08-26: one HR officer can be the authorised contact for two
 * companies, and asked for one e-mail to cover both. The e-mail is how a
 * person signs in, so the account stays one and the companies became many.
 *
 * Ordered oldest first, so "the first one" means the one they registered with
 * and not whatever the database happened to hand back.
 */
public function employerCompanies()
{
    return $this->hasMany(\App\Models\EmployerNsrpRegistration::class, 'user_id')
        ->orderBy('employer_nsrp_registrations_id');
}

/**
 * The establishment this account registered with.
 *
 * Still a hasOne, and still the right answer wherever the question is about
 * the ACCOUNT — who owns it, who to call, is it local or overseas. Where the
 * question is about the work being done right now, ask activeCompany()
 * instead: an HR holding two companies is doing that work for one of them.
 */
public function employerNsrp()
{
    return $this->hasOne(\App\Models\EmployerNsrpRegistration::class, 'user_id')
        ->oldest('employer_nsrp_registrations_id');
}

/**
 * The establishment this account is working on right now.
 *
 * Held in the session, because it is a property of the sitting, not of the
 * account: the same HR signs in and works on Company A this morning and
 * Company B this afternoon. Falls back to the first company, so an account
 * with only one — which is nearly all of them — never has to choose.
 *
 * The session id is checked against the account's own companies before it is
 * honoured. A pasted id from another account resolves to null, not to someone
 * else's establishment.
 */
public function activeCompany()
{
    $companies = $this->relationLoaded('employerCompanies')
        ? $this->employerCompanies
        : $this->employerCompanies()->get();

    if ($companies->isEmpty()) {
        return null;
    }

    $chosen = session('active_company_id');

    return $companies->firstWhere('employer_nsrp_registrations_id', (int) $chosen)
        ?: $companies->first();
}

/** Does this account hold more than one establishment? */
public function holdsManyCompanies(): bool
{
    return $this->employerCompanies()->count() > 1;
}

    public function registration()
    {
        return $this->hasOne(\App\Models\JobseekerRegistration::class, 'user_id');
    }

    // Relationship: User (staff) has one Staff profile record — normalized structure
    public function staff()
    {
        return $this->hasOne(\App\Models\Staff::class, 'user_id');
    }

    // Accessor — nagpahimo sa $user->staff_role para mo-work gihapon
    // bisan tangtangon na ang staff_role column sa users table
    public function getStaffRoleAttribute()
    {
        return $this->staff?->staff_role;
    }
}