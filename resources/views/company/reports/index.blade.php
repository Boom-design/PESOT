@extends('company.layouts.app')

@section('page-title', 'Reports')

@section('content')

@php
    $activeView = request('view', 'hired');
@endphp

<div class="peso-page-head">
    <h1 class="peso-page-title">Reports</h1>
    <p class="peso-page-sub">Job postings with hired jobseekers.</p>
</div>

{{-- TABS --}}
<div class="peso-tabs">
    <a href="{{ route('company.reports', ['view' => 'hired']) }}"
       class="peso-tab {{ $activeView === 'hired' ? 'active' : '' }}">
        <i class="{{ $activeView === 'hired' ? 'ph-fill' : 'ph' }} ph-user-list"></i> Hired Applicants
    </a>
    <a href="{{ route('company.reports', ['view' => 'archived']) }}"
       class="peso-tab {{ $activeView === 'archived' ? 'active' : '' }}">
        <i class="{{ $activeView === 'archived' ? 'ph-fill' : 'ph' }} ph-archive"></i> Archived Job Postings
    </a>
</div>

{{-- ── DATE RANGE + EXPORT — sa Hired Applicants ra.
     Ang Archived Job Postings kay rekord sa posting mismo, dili ihap sa tawo
     kada bulan, mao nga walay filter ug walay export didto. ── --}}
@if($activeView === 'hired')
@include('partials.date-range-filter', [
    'range'   => $range,
    'years'   => true,
    'action'  => route('company.reports'),
    'keep'    => array_filter(['view' => $activeView, 'search' => request('search')]),
    'exports' => [
        [
            'url'   => route('company.reports.export', array_merge($range->queryParams(), ['view' => $activeView, 'search' => request('search')])),
            'label' => 'Download Excel',
            'icon'  => 'ph-download-simple',
        ],
        [
            'url'    => route('company.reports.export', array_merge($range->queryParams(), ['view' => $activeView, 'search' => request('search'), 'format' => 'print'])),
            'label'  => 'Print',
            'icon'   => 'ph-printer',
            'newTab' => true,
        ],
    ],
])
@endif

