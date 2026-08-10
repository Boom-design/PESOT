<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'user_id',
        'staff_role',
        'first_name',
        'last_name',
        'middle_name',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function announcements()
    {
        return $this->hasMany(\App\Models\Announcement::class, 'staff_id');
    }
}