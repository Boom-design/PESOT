@extends('staff.layouts.app')

@section('content')

{{-- STAT CARDS

     The static quick-link cards that used to sit under the calendar were
     removed: they repeated the sidebar in a bigger shape and carried no number
     of their own. These three carry the numbers the desk works from, and each
     one opens the page its number was counted on — so the card and the list
     behind it can never disagree about what was meant. --}}
<div class="row g-3 mb-3">

    {{-- Ang bakante nga naghulat pa nga isulod sa usa ka fair. --}}
    <div class="col-md-4">
        <a href="{{ route('staff.jobfair.postings', ['invite' => 'pending']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div class="fs-2 fw-bold" style="color:var(--g-600);">{{ $pendingVacancies }}</div>
                <div class="text-muted small">Pending Job Fair Vacancies</div>
                <div class="mt-1" style="font-size:11px;color:var(--g-700);">
                    <i class="ph ph-briefcase me-1" style="color:var(--g-600);"></i>
                    Waiting to be placed on a fair
                </div>
            </div>
        </a>
    </div>

    {{-- Ang fair nga nagpadayon karon. Gi-ngalan kung usa ra: ang numero nga
         "1" wala mag-ingon kung asa ka fair. --}}
    <div class="col-md-4">
        <a href="{{ route('staff.jobfair.events', ['status' => 'ongoing']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div class="fs-2 fw-bold" style="color:var(--warn);">{{ $ongoingEvents->count() }}</div>
                <div class="text-muted small">Ongoing Job Fair Event</div>
                <div class="mt-1" style="font-size:11px;color:var(--g-700);">
                    @if($ongoingEvents->count() === 1)
                        <i class="ph ph-calendar-dots me-1" style="color:var(--g-600);"></i>
                        {{ $ongoingEvents->first()->title }}
                    @elseif($ongoingEvents->count() > 1)
                        <i class="ph ph-calendar-dots me-1" style="color:var(--g-600);"></i>
                        {{ $ongoingEvents->pluck('title')->implode(', ') }}
                    @else
                        <i class="ph ph-calendar-blank me-1" style="color:var(--n-300);"></i>
                        No fair running right now
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- Ang employer nga niapil na sa usa ka fair. --}}
    <div class="col-md-4">
        <a href="{{ route('staff.reports', ['tab' => 'top_employers']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div class="fs-2 fw-bold" style="color:var(--g-700);">{{ $participatingEmployers }}</div>
                <div class="text-muted small">Top 10 Employers</div>
                <div class="mt-1" style="font-size:11px;color:var(--g-700);">
                    <i class="ph ph-buildings me-1" style="color:var(--g-600);"></i>
                    Employers who took part in a fair
                </div>
            </div>
        </a>
    </div>
</div>

@include('partials.activity-calendar')

@endsection
