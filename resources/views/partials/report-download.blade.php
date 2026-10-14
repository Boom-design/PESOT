{{--
    The download on a report tab.

    PESO, 2026-10-14: three of the eight tabs had one and five did not, so a
    desk building a paper copy read the other five off the screen and typed
    them out again. Every tab that has rows now has the button.

    It carries the whole query string: the date range, the search, the status
    filter, the month on Top Employers. The file is what is on the screen —
    a download that quietly gives more than the page showed is a download
    nobody can check.

    `tab` is passed explicitly because the exporter defaults to Placed when it
    does not recognise one, and the registered tab arrives with no `tab` in the
    URL at all.
--}}
@php
    $downloadTab   = request('tab', 'registered');
    $downloadRoute = ($reportRouteName ?? 'staff.reports') === 'admin.reports.staff'
        ? 'staff.reports.export'
        : 'staff.reports.export';

    // The admin's copy says which desk it is reading; the desk's own page is
    // already that desk.
    $downloadQuery = array_merge(request()->query(), ['tab' => $downloadTab]);
@endphp

<a href="{{ route($downloadRoute, $downloadQuery) }}"
   class="btn btn-sm fw-semibold"
   style="background:#fff;color:var(--g-700);border:1px solid var(--n-200);
          border-radius:8px;font-size:12px;padding:5px 14px;white-space:nowrap;">
    <i class="ph ph-download-simple me-1"></i>Download Excel
</a>
