<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAlert extends Model
{
    protected $fillable = [
        'staff_id', 'type', 'title', 'message',
        'reference_type', 'reference_id', 'is_read',
        'sms_status', 'sms_sent_at',
    ];

    protected $casts = [
        'is_read'     => 'boolean',
        'sms_sent_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}