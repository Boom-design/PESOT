<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobActivityLog extends Model
{
    protected $primaryKey = 'job_activity_logs_id';

    protected $fillable = [
        'job_id', 'actor_user_id', 'actor_name',
        'action', 'summary', 'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function job()
    {
        return $this->belongsTo(Job::class, 'job_id', 'job_qualifications_id');
    }
}
