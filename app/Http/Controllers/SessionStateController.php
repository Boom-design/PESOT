<?php

namespace App\Http\Controllers;

use App\Support\SingleDeviceSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// ── Ang pangutana sa abli nga panid: "buhi pa ba ko?"
// ──
// ── Kini nga route tuyo nga WALA sa session middleware. Kung mosulod siya sa
// ── session, ang matag pagpangutana mo-usab sa `last_activity`, ug ang abli
// ── nga tab mahimong dili na gyud mahurot — ang usa ka admin nga nibiya sa
// ── iyang lamesa magpabilin nga naka-login hangtod sa hangtod. Mao nga ang
// ── session id gikuha gikan sa cookie mismo, ug walay gisulat.
class SessionStateController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // EncryptCookies has already decrypted this, so the value is the
        // session id itself. Reading the cookie starts nothing and writes
        // nothing.
        $state = SingleDeviceSession::state($request->cookie(config('session.cookie')));

        return response()
            ->json([
                'state'   => $state,
                'message' => $state === SingleDeviceSession::STATE_TAKEN_OVER
                    ? SingleDeviceSession::MESSAGE
                    : null,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
