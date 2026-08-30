{{--
    Shared date-range filter para sa tanan nga report page (employer, staff,
    admin). PESO interview 2026-08-13: start date ug end date, dili calendar.

    Gamit:
        @include('partials.date-range-filter', [
            'range'  => $range,                       // App\Support\DateRange
            'action' => route('company.reports'),
            'keep'   => ['view' => $activeView],      // dili mawala nga query params
            'exports' => [                            // opsyonal
                ['url' => route('...'), 'label' => 'Download Excel', 'icon' => 'ph-download-simple'],
            ],
            'years' => true,                          // ipakita ang Year dropdown
        ])
--}}
@php
    $keep    = $keep ?? [];
    $exports = $exports ?? [];
    $years   = $years ?? false;

    // Ang gipili nga tuig, kung ang range mao gyud ang tibuok tuig. Kung ang
    // tawo nag-type ug piho nga petsa, ang dropdown mobalik sa "All years" —
    // ang gi-type ang naghari, ug ang dropdown dili magpakaaron-ingnon.
    $selectedYear = $years ? $range->wholeYear() : null;
    $yearOptions = range(now()->year, now()->year - 5);

    // A year reached from a bookmark or a typed URL keeps its place in the
    // list; without this the dropdown would quietly read "All years" while the
    // page below showed that year's rows.
    if ($selectedYear && !in_array($selectedYear, $yearOptions, true)) {
        $yearOptions[] = $selectedYear;
        rsort($yearOptions);
    }
@endphp

<form method="GET" action="{{ $action }}"
      class="d-flex flex-wrap align-items-end gap-2 mb-3">
    @foreach($keep as $keepName => $keepValue)
        <input type="hidden" name="{{ $keepName }}" value="{{ $keepValue }}">
    @endforeach

    @if($years)
    <div>
        <label class="form-label fw-semibold mb-1" style="font-size:11px;color:var(--n-500);">Year</label>
        {{-- Picking a year clears the two date boxes: the year and a typed range
             are two answers to one question, and the server would ignore the
             year anyway. --}}
        <select name="year" class="form-select form-select-sm"
                onchange="this.form.from.value=''; this.form.to.value=''; this.form.submit();"
                style="font-size:12.5px;border-radius:8px;border-color:var(--n-200);min-width:120px;">
            <option value="">All years</option>
            @foreach($yearOptions as $yearOption)
                <option value="{{ $yearOption }}" @selected($selectedYear === $yearOption)>{{ $yearOption }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div>
        <label class="form-label fw-semibold mb-1" style="font-size:11px;color:var(--n-500);">From</label>
        <input type="date" name="from" value="{{ $range->from?->format('Y-m-d') }}"
               class="form-control form-control-sm"
               style="font-size:12.5px;border-radius:8px;border-color:var(--n-200);min-width:150px;">
    </div>
    <div>
        <label class="form-label fw-semibold mb-1" style="font-size:11px;color:var(--n-500);">To</label>
        <input type="date" name="to" value="{{ $range->to?->format('Y-m-d') }}"
               class="form-control form-control-sm"
               style="font-size:12.5px;border-radius:8px;border-color:var(--n-200);min-width:150px;">
    </div>

    <button type="submit" class="btn btn-sm fw-semibold"
            style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:6px 14px;">
        <i class="ph ph-funnel me-1"></i>Apply
    </button>

    @if($range->isActive())
        <a href="{{ $action }}{{ count($keep) ? '?' . http_build_query($keep) : '' }}"
           class="btn btn-sm fw-semibold"
           style="background:#fff;color:var(--g-700);border:1px solid var(--n-200);border-radius:8px;font-size:12px;padding:6px 14px;">
            <i class="ph ph-x me-1"></i>Clear
        </a>
    @endif

    @foreach($exports as $export)
        <a href="{{ $export['url'] }}"
           @if($export['newTab'] ?? false) target="_blank" @endif
           class="btn btn-sm fw-semibold"
           style="background:#fff;color:var(--g-700);border:1px solid var(--n-200);border-radius:8px;font-size:12px;padding:6px 14px;">
            <i class="ph {{ $export['icon'] ?? 'ph-download-simple' }} me-1"></i>{{ $export['label'] }}
        </a>
    @endforeach

    <div class="ms-auto" style="font-size:11.5px;color:var(--n-500);align-self:center;">
        Showing: <strong style="color:var(--g-700);">{{ $range->label() }}</strong>
    </div>
</form>