@if($activeView === 'archived')

    {{-- ── ARCHIVED JOB POSTINGS — nahuman na nga posting: milabay ang deadline o napuno ang slots ── --}}
    <p class="t-caption mb-3">
        Closed job postings — the deadline passed, every slot was filled, or PESO did not approve it.
        This is the record of the posting itself; who you hired is under
        <a href="{{ route('company.reports', ['view' => 'hired']) }}" style="color:var(--g-600);">Hired Applicants</a>.
    </p>

        <div class="peso-card" style="overflow:hidden;">
            <div class="table-responsive">
                <table class="peso-table">
                    <thead>
                        <tr>
                            <th>Job Position</th>
                            <th>Schedule Type</th>
                            <th>Posted Month</th>
                            <th>Status</th>
                            <th>Closed Because</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archivedJobs as $archived)
                        @php
                            $typeLabel = match($archived->schedule_type) {
                                'job_fair' => ['Job Fair', 'is-success'],
                                'inhouse'  => ['In-house', 'is-warn'],
                                default    => ['Company Interview', 'is-info'],
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="cell-strong">{{ $archived->title }}</div>
                                <div class="t-caption"><i class="ph ph-map-pin"></i> {{ $archived->location ?? 'None' }}</div>
                            </td>
                            <td><span class="badge-peso {{ $typeLabel[1] }}">{{ $typeLabel[0] }}</span></td>
                            <td>{{ $archived->created_at->format('F Y') }}</td>
                            <td>@include('partials.job-status-badge', ['job' => $archived])</td>
                            <td>
                                {{-- Parehas nga tulo ka kondisyon sa Job::lifecycle_status,
                                     gi-sulti sa pinulongan sa employer. --}}
                                @switch($archived->lifecycle_status)
                                    @case('filled')
                                        Slots filled
                                        <div class="t-caption">
                                            {{ $archived->group_hired }} of {{ $archived->slots }}
                                            @if($archived->group_external_hires > 0)
                                                — {{ $archived->group_peso_hires }} through PESO,
                                                {{ $archived->group_external_hires }} outside
                                            @endif
                                        </div>
                                        @break
                                    @case('expired')
                                        Deadline passed
                                        <div class="t-caption">{{ $archived->deadline?->format('M j, Y') ?? 'None' }}</div>
                                        @break
                                    @case('rejected')
                                        Not approved by PESO
                                        <div class="t-caption">{{ $archived->remarks ?? 'No reason was given.' }}</div>
                                        @break
                                    @default
                                        Closed by PESO staff
                                @endswitch
                            </td>
                            <td class="cell-num">
                                <a href="{{ route('company.jobs.details', $archived->job_qualifications_id) }}" class="btn-peso-outline btn-sm">
                                    <i class="ph ph-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                             gihapon ug ang pahina dili mag-usab ug porma. --}}
                        <tr>
                            <td colspan="6" class="text-center"
                                style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                <i class="ph ph-archive me-1"
                                   style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                No archived postings yet
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($archivedJobs->hasPages())
            <div class="peso-pagination">
                <div class="peso-pagination-info">
                    Showing {{ $archivedJobs->firstItem() }}–{{ $archivedJobs->lastItem() }} of {{ $archivedJobs->total() }} results
                </div>
                <nav>
                    <ul>
                        <li class="{{ $archivedJobs->onFirstPage() ? 'disabled' : '' }}">
                            <a href="{{ $archivedJobs->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($archivedJobs->getUrlRange(1, $archivedJobs->lastPage()) as $page => $url)
                        <li class="{{ $page == $archivedJobs->currentPage() ? 'active' : '' }}">
                            <a href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="{{ !$archivedJobs->hasMorePages() ? 'disabled' : '' }}">
                            <a href="{{ $archivedJobs->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

@else

    {{-- Klaruhon dayon nga PESO placements ra ang naa dinhi. Ang gi-hire nga
         wala miagi sa PESO mo-hurot ug slot, apan dili siya trabaho sa opisina
         ug dili siya maapil sa numero nga isumite sa Mayor's Office ug DOLE. --}}
    @if(($outsideHiresTotal ?? 0) > 0)
        <div class="d-flex align-items-start gap-2 p-3 mb-3 rounded-3"
             style="background:var(--n-50);border:1px solid var(--n-200);">
            <i class="ph ph-info" style="color:var(--g-600);font-size:17px;margin-top:1px;"></i>
            <div style="font-size:12px;color:var(--n-700);line-height:1.6;">
                This page counts <strong>hires made through PESO</strong> only —
                {{ $pesoHiresTotal ?? 0 }} so far.
                You also recorded <strong>{{ $outsideHiresTotal }}</strong> hire(s) made outside PESO;
                those filled your slots but are not counted as PESO placements.
                They are listed under
                <a href="{{ route('company.reports', ['view' => 'archived']) }}" style="color:var(--g-600);">Archived Job Postings</a>.
            </div>
        </div>
    @endif

    @include('partials.bar-chart', [
        'title' => 'Hired applicants per month',
        'rows'  => $hiredChart,
    ])

    <form method="GET" action="{{ route('company.reports') }}" class="mb-3 d-flex justify-content-end gap-2">
        <input type="hidden" name="view" value="hired">
        {{-- Ang date range dili mawala kung mangita ang employer. --}}
        @foreach($range->queryParams() as $rangeKey => $rangeValue)
            <input type="hidden" name="{{ $rangeKey }}" value="{{ $rangeValue }}">
        @endforeach
        <div class="peso-search">
            <span class="peso-search-icon"><i class="ph ph-magnifying-glass"></i></span>
            <input type="text" name="search" placeholder="Search job position..." value="{{ $search }}">
        </div>
        <button type="submit" class="btn-peso btn-sm">Search</button>
        @if($search)
            <a href="{{ route('company.reports', array_merge(['view' => 'hired'], $range->queryParams())) }}" class="btn-peso-outline btn-sm">Clear</a>
        @endif
    </form>

        <div class="peso-card" style="overflow:hidden;">
            <div class="table-responsive">
                <table class="peso-table">
                    <thead>
                        <tr>
                            <th>Job Position</th>
                            <th>Schedule Type</th>
                            <th>Date Posted</th>
                            <th style="text-align:center;">Hired</th>
                            <th style="text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                        @php
                            $typeLabel = match($job->schedule_type) {
                                'job_fair' => ['Job Fair', 'is-success'],
                                'inhouse'  => ['In-house', 'is-warn'],
                                default    => ['Company Interview', 'is-info'],
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="cell-strong">{{ $job->title }}</div>
                                <div class="t-caption"><i class="ph ph-map-pin"></i> {{ $job->location ?? 'None' }}</div>
                            </td>
                            <td>
                                <span class="badge-peso {{ $typeLabel[1] }}">{{ $typeLabel[0] }}</span>
                            </td>
                            {{-- The day the posting went up. Without it two
                                 rows with the same title and the same count are
                                 impossible to tell apart. --}}
                            <td>{{ $job->created_at?->format('F d, Y') ?? 'Not recorded' }}</td>
                            <td class="cell-num cell-strong">{{ $job->hired_count }} / {{ $job->slots }}</td>
                            <td class="cell-num">
                                <a href="{{ route('company.reports.show', $job->job_qualifications_id) }}" class="btn-peso-outline btn-sm">
                                    <i class="ph ph-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        {{-- Ang lamesa nagpabilin bisan walay laray, aron ang kolum makita
                             gihapon ug ang pahina dili mag-usab ug porma. --}}
                        <tr>
                            <td colspan="5" class="text-center"
                                style="padding:26px 16px;color:var(--n-500);font-size:13px;">
                                <i class="ph ph-user-list me-1"
                                   style="color:var(--n-200);font-size:18px;vertical-align:-3px;"></i>
                                Job postings with hired jobseekers will appear here.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($jobs->hasPages())
            <div class="peso-pagination">
                <div class="peso-pagination-info">
                    Showing {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }} of {{ $jobs->total() }} results
                </div>
                <nav>
                    <ul>
                        <li class="{{ $jobs->onFirstPage() ? 'disabled' : '' }}">
                            <a href="{{ $jobs->previousPageUrl() }}"><i class="ph ph-caret-left"></i></a>
                        </li>
                        @foreach($jobs->getUrlRange(1, $jobs->lastPage()) as $page => $url)
                        <li class="{{ $page == $jobs->currentPage() ? 'active' : '' }}">
                            <a href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="{{ !$jobs->hasMorePages() ? 'disabled' : '' }}">
                            <a href="{{ $jobs->nextPageUrl() }}"><i class="ph ph-caret-right"></i></a>
                        </li>
                    </ul>
                </nav>
            </div>
            @endif
        </div>

@endif

@endsection
