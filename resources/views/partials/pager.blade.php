{{--
    The pill pager — one partial for every paginated list.

    Laravel's own $paginator->links() renders Tailwind markup, and this project
    is Bootstrap, so those links came out unstyled: bare blue text in a row.
    The vacancy list already had a hand-written pill; this is that same pill,
    lifted out so the next list does not have to copy it again.

    Expects:
      pager      — the LengthAwarePaginator.
      pagerLabel — optional word before the numbers. Defaults to 'Page'.

    The query string is carried by the paginator itself, so callers must have
    already called ->withQueryString() (or ->appends()) on it. Nothing is
    appended here by hand: a pager that rebuilds the query string is a pager
    that quietly drops a filter somebody set.
--}}
@php
    $pagerLabel = $pagerLabel ?? 'Page';
@endphp

@if($pager->hasPages())
<div class="d-flex justify-content-center px-3 py-3">
    <div class="d-inline-flex align-items-center gap-2 px-2 py-1"
        style="background:#fff;border:1px solid var(--n-200);border-radius:30px;box-shadow:0 4px 14px rgba(0,0,0,0.06);">

        @if($pager->onFirstPage())
            <span class="d-flex align-items-center justify-content-center"
                style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--n-300);">
                <i class="ph ph-caret-left"></i>
            </span>
        @else
            <a href="{{ $pager->previousPageUrl() }}"
               class="d-flex align-items-center justify-content-center"
               style="width:34px;height:34px;border-radius:50%;border:1px solid var(--n-200);color:var(--g-700);text-decoration:none;">
                <i class="ph ph-caret-left"></i>
            </a>
        @endif

        <span class="fw-semibold px-2" style="font-size:13px;color:var(--g-700);white-space:nowrap;">
            {{ $pagerLabel }} {{ $pager->currentPage() }} of {{ $pager->lastPage() }}
        </span>

        @if($pager->hasMorePages())
            <a href="{{ $pager->nextPageUrl() }}"
               class="d-flex align-items-center gap-1 fw-semibold text-decoration-none"
               style="background:var(--g-600);color:#fff;border-radius:20px;padding:8px 18px;font-size:13px;">
                Next <i class="ph ph-caret-right"></i>
            </a>
        @else
            <span class="d-flex align-items-center gap-1 fw-semibold"
                style="background:var(--n-200);color:var(--n-400);border-radius:20px;padding:8px 18px;font-size:13px;">
                Next <i class="ph ph-caret-right"></i>
            </span>
        @endif
    </div>
</div>
@endif
