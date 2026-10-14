<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One thing one administrator did.
 *
 * Written by App\Support\AdminAudit, read by the Activity Logs page. Nothing
 * updates or deletes these rows: a log that can be edited answers no question
 * the other two administrators are asking it.
 */
class AdminActivityLog extends Model
{
    protected $primaryKey = 'admin_activity_logs_id';

    protected $fillable = [
        'actor_user_id', 'actor_name',
        'action', 'summary',
        'subject_type', 'subject_id',
    ];

    /** The icon and colour each kind of action is drawn with. */
    public const LOOKS = [
        'staff_created'         => ['ph-user-plus',      'var(--g-600)'],
        'staff_status_changed'  => ['ph-toggle-left',    'var(--warn)'],
        'staff_password_reset'  => ['ph-key',            'var(--warn)'],
        'staff_role_changed'    => ['ph-user-switch',    'var(--g-600)'],
        'calendar_added'        => ['ph-calendar-plus',  'var(--g-600)'],
        'calendar_updated'      => ['ph-calendar-dots',  'var(--warn)'],
        'calendar_deleted'      => ['ph-calendar-x',     'var(--danger)'],
        'employer_recovered'    => ['ph-user-switch',    'var(--warn)'],
        'profile_updated'       => ['ph-identification-card', 'var(--n-500)'],
        'password_changed'      => ['ph-lock-key',       'var(--n-500)'],
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id', 'users_id');
    }

    /** ['icon', 'colour'] for this row, with a plain fallback. */
    public function look(): array
    {
        return self::LOOKS[$this->action] ?? ['ph-dot-outline', 'var(--n-500)'];
    }
}
