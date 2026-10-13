{{--
    The search box on the reports page.

    It sat on the tab row, a line above the filter it narrows, and the two read
    as separate controls doing unrelated things. They ask the same question —
    which of these rows do I want — so they share a line, with the search at the
    far end.

    Written once because the tabs draw that line in different places: the
    registered tab has its own view selector to sit beside, and the rest put it
    back on the tab row where there is nothing else.
--}}
<div class="input-group" style="max-width:260px;width:100%;">
    <span class="input-group-text" style="border-color:var(--n-200);background:var(--n-50);">
        <i class="ph ph-magnifying-glass" style="color:var(--g-600);"></i>
    </span>
    <input type="text" id="searchInput" class="form-control"
        placeholder="Search name or email..."
        style="border-color:var(--n-200);font-size:13px;"
        value="{{ request('search') }}">
</div>
