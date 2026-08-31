<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A day the PESO office is occupied — meeting, training, activity, closure.
 *
 * Written by the admin only. Read by every staff calendar, and by the
 * scheduling rules: see App\Support\OfficeCalendar.
 */
class OfficeCalendarEvent extends Model
{
    protected $table = 'office_calendar_events';

    protected $primaryKey = 'office_calendar_events_id';

    protected $fillable = [
        'title',
        'type',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public const TYPES = [
        'meeting'  => 'Meeting',
        'training' => 'Training / Seminar',
        'activity' => 'Office Activity',
        'closure'  => 'Office Closed',
        'other'    => 'Other',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'users_id');
    }

    /** A one-day entry has no end_date — the start doubles as the end. */
    public function getLastDateAttribute()
    {
        return $this->end_date ?: $this->start_date;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? 'Other';
    }

    public function getDateRangeLabelAttribute(): string
    {
        if (!$this->end_date || $this->end_date->isSameDay($this->start_date)) {
            return $this->start_date->format('M d, Y');
        }

        return $this->start_date->format('M d') . ' – ' . $this->end_date->format('M d, Y');
    }

    public function getTimeLabelAttribute(): ?string
    {
        if (!$this->start_time) return null;

        $start = \Carbon\Carbon::parse($this->start_time)->format('h:i A');

        return $this->end_time
            ? $start . ' – ' . \Carbon\Carbon::parse($this->end_time)->format('h:i A')
            : $start;
    }
}
