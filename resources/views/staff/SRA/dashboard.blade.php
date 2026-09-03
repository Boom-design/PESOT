@extends('staff.layouts.app')

@section('content')

{{-- STAT CARDS — each one is also the way in.

     The static quick-link cards that used to sit under the calendar were
     removed: they repeated the sidebar in a bigger shape and carried no number
     of their own. A card that states a count and opens the list behind that
     count says both things at once.

     Every card lands on the page that holds the rows it counted, so the number
     pressed and the number arrived at are the same number. --}}
@php
    // One shape for all six, so a card cannot drift from its neighbours.
    $sraCard = 'transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;';
    $sraHoverIn  = "this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'";
    $sraHoverOut = "this.style.transform='translateY(0)';this.style.boxShadow=''";

    $sraCards = [
        [
            'value' => $pendingInhouse,
            'label' => 'Pending In-house Schedule',
            'hint'  => 'Open the requests waiting on you',
            'icon'  => 'ph-fill ph-calendar-check',
            'url'   => route('staff.inhouse'),
        ],
        [
            'value' => $ongoingActivities,
            'label' => 'On-going Job Activities',
            'hint'  => 'Open In-house Job Vacancy',
            'icon'  => 'ph-fill ph-briefcase',
            'url'   => route('staff.jobs', ['type' => 'inhouse']),
        ],
        [
            'value' => $pendingCompanyInterview,
            'label' => 'Pending Company Interview',
            'hint'  => 'Open the interviews still to come',
            'icon'  => 'ph-fill ph-hourglass-medium',
            'url'   => route('staff.jobs', ['type' => 'company_interview_pending']),
        ],
        [
            'value' => $totalJobsSolicited,
            'label' => 'Total Jobs',
            'hint'  => 'Open Job Vacancies Solicited',
            'icon'  => 'ph-fill ph-clipboard-text',
            // vacancy_month=all: this card counts every month, and the report
            // opens on one month unless it is told otherwise.
            'url'   => route('staff.reports', ['tab' => 'vacancies', 'vacancy_month' => 'all']),
        ],
        [
            'value' => $overseasEmployerTotal,
            'label' => 'Total Overseas Employer',
            'hint'  => 'Open Employers',
            'icon'  => 'ph-fill ph-buildings',
            'url'   => route('staff.employers', ['tab' => 'approved']),
        ],
        [
            'value' => $jobseekerTotal,
            'label' => 'Total Jobseeker (Overseas)',
            'hint'  => 'Open NSRP registrations',
            'icon'  => 'ph ph-user-list',
            'url'   => route('staff.registrations'),
        ],
    ];
@endphp

<div class="row g-3 mb-3">
    @foreach($sraCards as $card)
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ $card['url'] }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="{{ $sraCard }}"
                onmouseover="{{ $sraHoverIn }}"
                onmouseout="{{ $sraHoverOut }}">
                <div class="fs-2 fw-bold" style="color:var(--g-600);">{{ $card['value'] }}</div>
                <div class="text-muted small">{{ $card['label'] }}</div>
                <div class="mt-1" style="font-size:11px;color:var(--g-700);">
                    <i class="{{ $card['icon'] }} me-1"></i>{{ $card['hint'] }}
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

@include('partials.activity-calendar')

@endsection
