{{--
    Tab title and tab icon — one PESO identity for the whole system.

    Every page that opens its own <head> includes this: the admin, staff,
    employer and jobseeker layouts, the auth screens, and the public landing
    and job list. Everything else extends one of those layouts, so the profile
    pages, the notification-bell targets and every inner tab inherit it.

    $pageTitle is only passed by the printable report, whose title becomes the
    filename when the staff saves it as a PDF for the Mayor's Office and DOLE.
--}}
<title>{{ $pageTitle ?? 'PESO' }}</title>
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/peso_favicon.png') }}">
<link rel="apple-touch-icon" href="{{ asset('images/peso_logo.png') }}">
