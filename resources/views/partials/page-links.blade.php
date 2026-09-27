{{--
    The numbered part of a pager — windowed, so it never outgrows its row.

    PESO SRA, 2026-09-14: Archived Job Postings had 27 pages and the pager drew
    all 27 numbers in one line. The row ran past the card and page 27 could not
    be seen at all. A row of every page number only works while there are few
    pages; this draws the first page, the last page, the pages beside the one
    being read, and "…" for the gaps:

        1 … 13 14 15 … 27

    So the row stays the same short width at page 2 and at page 200, with no
    scrollbar.

    Expects:
      pager       — the LengthAwarePaginator (query string already carried)
      activeStyle — inline style of the current page's link
      idleStyle   — inline style of every other page's link
      side        — optional, pages shown each side of the current one (default 1)
      plain       — optional; true for a pager whose own stylesheet styles bare
                    <li>/<a> (the employer Reports page). No classes or styles
                    are added, so the page's CSS decides the look.

    Draws <li> items only; the caller keeps its own <ul>, previous and next.
--}}
@php
    $last    = $pager->lastPage();
    $current = $pager->currentPage();
    $side    = $side ?? 1;
    $plain   = $plain ?? false;

    // Always the first and the last page, and a small window round the current
    // one. A gap of exactly one page is shown as that page, not as "…".
    $shown = collect([1, $last])
        ->merge(range(max(1, $current - $side), min($last, $current + $side)))
        ->unique()
        ->sort()
        ->values();

    $items = [];
    $previous = null;
    foreach ($shown as $number) {
        if ($previous !== null && $number - $previous === 2) {
            $items[] = $previous + 1;
        } elseif ($previous !== null && $number - $previous > 2) {
            $items[] = null; // gap
        }
        $items[] = $number;
        $previous = $number;
    }
@endphp

@foreach($items as $number)
    @if($plain)
        @if($number === null)
        <li class="disabled"><span>…</span></li>
        @else
        <li class="{{ $number === $current ? 'active' : '' }}"><a href="{{ $pager->url($number) }}">{{ $number }}</a></li>
        @endif
    @elseif($number === null)
    <li class="page-item disabled">
        <span class="page-link rounded-2" style="border-color:transparent;background:transparent;color:var(--n-400);">…</span>
    </li>
    @else
    <li class="page-item {{ $number === $current ? 'active' : '' }}">
        <a class="page-link rounded-2"
           style="{{ $number === $current ? $activeStyle : $idleStyle }}"
           href="{{ $pager->url($number) }}">{{ $number }}</a>
    </li>
    @endif
@endforeach
