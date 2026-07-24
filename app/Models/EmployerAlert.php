<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployerAlert extends Model
{
    protected $fillable = [
        'employer_id', 'type', 'title', 'message',
        'reference_type', 'reference_id', 'is_read',
        'sms_status', 'sms_sent_at',
    ];

    protected $casts = [
        'is_read'     => 'boolean',
        'sms_sent_at' => 'datetime',
    ];

    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'employer_id');
    }
}