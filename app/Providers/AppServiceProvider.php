<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── THE STYLESHEET HAS TO REACH PEOPLE WHO HAVE BEEN HERE BEFORE ──
        //
        // PESO, 2026-10-24: peso.css was linked by a plain path, so a
        // browser that had the file kept the file. A change to it reached
        // nobody who had already visited — which is everybody who matters —
        // until they cleared their cache or pressed Ctrl+F5, and neither is
        // something a jobseeker on a phone is going to do.
        //
        // The file's own modified time is the version. It changes only when
        // the file does, so the cache still works; it simply stops lying
        // about which file it holds.
        View::share('pesoCssVersion', self::assetVersion('css/peso.css'));
    }

    /** A cache key for a public asset: its modified time, or nothing. */
    private static function assetVersion(string $path): string
    {
        $full = public_path($path);

        return is_file($full) ? (string) filemtime($full) : '';
    }
}
