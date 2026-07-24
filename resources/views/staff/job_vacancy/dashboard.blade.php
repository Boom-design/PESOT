@extends('staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-briefcase-fill me-2" style="color:#4dd9c0;"></i>Job Vacancy Dashboard
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Manage employer requirements and job vacancies</p>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
            <div class="fs-1 fw-bold" style="color:#f59e0b;">{{ $pendingCount }}</div>
            <div class="text-muted small">Pending Requirements</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
            <div class="fs-1 fw-bold" style="color:#2d7a5f;">{{ $approvedCount }}</div>
            <div class="text-muted small">Approved Requirements</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 p-4 text-center">
            <div class="fs-1 fw-bold" style="color:#e05252;">{{ $rejectedCount }}</div>
            <div class="text-muted small">Rejected Requirements</div>
        </div>
    </div>
</div>

{{-- TODAY'S ACTIVITIES --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header border-0 py-3 px-4"
                style="background:linear-gradient(90deg,#90d870,#4dd9c0);border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-calendar-event-fill me-2"></i>Job Fair Activities
                </h6>
            </div>
            <div class="card-body p-3">
                @forelse($todayJobFair as $event)
                <div class="d-flex align-items-start gap-3 mb-3 pb-3"
                    style="border-bottom:1px solid #f0f9f6;">
                    <div style="width:40px;height:40px;
                                background:linear-gradient(135deg,#90d870,#4dd9c0);
                                border-radius:10px;display:flex;align-items:center;
                                justify-content:center;flex-shrink:0;">
                        <i class="bi bi-calendar2-event text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="color:#2d7a5f;font-size:13px;">{{ $event->title }}</div>
                        <div style="font-size:11px;color:#888;">
                            <i class="bi bi-geo-alt me-1"></i>{{ $event->venue }}
                        </div>
                        <div style="font-size:11px;color:#888;">
                            <i class="bi bi-calendar me-1"></i>{{ $event->event_date->format('M d, Y') }}
                        </div>
                        <span style="font-size:11px;font-weight:600;
                            color:{{ $event->status === 'ongoing' ? '#f59e0b' : '#4dd9c0' }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-4" style="color:#aaa;font-size:13px;">
                    <i class="bi bi-calendar-x" style="font-size:32px;color:#c0e8dc;"></i>
                    <div class="mt-2">No upcoming job fair events</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header border-0 py-3 px-4"
                style="background:linear-gradient(90deg,#90d870,#4dd9c0);border-radius:12px 12px 0 0;">
                <h6 class="mb-0 fw-bold text-white">
                    <i class="bi bi-calendar-check-fill me-2"></i>In-house Activities Today
                </h6>
            </div>
            <div class="card-body p-3">
                @forelse($todayInhouse as $schedule)
                <div class="d-flex align-items-start gap-3 mb-3 pb-3"
                    style="border-bottom:1px solid #f0f9f6;">
                    <div style="width:40px;height:40px;
                                background:linear-gradient(135deg,#90d870,#4dd9c0);
                                border-radius:10px;display:flex;align-items:center;
                                justify-content:center;flex-shrink:0;">
                        <i class="bi bi-building text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="color:#2d7a5f;font-size:13px;">
                            {{ $schedule->employer->company_name ?? '—' }}
                        </div>
                        <div style="font-size:11px;color:#888;">
                            <i class="bi bi-clock me-1"></i>
                            {{ \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') }}
                        </div>
                        <div style="font-size:11px;color:#888;">
                            <i class="bi bi-people me-1"></i>
                            {{ $schedule->num_applicants }} applicants
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4" style="color:#aaa;font-size:13px;">
                    <i class="bi bi-calendar-x" style="font-size:32px;color:#c0e8dc;"></i>
                    <div class="mt-2">No in-house schedules today</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- QUICK LINKS --}}
<div class="row g-3">
    <div class="col-6 col-md-4">
        <a href="{{ route('staff.employers') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-building"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">Employer Requirements</div>
                <div class="text-muted small mt-1">Review and manage requirements</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('staff.jobs') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">Job Vacancies</div>
                <div class="text-muted small mt-1">Manage job postings</div>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-4">
        <a href="{{ route('staff.profile') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">My Profile</div>
                <div class="text-muted small mt-1">Manage your account</div>
            </div>
        </a>
    </div>
</div>

@endsection