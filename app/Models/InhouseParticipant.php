<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InhouseParticipant extends Model
{
    protected $fillable = [
        'inhouse_schedule_id',
        'jobseeker_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(InhouseSchedule::class, 'inhouse_schedule_id');
    }

    public function jobseeker()
    {
        return $this->belongsTo(JobseekerRegistration::class, 'jobseeker_id');
    }
}