{{--
    Red count on a sidebar item that has something waiting for this user.

    Shared by all four portals so the meaning and the look never drift apart.
    The counts come from App\Support\NavAlerts and reach here as $navAlerts.

    Expects: $navKey. Optionally $navLocked (bool) — a locked item cannot be
    opened, so a dot on it would only nag.
--}}
@php $navDotCount = ($navLocked ?? false) ? 0 : ($navAlerts[$navKey] ?? 0); @endphp

@if($navDotCount > 0)
    <span class="nav-dot" title="{{ $navDotCount }} item(s) need your attention">
        {{ $navDotCount > 9 ? '9+' : $navDotCount }}
    </span>
@endif
