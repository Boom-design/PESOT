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
    'email',
    'password',
    'role',
    'status',
    'phone',
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

public function employerNsrp()
{
    return $this->hasOne(\App\Models\EmployerNsrpRegistration::class, 'user_id');
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