<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// ── Kung ang PESO staff o ang Admin nagbutang ug temporary password para sa
// ── usa ka employer nga nawad-an ug access, ang tawo sa opisina nakahibalo
// ── ana nga password — gibasa niya kini sa telepono.
// ──
// ── Kini nga middleware maoy naghimo nga mubo kaayo kadto nga panahon: walay
// ── laing page nga maabot ang employer hangtod mo-ilis siya. Dili igo ang
// ── pagpahinumdom lang; ang pugos maoy naghimo nga sigurado. ──
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        // Ang mga route nga kinahanglan gyud maabot, kay kung dili, mag-libot
        // ra siya sa redirect: ang screen mismo, ang pag-save, ug ang logout.
        $allowed = [
            'password.force',
            'password.force.update',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $allowed, true)) {
            return $next($request);
        }

        // Ang AJAX ug ang JSON dili angay hatagan ug redirect ngadto sa HTML.
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You must change your temporary password before continuing.',
            ], 423);
        }

        return redirect()->route('password.force');
    }
}
