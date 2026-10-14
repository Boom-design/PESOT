<?php

namespace App\Support;

use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * The one way an administrator's action gets written down.
 *
 * PESO, 2026-10-14: three administrators, one login between them, so every
 * action in the office was the work of "PESO Admin". Three accounts now, and
 * each of them can see what the other two did.
 *
 * One entry point, because the alternative is each controller inventing its
 * own wording and its own idea of what is worth recording — and a log that
 * covers four of the six things an admin can do is worse than none, since the
 * two it misses look like they never happened.
 *
 * Never throws. A log that can break the thing it is recording is a log that
 * will one day stop somebody switching off an account they needed to switch
 * off. A failure goes to laravel.log instead.
 */
class AdminAudit
{
    public static function record(
        string $action,
        string $summary,
        ?string $subjectType = null,
        ?int $subjectId = null
    ): void {
        try {
            $actor = Auth::user();

            // Only an administrator's own work belongs here. Staff decisions
            // are recorded against the thing they decided — reviewed_by on the
            // posting, the schedule, the requirement — and reach their own
            // desks there.
            if (!$actor || $actor->role !== 'admin') {
                return;
            }

            AdminActivityLog::create([
                'actor_user_id' => $actor->users_id,
                // Copied, not looked up later: an admin who renames themselves
                // must not rewrite what the log says they did last month.
                'actor_name'    => $actor->name,
                'action'        => $action,
                'summary'       => mb_substr($summary, 0, 500),
                'subject_type'  => $subjectType,
                'subject_id'    => $subjectId,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Admin activity log failed to write', [
                'action' => $action,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
