<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobseekerAlert extends Model
{
    protected $fillable = [
        'jobseeker_id', 'type', 'title', 'message',
        'reference_type', 'reference_id', 'is_read',
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
}