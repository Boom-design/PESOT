<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// ── Usa ra ka device kada office account.
// ──
// ── PESO, 2026-10-26: "bawal" nga bukas ang usa ka admin account sa duha ka
// ── device dungan. Ang Laravel by default motugot: kada device naay
// ── kaugalingon nga row sa `sessions`, ug walay usa kanila nga mahibalo sa
// ── uban. Ang bag-ong login mao ang magpabilin; ang nauna gipapahawa.
// ──
// ── Ang employer ug ang jobseeker wala giapil. Ang usa ka jobseeker mobasa
// ── sa posting sa iyang telepono ug mo-apply sa hinulaman nga laptop; kung
// ── atong pugngan kana, ang system ang mahimong daw guba sa mata sa tawo nga
// ── atong gialagaran.
class SingleDeviceSession
{
    /** The roles that may only hold one live session at a time. */
    private const ROLES = ['admin', 'staff'];

    /** How long the explanation waits for the device that lost its session. */
    private const NOTICE_TTL_HOURS = 12;

    /** What the device that lost its session is told, wherever it is told. */
    public const MESSAGE = 'You were signed out because this account signed in on another device.';

    /** This browser's session is still live. */
    public const STATE_ACTIVE = 'active';

    /** It was ended by a sign-in somewhere else, and nobody has said so yet. */
    public const STATE_TAKEN_OVER = 'taken_over';

    /** Nobody is signed in here, and nothing was taken away. */
    public const STATE_GUEST = 'guest';

    public static function applies(?User $user): bool
    {
        return $user !== null && in_array($user->role, self::ROLES, true);
    }

    /**
     * Ends every other session this account still has open.
     *
     * Call this straight after the session id has been regenerated, so the id
     * kept below is the one the device signing in will carry from here on.
     *
     * Returns how many sessions were ended.
     */
    public static function enforce(Request $request, ?User $user): int
    {
        // Only the database driver keeps one row per session, which is what
        // makes the other devices reachable at all. With any other driver the
        // rule simply does not apply instead of failing a login.
        if (!self::applies($user) || config('session.driver') !== 'database') {
            return 0;
        }

        $table   = config('session.table', 'sessions');
        $current = $request->session()->getId();

        // The session row's user_id is written by Laravel from the auth
        // identifier, and this model's key is `users_id`, not `id` — asking
        // for `$user->id` here would match nothing at all.
        $others = DB::table($table)
            ->where('user_id', $user->getAuthIdentifier())
            ->where('id', '!=', $current)
            ->pluck('id');

        if ($others->isEmpty()) {
            return 0;
        }

        // Leave the reason behind before the row goes. The other device still
        // holds its cookie, so its next request arrives carrying this same id
        // and the person can be told what happened — instead of being dropped
        // on a login page that explains nothing.
        foreach ($others as $id) {
            Cache::put(self::noticeKey($id), true, now()->addHours(self::NOTICE_TTL_HOURS));
        }

        DB::table($table)->whereIn('id', $others->all())->delete();

        return $others->count();
    }

    /**
     * Was this session ended by a sign-in somewhere else?
     *
     * The answer is read once and then forgotten, so the message is shown to
     * the person a single time.
     */
    public static function wasSignedOutElsewhere(string $sessionId): bool
    {
        return Cache::pull(self::noticeKey($sessionId)) === true;
    }

    /**
     * What has become of the session behind this id.
     *
     * Asked by the open page itself, every few seconds, so a device that has
     * been signed out elsewhere finds out while it sits there — instead of
     * looking live until the next click.
     *
     * The answer is read without consuming it: the login page the person
     * lands on afterwards still has its own reason to show.
     */
    public static function state(?string $sessionId): string
    {
        if ($sessionId === null || $sessionId === '') {
            return self::STATE_GUEST;
        }

        if (config('session.driver') === 'database') {
            $live = DB::table(config('session.table', 'sessions'))
                ->where('id', $sessionId)
                ->whereNotNull('user_id')
                ->exists();

            if ($live) {
                return self::STATE_ACTIVE;
            }
        }

        return Cache::get(self::noticeKey($sessionId)) === true
            ? self::STATE_TAKEN_OVER
            : self::STATE_GUEST;
    }

    private static function noticeKey(string $sessionId): string
    {
        return 'peso:session-taken-over:' . $sessionId;
    }
}
