@extends('staff.layouts.app')

@section('content')

{{-- STAT CARDS — each one is also the way in.

     The static quick-link cards that used to sit under the calendar were
     removed: they repeated the sidebar in a bigger shape and carried no number
     of their own. A card that states a count and opens the list behind that
     count says both things at once. --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <a href="{{ route('staff.employers', ['tab' => 'approved']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div class="fs-2 fw-bold" style="color:var(--g-600);">{{ $employerTotal }}</div>
                <div class="text-muted small">Total Local Employer Registered</div>
                <div class="mt-1" style="font-size:11px;color:var(--g-700);">
                    <i class="ph ph-buildings me-1"></i> View approved employers
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('staff.registrations') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div class="fs-2 fw-bold" style="color:var(--g-600);">{{ $jobseekerTotal }}</div>
                <div class="text-muted small">Total Local Jobseeker</div>
                <div class="mt-1" style="font-size:11px;color:var(--g-700);">
                    <i class="ph ph-user-list me-1"></i> View NSRP registrations
                </div>
            </div>
        </a>
    </div>
</div>

@include('partials.activity-calendar')

@endsection
