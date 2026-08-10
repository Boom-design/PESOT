<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'title',
        'message',
        'is_read',
        'user_id',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // ── RELATIONSHIP ──
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}