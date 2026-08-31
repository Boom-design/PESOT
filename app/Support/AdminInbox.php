<?php

namespace App\Support;

use App\Models\Announcement;
use Illuminate\Support\Facades\DB;

/**
 * What the admin has not looked at yet, and where it lives.
 *
 * The bell told the admin that something happened but never where, so finding
 * it meant opening Job Activities and clicking through four tabs. This turns
 * the same rows into counters that sit on the sidebar link and on the tab
 * button itself: the number says how many, the red says which tab, and opening
 * that tab clears it.
 *
 * "Seen" here is the admin's own mark (`announcements.admin_seen_at`), never
 * `is_read` — that one belongs to the jobseeker, employer or staff member the
 * notice was written for, and the admin must not answer their mail for them.
 */
class AdminInbox
{
    /**
     * Which notice belongs on which tab of Job Activities.
     *
     * A notice about a vacancy carries `reference_type = 'job'` whatever kind
     * of vacancy it is, so those are split further by the posting's own
     * schedule_type below.
     */
    private const TAB_REFERENCES = [
        'inhouse'          => ['inhouse_schedule'],
        'jobfair'          => ['job_fair'],
        'companyinterview' => [],
    ];

    /** The posting kind each tab is about, for `reference_type = 'job'` rows. */
    private const TAB_SCHEDULE_TYPES = [
        'inhouse'          => 'inhouse',
        'jobfair'          => 'job_fair',
        'companyinterview' => 'company_interview',
    ];

    /** Everything the Registrations page answers for. */
    private const REGISTRATION_REFERENCES = [
        'jobseeker_registration',
        'employer_registration',
        'employer_requirement',
        'employer_inactivity',
    ];

    /** Unseen count per Job Activities tab, keyed the same way as `?tab=`. */
    public static function jobActivityCounts(): array
    {
        $counts = [];

        foreach (self::TAB_REFERENCES as $tab => $references) {
            $counts[$tab] = self::tabQuery($tab, $references)->count();
        }

        return $counts;
    }

    /** Unseen notices across every Job Activities tab. */
    public static function jobActivityTotal(): int
    {
        return array_sum(self::jobActivityCounts());
    }

    /**
     * The tab the admin should land on — the one holding the oldest unseen
     * notice, so nothing waits behind a newer arrival. Falls back to the tab
     * the page opens on when there is nothing to see.
     */
    public static function firstTabWithNews(): string
    {
        $oldest = null;
        $answer = 'inhouse';

        foreach (self::TAB_REFERENCES as $tab => $references) {
            $at = self::tabQuery($tab, $references)->min('created_at');

            if ($at !== null && ($oldest === null || $at < $oldest)) {
                $oldest = $at;
                $answer = $tab;
            }
        }

        return $answer;
    }

    /** Stop counting this tab's notices — the admin is looking at them now. */
    public static function markTabSeen(string $tab): void
    {
        $references = self::TAB_REFERENCES[$tab] ?? null;
        if ($references === null) return;

        self::tabQuery($tab, $references)->update(['admin_seen_at' => now()]);
    }

    public static function registrationCount(): int
    {
        return self::registrationQuery()->count();
    }

    public static function markRegistrationsSeen(): void
    {
        self::registrationQuery()->update(['admin_seen_at' => now()]);
    }

    /** Everything unseen, wherever it lives — the number on the bell. */
    public static function bellCount(): int
    {
        return Announcement::whereNull('admin_seen_at')->count();
    }

    public static function markAllSeen(): void
    {
        Announcement::whereNull('admin_seen_at')->update(['admin_seen_at' => now()]);
    }

    // ───────────────────────────────
    private static function registrationQuery()
    {
        return Announcement::whereNull('admin_seen_at')
            ->whereIn('reference_type', self::REGISTRATION_REFERENCES);
    }

    private static function tabQuery(string $tab, array $references)
    {
        $scheduleType = self::TAB_SCHEDULE_TYPES[$tab] ?? null;

        return Announcement::whereNull('admin_seen_at')
            ->where(function ($q) use ($references, $scheduleType) {
                if ($references !== []) {
                    $q->whereIn('reference_type', $references);
                }

                // A vacancy notice points at a job row; that row says which
                // kind of posting it is, and so which tab explains it.
                if ($scheduleType !== null) {
                    $q->orWhere(function ($j) use ($scheduleType) {
                        $j->where('reference_type', 'job')
                          ->whereIn('reference_id', function ($sub) use ($scheduleType) {
                              $sub->select('job_qualifications_id')
                                  ->from('job_qualifications')
                                  ->where('schedule_type', $scheduleType);
                          });
                    });
                }

                // Nothing claims this tab — make sure the clause matches nothing
                // rather than everything.
                if ($references === [] && $scheduleType === null) {
                    $q->whereRaw('1 = 0');
                }
            });
    }
}
