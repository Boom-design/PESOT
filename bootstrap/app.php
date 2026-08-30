<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Walay api: dinhi. Ang routes/api.php gitangtang — para kadto sa
        // Flutter app nga wala na, ug ang /api/register niya maghimo ug
        // employer account nga approved dayon, walay login ug walay throttle,
        // nga molaktaw sa staff approval nga gipatuman sa web.
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // ── Ang schedule naa tanan sa routes/console.php. Ayaw ni balika dinhi:
    // ── kung naa sa duha ka lugar, doble ma-register ang kada command ug doble
    // ── pud ang notification nga madawat sa employer kada adlaw. ──
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login'));

        // ── Ang employer nga gihatagan ug temporary password sa opisina dili
        // ── makaabot sa bisan unsang page hangtod mo-ilis siya. Naa sa web
        // ── group aron walay route nga malaktawan. ──
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsurePasswordChanged::class);

        // ── Ang employer nga na-disable kay wala mitubag sa pangutana sa
        // ── opisina makasulod gihapon, apan usa ra ka pahina ang iyang maabot:
        // ── didto niya isulti ang iyang rason. ──
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureEmployerActive::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();