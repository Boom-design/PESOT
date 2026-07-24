@extends('admin.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-speedometer2 me-2" style="color:#4dd9c0;"></i>Admin Dashboard
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">Public Employment Service Office — Overview</p>
</div>

{{-- QUICK ACTIONS (unified — count + link) --}}
<h6 class="fw-semibold mb-3" style="color:#2d7a5f;">Quick Actions</h6>
<div class="row g-3 mb-4 align-items-stretch">
    <div class="col-6 col-md-3 d-flex">
        <a href="{{ route('admin.users.manage') }}" class="text-decoration-none w-100">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">Total Users</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ $totalUsers }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 d-flex">
        <a href="{{ route('admin.registrations') }}" class="text-decoration-none w-100">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">Jobseekers</div>
                <div class="fs-4 fw-bold mt-1" style="color:#90d870;">{{ $jobseekerCount }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 d-flex">
        <a href="{{ route('admin.users.manage') }}" class="text-decoration-none w-100">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-building-fill"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">Companies</div>
                <div class="fs-4 fw-bold mt-1" style="color:#2d7a5f;">{{ $companyCount }}</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3 d-flex">
        <a href="{{ route('admin.job.activities', ['tab' => 'jobfair']) }}" class="text-decoration-none w-100">
            <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100 d-flex flex-column justify-content-center"
                style="transition:transform 0.2s,box-shadow 0.2s;cursor:pointer;"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(77,217,192,0.2)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow=''">
                <div style="width:56px;height:56px;
                            background:linear-gradient(135deg,#90d870,#4dd9c0);
                            border-radius:50%;display:flex;align-items:center;
                            justify-content:center;margin:0 auto 12px;
                            font-size:24px;color:#fff;">
                    <i class="bi bi-calendar2-event-fill"></i>
                </div>
                <div class="fw-bold" style="color:#2d7a5f;font-size:14px;">Job Fair Events</div>
                <div class="fs-4 fw-bold mt-1" style="color:#4dd9c0;">{{ $monthlyJobFairCount }}</div>
                <div class="text-muted small mt-1">for month of {{ now()->format('F') }}</div>
            </div>
        </a>
    </div>
</div>

{{-- TODAY'S ACTIVITIES --}}
<h6 class="fw-semibold mb-3 mt-4" style="color:#2d7a5f;">
    <i class="bi bi-calendar-check me-2" style="color:#4dd9c0;"></i>Today's Activities
</h6>
<div class="row g-3">

    {{-- Job Fair Events --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="fw-semibold mb-2" style="font-size:13px;color:#2d7a5f;">
                <i class="bi bi-people-fill me-1" style="color:#4dd9c0;"></i> Job Fair Events
            </div>
            @if($todayJobFairs->isEmpty())
                <div class="text-muted" style="font-size:13px;padding:8px 0;">
                    No job fair events today.
                </div>
            @else
                @foreach($todayJobFairs as $event)
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2"
                     style="background:#f0f9f6;border:1px solid #a8e6cf;">
                    <i class="bi bi-calendar-event" style="color:#4dd9c0;font-size:20px;"></i>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#2d7a5f;">{{ $event->title }}</div>
                        <div style="font-size:12px;color:#888;">
                            <i class="bi bi-geo-alt me-1"></i>{{ $event->venue }}
                            &nbsp;•&nbsp;
                            <span style="color:#4dd9c0;font-weight:600;text-transform:capitalize;">{{ $event->status }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- In-house Interviews --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 p-3">
            <div class="fw-semibold mb-2" style="font-size:13px;color:#2d7a5f;">
                <i class="bi bi-building me-1" style="color:#4dd9c0;"></i> In-house Interviews
            </div>
            @if($todayInhouse->isEmpty())
                <div class="text-muted" style="font-size:13px;padding:8px 0;">
                    No in-house interviews today.
                </div>
            @else
                @foreach($todayInhouse as $schedule)
                <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2"
                     style="background:#f0f9f6;border:1px solid #a8e6cf;">
                    <i class="bi bi-clock" style="color:#4dd9c0;font-size:20px;"></i>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#2d7a5f;">
                            {{ $schedule->employer->company_name ?? $schedule->employer->name ?? 'N/A' }}
                        </div>
                        <div style="font-size:12px;color:#888;">
                            <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($schedule->confirmed_time)->format('h:i A') }}
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>

</div>

@endsection