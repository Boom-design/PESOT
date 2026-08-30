<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// ── Ang employer nga na-disable kay wala mitubag sa pangutana sa opisina kung
// ── nagpadayon pa ba sila sa pagpangita ug tawo.
// ──
// ── Makasulod gihapon siya — kinahanglan gyud — kay didto ra man niya isulti
// ── ang iyang rason, ug kana nga rason mao ang mag-abli pag-usab sa iyang
// ── account. Mao nga dili 'deactivated' ang gigamit: kana mo-babag sa login
// ── mismo ug wala nay paagi nga makasulti pa siya.
// ──
// ── Usa ra ka pahina ang iyang maabot samtang dormant siya. Ang pagpahinumdom
// ── lang dili igo: kung makasulod siya sa dashboard, ang porma sa rason
// ── mahimong usa na lang ka butang nga malaktawan. ──
class EnsureEmployerActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'company' || $user->status !== 'dormant') {
            return $next($request);
        }

        // Ang screen mismo, ang pag-save, ug ang logout — kung dili ni
        // tugotan, mag-libot ra siya sa redirect.
        $allowed = [
            'employer.dormant',
            'employer.dormant.submit',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $allowed, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your account is disabled. Please tell PESO your hiring status to reopen it.',
            ], 423);
        }

        return redirect()->route('employer.dormant');
    }
}
