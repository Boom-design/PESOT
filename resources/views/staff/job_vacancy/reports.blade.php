@extends($layout ?? 'staff.layouts.app')

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1" style="color:#2d7a5f;">
        <i class="bi bi-bar-chart-fill me-2" style="color:#4dd9c0;"></i>
        Job Vacancies Solicited Report
    </h5>
    <p class="mb-0" style="font-size:13px;color:#888;">
        List of approved job vacancies solicited from local employers
    </p>
</div>

@if(($layout ?? '') === 'admin.layouts.app')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'lra']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:#f0f9f6;">
                <i class="bi bi-person-lines-fill mb-2" style="font-size:26px;color:#4dd9c0;"></i>
                <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">LRA Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'sra']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:#f0f9f6;">
                <i class="bi bi-globe mb-2" style="font-size:26px;color:#4dd9c0;"></i>
                <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">SRA Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staff', ['role' => 'job_fair']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:#f0f9f6;">
                <i class="bi bi-calendar-event-fill mb-2" style="font-size:26px;color:#4dd9c0;"></i>
                <div class="fw-semibold" style="font-size:13px;color:#2d7a5f;">Job Fair Reports</div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reports.staffJobVacancy') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center h-100" style="background:linear-gradient(135deg,#90d870,#4dd9c0);">
                <i class="bi bi-briefcase-fill mb-2" style="font-size:26px;color:#fff;"></i>
                <div class="fw-semibold" style="font-size:13px;color:#fff;">Job Vacancy Reports</div>
            </div>
        </a>
    </div>
</div>
@endif

{{-- MONTH FILTER + SEARCH --}}
<form method="GET" action="{{ route($reportRouteName ?? 'staff.reports.employers') }}" class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        <label class="fw-semibold" style="font-size:12px;color:#2d7a5f;">Month:</label>
        <input type="month" name="month" value="{{ $month }}"
            class="form-control form-control-sm"
            style="border:1.5px solid #a8e6cf;border-radius:8px;font-size:12px;width:auto;"
            onchange="this.form.submit()">
    </div>
    <div class="input-group" style="max-width:260px;">
        <span class="input-group-text" style="border-color:#a8e6cf;background:#f0fdf9;">
            <i class="bi bi-search" style="color:#4dd9c0;"></i>
        </span>
        <input type="text" name="search" class="form-control"
            placeholder="Search job title or employer..."
            style="border-color:#a8e6cf;font-size:13px;outline:none;box-shadow:none;"
            autocomplete="off"
            value="{{ $search }}">
    </div>
</form>

{{-- TABLE --}}
@if($jobs->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 p-5 text-center">
        <i class="bi bi-inbox" style="font-size:48px;color:#c0e8dc;"></i>
        <div class="mt-3 fw-semibold" style="color:#2d7a5f;">No job vacancies solicited for this month</div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="p-3" style="border-bottom:1px solid #f0f9f6;">
            <div class="fw-bold text-center" style="color:#2d7a5f;font-size:14px;">JOB VACANCIES SOLICITED</div>
            <div class="text-center" style="font-size:12px;color:#888;">
                For the month of {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 12px;">No.</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 12px;">Job Title</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 12px;text-align:center;">No. of Vacancies</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 12px;text-align:center;">Age</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 12px;text-align:center;">Sex</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 12px;text-align:center;">Civil Status</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 12px;">Educational Attainment</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 12px;">Work Experience</th>
                        <th style="background:linear-gradient(90deg,#90d870,#4dd9c0);color:#fff;font-size:11px;border:none;padding:10px 12px;">Employer Name</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $i => $job)
                    <tr style="font-size:12px;">
                        <td style="padding:10px 12px;color:#888;">{{ $i + 1 }}</td>
                        <td style="padding:10px 12px;font-weight:600;color:#2d7a5f;">
                            {{ strtoupper($job->title) }}
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:#555;">
                            {{ $job->slots }}
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:#555;">
                            @if($job->age_required && $job->age_min && $job->age_max)
                                {{ $job->age_min }}-{{ $job->age_max }}
                            @else
                                None
                            @endif
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:#555;">
                            @php
                                $sexMap = ['Any' => 'M/F', 'Male' => 'M', 'Female' => 'F'];
                            @endphp
                            {{ $sexMap[$job->sex_preference] ?? ($job->sex_preference ?? 'None') }}
                        </td>
                        <td style="padding:10px 12px;text-align:center;color:#555;">
                            {{ $job->civil_status && $job->civil_status !== 'Any' ? strtoupper($job->civil_status) : 'None' }}
                        </td>
                        <td style="padding:10px 12px;color:#555;">
                            {{ $job->education_required ? strtoupper($job->education_required) : 'None' }}
                        </td>
                        <td style="padding:10px 12px;color:#555;">
                            @if($job->experience_months)
                                {{ $job->experience_months }} mo/s
                            @else
                                None
                            @endif
                        </td>
                        <td style="padding:10px 12px;color:#555;">
                            {{ strtoupper($job->company->company_name ?? 'None') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f0f9f6;">
                        <td colspan="2" style="padding:10px 12px;font-weight:700;color:#2d7a5f;text-align:right;">TOTAL</td>
                        <td style="padding:10px 12px;font-weight:700;color:#2d7a5f;text-align:center;">{{ $totalVacancies }}</td>
                        <td colspan="6"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif

@endsection