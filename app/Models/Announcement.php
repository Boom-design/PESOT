<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'jobseeker_id', 'employer_id', 'staff_id',
        'type', 'title', 'message', 'is_read',
        'reference_type', 'reference_id',
        'sms_status', 'sms_sent_at',
    ];

    protected $casts = [
        'is_read'     => 'boolean',
        'sms_sent_at' => 'datetime',
    ];

    public function jobseeker()
    {
        return $this->belongsTo(JobseekerRegistration::class, 'jobseeker_id');
    }

    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'employer_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    // ── Helpers — parehas ra ka signature sa daan, pero karon 1 row per recipient sa parehas nga table ──
    public static function sendToJobseekers(array $data, $jobseekerIds)
    {
        foreach ((array) (is_iterable($jobseekerIds) ? $jobseekerIds->toArray() ?? $jobseekerIds : [$jobseekerIds]) as $id) {
            self::create(array_merge($data, ['jobseeker_id' => $id]));
        }
    }

    public static function sendToEmployers(array $data, $employerIds)
    {
        foreach ((array) (is_iterable($employerIds) ? $employerIds->toArray() ?? $employerIds : [$employerIds]) as $id) {
            self::create(array_merge($data, ['employer_id' => $id]));
        }
    }

    public static function sendToStaff(array $data, $staffIds)
    {
        foreach ((array) (is_iterable($staffIds) ? $staffIds->toArray() ?? $staffIds : [$staffIds]) as $id) {
            self::create(array_merge($data, ['staff_id' => $id]));
        }
    }
}