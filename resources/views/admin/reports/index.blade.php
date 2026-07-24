@extends('admin.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-bar-chart-fill me-2" style="color:#4dd9c0;"></i>List of Reports
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        View reports submitted by PESO staff (LRA, SRA, Job Fair, Job Vacancy)
    </p>
</div>

<div class="card border-0 shadow-sm rounded-3 p-4">
    <div class="fw-bold mb-3" style="color:#2d7a5f;font-size:14px;">
        <i class="bi bi-people me-1" style="color:#4dd9c0;"></i> Staff-level Reports
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <a href="{{ route('admin.reports.staff', ['role' => 'lra']) }}" class="text-decoration-none">
                <div class="card border-0 p-3 text-center h-100" style="background:#f0f9f6;border-radius:12px;transition:all 0.2s;">
                    <i class="bi bi-person-lines-fill mb-2" style="font-size:28px;color:#4dd9c0;"></i>
                    <div class="fw-semibold" style="color:#2d7a5f;font-size:13px;">LRA Reports</div>
                    <div class="text-muted" style="font-size:11px;">Local applicants registered/placed/referred</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.reports.staff', ['role' => 'sra']) }}" class="text-decoration-none">
                <div class="card border-0 p-3 text-center h-100" style="background:#f0f9f6;border-radius:12px;">
                    <i class="bi bi-globe mb-2" style="font-size:28px;color:#4dd9c0;"></i>
                    <div class="fw-semibold" style="color:#2d7a5f;font-size:13px;">SRA Reports</div>
                    <div class="text-muted" style="font-size:11px;">Overseas applicants registered/placed/referred</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.reports.staff', ['role' => 'job_fair']) }}" class="text-decoration-none">
                <div class="card border-0 p-3 text-center h-100" style="background:#f0f9f6;border-radius:12px;">
                    <i class="bi bi-calendar-event-fill mb-2" style="font-size:28px;color:#4dd9c0;"></i>
                    <div class="fw-semibold" style="color:#2d7a5f;font-size:13px;">Job Fair Reports</div>
                    <div class="text-muted" style="font-size:11px;">7-tab job fair event reports</div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.reports.staffJobVacancy') }}" class="text-decoration-none">
                <div class="card border-0 p-3 text-center h-100" style="background:#f0f9f6;border-radius:12px;">
                    <i class="bi bi-briefcase-fill mb-2" style="font-size:28px;color:#4dd9c0;"></i>
                    <div class="fw-semibold" style="color:#2d7a5f;font-size:13px;">Job Vacancy Reports</div>
                    <div class="text-muted" style="font-size:11px;">Job vacancies solicited from local employers</div>
                </div>
            </a>
        </div>
    </div>
</div>

@endsection