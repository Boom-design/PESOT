@extends('admin.layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1" style="color:var(--g-700);">
            <i class="ph-fill ph-chart-bar me-2" style="color:var(--g-600);"></i>Office Overview
        </h5>
        <p class="mb-0" style="font-size:13px;color:var(--n-500);">
            What the office did between
            <strong style="color:var(--g-700);">{{ $from->format('M d, Y') }}</strong>
            and
            <strong style="color:var(--g-700);">{{ $to->format('M d, Y') }}</strong>.
        </p>
    </div>
</div>

{{-- ── THE DATES ──
     PESO, 2026-08-26: the Admin asked for these counts "for this month", and
     asked to be able to move the dates. The month is the default because it is
     the question actually asked; the two boxes are for every other question. --}}
<div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
    <form method="GET" action="{{ route('admin.reports') }}"
          class="d-flex align-items-end gap-2 flex-wrap">
        <div>
            <label class="form-label fw-semibold mb-1" style="color:var(--g-700);font-size:12px;">From</label>
            <input type="date" name="from" class="form-control form-control-sm"
                   style="border-color:var(--n-200);font-size:13px;border-radius:8px;"
                   value="{{ $from->format('Y-m-d') }}">
        </div>
        <div>
            <label class="form-label fw-semibold mb-1" style="color:var(--g-700);font-size:12px;">To</label>
            <input type="date" name="to" class="form-control form-control-sm"
                   style="border-color:var(--n-200);font-size:13px;border-radius:8px;"
                   value="{{ $to->format('Y-m-d') }}">
        </div>
        <button type="submit" class="btn btn-sm fw-semibold"
                style="background:var(--g-600);color:#fff;border:none;border-radius:8px;font-size:12px;padding:7px 18px;">
            <i class="ph ph-funnel me-1"></i>Apply
        </button>

        {{-- The shortcuts the office actually asks for, so nobody has to work
             out the first and last day of a month by hand. --}}
        @php
            $quick = [
                'This month'  => [now()->startOfMonth(), now()->endOfMonth()],
                'Last month'  => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
                'This year'   => [now()->startOfYear(), now()->endOfYear()],
            ];
        @endphp
        @foreach($quick as $label => [$qFrom, $qTo])
            @php $isOn = $from->isSameDay($qFrom) && $to->isSameDay($qTo); @endphp
            <a href="{{ route('admin.reports', ['from' => $qFrom->format('Y-m-d'), 'to' => $qTo->format('Y-m-d')]) }}"
               class="btn btn-sm fw-semibold"
               style="{{ $isOn
                   ? 'background:var(--g-600);color:#fff;border:1px solid var(--g-600);'
                   : 'background:#fff;color:var(--g-700);border:1px solid var(--n-200);' }}
                   border-radius:8px;font-size:12px;padding:6px 14px;white-space:nowrap;">
                {{ $label }}
            </a>
        @endforeach
    </form>
</div>

{{-- ── THE THREE THE OFFICE ASKED FOR ── --}}
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['Hired',                 $hired,            'ph-handshake',   'var(--g-700)',
             $hired ? $hiredLocal . ' local · ' . $hiredOverseas . ' overseas' : 'No placements in this range'],
            ['Applications',          $applied,          'ph-paper-plane-tilt', 'var(--info)',
             'Jobseekers who applied to a vacancy'],
            ['Employers registered',  $employersJoined,  'ph-buildings',   'var(--warn)',
             'New establishments on the books'],
        ];
    @endphp
    @foreach($cards as [$label, $value, $icon, $colour, $hint])
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="ph-fill {{ $icon }}" style="font-size:20px;color:{{ $colour }};"></i>
                <div style="font-size:12px;color:var(--n-500);text-transform:uppercase;letter-spacing:0.04em;">
                    {{ $label }}
                </div>
            </div>
            <div class="fw-bold" style="font-size:34px;line-height:1.1;color:{{ $colour }};">{{ $value }}</div>
            <div style="font-size:11.5px;color:var(--n-500);margin-top:4px;">{{ $hint }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- The two that come free with the same range, and that the office reads in
     the same breath as the three above. --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 d-flex flex-row align-items-center gap-3">
            <i class="ph-fill ph-user-plus" style="font-size:22px;color:var(--g-600);"></i>
            <div>
                <div class="fw-bold" style="font-size:22px;color:var(--g-700);line-height:1.1;">{{ $jobseekersJoined }}</div>
                <div style="font-size:12px;color:var(--n-500);">Jobseekers registered</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 d-flex flex-row align-items-center gap-3">
            <i class="ph-fill ph-briefcase" style="font-size:22px;color:var(--g-600);"></i>
            <div>
                <div class="fw-bold" style="font-size:22px;color:var(--g-700);line-height:1.1;">{{ $vacanciesPosted }}</div>
                <div style="font-size:12px;color:var(--n-500);">Vacancies posted</div>
            </div>
        </div>
    </div>
</div>

{{-- ── THE DESK REPORTS ──
     The overview answers "how did the office do". These answer "how is this
     desk doing", and they are where the office was already going. --}}
<div class="card border-0 shadow-sm rounded-3 p-4">
    <div class="fw-bold mb-3" style="color:var(--g-700);font-size:14px;">
        <i class="ph ph-users-three me-1" style="color:var(--g-600);"></i> Staff-level Reports
    </div>
    <div class="row g-3">
        @foreach([
            ['LRA Reports',         'Local applicants registered, placed, referred', 'ph-user-list',      route('admin.reports.staff', ['role' => 'lra'])],
            ['SRA Reports',         'Overseas applicants and agencies',              'ph-globe-hemisphere-east', route('admin.reports.staff', ['role' => 'sra'])],
            ['Job Fair Reports',    'Events, employers, attendance',                 'ph-calendar-dots',  route('admin.reports.staff', ['role' => 'job_fair'])],
            ['Job Vacancy Reports', 'Postings and requirements',                     'ph-briefcase',      route('admin.reports.staffJobVacancy')],
        ] as [$label, $hint, $icon, $url])
        <div class="col-md-3">
            <a href="{{ $url }}" class="text-decoration-none">
                <div class="card border-0 p-3 text-center h-100" style="background:var(--n-50);border-radius:12px;">
                    <i class="ph-fill {{ $icon }} mb-2" style="font-size:28px;color:var(--g-600);"></i>
                    <div class="fw-semibold" style="color:var(--g-700);font-size:13px;">{{ $label }}</div>
                    <div class="text-muted" style="font-size:11px;">{{ $hint }}</div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>

@endsection
