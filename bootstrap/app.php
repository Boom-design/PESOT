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

        // ── Ang naka-login na nga mipindot ug Register.
        // ──
        // ── PESO, 2026-10-23: "pag mo register sila ug e select ang jobseeker
        // ── e balik sila sa login". Dili bug sa ilang laptop — buhi pa ang
        // ── ilang session gikan sa nauna nga pagsulay, ug ang `guest` nga
        // ── middleware nag-abog sa naka-login. Ang gipadad-an mao ang
        // ── default sa Laravel: wala tay route nga ginganlan ug `dashboard`
        // ── o `home`, mao nga `/` — nga mao gyuy landing page nga nagdala sa
        // ── login form. Ang nakita nila, login page, ug walay nagsulti ngano.
        // ──
        // ── Ipadala sila sa ILANG kaugalingon nga panid, ug sultihi sila.
        // ── Ang usa ka tawo nga dili makasabot ngano gi-abog siya mosulay
        // ── pag-usab, ug pag-usab. ──
        \Illuminate\Auth\Middleware\RedirectIfAuthenticated::redirectUsing(function () {
            $user = \Illuminate\Support\Facades\Auth::user();

            session()->flash('notice',
                'You are already signed in'
                . ($user?->name ? ' as ' . $user->name : '')
                . '. Log out first if you want to register another account.');

            return match ($user?->role) {
                'admin'     => route('admin.dashboard'),
                'company'   => route('company.dashboard'),
                'staff'     => route('staff.dashboard'),
                'jobseeker' => route('jobseeker.dashboard'),
                default     => route('login'),
            };
        });

        // ── Ang employer nga gihatagan ug temporary password sa opisina dili
        // ── makaabot sa bisan unsang page hangtod mo-ilis siya. Naa sa web
        // ── group aron walay route nga malaktawan. ──
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsurePasswordChanged::class);

        // ── Ang employer nga na-disable kay wala mitubag sa pangutana sa
        // ── opisina makasulod gihapon, apan usa ra ka pahina ang iyang maabot:
        // ── didto niya isulti ang iyang rason. ──
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureEmployerActive::class);

        // ── Usa ra ka device kada admin ug staff account. Kung ang iPhone
        // ── mosulod sa samang account, ang laptop mapahawa — ug kini ang
        // ── mosulti kaniya ngano. ──
        $middleware->appendToGroup('web', \App\Http\Middleware\ExplainSessionTakeover::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();