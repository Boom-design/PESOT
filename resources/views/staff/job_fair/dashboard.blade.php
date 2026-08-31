@extends('staff.layouts.app')

@section('content')

{{-- STAT CARD --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
            <div class="fs-2 fw-bold" style="color:var(--g-600);">{{ $totalSent }}</div>
            <div class="text-muted small">Total Notifications Sent</div>
        </div>
    </div>
</div>

@include('partials.activity-calendar')

{{-- QUICK LINKS --}}
<div class="row g-3">
    <div class="col-6 col-md-4">
        <a href="{{ route('staff.jobfair.events') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:44px;height:44px;
                            background:var(--g-600);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="ph-fill ph-calendar-dots"></i>
                </div>
                <div class="fw-bold" style="color:var(--g-700);font-size:14px;">Job Fair Events</div>
                <div class="text-muted small mt-1">Manage events and venues</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('staff.profile') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:44px;height:44px;
                            background:var(--g-600);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="ph ph-user-circle"></i>
                </div>
                <div class="fw-bold" style="color:var(--g-700);font-size:14px;">My Profile</div>
                <div class="text-muted small mt-1">Manage your account</div>
            </div>
        </a>
    </div>
</div>


@endsection