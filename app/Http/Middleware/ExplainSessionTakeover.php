<?php

namespace App\Http\Middleware;

use App\Support\SingleDeviceSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// ── Nganong napahawa siya.
// ──
// ── Ang device nga napapahawa sa SingleDeviceSession walay nahibaloan: ang
// ── sunod niyang pagpindot mobalik ra sa login page. Ang tawo nga wala
// ── masayod sa rason mosulay pag-usab, ug mosulay pag-usab, ug moingon nga
// ── ang system ang nagka-bug. Isulti nato kaniya.
class ExplainSessionTakeover
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() && $request->hasSession()) {
            $session = $request->session();

            if (SingleDeviceSession::wasSignedOutElsewhere($session->getId())) {
                $message = 'You were signed out because this account signed in on another device.';

                // On the login page the message has to be readable right now;
                // anywhere else the guest redirect happens first, so it has to
                // survive one hop.
                $request->routeIs('login')
                    ? $session->now('notice', $message)
                    : $session->flash('notice', $message);
            }
        }

        return $next($request);
    }
}
